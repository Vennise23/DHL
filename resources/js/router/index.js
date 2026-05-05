import { createRouter, createWebHistory } from 'vue-router'

import Login from '../pages/Login.vue'
import Dashboard from '../pages/Dashboard.vue'
import Incident from '../pages/Incident.vue'
import AIPage from '../pages/AIPage.vue'
import RpaLogs from '../pages/RpaLogs.vue'
import Reports from '../pages/Reports.vue'
import Settings from '../pages/Settings.vue'

const routes = [
  { path: "/", component: Login },
  { path: "/dashboard", component: Dashboard },
  { path: "/incidents", component: Incident },
  { path: "/ai", component: AIPage },
  { path: "/rpa", component: RpaLogs },
  { path: "/reports", component: Reports },
  { path: "/settings", component: Settings },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// 🔐 ROLE GUARD
router.beforeEach((to) => {
    const token = localStorage.getItem("token");
    const user = JSON.parse(localStorage.getItem("user") || "null");

    // ✅ allow login page always
    if (to.path === "/") {
        return true;
    }

    // ❌ not logged in → go login
    if (!token) {
        return "/";
    }

    // ❌ no user data → reset
    if (!user) {
        return "/";
    }

    // role protection
    if (to.path === "/ai" && user.role !== "admin") {
        return "/dashboard";
    }

    if (to.path === "/rpa" && user.role !== "admin") {
        return "/dashboard";
    }

    if (to.path === "/reports" && user.role === "staff") {
        return "/dashboard";
    }

    return true;
});

export default router