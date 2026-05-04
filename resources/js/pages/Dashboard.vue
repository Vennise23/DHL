<template>
  <div class="min-h-screen bg-gray-100 p-6">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">
        Incident Dashboard
      </h1>

      <button
        @click="logout"
        class="bg-red-500 text-white px-4 py-2 rounded-lg"
      >
        Logout
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow mb-6 flex gap-3">
      <input
        v-model="search"
        placeholder="Search incidents..."
        class="border px-3 py-2 rounded-lg w-full"
      />

      <select v-model="status" class="border px-3 py-2 rounded-lg">
        <option value="">All Status</option>
        <option value="draft">Draft</option>
        <option value="reviewed">Reviewed</option>
        <option value="published">Published</option>
      </select>
    </div>

    <!-- Incident List -->
    <div class="grid gap-4">

      <div
        v-for="incident in filteredIncidents"
        :key="incident.id"
        class="bg-white p-4 rounded-xl shadow hover:shadow-md cursor-pointer"
        @click="openIncident(incident.id)"
      >
        <div class="flex justify-between">
          <h2 class="font-semibold">
            {{ incident.title }}
          </h2>

          <span
            class="text-xs px-2 py-1 rounded"
            :class="statusColor(incident.status)"
          >
            {{ incident.status }}
          </span>
        </div>

        <p class="text-sm text-gray-500 mt-2">
          {{ incident.description }}
        </p>

        <div class="text-xs text-gray-400 mt-2">
          Created: {{ incident.date }}
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const incidents = ref([])
const search = ref('')
const status = ref('')

onMounted(async () => {
  const res = await axios.get('/api/incidents')
  incidents.value = res.data
})

const filteredIncidents = computed(() => {
  return incidents.value.filter(i => {
    return (
      i.title.toLowerCase().includes(search.value.toLowerCase()) &&
      (status.value === '' || i.status === status.value)
    )
  })
})

const openIncident = (id) => {
  router.push(`/incident/${id}`)
}

const logout = () => {
  localStorage.removeItem('token')
  router.push('/')
}

const statusColor = (s) => {
  if (s === 'draft') return 'bg-yellow-200 text-yellow-800'
  if (s === 'reviewed') return 'bg-blue-200 text-blue-800'
  if (s === 'published') return 'bg-green-200 text-green-800'
}
</script>