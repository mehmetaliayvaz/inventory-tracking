<template>
  <div class="page-wrapper">
    <div class="container max-w-3xl">
      <div class="mb-6">
        <Button icon="pi pi-arrow-left" label="Geri" class="p-button-text" @click="router.push({ name: 'products' })" />
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Ürün Detayı</h1>
      </div>

      <div v-if="loading" class="bg-white shadow rounded-xl p-10 text-center text-gray-500">Yükleniyor...</div>

      <div v-else-if="!product" class="bg-white shadow rounded-xl p-10 text-center text-gray-500">
        Ürün bulunamadı.
      </div>

      <template v-else>
        <div class="bg-white shadow rounded-xl p-6">
          <div class="flex items-start gap-6 mb-6">
            <img v-if="product.image" :src="product.image" alt="Ürün görseli" class="w-32 h-32 object-cover rounded-xl border" />
            <div v-else class="w-32 h-32 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400">
              <i class="pi pi-box" style="font-size: 3rem"></i>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800">{{ product.name }}</h2>
              <p v-if="product.brand" class="text-gray-500">{{ product.brand }}</p>
              <div class="flex gap-2 mt-3">
                <Tag v-if="product.barcode" :value="`Barkod: ${product.barcode}`" severity="info" />
                <Tag v-if="product.category" :value="product.category" severity="secondary" />
              </div>
            </div>
          </div>

          <form @submit.prevent="handleSave" class="space-y-5">
            <div class="grid grid-cols-2 gap-5">
              <div class="col-span-2">
                <label for="name" class="block font-semibold mb-1 text-gray-700">Ürün Adı *</label>
                <InputText id="name" v-model="product.name" class="w-full" required />
              </div>
              <div>
                <label for="barcode" class="block font-semibold mb-1 text-gray-700">Barkod</label>
                <InputText id="barcode" v-model="product.barcode" class="w-full" />
              </div>
              <div>
                <label for="brand" class="block font-semibold mb-1 text-gray-700">Marka</label>
                <InputText id="brand" v-model="product.brand" class="w-full" />
              </div>
              <div>
                <label for="category" class="block font-semibold mb-1 text-gray-700">Kategori</label>
                <InputText id="category" v-model="product.category" class="w-full" />
              </div>
              <div>
                <label for="price" class="block font-semibold mb-1 text-gray-700">Fiyat (₺) *</label>
                <InputNumber id="price" v-model="product.price" :min="0" mode="currency" currency="TRY" locale="tr-TR" class="w-full" />
              </div>
              <div>
                <label for="stock" class="block font-semibold mb-1 text-gray-700">Stok Adedi *</label>
                <InputNumber id="stock" v-model="product.stock" :min="0" :useGrouping="false" class="w-full" />
              </div>
              <div class="col-span-2">
                <label for="description" class="block font-semibold mb-1 text-gray-700">Açıklama</label>
                <Textarea id="description" v-model="product.description" rows="3" class="w-full" />
              </div>
              <div class="col-span-2">
                <label for="image" class="block font-semibold mb-1 text-gray-700">Görsel URL</label>
                <InputText id="image" v-model="product.image" class="w-full" />
              </div>
            </div>

            <div class="flex gap-3 pt-2">
              <Button type="submit" label="Kaydet" icon="pi pi-check" :loading="saving" />
              <Button label="Sil" icon="pi pi-trash" severity="danger" outlined class="ml-auto" @click="handleDelete" />
            </div>
          </form>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import Button from "primevue/button"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import Textarea from "primevue/textarea"
import Tag from "primevue/tag"
import { useToast } from "primevue/usetoast"
import api from "../../api"

const route = useRoute()
const router = useRouter()
const toast = useToast()

const product = ref(null)
const loading = ref(false)
const saving = ref(false)

async function loadProduct() {
  loading.value = true
  try {
    const { data } = await api.get(`/products/${route.params.id}`)
    product.value = data.data
  } catch (error) {
    toast.add({ severity: "error", summary: "Hata", detail: "Ürün yüklenemedi.", life: 3000 })
  } finally {
    loading.value = false
  }
}

async function handleSave() {
  saving.value = true
  try {
    await api.put(`/products/${product.value.id}`, product.value)
    toast.add({ severity: "success", summary: "Başarılı", detail: "Ürün güncellendi.", life: 3000 })
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : "Ürün güncellenemedi."
    toast.add({ severity: "error", summary: "Hata", detail: msg, life: 4000 })
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  try {
    await api.delete(`/products/${product.value.id}`)
    toast.add({ severity: "success", summary: "Silindi", detail: "Ürün silindi.", life: 3000 })
    router.push({ name: "products" })
  } catch (error) {
    toast.add({ severity: "error", summary: "Hata", detail: "Ürün silinemedi.", life: 3000 })
  }
}

onMounted(loadProduct)
</script>

<style></style>