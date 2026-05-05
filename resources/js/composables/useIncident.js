import { ref, computed } from "vue";
import axios from "axios";

let timeout;

const aiLoading = ref(false);

const mode = ref("search");

const incidents = ref([]);
const histories = ref([]);
const rpaLogs = ref([]);
const attachments = ref([]);

const showAttachmentModal = ref(false);

const form = ref({

});
const search = ref({
    title: "",
    category: "",
    status: "",
    priority: "",
});

const loadIncidents = async () => {
    const res = await axios.get("/api/incidents");
    incidents.value = res.data;
};

const filteredIncidents = computed(() => {
    return incidents.value.filter((i) => {
        return (
            (!search.value.title || i.title.includes(search.value.title)) &&
            (!search.value.status || i.status === search.value.status)
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
        category: ""
    };
};

const searchIncidents = () => {
    mode.value = "search";
};

const saveIncident = async () => {
    if (mode.value === "create") {
        await axios.post("/api/incidents", form.value);
    } else {
        await axios.put(`/api/incidents/${form.value.id}`, form.value);
    }

    reset();
    loadIncidents();
};

const reset = () => {
    mode.value = "search";
    form.value = {};
};

const viewIncident = async (i) => {
    mode.value = "view";

    const res = await axios.get(`/api/incidents/${i.id}`);
    form.value = res.data;

    histories.value = res.data.histories || [];
    rpaLogs.value = res.data.rpa_logs || [];
};

const editIncident = (i) => {
    mode.value = "update";
    form.value = i;
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

const autoCategorize = () => {
    clearTimeout(timeout);

    if (!form.value.title || !form.value.description) return;

    timeout = setTimeout(async () => {
        try {
          aiLoading.value = true;

            const res = await axios.post("/api/ai/categorize", {
                title: form.value.title,
                description: form.value.description,
            });

            form.value.category = res.data.category;
            form.value.priority = res.data.priority;

        } catch (err) {
            console.error("AI error:", err);
        } finally {
          aiLoading.value = false;
        }
    }, 1200); // ⬅️ increase delay (important)
};

export function useIncident() {
    return {
        // state
        incidents,
        histories,
        rpaLogs,
        attachments,
        showAttachmentModal,
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

        // helpers
        statusColor,
        priorityColor,

        // AI
        autoCategorize,
    };
}
