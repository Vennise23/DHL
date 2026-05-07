<template>
  <div class="flex h-screen">

    <!-- LEFT SIDEBAR -->
    <SideMenu v-if="!isLoginPage" />

    <!-- MAIN CONTENT -->
    <div id="app-scroll" class="flex-1 overflow-auto">
      <router-view />
    </div>

  </div>
</template>
<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import SideMenu from "./pages/SideMenu.vue";

const route = useRoute();

// detect login page
const isLoginPage = computed(() => route.path === "/");
</script>