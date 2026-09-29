<template>
  <div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
      <Button icon="pi pi-arrow-left" class="p-button-text p-button-rounded" @click="router.push({ name: 'products' })" />
      <h2 class="text-lg font-semibold text-gray-800">{{ t("product.detail.title") }}</h2>
    </div>

    <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
      <i class="pi pi-spin pi-spinner mr-2"></i>{{ t("common.loading") }}
    </div>

    <div v-else-if="!product" class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
      {{ t("product.not-found") }}
    </div>

    <template v-else>
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start gap-5 mb-6">
          <product-image :src="product.image" size="lg" />
          <div>
            <h3 class="text-xl font-bold text-gray-800">{{ product.name }}</h3>
            <div class="flex gap-2 mt-2 flex-wrap">
              <Tag v-if="product.barcode" :value="`${t('products.barcode')}: ${product.barcode}`" severity="info" />
              <Tag v-if="product.brand" :value="product.brand.name" severity="secondary" />
              <Tag v-if="product.category" :value="product.category.name" severity="success" />
            </div>
          </div>
        </div>

        <form v-if="isAdmin" @submit.prevent="handleSave" class="space-y-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
              <label for="name" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.name-label") }} *</label>
              <InputText id="name" v-model="product.name" class="w-full" required />
            </div>
            <div>
              <label for="barcode" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.barcode-label") }}</label>
              <InputText id="barcode" v-model="product.barcode" class="w-full" />
            </div>
            <div>
              <label for="brand" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.brand-select") }}</label>
              <Dropdown id="brand" v-model="product.brand_id" :options="brands" optionLabel="name" optionValue="id" :placeholder="t('product.brand-placeholder')" class="w-full" showClear />
            </div>
            <div>
              <label for="category" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.category-select") }}</label>
              <Dropdown id="category" v-model="product.category_id" :options="categories" optionLabel="name" optionValue="id" :placeholder="t('product.category-placeholder')" class="w-full" showClear />
            </div>
            <div>
              <label for="price" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("products.price") }} (₺) *</label>
              <InputNumber id="price" v-model="product.price" :min="0" mode="currency" currency="TRY" locale="tr-TR" class="w-full" />
            </div>
            <div>
              <label for="stock" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("products.stock") }} *</label>
              <InputNumber id="stock" v-model="product.stock" :min="0" :useGrouping="false" class="w-full" />
            </div>
            <div class="sm:col-span-2">
              <label for="description" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.description") }}</label>
              <Textarea id="description" v-model="product.description" rows="3" class="w-full" />
            </div>
            <div class="sm:col-span-2">
              <label for="image" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.image-url") }}</label>
              <div class="flex items-center gap-3">
                <product-image :src="product.image" size="md" />
                <InputText id="image" v-model="product.image" class="flex-1" placeholder="https://..." />
              </div>
            </div>
          </div>

          <div class="pt-2 flex gap-2">
            <Button type="submit" :label="t('common.save')" icon="pi pi-check" :loading="saving" />
            <Button :label="t('common.delete')" icon="pi pi-trash" severity="danger" outlined class="ml-auto" @click="handleDelete" />
          </div>
        </form>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <div class="text-sm text-gray-500">{{ t("products.barcode") }}</div>
            <div class="font-semibold text-gray-800">{{ product.barcode || "—" }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">{{ t("products.brand") }}</div>
            <div class="font-semibold text-gray-800">{{ product.brand?.name || "—" }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">{{ t("products.category") }}</div>
            <div class="font-semibold text-gray-800">{{ product.category?.name || "—" }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">{{ t("products.price") }}</div>
            <div class="font-semibold text-gray-800">{{ formatPrice(product.price) }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">{{ t("products.stock") }}</div>
            <div class="font-semibold text-gray-800">{{ product.stock }}</div>
          </div>
          <div v-if="product.description">
            <div class="text-sm text-gray-500">{{ t("product.description") }}</div>
            <div class="font-semibold text-gray-800 text-sm">{{ product.description }}</div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import Button from "primevue/button"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import Dropdown from "primevue/dropdown"
import Textarea from "primevue/textarea"
import Tag from "primevue/tag"
import { useToast } from "primevue/usetoast"
import ProductImage from "../../components/ProductImage.vue"
import api from "../../api"
import { user } from "../../composables/useAuth"
import { t, getLocale } from "../../i18n"

const route = useRoute()
const router = useRouter()
const toast = useToast()

const product = ref(null)
const loading = ref(false)
const saving = ref(false)
const categories = ref([])
const brands = ref([])

const isAdmin = computed(() => user.value?.role === "admin")

async function loadProduct() {
  loading.value = true
  try {
    const { data } = await api.get(`/products/${route.params.id}`)
    product.value = data.data
  } catch (error) {
    toast.add({ severity: "error", summary: t("product.detail.title"), detail: t("product.load-error"), life: 3000 })
  } finally {
    loading.value = false
  }
}

async function loadOptions() {
  const [catRes, brandRes] = await Promise.all([api.get("/categories"), api.get("/brands")])
  categories.value = catRes.data.data
  brands.value = brandRes.data.data
}

async function handleSave() {
  saving.value = true
  try {
    await api.put(`/products/${product.value.id}`, product.value)
    toast.add({ severity: "success", summary: t("product.detail.title"), detail: t("product.updated"), life: 3000 })
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : t("settings.error")
    toast.add({ severity: "error", summary: t("product.detail.title"), detail: msg, life: 4000 })
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  try {
    await api.delete(`/products/${product.value.id}`)
    toast.add({ severity: "success", summary: t("product.detail.title"), detail: t("product.deleted"), life: 3000 })
    router.push({ name: "products" })
  } catch (error) {
    toast.add({ severity: "error", summary: t("product.detail.title"), detail: t("settings.error"), life: 3000 })
  }
}

function formatPrice(value) {
  return new Intl.NumberFormat(getLocale() === "en" ? "en-US" : "tr-TR", { style: "currency", currency: "TRY" }).format(value)
}

onMounted(() => {
  loadProduct()
  if (isAdmin.value) {
    loadOptions()
  }
})
</script>

<style></style>