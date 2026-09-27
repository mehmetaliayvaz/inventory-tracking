<template>
  <div class="page-wrapper">
    <div class="container max-w-3xl">
      <div class="mb-6">
        <Button icon="pi pi-arrow-left" label="Geri" class="p-button-text" @click="router.push({ name: 'products' })" />
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Ürün Ekle</h1>
      </div>

      <div class="bg-white shadow rounded-xl p-6">
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label for="barcode" class="block font-semibold mb-1 text-gray-700">
              Barkod <span class="text-xs font-normal text-gray-400">(okutun veya yazın, bilgiler otomatik dolsun)</span>
            </label>
            <div class="flex gap-2">
              <InputText id="barcode" v-model="form.barcode" class="flex-1" placeholder="Barkodu okutun veya yazın" autofocus @keydown.enter.prevent="handleLookup" />
              <Button type="button" label="Ara" :loading="lookup.loading.value" @click="handleLookup" />
            </div>
            <div v-if="lookup.loading.value" class="text-sm text-gray-400 mt-2">
              <i class="pi pi-spin pi-spinner mr-1"></i> Barkod bilgileri aranıyor...
            </div>
            <div v-else-if="lookup.notFound.value" class="text-sm text-amber-600 mt-2">
              Barkod için bilgi bulunamadı, ürün bilgilerini elle girebilirsiniz.
            </div>
            <div v-else-if="lookup.error.value" class="text-sm text-red-600 mt-2">
              {{ lookup.error.value }}
            </div>
            <div v-else-if="autoFilled" class="text-sm text-green-600 mt-2">
              <i class="pi pi-check mr-1"></i> Barkod bilgileri bulundu ve dolduruldu.
            </div>
          </div>

          <div class="grid grid-cols-2 gap-5">
            <div class="col-span-2">
              <label for="name" class="block font-semibold mb-1 text-gray-700">Ürün Adı *</label>
              <InputText id="name" v-model="form.name" class="w-full" required />
            </div>
            <div>
              <label for="brand" class="block font-semibold mb-1 text-gray-700">Marka</label>
              <InputText id="brand" v-model="form.brand" class="w-full" />
            </div>
            <div>
              <label for="category" class="block font-semibold mb-1 text-gray-700">Kategori</label>
              <InputText id="category" v-model="form.category" class="w-full" />
            </div>
            <div>
              <label for="price" class="block font-semibold mb-1 text-gray-700">Fiyat (₺) *</label>
              <InputNumber id="price" v-model="form.price" :min="0" mode="currency" currency="TRY" locale="tr-TR" class="w-full" />
            </div>
            <div>
              <label for="stock" class="block font-semibold mb-1 text-gray-700">Stok Adedi *</label>
              <InputNumber id="stock" v-model="form.stock" :min="0" :useGrouping="false" class="w-full" />
            </div>
            <div class="col-span-2">
              <label for="description" class="block font-semibold mb-1 text-gray-700">Açıklama</label>
              <Textarea id="description" v-model="form.description" rows="3" class="w-full" />
            </div>
          </div>

          <div class="flex items-center gap-3 pt-2">
            <img v-if="form.image" :src="form.image" alt="Ürün görseli" class="w-16 h-16 object-cover rounded-lg border" />
            <Button type="submit" label="Ürünü Kaydet" icon="pi pi-check" :loading="saving" />
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from "vue"
import { useRouter } from "vue-router"
import Button from "primevue/button"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import Textarea from "primevue/textarea"
import { useToast } from "primevue/usetoast"
import api from "../../api"
import { useBarcodeLookup } from "../../composables/useBarcodeLookup"
import { useBarcodeScanner } from "../../composables/useBarcodeScanner"

const router = useRouter()
const toast = useToast()
const lookup = useBarcodeLookup()

const form = reactive({
  barcode: "",
  name: "",
  brand: "",
  category: "",
  price: null,
  stock: null,
  description: "",
  image: "",
})
const saving = ref(false)
const autoFilled = ref(false)

let lookupTimer = null
const lastLookedCode = ref("")

watch(
  () => form.barcode,
  (value) => {
    clearTimeout(lookupTimer)
    const code = String(value || "").trim()
    if (
      code.length >= 8 &&
      code.length <= 14 &&
      /^\d+$/.test(code) &&
      lastLookedCode.value !== code
    ) {
      lookupTimer = setTimeout(() => {
        if (code === String(form.barcode).trim()) {
          runLookup(code)
        }
      }, 350)
    }
  }
)

async function runLookup(code) {
  if (lastLookedCode.value === code) return
  lastLookedCode.value = code
  autoFilled.value = false
  const result = await lookup.lookupBarcode(code)
  if (result) {
    form.name = form.name || result.name || ""
    form.brand = result.brand || ""
    form.category = result.category || ""
    form.image = result.image || ""
    if (result.quantity && !form.description) {
      form.description = result.quantity
    }
    autoFilled.value = true
  }
}

function handleLookup() {
  const code = String(form.barcode || "").trim()
  if (!code) return
  runLookup(code)
}

useBarcodeScanner((code) => {
  form.barcode = code
  handleLookup()
})

async function handleSubmit() {
  if (!form.name) {
    toast.add({ severity: "error", summary: "Hata", detail: "Ürün adı zorunludur.", life: 3000 })
    return
  }
  if (form.price === null || form.price === undefined) {
    toast.add({ severity: "error", summary: "Hata", detail: "Fiyat zorunludur.", life: 3000 })
    return
  }
  saving.value = true
  try {
    await api.post("/products", form)
    toast.add({ severity: "success", summary: "Başarılı", detail: "Ürün eklendi.", life: 3000 })
    router.push({ name: "products" })
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : "Ürün kaydedilemedi."
    toast.add({ severity: "error", summary: "Hata", detail: msg, life: 4000 })
  } finally {
    saving.value = false
  }
}
</script>

<style></style>