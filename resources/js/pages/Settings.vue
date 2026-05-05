<template>
    <div class="min-h-screen bg-gray-100 p-6 flex flex-col">
        <div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow flex-1">
            <h1 class="text-xl font-bold mb-6">User Settings</h1>

            <!-- NAME -->
            <div class="mb-4">
                <label class="text-sm">Name</label>
                <input v-model="user.name" class="w-full border p-2 rounded" />
            </div>

            <!-- EMAIL -->
            <div class="mb-4">
                <label class="text-sm">Email</label>
                <input v-model="user.email" class="w-full border p-2 rounded" />
            </div>

            <!-- PASSWORD -->
            <div class="mb-4">
                <label class="text-sm">New Password</label>
                <input
                    v-model="user.password"
                    type="password"
                    placeholder="Leave blank to keep old password"
                    class="w-full border p-2 rounded"
                />
            </div>

            <!-- SAVE BUTTON -->
            <div class="mt-6">
                <button
                    @click="updateProfile"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 cursor-pointer"
                >
                    Save Changes
                </button>
            </div>
        </div>

        <!-- LOGOUT SECTION (BOTTOM OF PAGE) -->
        <div class="max-w-xl mx-auto mt-8">
            <button
                @click="logout"
                class="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 cursor-pointer transition font-medium"
            >
                Logout
            </button>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useSetting } from "@/composables/useSetting";

const { user, loadProfile, updateProfile, logout } = useSetting();

onMounted(() => {
    loadProfile();
});
</script>
