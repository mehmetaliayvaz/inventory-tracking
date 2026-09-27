<template>
  <div class="min-h-screen flex">
    <div class="hidden lg:flex w-1/2 bg-slate-900 text-white flex-col justify-between p-12">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-400 flex items-center justify-center text-slate-900 font-extrabold text-xl">S</div>
        <span class="font-bold text-xl">{{ t("app.name") }}</span>
      </div>
      <div>
        <h2 class="text-3xl font-bold leading-tight mb-3">
          Stoklarınızı kolayca yönetin.
        </h2>
        <p class="text-gray-400 text-sm">Ürün, barkod ve satış takibini tek yerden yapın.</p>
      </div>
      <div class="text-xs text-gray-500">© 2026 {{ t("app.name") }}</div>
    </div>

    <div class="flex-1 flex items-center justify-center p-6 bg-gray-50">
      <div class="w-full max-w-md">
        <div class="mb-8 lg:hidden">
          <div class="flex items-center gap-3 justify-center">
            <div class="w-10 h-10 rounded-lg bg-amber-400 flex items-center justify-center text-slate-900 font-extrabold text-xl">S</div>
            <span class="font-bold text-xl text-gray-800">{{ t("app.name") }}</span>
          </div>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-1">{{ t("auth.login.title") }}</h1>

        <form @submit.prevent="handleSubmit" class="mt-6 space-y-5">
          <div>
            <label for="email" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("auth.email") }}</label>
            <InputText id="email" v-model="form.email" type="email" class="w-full" placeholder="ornek@mail.com" autocomplete="email" />
          </div>
          <div>
            <label for="password" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("auth.password") }}</label>
            <Password id="password" v-model="form.password" :feedback="false" class="w-full" toggleMask placeholder="••••••••" />
          </div>

          <div v-if="errorMessage" class="bg-red-50 text-red-600 border border-red-200 rounded-md p-3 text-sm">
            {{ errorMessage }}
          </div>

          <Button type="submit" :label="t('auth.login.submit')" icon="pi pi-sign-in" class="w-full" :loading="loading" />
        </form>

        <p class="text-center text-gray-500 mt-6 text-sm">
          {{ t("auth.login.no-account") }}
          <router-link to="/register" class="text-amber-600 font-semibold hover:underline">{{ t("auth.login.signup-link") }}</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue"
import { useRouter } from "vue-router"
import InputText from "primevue/inputtext"
import Password from "primevue/password"
import Button from "primevue/button"
import { login } from "../../composables/useAuth"
import { t } from "../../i18n"

const router = useRouter()
const form = reactive({
  email: "",
  password: "",
})
const errorMessage = ref("")
const loading = ref(false)

async function handleSubmit() {
  errorMessage.value = ""
  loading.value = true
  try {
    await login(form)
    router.push({ name: "home" })
  } catch (error) {
    if (error.response && error.response.data.errors) {
      errorMessage.value = Object.values(error.response.data.errors)[0][0]
    } else {
      errorMessage.value = t("auth.login.fail")
    }
  } finally {
    loading.value = false
  }
}
</script>

<style></style>