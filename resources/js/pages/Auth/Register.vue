<template>
  <div class="min-h-screen flex">
    <div class="hidden lg:flex w-1/2 bg-slate-900 text-white flex-col justify-between p-12">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-400 flex items-center justify-center text-slate-900 font-extrabold text-xl">S</div>
        <span class="font-bold text-xl">{{ t("app.name") }}</span>
      </div>
      <div>
        <h2 class="text-3xl font-bold leading-tight mb-3">
          Ekibinizle birlikte çalışın.
        </h2>
        <p class="text-gray-400 text-sm">Yönetici ve üye rollerini tek hesapta yönetin.</p>
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

        <h1 class="text-2xl font-bold text-gray-800 mb-1">{{ t("auth.register.title") }}</h1>

        <form @submit.prevent="handleSubmit" class="mt-6 space-y-5">
          <div>
            <label for="name" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("auth.name") }}</label>
            <InputText id="name" v-model="form.name" class="w-full" placeholder="Adınız Soyadınız" autocomplete="name" />
          </div>
          <div>
            <label for="company-name" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("auth.company-name") }}</label>
            <InputText id="company-name" v-model="form.company_name" class="w-full" placeholder="Örn. Yıldız Market" autocomplete="organization" />
            <div class="text-xs text-gray-400 mt-1">{{ t("auth.company-name-hint") }}</div>
          </div>
          <div>
            <label for="email" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("auth.email") }}</label>
            <InputText id="email" v-model="form.email" type="email" class="w-full" placeholder="ornek@mail.com" autocomplete="email" />
          </div>
          <div>
            <label for="password" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("auth.password") }}</label>
            <Password id="password" v-model="form.password" :feedback="false" class="w-full" toggleMask placeholder="En az 6 karakter" />
          </div>

          <div v-if="errorMessage" class="bg-red-50 text-red-600 border border-red-200 rounded-md p-3 text-sm">
            {{ errorMessage }}
          </div>

          <Button type="submit" :label="t('auth.register.submit')" icon="pi pi-user-plus" class="w-full" :loading="loading" />
        </form>

        <p class="text-center text-gray-500 mt-6 text-sm">
          {{ t("auth.register.has-account") }}
          <router-link to="/login" class="text-amber-600 font-semibold hover:underline">{{ t("auth.register.login-link") }}</router-link>
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
import { register } from "../../composables/useAuth"
import { t } from "../../i18n"

const router = useRouter()
const form = reactive({
  name: "",
  company_name: "",
  email: "",
  password: "",
})
const errorMessage = ref("")
const loading = ref(false)

async function handleSubmit() {
  errorMessage.value = ""
  loading.value = true
  try {
    await register(form)
    router.push({ name: "home" })
  } catch (error) {
    if (error.response && error.response.data.errors) {
      errorMessage.value = Object.values(error.response.data.errors)[0][0]
    } else {
      errorMessage.value = t("auth.register.fail")
    }
  } finally {
    loading.value = false
  }
}
</script>

<style></style>