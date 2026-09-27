<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Stok-Takip</h1>
        <p class="text-gray-500 mt-1">Yeni hesap oluşturun</p>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div>
          <label for="name" class="block font-semibold mb-1 text-gray-700">Ad Soyad</label>
          <InputText id="name" v-model="form.name" class="w-full" placeholder="Adınız Soyadınız" autocomplete="name" />
        </div>
        <div>
          <label for="email" class="block font-semibold mb-1 text-gray-700">E-posta</label>
          <InputText id="email" v-model="form.email" type="email" class="w-full" placeholder="ornek@mail.com" autocomplete="email" />
        </div>
        <div>
          <label for="password" class="block font-semibold mb-1 text-gray-700">Şifre</label>
          <Password id="password" v-model="form.password" :feedback="false" class="w-full" toggleMask placeholder="En az 6 karakter" />
        </div>

        <div v-if="errorMessage" class="bg-red-50 text-red-600 border border-red-200 rounded-md p-3 text-sm">
          {{ errorMessage }}
        </div>

        <Button type="submit" label="Üye Ol" class="w-full" :loading="loading" />
      </form>

      <p class="text-center text-gray-500 mt-6 text-sm">
        Zaten hesabınız var mı?
        <router-link to="/login" class="text-blue-600 font-semibold hover:underline">Giriş Yap</router-link>
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
import { register } from "../../composables/useAuth"

const router = useRouter()
const form = reactive({
  name: "",
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
      errorMessage.value = "Kayıt oluşturulamadı, lütfen tekrar deneyin."
    }
  } finally {
    loading.value = false
  }
}
</script>

<style></style>