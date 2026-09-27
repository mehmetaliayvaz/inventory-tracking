import "./bootstrap"
import { createApp } from "vue"
import App from "./App.vue"
import router from "./router"
import PrimeVue from "primevue/config"
import ToastService from "primevue/toastservice"
import "primevue/resources/themes/aura-light-amber/theme.css"
import "primeicons/primeicons.css"

const app = createApp(App)

app.use(router)
app.use(PrimeVue)
app.use(ToastService)
app.mount("#app")