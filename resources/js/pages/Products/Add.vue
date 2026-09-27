<template>
  <div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
      <Button icon="pi pi-arrow-left" class="p-button-text p-button-rounded" @click="router.push({ name: 'products' })" />
      <h2 class="text-lg font-semibold text-gray-800">{{ t("product.add.title") }}</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div>
          <label for="barcode" class="block text-sm font-semibold mb-1.5 text-gray-700">
            {{ t("product.barcode-label") }}
            <span class="text-xs font-normal text-gray-400">{{ t("product.barcode-hint") }}</span>
          </label>
          <div class="flex gap-2">
            <InputText id="barcode" v-model="form.barcode" class="flex-1" :placeholder="t('product.barcode-label')" autofocus @keydown.enter.prevent="handleLookup" />
            <Button type="button" icon="pi pi-search" :label="t('common.search')" :loading="checking" @click="handleLookup" />
          </div>
          <div v-if="checking" class="text-sm text-gray-400 mt-2">
            <i class="pi pi-spin pi-spinner mr-1"></i> {{ t("product.searching") }}
          </div>
          <div v-else-if="existingProduct" class="text-sm text-blue-600 mt-2 flex items-center gap-1.5">
            <i class="pi pi-info-circle"></i>
            <span>
              {{ t("product.exists", { name: existingProduct.name }) }}
              <router-link :to="{ name: 'products.detail', params: { id: existingProduct.id } }" class="underline font-semibold">
                {{ t("product.go-to") }}
              </router-link>
            </span>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div class="sm:col-span-2">
            <label for="name" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.name-label") }} *</label>
            <InputText id="name" v-model="form.name" class="w-full" required />
          </div>

          <div>
            <label for="brand" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.brand-select") }}</label>
            <Dropdown id="brand" v-model="form.brand_id" :options="brands" optionLabel="name" optionValue="id" :placeholder="t('product.brand-placeholder')" class="w-full" showClear />
          </div>

          <div>
            <label for="category" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.category-select") }}</label>
            <Dropdown id="category" v-model="form.category_id" :options="categories" optionLabel="name" optionValue="id" :placeholder="t('product.category-placeholder')" class="w-full" showClear />
          </div>

          <div>
            <label for="price" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("products.price") }} (₺) *</label>
            <InputNumber id="price" v-model="form.price" :min="0" mode="currency" currency="TRY" locale="tr-TR" class="w-full" />
          </div>

          <div>
            <label for="stock" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("products.stock") }} *</label>
            <InputNumber id="stock" v-model="form.stock" :min="0" :useGrouping="false" class="w-full" />
          </div>

          <div class="sm:col-span-2">
            <label for="description" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.description") }}</label>
            <Textarea id="description" v-model="form.description" rows="3" class="w-full" />
          </div>

          <div class="sm:col-span-2">
            <label for="image" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.image-url") }}</label>
            <div class="flex items-center gap-3">
              <product-image :src="form.image" size="md" />
              <InputText id="image" v-model="form.image" class="flex-1" :placeholder="'https://...'" />
            </div>
          </div>
        </div>

        <div class="pt-2 flex justify-end gap-2">
          <Button type="button" :label="t('common.cancel')" class="p-button-outlined p-button-secondary" @click="router.push({ name: 'products' })" />
          <Button type="submit" :label="t('product.save')" icon="pi pi-check" :loading="saving" />
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from "vue"
import { useRouter } from "vue-router"
import Button from "primevue/button"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import Dropdown from "primevue/dropdown"
import Textarea from "primevue/textarea"
import { useToast } from "primevue/usetoast"
import ProductImage from "../../components/ProductImage.vue"
import api from "../../api"
import { useBarcodeScanner } from "../../composables/useBarcodeScanner"
import { t } from "../../i18n"

const router = useRouter()
const toast = useToast()

const categories = ref([])
const brands = ref([])

const form = reactive({
  barcode: "",
  name: "",
  brand_id: null,
  category_id: null,
  price: null,
  stock: null,
  description: "",
  image: "",
})
const saving = ref(false)
const checking = ref(false)
const existingProduct = ref(null)

let lookupTimer = null
const lastLookedCode = ref("")

watch(
  () => form.barcode,
  (value) => {
    clearTimeout(lookupTimer)
    const code = String(value || "").trim()
    if (code.length >= 8 && code.length <= 14 && /^\d+$/.test(code) && lastLookedCode.value !== code) {
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
  checking.value = true
  existingProduct.value = null

  const existing = await findInOwnProducts(code)
  checking.value = false
  if (existing) {
    existingProduct.value = existing
    fillForm(existing)
  }
}

async function findInOwnProducts(code) {
  try {
    const { data } = await api.get("/products", { params: { q: code } })
    return data.data.find((p) => p.barcode === code) || null
  } catch (error) {
    return null
  }
}

function fillForm(source) {
  form.name = form.name || source.name || ""
  form.brand_id = source.brand_id ?? null
  form.category_id = source.category_id ?? null
  form.image = source.image || ""
  if (source.description && !form.description) {
    form.description = source.description
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

async function loadOptions() {
  const [catRes, brandRes] = await Promise.all([api.get("/categories"), api.get("/brands")])
  categories.value = catRes.data.data
  brands.value = brandRes.data.data
}

async function handleSubmit() {
  if (!form.name) {
    toast.add({ severity: "error", summary: t("product.add.title"), detail: t("product.name-required"), life: 3000 })
    return
  }
  if (form.price === null || form.price === undefined) {
    toast.add({ severity: "error", summary: t("product.add.title"), detail: t("product.price-required"), life: 3000 })
    return
  }
  saving.value = true
  try {
    await api.post("/products", form)
    toast.add({ severity: "success", summary: t("product.add.title"), detail: t("product.saved"), life: 3000 })
    router.push({ name: "products" })
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : t("settings.error")
    toast.add({ severity: "error", summary: t("product.add.title"), detail: msg, life: 4000 })
  } finally {
    saving.value = false
  }
}

onMounted(loadOptions)
</script>

<style></style>