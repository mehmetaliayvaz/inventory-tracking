<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h2 class="text-lg font-semibold text-gray-700">{{ t("products.title") }}</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ t("products.count", { count: products.length }) }}</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <Button v-if="isAdmin" :label="t('products.add')" icon="pi pi-plus" class="w-full sm:w-auto" @click="router.push({ name: 'products.add' })" />
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div class="p-4 border-b border-gray-100">
        <div class="relative max-w-md">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
          <InputText v-model="searchQuery" :placeholder="t('products.search')" class="w-full pl-9" @keydown.enter.prevent="loadProducts" />
        </div>
      </div>

      <div v-if="loading" class="py-16 text-center text-gray-500">
        <i class="pi pi-spin pi-spinner mr-2"></i>{{ t("common.loading") }}
      </div>

      <div v-else class="overflow-x-auto">
        <DataTable
          :value="products"
          paginator
          :rows="10"
          :rowsPerPageOptions="[10, 20, 50]"
          dataKey="id"
          class="p-datatable-sm"
          @row-click="(event) => openProduct(event.data.id)"
        >
        <Column field="image" header="" style="width: 70px">
          <template #body="{ data }">
            <product-image :src="data.image" size="sm" />
          </template>
        </Column>
        <Column :header="t('products.name')">
          <template #body="{ data }">
            <span class="font-semibold text-gray-800">{{ data.name }}</span>
            <span v-if="data.brand" class="block text-xs text-gray-400">{{ data.brand?.name }}</span>
          </template>
        </Column>
        <Column :header="t('products.barcode')" style="width: 150px">
          <template #body="{ data }">
            <span class="text-gray-600 text-sm">{{ data.barcode || "—" }}</span>
          </template>
        </Column>
        <Column :header="t('products.category')" style="width: 130px">
          <template #body="{ data }">
            <Tag v-if="data.category" :value="data.category.name" severity="secondary" />
            <span v-else class="text-gray-300">{{ t("common.none") }}</span>
          </template>
        </Column>
        <Column :header="t('products.price')" style="width: 120px" :sortable="true">
          <template #body="{ data }">
            <span class="font-medium">{{ formatPrice(data.price) }}</span>
          </template>
        </Column>
        <Column :header="t('products.stock')" style="width: 160px" :sortable="true">
          <template #body="{ data }">
            <div class="flex items-center gap-2" @click.stop>
              <InputNumber v-if="isAdmin" v-model="data.stock" :useGrouping="false" class="w-24" @change="() => updateStock(data)" />
              <span v-else class="font-semibold" :class="data.stock === 0 ? 'text-red-500' : 'text-gray-800'">{{ data.stock }}</span>
              <i v-if="data.stockSaving" class="pi pi-spin pi-spinner text-gray-400"></i>
              <i v-else-if="data.stockSaved" class="pi pi-check text-green-500"></i>
            </div>
          </template>
        </Column>
        <Column :header="t('common.actions')" style="width: 90px">
          <template #body="{ data }">
            <div class="flex gap-1" @click.stop>
              <Button icon="pi pi-eye" class="p-button-text p-button-sm" @click="openProduct(data.id)" />
              <Button v-if="isAdmin" icon="pi pi-trash" class="p-button-text p-button-sm p-button-danger" @click="deleteProduct(data)" />
            </div>
          </template>
        </Column>
        </DataTable>
      </div>

      <div v-if="!loading && products.length === 0" class="py-16 text-center text-gray-500">
        <i class="pi pi-box text-4xl text-gray-300 block mb-3"></i>
        {{ t("products.empty") }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { useRouter } from "vue-router"
import Button from "primevue/button"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Tag from "primevue/tag"
import { useToast } from "primevue/usetoast"
import ProductImage from "../../components/ProductImage.vue"
import api from "../../api"
import { useBarcodeScanner } from "../../composables/useBarcodeScanner"
import { user } from "../../composables/useAuth"
import { t, getLocale } from "../../i18n"

const router = useRouter()
const toast = useToast()

const products = ref([])
const loading = ref(false)
const searchQuery = ref("")

const isAdmin = computed(() => user.value?.role === "admin")

async function loadProducts() {
  loading.value = true
  try {
    const { data } = await api.get("/products", { params: { q: searchQuery.value } })
    products.value = data.data.map((p) => ({ ...p, stockSaving: false, stockSaved: false }))
  } catch (error) {
    toast.add({ severity: "error", summary: t("products.title"), detail: t("products.load-error"), life: 3000 })
  } finally {
    loading.value = false
  }
}

useBarcodeScanner((code) => {
  searchQuery.value = code
  loadProducts()
})

async function updateStock(product) {
  product.stockSaving = true
  product.stockSaved = false
  try {
    await api.put(`/products/${product.id}`, { stock: product.stock })
    product.stockSaved = true
    setTimeout(() => {
      product.stockSaved = false
    }, 1500)
  } catch (error) {
    toast.add({ severity: "error", summary: t("products.title"), detail: t("products.stock-error"), life: 3000 })
  } finally {
    product.stockSaving = false
  }
}

async function deleteProduct(product) {
  try {
    await api.delete(`/products/${product.id}`)
    toast.add({ severity: "success", summary: t("products.title"), detail: t("product.deleted"), life: 3000 })
    loadProducts()
  } catch (error) {
    toast.add({ severity: "error", summary: t("products.title"), detail: t("settings.error"), life: 3000 })
  }
}

function openProduct(id) {
  router.push({ name: "products.detail", params: { id } })
}

function formatPrice(value) {
  return new Intl.NumberFormat(getLocale() === "en" ? "en-US" : "tr-TR", { style: "currency", currency: "TRY" }).format(value)
}

onMounted(loadProducts)
</script>

<style></style>