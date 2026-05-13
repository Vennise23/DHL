import { computed,ref } from "vue";
import { useAuth } from "@/composables/useAuth";

const { isAdmin, isReviewer, isStaff } = useAuth();

const canViewDashboard = computed(() => isAdmin());
const canViewRpaLogs = computed(() => isAdmin());
const canViewAssignedOnly = computed(() => isStaff());

const incidents = ref([]);
const rpaLogs = ref([]);

const stats = ref({
  total: 0,
  open: 0,
  resolved: 0,
  critical: 0,
});

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

const loadDashboard = async () => {
  const res = await axios.get("/api/dashboard");

  incidents.value = res.data.recentIncidents;
  rpaLogs.value = res.data.rpaLogs;

  stats.value = res.data.stats;
};

export function useDashboard() {
    return {
        // state
        canViewDashboard,
        canViewRpaLogs,
        canViewAssignedOnly,

        incidents,
        rpaLogs,
        stats,

        // utils
        formatDate,
        loadDashboard,
    };
}