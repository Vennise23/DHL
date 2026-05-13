import { ref, computed, onBeforeUnmount, nextTick } from "vue";
import axios from "axios";
import { parse } from "filepond";
import { useAuth } from "@/composables/useAuth";

const auth = useAuth();

const { isAdmin, isReviewer, isStaff, can, initAuth } = auth;

let timeout;

const aiLoading = ref(false);

const mode = ref("search");

const incidents = ref([]);
const histories = ref([]);
const rpaLogs = ref([]);
const attachments = ref([]);
const users = ref([]);

const showAttachmentModal = ref(false);
const selectedAttachment = ref(null);

const form = ref({});
const search = ref({
    title: "",
    category: "",
    status: "",
    priority: "",
});

const loadIncidents = async () => {
    // 🔥 ensure auth loaded first
    await initAuth();

    let url = "/api/incidents";

    if (isStaff()) {
        url = "/api/assigned-incidents";
    }
    const res = await axios.get(url);
    incidents.value = res.data;
};

const filteredIncidents = computed(() => {
    return incidents.value.filter((i) => {
        return (
            (!search.value.title ||
                i.title
                    ?.toLowerCase()
                    .includes(search.value.title.toLowerCase())) &&
            (!search.value.status ||
                i.status?.toLowerCase() ===
                    search.value.status.toLowerCase()) &&
            (!search.value.priority ||
                i.priority?.toLowerCase() ===
                    search.value.priority.toLowerCase()) &&
            (!search.value.category ||
                i.category
                    ?.toLowerCase()
                    .includes(search.value.category.toLowerCase()))
        );
    });
});

const setCreate = () => {
    mode.value = "create";
    form.value = {
        source: "manual",
        status: "draft",
        priority: "low",
        title: "",
        description: "",
        category: "",
    };
};

const searchIncidents = () => {
    mode.value = "search";
};

const saveIncident = async () => {
    try {
        const formData = new FormData();

        // normal fields
        formData.append("title", form.value.title);
        formData.append("description", form.value.description);
        formData.append("priority", form.value.priority);
        formData.append("category", form.value.category);
        formData.append("source", form.value.source);
        formData.append("status", form.value.status);
        formData.append("assigned_to", form.value.assigned_to || "");
        formData.append("note", form.value.note || "");

        // multiple files
        attachments.value.forEach((a) => {
            // old attachment
            if (a.id) {
                formData.append("existing_attachments[]", a.id);
            }

            // new attachment
            if (a.file) {
                formData.append("attachments[]", a.file);
            }
        });

        if (form.value.id) {
            formData.append("_method", "PUT");
            await axios.post(`/api/incidents/${form.value.id}`, formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });
        } else {
            const res = await axios.post("/api/incidents", formData, {
                headers: { "Content-Type": "multipart/form-data" },
            });
        }
    } catch (err) {
        console.error("Save failed:", err);
    } finally {
        reset();
        loadIncidents();
        loadUsers();
    }
};

const reset = () => {
    mode.value = "search";
    aiLoading.value = false;

    incidents.value = [];
    histories.value = [];
    rpaLogs.value = [];
    attachments.value = [];
    //users.value = [];

    showAttachmentModal.value = false;
    selectedAttachment.value = null;

    search.value = {
        title: "",
        category: "",
        status: "",
        priority: "",
    };

    form.value = {};
    loadIncidents();
};

const viewIncident = async (i) => {
    reset();
    mode.value = "view";
    getIncidentById(i);
};

const editIncident = (i) => {
    reset();
    mode.value = "update";
    getIncidentById(i);
};

const setUpdate = () => {
    mode.value = "update";
};

const setAssign = (i) => {
    mode.value = "assign";
    getIncidentById(i);
};

const getIncidentById = async (i) => {
    try {
        const res = await axios.get(`/api/incidents/${i.id}`);

        const data = res.data;

        // main form
        form.value = {
            ...data,
            ai_summary: data.ai_processing?.ai_summary,
            ai_tags: safeJsonParse(data.ai_processing?.ai_tags),
            ai_suggestions: data.ai_processing?.ai_suggestions,
        };

        // relations
        histories.value = data.histories || [];
        rpaLogs.value = data.rpa_logs || [];
        attachments.value = (res.data.attachments || []).map((a) => ({
            id: a.id,
            name: a.file_name,
            path: a.file_path,
            preview: /\.(jpg|jpeg|png|gif|webp)$/i.test(a.file_path)
                ? `/storage/${a.file_path}`
                : null,
        }));
    } catch (err) {
        console.error("View failed:", err);
    } finally {
        await nextTick();

        requestAnimationFrame(() => {
            const el = document.getElementById("app-scroll");

            if (el) {
                el.scrollTo({
                    top: 0,
                    behavior: "smooth",
                });
            }
        });
    }
};

const deleteIncident = async () => {
    if (!confirm("Delete this incident?")) return;

    await axios.delete(`/api/incidents/${form.value.id}`);
    reset();
    loadIncidents();
};

const confirmDelete = async (id) => {
    if (!confirm("Delete?")) return;

    await axios.delete(`/api/incidents/${id}`);
    loadIncidents();
};

const statusColor = (s) => {
    if (s === "draft") return "bg-yellow-200 border-yellow-500 text-yellow-800";
    if (s === "reviewed") return "bg-blue-200 border-blue-500 text-blue-800";
    if (s === "published")
        return "bg-green-200 border-green-500 text-green-800";
    if (s === "rejected")
        return "bg-red-200 border-red-500 text-red-800";
};

const priorityColor = (value) => {
    switch (value) {
        case "low":
            return "border-green-500 text-green-700 bg-green-100";
        case "medium":
            return "border-orange-400 text-orange-600 bg-orange-100";
        case "high":
            return "border-red-500 text-red-600 bg-red-100";
        default:
            return "border-gray-700 text-gray-700 bg-white";
    }
};

const runAI = () => {
    clearTimeout(timeout);

    if (!form.value.title || !form.value.description) return;

    aiLoading.value = true;

    timeout = setTimeout(async () => {
        try {
            const formData = new FormData();
            formData.append("title", form.value.title || "");
            formData.append("description", form.value.description || "");

            // 📎 append images/files
            attachments.value.forEach((file, i) => {
                formData.append(`files[${i}]`, file);
            });
            const res = await axios.post("/api/ai/process", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });

            form.value.category = res.data.category;
            form.value.priority = res.data.priority;
            form.value.ai_summary = res.data.summary;
            form.value.ai_tags = res.data.tags || [];
            form.value.ai_suggestions = res.data.suggestions;
        } catch (err) {
            console.error("AI error:", err);
        } finally {
            aiLoading.value = false;
        }
    }, 1200);
};

const safeJsonParse = (value) => {
    try {
        return JSON.parse(value);
    } catch (e) {
        return typeof value === "string"
            ? value.split(",").map((v) => v.trim()) // fallback for "a, b, c"
            : [];
    }
};

const handleFileChange = (event) => {
    const files = Array.from(event.target.files);

    files.forEach((file) => {
        attachments.value.push({
            file,
            name: file.name,
            type: file.type,
            path: file.webkitRelativePath || null,
            preview: file.type.startsWith("image/")
                ? URL.createObjectURL(file)
                : null,
        });
    });
};

const handleDrop = (event) => {
    const files = Array.from(event.dataTransfer.files);

    files.forEach((file) => {
        attachments.value.push({
            file,
            name: file.name,
            type: file.type,
            path: file.webkitRelativePath || null,
            preview: file.type.startsWith("image/")
                ? URL.createObjectURL(file)
                : null,
        });
    });
};

const processFiles = (files) => {
    files.forEach((file) => {
        attachments.value.push({
            file, // real File object (IMPORTANT)
            name: file.webkitRelativePath || file.name,
            type: file.type,
            size: file.size,
        });
    });
};

const priorityLabel = (value) => {
    const map = {
        low: "🟢 Low",
        medium: "🟡 Medium",
        high: "🟠 High",
    };
    return map[value] || "Not set";
};

const sourceLabel = (value) => {
    const map = {
        manual: "Manual",
        email: "Email",
        telegram: "Telegram",
        teams: "Teams",
        rpa: "RPA",
    };
    return map[value] || "Not set";
};

const statusLabel = (value) => {
    const map = {
        draft: "Draft",
        reviewed: "Reviewed",
        published: "Published",
        rejected: "Rejected",
    };
    return map[value] || "Not set";
};

const openAttachment = (file) => {
    selectedAttachment.value = file;
    showAttachmentModal.value = true;
};

const loadUsers = async () => {
    await initAuth();

    let url;

    if (isAdmin() || isReviewer()) {
        url = "/api/users";
    } else {
        url = "/api/me";
    }

    try{
    const res = await axios.get(url);

    users.value = Array.isArray(res.data)
        ? res.data
        : [res.data];
    }catch(err){
        console.error("Failed to load users", err);
    }
};

const userLabel = (id) => {
    const user = users.value.find((u) => u.id === id);

    return user ? user.name : "Not assigned";
};

const removeAttachment = (index) => {
    const file = attachments.value[index];

    // revoke preview URL
    if (file.preview?.startsWith("blob:")) {
        URL.revokeObjectURL(file.preview);
    }

    attachments.value.splice(index, 1);
};

const formatDate = (dateStr) => {
    if (!dateStr) return "-";

    const date = new Date(dateStr);

    return date.toLocaleString("en-MY", {
        year: "numeric",
        month: "short",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    });
};

export function useIncident() {
    onBeforeUnmount(() => {
        attachments.value.forEach((f) => {
            if (f.preview) URL.revokeObjectURL(f.preview);
        });
    });

    return {
        // state
        isAdmin,
        isReviewer,
        isStaff,
        incidents,
        histories,
        rpaLogs,
        attachments,
        users,
        showAttachmentModal,
        selectedAttachment,
        form,
        search,
        mode,
        aiLoading,

        // computed
        filteredIncidents,

        // actions
        loadIncidents,
        setCreate,
        searchIncidents,
        saveIncident,
        reset,
        viewIncident,
        editIncident,
        deleteIncident,
        confirmDelete,
        handleFileChange,
        handleDrop,
        openAttachment,
        removeAttachment,
        setUpdate,
        setAssign,

        // helpers
        statusColor,
        priorityColor,
        priorityLabel,
        sourceLabel,
        statusLabel,
        userLabel,
        formatDate,

        // relations
        loadUsers,
        can,

        // AI
        runAI,
    };
}
