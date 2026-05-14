<template>
    <div id="main" class="min-h-screen bg-gray-100 p-6">
        <!-- ================= TOP BAR ================= -->
        <div class="flex justify-between items-center p-4 rounded-xl">
            <h1 class="text-xl font-bold">Incident Management</h1>

            <div class="flex gap-2">
                <button
                    v-if="mode === 'search' && can('incident.create')"
                    @click="setCreate"
                    class="bg-blue-600 text-white px-3 py-1 rounded"
                >
                    Create
                </button>

                <button
                    v-if="
                        (mode === 'create' ||
                            mode === 'update' ||
                            mode === 'assign') &&
                        (can('incident.update') ||
                            can('incident.create') ||
                            can('incident.update.all') ||
                            can('incident.assign'))
                    "
                    @click="saveIncident"
                    class="bg-blue-600 text-white px-3 py-1 rounded"
                >
                    Save
                </button>

                <button
                    v-if="
                        mode === 'create' ||
                        mode === 'update' ||
                        mode === 'view' ||
                        mode === 'assign'
                    "
                    @click="reset"
                    class="bg-gray-500 text-white px-3 py-1 rounded"
                >
                    Cancel
                </button>

                <button
                    v-if="mode === 'view' && can('incident.update')"
                    @click="setUpdate"
                    class="bg-yellow-500 text-white px-3 py-1 rounded"
                >
                    Update
                </button>

                <button
                    v-if="mode === 'view' && can('incident.assign')"
                    @click="setAssign"
                    class="bg-yellow-500 text-white px-3 py-1 rounded"
                >
                    Assign
                </button>

                <button
                    v-if="
                        (mode === 'view' || mode === 'update') &&
                        can('incident.delete')
                    "
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
                    <option value="rejected">Rejected</option>
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
                        v-if="
                            (mode == 'create' && can('incident.create')) ||
                            (mode == 'update' && can('incident.update.all'))
                        "
                        v-model="form.title"
                        :disabled="aiLoading"
                        placeholder="Enter incident title"
                        class="mt-1 w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                    />
                    <p
                        v-else
                        class="mt-1 w-full p-2 rounded-lg bg-gray-50 text-gray-700"
                    >
                        {{ form.title }}
                    </p>
                </div>

                <!-- CATEGORY (ONLY SHOW WHEN NOT CREATING) -->
                <div
                    v-if="mode !== 'create' || form.category != '' || aiLoading"
                    class="relative"
                >
                    <label
                        class="text-sm text-gray-600 flex items-center gap-2"
                    >
                        AI Category
                        <span class="text-xs text-gray-400"
                            >(auto-generated)</span
                        >
                    </label>

                    <input
                        v-if="mode == 'update' && can('incident.update.all')"
                        v-model="form.category"
                        placeholder="AI will classify this incident"
                        class="mt-1 w-full p-2 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400 outline-none"
                        :readonly="mode === 'view'"
                        :disabled="aiLoading"
                    />
                    <p
                        v-else
                        class="mt-1 w-full p-2 rounded-lg bg-gray-50 text-gray-700"
                    >
                        {{ form.category || "AI will classify this incident" }}
                    </p>
                    <i
                        v-if="aiLoading"
                        class="pi pi-spin pi-spinner absolute right-3 top-3 text-blue-500"
                    ></i>
                </div>

                <!-- DESCRIPTION -->
                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600">Description</label>
                    <textarea
                        v-if="
                            (mode == 'create' && can('incident.create')) ||
                            (mode == 'update' && can('incident.update.all'))
                        "
                        v-model="form.description"
                        rows="4"
                        :disabled="aiLoading"
                        placeholder="Describe the incident in detail..."
                        class="mt-1 w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                    ></textarea>
                    <p
                        v-else
                        class="mt-1 w-full p-2 rounded-lg bg-gray-50 text-gray-700"
                    >
                        {{ form.description }}
                    </p>
                </div>

                <!-- PRIORITY -->
                <div class="relative">
                    <label class="text-sm text-gray-600">Priority</label>
                    <select
                        v-if="
                            (mode == 'create' && can('incident.create')) ||
                            (mode == 'update' &&
                                (can('incident.update.all') ||
                                    can('incident.update.priority')))
                        "
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
                    <p
                        v-else
                        :class="[
                            'mt-1 w-full p-2 rounded-lg bg-gray-50 text-gray-700',
                            priorityColor(form.priority),
                        ]"
                    >
                        {{ priorityLabel(form.priority) }}
                    </p>
                    <i
                        v-if="aiLoading"
                        class="pi pi-spin pi-spinner absolute right-3 top-3 text-orange-500"
                    ></i>
                </div>

                <!-- ASSIGNED TO -->
                <div class="relative">
                    <label class="text-sm text-gray-600">Assigned To</label>
                    <select
                        v-if="
                            (mode == 'update' && can('incident.update.all')) ||
                            (mode == 'assign' &&
                                can('incident.update.assigned_to')) ||
                            (mode == 'create' && can('incident.create'))
                        "
                        v-model="form.assigned_to"
                        class="mt-1 w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                        :disabled="aiLoading"
                    >
                        <option disabled value="">Select User</option>
                        <option
                            v-for="u in users"
                            :key="u.id"
                            :value="String(u.id)"
                        >
                            {{ u.name }} ({{ u.email }})
                        </option>
                    </select>
                    <p
                        v-else
                        class="mt-1 w-full p-2 rounded-lg bg-gray-50 text-gray-700"
                    >
                        {{ userLabel(form.assigned_to) }}
                    </p>
                </div>

                <!-- SOURCE -->
                <div>
                    <label class="text-sm text-gray-600">Source</label>
                    <select
                        v-if="
                            (mode == 'create' && can('incident.create')) ||
                            (mode == 'update' &&
                                (can('incident.update.all') ||
                                    can('incident.update.source')))
                        "
                        v-model="form.source"
                        class="mt-1 w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                    >
                        <option value="manual">Manual</option>
                        <option value="email">Email</option>
                        <option value="telegram">Telegram</option>
                        <option value="teams">Teams</option>
                        <option value="rpa">RPA</option>
                    </select>
                    <p
                        v-else
                        class="mt-1 w-full p-2 rounded-lg bg-gray-50 text-gray-700"
                    >
                        {{ sourceLabel(form.source) }}
                    </p>
                </div>

                <!-- STATUS -->
                <div v-if="mode != 'create'">
                    <label class="text-sm text-gray-600">Status</label>
                    <select
                        v-if="
                            (mode == 'update' &&
                                (can('incident.update.all') ||
                                    can('incident.update.status'))) ||
                            (mode == 'assign' && can('incident.change_status'))
                        "
                        v-model="form.status"
                        :class="[
                            'mt-1 w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none transition',
                            statusColor(form.status),
                        ]"
                    >
                        <option value="draft">Draft</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="published">Published</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <p
                        v-else
                        :class="[
                            'mt-1 w-full p-2 rounded-lg bg-gray-50 text-gray-700',
                            statusColor(form.status),
                        ]"
                    >
                        {{ statusLabel(form.status) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 📎 ATTACHMENT UPLOAD AREA -->
        <div
            class="border-2 mt-4 border-dashed rounded-xl p-6 text-center cursor-pointer hover:bg-gray-50 transition"
            @click="$refs.fileInput.click()"
            @dragover.prevent
            @drop.prevent="handleDrop"
            v-if="
                mode !== 'search' &&
                mode !== 'view' &&
                (can('incident.create') || can('incident.update.all'))
            "
        >
            <input
                type="file"
                ref="fileInput"
                class="hidden"
                multiple
                accept=".png,.jpg,.jpeg,.gif,.pdf,.webp,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                @change="handleFileChange"
            />

            <i class="pi pi-upload text-3xl text-gray-400"></i>

            <p class="text-sm text-gray-500 mt-2">
                Drag & drop files here or click to upload
            </p>

            <p class="text-xs text-gray-400">Supports images, PDF, DOCX</p>
        </div>

        <!-- 📄 FILE PREVIEW -->
        <div v-if="attachments.length > 0" class="mt-3 flex flex-wrap gap-2">
            <div
                v-for="(file, index) in attachments"
                :key="index"
                class="relative px-3 py-1 bg-blue-100 text-blue-600 rounded-xl text-xs"
            >
                <!-- REMOVE BUTTON -->
                <button
                    v-if="
                        (mode === 'update' && can('incident.update.all')) ||
                        (mode === 'create' && can('incident.create'))
                    "
                    @click.stop="removeAttachment(index)"
                    class="absolute -top-2 -right-2 bg-red-500 text-white w-5 h-5 rounded-full text-[10px] flex items-center justify-center hover:bg-red-600"
                >
                    ✕
                </button>

                <!-- PREVIEW -->
                <div
                    class="inline-flex flex-col w-fit cursor-pointer"
                    @click="openAttachment(file)"
                >
                    <img
                        v-if="file.preview"
                        :src="file.preview"
                        class="w-20 h-20 object-cover rounded m-auto"
                    />

                    <span class="font-medium break-words text-center">
                        {{ file.name }}
                    </span>

                    <span class="text-xs text-gray-400 break-words text-center">
                        {{ file.path }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ================= AI SECTION ================= -->
        <div
            v-if="mode !== 'search' && mode !== 'create'"
            class="bg-white p-6 mt-6 rounded-2xl shadow-sm border border-gray-100"
        >
            <!-- HEADER -->
            <div class="flex items-center justify-between mb-4">
                <h2
                    class="text-lg font-semibold text-gray-800 flex items-center gap-2"
                >
                    <i class="pi pi-sparkles text-blue-500"></i>
                    AI Insights
                </h2>

                <!-- LOADING -->
                <div
                    v-if="aiLoading"
                    class="flex items-center gap-2 text-sm text-gray-500"
                >
                    <i class="pi pi-spin pi-spinner text-orange-500"></i>
                    Processing...
                </div>
            </div>

            <!-- CONTENT -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- CATEGORY -->
                <div class="p-4 rounded-xl bg-gray-50 border">
                    <p class="text-xs text-gray-500 mb-1">Category</p>
                    <p class="font-semibold text-gray-800">
                        {{ form.category || "—" }}
                    </p>
                </div>

                <!-- PRIORITY -->
                <div
                    class="p-4 rounded-xl border"
                    :class="priorityColor(form.priority)"
                >
                    <p class="text-xs mb-1">Priority</p>
                    <p class="font-semibold capitalize">
                        {{ form.priority || "—" }}
                    </p>
                </div>

                <!-- SUMMARY -->
                <div class="col-span-2 p-4 rounded-xl bg-gray-50 border">
                    <p
                        class="text-xs text-gray-500 mb-1 flex items-center gap-1"
                    >
                        <i class="pi pi-align-left"></i>
                        Summary
                    </p>
                    <p class="text-gray-700">
                        {{ form.ai_summary || "No summary yet" }}
                    </p>
                </div>

                <!-- TAGS -->
                <div class="col-span-2 p-4 rounded-xl bg-gray-50 border">
                    <p
                        class="text-xs text-gray-500 mb-2 flex items-center gap-1"
                    >
                        <i class="pi pi-tags"></i>
                        Tags
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="tag in form.ai_tags"
                            :key="tag"
                            class="px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded-full"
                        >
                            {{ tag }}
                        </span>

                        <span
                            v-if="!form.ai_tags || form.ai_tags.length === 0"
                            class="text-gray-400 text-sm"
                        >
                            No tags generated
                        </span>
                    </div>
                </div>

                <!-- SUGGESTIONS -->
                <div class="col-span-2 p-4 rounded-xl bg-gray-50 border">
                    <p
                        class="text-xs text-gray-500 mb-1 flex items-center gap-1"
                    >
                        <i class="pi pi-lightbulb"></i>
                        Suggestions
                    </p>
                    <p class="text-gray-700">
                        {{ form.ai_suggestions || "No suggestions yet" }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ================= NOTES ================= -->
        <div
            v-if="mode === 'assign' && can('incident.assign')"
            class="mt-4 rounded-xl shadow bg-white p-4"
        >
            <h2 class="font-bold mb-3">Add Note</h2>

            <div class="flex flex-col gap-3">
                <textarea
                    v-model="form.note"
                    rows="3"
                    placeholder="Write a note about this assignment..."
                    class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                ></textarea>
            </div>
        </div>

        <!-- ================= HISTORY ================= -->
        <div
            v-if="mode === 'view' && can('incident.view.history')"
            class="mt-4 rounded-xl shadow bg-white p-4"
        >
            <h2 class="font-bold mb-3">Status History</h2>

            <DataTable
                :value="histories"
                paginator
                :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]"
                scrollable
                scrollHeight="400px"
                responsiveLayout="scroll"
                class="text-sm"
                stripedRows
                rowHover
            >
                <Column field="status" header="Status" />
                <Column field="note" header="Note" />
                <Column header="Created At">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.created_at) }}
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- ================= RPA LOGS ================= -->
        <div
            v-if="mode === 'view' && can('incident.view.rpa_logs')"
            class="mt-4 rounded-xl shadow bg-white p-4"
        >
            <h2 class="font-bold mb-3">RPA Logs</h2>

            <DataTable
                :value="rpaLogs"
                paginator
                :rows="10"
                :rowsPerPageOptions="[5, 10, 20, 50]"
                scrollable
                scrollHeight="400px"
                responsiveLayout="scroll"
                class="text-sm"
            >
                <Column field="source_type" header="Source" />
                <Column field="action" header="Action" />
                <Column field="status" header="Status" />
                <Column field="message" header="Message" />
            </DataTable>
        </div>

        <!-- ================= INCIDENT TABLE ================= -->
        <div
            class="bg-white p-4 mt-4 rounded-xl shadow"
            v-if="mode === 'search'"
        >
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
                                v-if="can('incident.update')"
                                icon="pi pi-pencil"
                                class="p-button-sm p-button-warning"
                                v-tooltip.top="'Edit'"
                                tooltip-options="{ showDelay: 500 }"
                                @click="editIncident(slotProps.data)"
                            />

                            <Button
                                v-if="can('incident.assign')"
                                icon="pi pi-user-edit"
                                class="p-button-sm p-button-success"
                                v-tooltip.top="'Assign'"
                                tooltip-options="{ showDelay: 500 }"
                                @click="setAssign(slotProps.data)"
                            />

                            <Button
                                v-if="can('incident.delete')"
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
            <div class="bg-white p-4 rounded-xl w-full">
                <h2 class="font-bold mb-3">Attachment Preview</h2>

                <!-- IMAGE -->
                <img
                    v-if="selectedAttachment?.preview"
                    :src="selectedAttachment.preview"
                    class="w-full max-h-100 object-contain rounded"
                />

                <!-- NON-IMAGE -->
                <div v-else class="text-center text-gray-500">
                    <p>{{ selectedAttachment?.name }}</p>
                    <a
                        :href="`/storage/${selectedAttachment?.path}`"
                        target="_blank"
                        class="text-blue-500 underline"
                    >
                        Open File
                    </a>
                </div>

                <button
                    @click="showAttachmentModal = false"
                    class="mt-4 bg-gray-500 text-white px-3 py-1 rounded"
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

const {
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
} = useIncident();

onMounted(async () => {
    await loadIncidents();
    await loadUsers();
});
</script>
