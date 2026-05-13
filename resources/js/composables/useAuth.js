import { ref } from "vue";
import axios from "axios";

const user = ref(null);
const loading = ref(false);
const initialized = ref(false);

const loadUser = async () => {
    loading.value = true;

    try {
        const res = await axios.get("/api/me");
        user.value = res.data;
        initialized.value = true;
        console.log("User loaded:", user.value);
    } catch (err) {
        console.error("Failed to load user", err);
    } finally {
        loading.value = false;
    }
};

const initAuth = async () => {
    if (!initialized.value) {
        await loadUser();
    }
};

const role = () => user.value?.role || "guest";

// roles
const isAdmin = () => role() === "admin";
const isReviewer = () => role() === "reviewer";
const isStaff = () => role() === "staff";
const isRpa = () => role() === "rpa";
// permissions (OPTIONAL fallback)
const rolePermissions = {
    admin: [
        "incident.create",
        "incident.update",
        "incident.update.all",
        "incident.delete",
        "incident.view.all",
        "incident.change_status",
        "incident.view.rpa_logs",
        "incident.view.history",
    ],

    reviewer: [
        "incident.view.all",
        "incident.update.priority",
        "incident.update.assigned_to",
        "incident.change_status",
        "incident.assign",
    ],

    staff: [
        "incident.create",
        "incident.view.own",
        "incident.update.all",
        "incident.update",
    ],

    rpa: [
        "incident.create",
        "incident.view.all",
        "incident.update.all",
        "incident.delete",
        "incident.view.rpa_logs",
    ]
};

const can = (action) => {
    const r = user.value?.role;
    return rolePermissions[r]?.includes(action) ?? false;
};

const resetAuth = () => {
    user.value = null;
    loading.value = false;
    initialized.value = false;
};

export function useAuth() {
    return {
        user,
        loadUser,
        initAuth,   
        resetAuth,
        role,
        isAdmin,
        isReviewer,
        isStaff,
        can,
    };
}