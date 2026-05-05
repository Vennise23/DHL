<template>
    <div class="min-h-screen bg-gray-100 p-6">
        <!-- ================= TOP BAR ================= -->
        <div class="flex justify-between items-center p-4 rounded-xl">
            <h1 class="text-xl font-bold">Incident Management</h1>

            <div class="flex gap-2">
                <button
                    v-if="mode === 'search'"
                    @click="setCreate"
                    class="bg-blue-600 text-white px-3 py-1 rounded"
                >
                    Create
                </button>

                <button
                    v-if="mode === 'search'"
                    @click="searchIncidents"
                    class="bg-green-600 text-white px-3 py-1 rounded"
                >
                    Search
                </button>

                <button
                    v-if="mode === 'create' || mode === 'update'"
                    @click="saveIncident"
                    class="bg-blue-600 text-white px-3 py-1 rounded"
                >
                    Save
                </button>

                <button
                    v-if="mode === 'create' || mode === 'update'"
                    @click="reset"
                    class="bg-gray-500 text-white px-3 py-1 rounded"
                >
                    Cancel
                </button>

                <button
                    v-if="mode === 'view'"
                    @click="setUpdate"
                    class="bg-yellow-500 text-white px-3 py-1 rounded"
                >
                    Update
                </button>

                <button
                    v-if="mode === 'view' || mode === 'update'"
                    @click="deleteIncident"
                    class="bg-red-600 text-white px-3 py-1 rounded"
                >
                    Delete
                </button>
            </div>
        </div>

        <!-- ================= SEARCH FORM ================= -->
        <div
            v-if="mode === 'search'"
            class="bg-white p-6 rounded-2xl shadow-lg mb-6 border border-gray-100"
        >
            <!-- FORM GRID -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- TITLE -->
                <div class="relative">
                    <i
                        class="pi pi-search absolute left-3 top-3 text-gray-400"
                    ></i>
                    <input
                        v-model="search.title"
                        placeholder="Title"
                        class="w-full pl-10 p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                    />
                </div>

                <!-- CATEGORY -->
                <div class="relative">
                    <i
                        class="pi pi-tag absolute left-3 top-3 text-gray-400"
                    ></i>
                    <input
                        v-model="search.category"
                        placeholder="Category"
                        class="w-full pl-10 p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                    />
                </div>

                <!-- STATUS -->
                <select
                    v-model="search.status"
                    class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                >
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="published">Published</option>
                </select>

                <!-- PRIORITY -->
                <select
                    v-model="search.priority"
                    class="p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                >
                    <option value="">All Priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
        </div>

        <!-- ================= INCIDENT FORM ================= -->
        <div
            v-if="mode !== 'search'"
            class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100"
        >
            <!-- HEADER -->
            <div class="mb-5">
                <h2 class="text-lg font-semibold text-gray-700">
                    📝 Incident Details
                </h2>
            </div>

            <!-- GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- TITLE -->
                <div>
                    <label class="text-sm text-gray-600">Title</label>
                    <input
                        v-model="form.title"
                        @blur="autoCategorize"
                        placeholder="Enter incident title"
                        class="mt-1 w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                    />
                </div>

                <!-- CATEGORY (ONLY SHOW WHEN NOT CREATING) -->
                <div v-if="mode !== 'create' || form.category != '' || aiLoading" class="relative">
                    <label
                        class="text-sm text-gray-600 flex items-center gap-2"
                    >
                        AI Category
                        <span class="text-xs text-gray-400"
                            >(auto-generated)</span
                        >
                    </label>

                    <input
                        v-model="form.category"
                        placeholder="AI will classify this incident"
                        class="mt-1 w-full p-2 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400 outline-none"
                        :readonly="mode === 'view'"
                        :disabled="aiLoading"
                    />
                    <i
                        v-if="aiLoading"
                        class="pi pi-spin pi-spinner absolute right-3 top-3 text-blue-500"
                    ></i>
                </div>

                <!-- DESCRIPTION -->
                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        @blur="autoCategorize"
                        placeholder="Describe the incident in detail..."
                        class="mt-1 w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                    ></textarea>
                </div>

                <!-- PRIORITY -->
                <div class="relative">
                    <label class="text-sm text-gray-600">Priority</label>
                    <select
                        v-model="form.priority"
                        :class="[
                            'mt-1 w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none transition',
                            priorityColor(form.priority),
                        ]"
                        :disabled="aiLoading"
                    >
                        <option disabled value="">Select priority</option>
                        <option value="low">🟢 Low</option>
                        <option value="medium">🟡 Medium</option>
                        <option value="high">🟠 High</option>
                    </select>
                    <i
                        v-if="aiLoading"
                        class="pi pi-spin pi-spinner absolute right-3 top-3 text-orange-500"
                    ></i>
                </div>

                <!-- SOURCE -->
                <div>
                    <label class="text-sm text-gray-600">Source</label>
                    <select
                        v-model="form.source"
                        class="mt-1 w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                    >
                        <option value="manual">Manual</option>
                        <option value="email">Email</option>
                        <option value="telegram">Telegram</option>
                        <option value="teams">Teams</option>
                        <option value="rpa">RPA</option>
                    </select>
                </div>

                <!-- STATUS -->
                <div v-if="mode != 'create'">
                    <label class="text-sm text-gray-600">Status</label>
                    <select
                        v-model="form.status"
                        :class="[
                            'mt-1 w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none transition',
                            statusColor(form.status),
                        ]"
                    >
                        <option value="draft">Draft</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ================= AI SECTION ================= -->
        <div
            v-if="mode !== 'search'"
            class="bg-white p-4 mt-4 rounded-xl shadow"
        >
            <h2 class="font-bold mb-2">AI Processing</h2>

            <p><b>Summary:</b> {{ form.ai_summary || "-" }}</p>
            <p><b>Tags:</b> {{ form.ai_tags || "-" }}</p>
            <p><b>Suggestions:</b> {{ form.ai_suggestions || "-" }}</p>
        </div>

        <!-- ================= HISTORY ================= -->
        <div
            v-if="mode !== 'search'"
            class="bg-white p-4 mt-4 rounded-xl shadow"
        >
            <h2 class="font-bold mb-2">Status History</h2>

            <table class="w-full text-sm">
                <tr v-for="h in histories" :key="h.id" class="border-b">
                    <td>{{ h.status }}</td>
                    <td>{{ h.note }}</td>
                    <td>{{ h.created_at }}</td>
                </tr>
            </table>
        </div>

        <!-- ================= RPA LOGS ================= -->
        <div
            v-if="mode !== 'search'"
            class="bg-white p-4 mt-4 mb-4 rounded-xl shadow"
        >
            <h2 class="font-bold mb-2">RPA Logs</h2>

            <table class="w-full text-sm">
                <tr v-for="r in rpaLogs" :key="r.id" class="border-b">
                    <td>{{ r.source_type }}</td>
                    <td>{{ r.created_count }}</td>
                    <td>{{ r.duplicate_count }}</td>
                    <td>{{ r.failed_count }}</td>
                </tr>
            </table>
        </div>

        <!-- ================= INCIDENT TABLE ================= -->
        <div class="bg-white p-4 rounded-xl shadow">
            <DataTable
                :value="filteredIncidents"
                paginator
                :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]"
                scrollable
                scrollHeight="400px"
                responsiveLayout="scroll"
                class="text-sm"
            >
                <Column field="title" header="Title"></Column>
                <Column field="priority" header="Priority" class="text-center">
                    <template #body="slotProps">
                        <span
                            class="px-2 py-1 rounded text-xs text-center block"
                            :class="{
                                'bg-green-200 text-green-800':
                                    slotProps.data.priority === 'low',
                                'bg-orange-200 text-orange-800':
                                    slotProps.data.priority === 'medium',
                                'bg-red-200 text-red-800':
                                    slotProps.data.priority === 'high',
                            }"
                        >
                            {{ slotProps.data.priority }}
                        </span>
                    </template>
                </Column>
                <Column field="category" header="Category"></Column>

                <Column header="Status" class="text-center">
                    <template #body="slotProps">
                        <span
                            class="px-2 py-1 rounded text-xs text-center block"
                            :class="statusColor(slotProps.data.status)"
                        >
                            {{ slotProps.data.status }}
                        </span>
                    </template>
                </Column>

                <Column header="Action" headerClass="text-center">
                    <template #body="slotProps">
                        <div class="flex justify-center gap-2">
                            <Button
                                icon="pi pi-search"
                                class="p-button-sm p-button-info"
                                v-tooltip.top="'View'"
                                tooltip-options="{ showDelay: 500 }"
                                @click="viewIncident(slotProps.data)"
                            />

                            <Button
                                icon="pi pi-pencil"
                                class="p-button-sm p-button-warning"
                                v-tooltip.top="'Edit'"
                                tooltip-options="{ showDelay: 500 }"
                                @click="editIncident(slotProps.data)"
                            />

                            <Button
                                icon="pi pi-trash"
                                v-tooltip.top="'Delete'"
                                tooltip-options="{ showDelay: 500 }"
                                class="p-button-sm p-button-danger"
                                @click="confirmDelete(slotProps.data.id)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- ================= ATTACHMENT MODAL ================= -->
        <div
            v-if="showAttachmentModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
        >
            <div class="bg-white p-4 rounded-xl w-96">
                <h2 class="font-bold mb-2">Attachments</h2>

                <div v-for="a in attachments" :key="a.id" class="border-b p-2">
                    {{ a.file_name }}
                </div>

                <button
                    @click="showAttachmentModal = false"
                    class="mt-3 bg-gray-500 text-white px-3 py-1 rounded"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useIncident } from "@/composables/useIncident";

onMounted(() => {
    loadIncidents();
});

const {
    incidents,
    histories,
    rpaLogs,
    attachments,
    showAttachmentModal,
    form,
    search,
    mode,
    filteredIncidents,
    loadIncidents,
    setCreate,
    searchIncidents,
    saveIncident,
    reset,
    viewIncident,
    editIncident,
    deleteIncident,
    confirmDelete,
    statusColor,
    priorityColor,
    autoCategorize,
} = useIncident();

onMounted(() => {
    loadIncidents();
});
</script>
