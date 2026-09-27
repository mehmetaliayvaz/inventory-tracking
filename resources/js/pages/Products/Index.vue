<template>
  <div class="page-wrapper">
    <div class="container">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Ürünler</h1>
          <p class="text-gray-500 text-sm mt-1">{{ products.length }} ürün</p>
        </div>
        <Button label="Ürün Ekle" icon="pi pi-plus" @click="router.push({ name: 'products.add' })" />
      </div>

      <div class="bg-white shadow rounded-xl p-4">
        <div class="flex items-center gap-3 mb-4">
          <div class="relative">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <InputText v-model="searchQuery" placeholder="Ürün adı, barkod veya marka ara..." class="w-96 pl-9" />
          </div>
          <Button v-if="searchQuery" label="Ara" @click="loadProducts" />
        </div>

        <div v-if="loading" class="py-10 text-center text-gray-500">Yükleniyor...</div>

        <DataTable v-else :value="products" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]" :loading="loading" dataKey="id" @row-click="(event) => openProduct(event.data.id)">
          <Column field="image" header="" style="width: 70px">
            <template #body="{ data }">
              <img v-if="data.image" :src="data.image" alt="" class="w-10 h-10 object-cover rounded" />
              <div v-else class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                <i class="pi pi-box"></i>
              </div>
            </template>
          </Column>
          <Column field="name" header="Ürün Adı">
            <template #body="{ data }">
              <span class="font-semibold text-gray-800">{{ data.name }}</span>
              <span v-if="data.brand" class="block text-xs text-gray-400">{{ data.brand }}</span>
            </template>
          </Column>
          <Column field="barcode" header="Barkod" style="width: 140px" />
          <Column field="price" header="Fiyat" style="width: 110px">
            <template #body="{ data }">
              {{ formatPrice(data.price) }}
            </template>
          </Column>
          <Column field="stock" header="Stok" style="width: 150px">
            <template #body="{ data }">
              <div class="flex items-center gap-2" @click.stop>
                <InputNumber v-model="data.stock" :useGrouping="false" class="w-24" inputId="integeronly" @change="() => updateStock(data)" />
                <i v-if="data.stockSaving" class="pi pi-spin pi-spinner text-gray-400"></i>
                <i v-else-if="data.stockSaved" class="pi pi-check text-green-500"></i>
              </div>
            </template>
          </Column>
          <Column field="category" header="Kategori" style="width: 130px">
            <template #body="{ data }">
              <Tag v-if="data.category" :value="data.category" severity="secondary" />
              <span v-else class="text-gray-300">-</span>
            </template>
          </Column>
          <Column header="" style="width: 60px">
            <template #body="{ data }">
              <Button icon="pi pi-angle-right" class="p-button-text" @click.stop="openProduct(data.id)" />
            </template>
          </Column>
        </DataTable>

        <div v-if="!loading && products.length === 0" class="py-10 text-center text-gray-500">
          Ürün bulunamadı.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import Button from "primevue/button"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Tag from "primevue/tag"
import { useToast } from "primevue/usetoast"
import Toast from "primevue/toast"
import api from "../../api"

const router = useRouter()
const toast = useToast()

const products = ref([])
const loading = ref(false)
const searchQuery = ref("")

async function loadProducts() {
  loading.value = true
  try {
    const { data } = await api.get("/products", { params: { q: searchQuery.value } })
    products.value = data.data.map((p) => ({ ...p, stockSaving: false, stockSaved: false }))
  } catch (error) {
    toast.add({ severity: "error", summary: "Hata", detail: "Ürünler yüklenemedi.", life: 3000 })
  } finally {
    loading.value = false
  }
}

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
    toast.add({ severity: "error", summary: "Hata", detail: "Stok güncellenemedi.", life: 3000 })
  } finally {
    product.stockSaving = false
  }
}

function openProduct(id) {
  router.push({ name: "products.detail", params: { id } })
}

function formatPrice(value) {
  return new Intl.NumberFormat("tr-TR", { style: "currency", currency: "TRY" }).format(value)
}

onMounted(loadProducts)
</script>

<style></style>