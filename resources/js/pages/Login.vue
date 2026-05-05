<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
            <h1 class="text-2xl font-bold mb-6 text-center">
                DHL Incident System Login
            </h1>

            <form @submit.prevent="login" class="space-y-4">
                <div>
                    <label class="text-sm">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full border px-3 py-2 rounded-lg"
                        required
                    />
                </div>

                <div>
                    <label class="text-sm">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full border px-3 py-2 rounded-lg"
                        required
                    />
                </div>

                <button
                    class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700"
                >
                    Login
                </button>

                <p v-if="error" class="text-red-500 text-sm text-center">
                    {{ error }}
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

const form = reactive({
    email: "",
    password: "",
});

const error = ref("");

const login = async () => {
    error.value = "";

    try {
        const res = await axios.post("/api/login", form);

        localStorage.setItem("token", res.data.token);

        router.push("/dashboard");
    } catch (e) {
        console.log("LOGIN ERROR:", e);

        if (e.response) {
            console.log("Backend Response:", e.response.data);
            console.log("Status:", e.response.status);

            error.value =
                e.response.data.message || JSON.stringify(e.response.data);
        } else if (e.request) {
            error.value = "No response from server (API not reachable)";
        } else {
            error.value = e.message;
        }
    }
};
</script>
