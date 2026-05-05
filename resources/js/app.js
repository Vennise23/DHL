import './bootstrap';
import '../css/app.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'
import 'primeicons/primeicons.css'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Tooltip from 'primevue/tooltip'
import axios from 'axios'

axios.defaults.baseURL = "http://127.0.0.1:8000"

axios.interceptors.request.use(config => {
    const token = localStorage.getItem('token')

    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }

    return config
})

axios.defaults.headers.common['Authorization'] =
  `Bearer ${localStorage.getItem("token")}`;

createApp(App)
  .use(router)
  .use(createPinia())
  .use(PrimeVue, {
      theme: {
          preset: Aura
      }
  })
  .component('DataTable', DataTable)
  .component('Column', Column)
  .component('Button', Button)
  .directive('tooltip', Tooltip)
  .mount('#app')