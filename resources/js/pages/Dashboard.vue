<template>
    <div v-if="isAdmin()">
        <h1>Admin Dashboard</h1>
    </div>

    <div v-if="isReviewer()">
        <h1>Reviewer Queue</h1>
    </div>

    <div v-if="isStaff()">
        <h1>My Assigned Incidents</h1>
    </div>
</template>
<script setup>
import { onMounted,ref } from "vue";
import { useDashboard } from "@/composables/useDashboard";
import { useAuth } from "@/composables/useAuth";

const { stats, incidents, rpaLogs, loadDashboard, formatDate } =
    useDashboard();

const { isAdmin, isReviewer, isStaff, can } = useAuth();


onMounted(() => {
    loadDashboard();
});
</script>
