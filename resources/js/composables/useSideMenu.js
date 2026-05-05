import { ref, computed } from "vue";

export function useSideMenu() {
    const collapsed = ref(false);

    // ✅ keep raw object (NOT ref inside ref confusion)
    const user = ref(
        JSON.parse(localStorage.getItem("user") || "null")
    );

    const menu = [
        {
            name: "Dashboard",
            path: "/dashboard",
            icon: "pi pi-home",
            roles: ["admin", "reviewer", "staff"],
        },
        {
            name: "Incidents",
            path: "/incidents",
            icon: "pi pi-exclamation-circle",
            roles: ["admin", "reviewer", "staff"],
        },
        {
            name: "AI",
            path: "/ai",
            icon: "pi pi-bolt",
            roles: ["admin"],
        },
        {
            name: "RPA Logs",
            path: "/rpa",
            icon: "pi pi-cog",
            roles: ["admin"],
        },
        {
            name: "Reports",
            path: "/reports",
            icon: "pi pi-chart-bar",
            roles: ["admin", "reviewer"],
        },
        {
            name: "Settings",
            path: "/settings",
            icon: "pi pi-sliders-h",
            roles: ["admin", "reviewer", "staff"],
        },
    ];

    // ✅ SAFE filtering
    const filteredMenu = computed(() => {
        const role = user.value?.role;
        if (!role) return [];

        return menu.filter((item) => item.roles.includes(role));
    });

    const toggleMenu = () => {
        collapsed.value = !collapsed.value;
    };

    // ✅ FIXED ICON FUNCTION (IMPORTANT)
    const roleIcon = (role) => {
        if (!role) {
            return "pi pi-exclamation-triangle text-red-500";
        }

        switch (role) {
            case "admin":
                return "pi pi-shield text-blue-600";
            case "reviewer":
                return "pi pi-eye text-green-600";
            case "staff":
                return "pi pi-truck text-orange-500";
            default:
                return "pi pi-question-circle text-gray-500";
        }
    };

    return {
        collapsed,
        user,
        toggleMenu,
        filteredMenu,
        roleIcon,
    };
}