<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-lg font-semibold text-gray-700">{{ t("orders.title") }}</h2>
      <p class="text-sm text-gray-500 mt-0.5">{{ t("orders.count", { count: orders.length }) }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div v-if="loading" class="py-16 text-center text-gray-500">
        <i class="pi pi-spin pi-spinner mr-2"></i>{{ t("common.loading") }}
      </div>

      <DataTable v-else :value="orders" paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]" dataKey="id">
        <Column field="id" :header="t('orders.no')" style="width: 90px">
          <template #body="{ data }">
            <span class="font-semibold text-gray-800">#{{ data.id }}</span>
          </template>
        </Column>
        <Column field="created_at" :header="t('orders.date')" style="width: 180px">
          <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
        </Column>
        <Column field="items" :header="t('orders.items')" style="width: 170px">
          <template #body="{ data }">
            {{ t("orders.items-detail", { items: data.items.length, quantity: totalQuantity(data.items) }) }}
          </template>
        </Column>
        <Column field="total" :header="t('orders.total')" style="width: 130px">
          <template #body="{ data }">
            <span class="font-semibold text-gray-800">{{ formatPrice(data.total) }}</span>
          </template>
        </Column>
        <Column :header="t('common.actions')" style="width: 220px">
          <template #body="{ data }">
            <div class="flex gap-2">
              <Button :label="t('orders.view')" icon="pi pi-eye" size="small" @click="viewOrder(data)" />
              <Button :label="t('orders.print')" icon="pi pi-print" size="small" outlined @click="printOrder(data)" />
            </div>
          </template>
        </Column>
      </DataTable>

      <div v-if="!loading && orders.length === 0" class="py-16 text-center text-gray-500">
        <i class="pi pi-receipt text-4xl text-gray-300 block mb-3"></i>
        {{ t("orders.empty") }}
      </div>
    </div>

    <Dialog v-model:visible="dialogVisible" :header="t('orders.detail')" modal :style="{ width: '90mm' }">
      <div v-if="selectedOrder" class="flex justify-center">
        <receipt-preview :order="selectedOrder" />
      </div>
      <div v-if="selectedOrder" class="mt-4">
        <Button :label="t('orders.print')" icon="pi pi-print" class="w-full" @click="printOrder(selectedOrder)" />
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
import { t, getLocale } from "../../i18n"

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
    toast.add({ severity: "error", summary: t("orders.title"), detail: t("orders.load-error"), life: 3000 })
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
  return new Intl.NumberFormat(getLocale() === "en" ? "en-US" : "tr-TR", { style: "currency", currency: "TRY" }).format(value)
}

function formatDate(value) {
  return new Date(value).toLocaleDateString(getLocale() === "en" ? "en-US" : "tr-TR", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}

onMounted(loadOrders)
</script>

<style></style>