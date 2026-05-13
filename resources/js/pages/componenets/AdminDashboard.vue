<template>
    <!-- Admin Dashboard -->
    <div class="p-6 space-y-6 bg-gray-100 min-h-screen">
        <!-- HEADER -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
        </div>

        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl shadow">
                <p class="text-gray-500 text-sm">Total Incidents</p>
                <p class="text-2xl font-bold">{{ stats.total }}</p>
            </div>

            <div class="bg-white p-4 rounded-xl shadow">
                <p class="text-gray-500 text-sm">Open</p>
                <p class="text-2xl font-bold text-yellow-500">
                    {{ stats.open }}
                </p>
            </div>

            <div class="bg-white p-4 rounded-xl shadow">
                <p class="text-gray-500 text-sm">Resolved</p>
                <p class="text-2xl font-bold text-green-500">
                    {{ stats.resolved }}
                </p>
            </div>

            <div class="bg-white p-4 rounded-xl shadow">
                <p class="text-gray-500 text-sm">Critical</p>
                <p class="text-2xl font-bold text-red-500">
                    {{ stats.critical }}
                </p>
            </div>
        </div>

        <!-- TABLE + RECENT INCIDENTS -->
        <div class="bg-white p-4 rounded-xl shadow">
            <h2 class="font-bold mb-3">Recent Incidents</h2>

            <DataTable :value="incidents" paginator :rows="5">
                <Column field="title" header="Title" />
                <Column field="category" header="Category" />
                <Column field="priority" header="Priority" />
                <Column field="status" header="Status" />
                <Column header="Created At">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.created_at) }}
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- RPA LOGS -->
        <div class="bg-white p-4 rounded-xl shadow">
            <h2 class="font-bold mb-3">RPA Execution Logs</h2>

            <DataTable :value="rpaLogs" paginator :rows="5">
                <Column field="source_type" header="Source" />
                <Column field="created_count" header="Created" />
                <Column field="duplicate_count" header="Duplicate" />
                <Column field="failed_count" header="Failed" />
            </DataTable>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useDashboard } from "@/composables/useDashboard";

const { stats, incidents, rpaLogs, loadDashboard, formatDate } = useDashboard();

onMounted(() => {
    loadDashboard();
});
</script>
