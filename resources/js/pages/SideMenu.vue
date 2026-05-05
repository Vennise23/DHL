<template>
    <div
        class="h-screen bg-white shadow-lg flex flex-col transition-all duration-300"
        :class="collapsed ? 'w-16' : 'w-64'"
    >
        <!-- TOP HEADER -->
        <div class="flex items-center justify-between p-4 border-b">
            <div v-if="!collapsed" class="font-bold text-lg text-blue-600">
                DHL System
            </div>

            <!-- toggle button (ICON ONLY) -->
            <button
                @click="toggleMenu"
                class="text-gray-600 hover:text-blue-600 cursor-pointer flex items-center"
            >
                <i
                    class="pi"
                    :class="collapsed ? 'pi-angle-right' : 'pi-angle-left'"
                ></i>
            </button>
        </div>

        <!-- MENU -->
        <nav class="flex-1 mt-4 space-y-1">
            <router-link
                v-for="item in filteredMenu"
                :key="item.path"
                :to="item.path"
                class="flex items-center gap-3 px-4 py-3 mx-2 rounded-lg transition-all"
                active-class="bg-blue-100 text-blue-600 font-semibold"
            >
                <!-- ICON -->
                <i :class="item.icon" class="text-lg"></i>

                <!-- TEXT -->
                <span v-if="!collapsed">{{ item.name }}</span>
            </router-link>
        </nav>

        <!-- USER SECTION -->
        <div class="border-t p-4 text-sm">
            <!-- EXPANDED VIEW -->
            <div v-if="!collapsed">
                <div class="flex items-center gap-2 mb-1">
                    <!-- ROLE ICON -->
                    <i class="text-lg" :class="roleIcon(user?.role)"></i>

                    <!-- NAME -->
                    <p class="font-semibold">
                        {{ user?.name || "Unknown User" }}
                    </p>
                </div>

                <!-- ROLE TEXT -->
                <p class="text-gray-500">
                    {{ user?.role || "No role assigned" }}
                </p>
            </div>

            <!-- COLLAPSED VIEW -->
            <div v-else class="text-center">
                <i class="text-xl" :class="roleIcon(user?.role)"></i>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useSideMenu } from "@/composables/useSideMenu";

const { collapsed, toggleMenu, filteredMenu, user,roleIcon } = useSideMenu();
</script>
