<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Stok-Takip</h1>
        <p class="text-gray-500 mt-1">Hesabınıza giriş yapın</p>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div>
          <label for="email" class="block font-semibold mb-1 text-gray-700">E-posta</label>
          <InputText id="email" v-model="form.email" type="email" class="w-full" placeholder="ornek@mail.com" autocomplete="email" />
        </div>
        <div>
          <label for="password" class="block font-semibold mb-1 text-gray-700">Şifre</label>
          <Password id="password" v-model="form.password" :feedback="false" class="w-full" toggleMask placeholder="••••••••" />
        </div>

        <div v-if="errorMessage" class="bg-red-50 text-red-600 border border-red-200 rounded-md p-3 text-sm">
          {{ errorMessage }}
        </div>

        <Button type="submit" label="Giriş Yap" class="w-full" :loading="loading" />
      </form>

      <p class="text-center text-gray-500 mt-6 text-sm">
        Hesabınız yok mu?
        <router-link to="/register" class="text-blue-600 font-semibold hover:underline">Üye Ol</router-link>
      </p>
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
      errorMessage.value = "Giriş yapılamadı, lütfen tekrar deneyin."
    }
  } finally {
    loading.value = false
  }
}
</script>

<style></style>