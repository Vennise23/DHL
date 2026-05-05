import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

export function useSetting() {
    const router = useRouter();

    const user = ref({
        name: "",
        email: "",
        password: "",
    });

    const loading = ref(false);

    const loadProfile = async () => {
        const res = await axios.get("/api/me");
        user.value = res.data;
    };

    const updateProfile = async () => {
        loading.value = true;

        await axios.put("/api/me", user.value);

        alert("Profile updated!");
        loading.value = false;
    };

    const logout = async () => {
        await axios.post("/api/logout");

        localStorage.removeItem("token");
        localStorage.removeItem("user");

        router.push("/");
    };

    return {
        user,
        loading,
        loadProfile,
        updateProfile,
        logout,
    };
}