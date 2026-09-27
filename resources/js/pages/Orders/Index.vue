<template>
  <div class="page-wrapper">
    <div class="container">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Sipariş Geçmişi</h1>
        <p class="text-gray-500 text-sm mt-1">{{ orders.length }} sipariş</p>
      </div>

      <div class="bg-white shadow rounded-xl p-4">
        <div v-if="loading" class="py-10 text-center text-gray-500">Yükleniyor...</div>

        <DataTable v-else :value="orders" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]" dataKey="id">
          <Column field="id" header="No" style="width: 80px">
            <template #body="{ data }">#{{ data.id }}</template>
          </Column>
          <Column field="created_at" header="Tarih" style="width: 170px">
            <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
          </Column>
          <Column field="items" header="Ürünler" style="width: 150px">
            <template #body="{ data }">
              {{ data.items.length }} kalem ({{ totalQuantity(data.items) }} adet)
            </template>
          </Column>
          <Column field="total" header="Toplam" style="width: 130px">
            <template #body="{ data }">
              <span class="font-semibold">{{ formatPrice(data.total) }}</span>
            </template>
          </Column>
          <Column header="İşlem" style="width: 230px">
            <template #body="{ data }">
              <div class="flex gap-2">
                <Button label="Görüntüle" icon="pi pi-eye" size="small" @click="viewOrder(data)" />
                <Button label="Yazdır" icon="pi pi-print" size="small" outlined @click="printOrder(data)" />
              </div>
            </template>
          </Column>
        </DataTable>

        <div v-if="!loading && orders.length === 0" class="py-10 text-center text-gray-500">
          Henüz sipariş yok.
        </div>
      </div>
    </div>

    <Dialog v-model:visible="dialogVisible" header="Sipariş Detayı" modal :style="{ width: '90mm' }">
      <div v-if="selectedOrder" class="flex justify-center">
        <receipt-preview :order="selectedOrder" />
      </div>
      <div v-if="selectedOrder" class="mt-4">
        <Button label="Yazdır" icon="pi pi-print" class="w-full" @click="printOrder(selectedOrder)" />
      </div>
    </Dialog>

    <div v-if="printTarget" id="print-receipt" class="print-receipt">
      <receipt-preview :order="printTarget" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import Button from "primevue/button"
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Dialog from "primevue/dialog"
import { useToast } from "primevue/usetoast"
import api from "../../api"
import ReceiptPreview from "../Sales/partials/ReceiptPreview.vue"

const toast = useToast()

const orders = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const selectedOrder = ref(null)
const printTarget = ref(null)

async function loadOrders() {
  loading.value = true
  try {
    const { data } = await api.get("/orders")
    orders.value = data.data
  } catch (error) {
    toast.add({ severity: "error", summary: "Hata", detail: "Siparişler yüklenemedi.", life: 3000 })
  } finally {
    loading.value = false
  }
}

function viewOrder(order) {
  selectedOrder.value = order
  dialogVisible.value = true
}

function printOrder(order) {
  printTarget.value = order
  setTimeout(() => {
    window.print()
  }, 100)
}

function totalQuantity(items) {
  return items.reduce((sum, item) => sum + Number(item.quantity), 0)
}

function formatPrice(value) {
  return new Intl.NumberFormat("tr-TR", { style: "currency", currency: "TRY" }).format(value)
}

function formatDate(value) {
  return new Date(value).toLocaleDateString("tr-TR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}

onMounted(loadOrders)
</script>

<style></style>