<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-gray-700">{{ t("dash.welcome") }}, {{ user?.name }}</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ formattedToday }}</p>
      </div>
      <div class="flex gap-2">
        <Button v-if="isAdmin" :label="t('dash.add-product')" icon="pi pi-plus" @click="router.push({ name: 'products.add' })" />
        <Button :label="t('dash.new-sale')" icon="pi pi-shopping-cart" class="p-button-outlined" @click="router.push({ name: 'sales' })" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
      <div v-for="card in statCards" :key="card.labelKey" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg flex items-center justify-center" :class="card.iconClass">
          <i :class="['pi', card.icon]"></i>
        </div>
        <div>
          <div class="text-sm text-gray-500">{{ t(card.labelKey) }}</div>
          <div class="text-2xl font-bold text-gray-800">{{ card.value }}</div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">{{ t("dash.recent-products") }}</h3>
          <router-link to="/products" class="text-sm text-amber-600 hover:underline font-medium">{{ t("dash.view-all") }}</router-link>
        </div>
        <div class="p-2">
          <div v-if="recentProducts.length === 0" class="text-gray-400 text-center py-8 text-sm">{{ t("dash.no-products") }}</div>
          <router-link
            v-for="product in recentProducts"
            :key="product.id"
            :to="{ name: 'products.detail', params: { id: product.id } }"
            class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50 rounded-lg transition"
          >
            <product-image :src="product.image" size="sm" />
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-gray-800 text-sm truncate">{{ product.name }}</div>
              <div class="text-xs text-gray-400">{{ product.barcode || "—" }}</div>
            </div>
            <div class="text-sm font-semibold text-gray-700">{{ formatPrice(product.price) }}</div>
          </router-link>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-800">{{ t("dash.recent-orders") }}</h3>
          <router-link to="/orders" class="text-sm text-amber-600 hover:underline font-medium">{{ t("dash.view-all") }}</router-link>
        </div>
        <div class="p-2">
          <div v-if="recentOrders.length === 0" class="text-gray-400 text-center py-8 text-sm">{{ t("dash.no-orders") }}</div>
          <div v-for="order in recentOrders" :key="order.id" class="flex items-center gap-3 px-3 py-2.5">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
              <i class="pi pi-receipt"></i>
            </div>
            <div class="flex-1">
              <div class="font-semibold text-gray-800 text-sm">#{{ order.id }}</div>
              <div class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</div>
            </div>
            <div class="text-sm font-semibold text-gray-700">{{ formatPrice(order.total) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { useRouter } from "vue-router"
import Button from "primevue/button"
import ProductImage from "../../components/ProductImage.vue"
import { user } from "../../composables/useAuth"
import { t, getLocale } from "../../i18n"
import api from "../../api"

const router = useRouter()

const stats = ref({
  totalProducts: 0,
  totalStock: 0,
  totalOrders: 0,
  stockValue: 0,
})
const recentProducts = ref([])
const recentOrders = ref([])

const isAdmin = computed(() => user.value?.role === "admin")

const statCards = computed(() => [
  {
    labelKey: "dash.total-products",
    icon: "pi-box",
    iconClass: "bg-blue-50 text-blue-600",
    value: stats.value.totalProducts,
  },
  {
    labelKey: "dash.total-stock",
    icon: "pi-database",
    iconClass: "bg-emerald-50 text-emerald-600",
    value: `${stats.value.totalStock} ${t("dash.unit")}`,
  },
  {
    labelKey: "dash.total-orders",
    icon: "pi-receipt",
    iconClass: "bg-amber-50 text-amber-600",
    value: stats.value.totalOrders,
  },
  {
    labelKey: "dash.stock-value",
    icon: "pi-wallet",
    iconClass: "bg-purple-50 text-purple-600",
    value: formatPrice(stats.value.stockValue),
  },
])

const formattedToday = computed(() =>
  new Intl.DateTimeFormat(getLocale() === "en" ? "en-US" : "tr-TR", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  }).format(new Date())
)

async function loadData() {
  const [productsRes, ordersRes] = await Promise.all([api.get("/products"), api.get("/orders")])

  const products = productsRes.data.data
  const orders = ordersRes.data.data

  recentProducts.value = products.slice(0, 5)
  recentOrders.value = orders.slice(0, 5)

  stats.value = {
    totalProducts: products.length,
    totalStock: products.reduce((sum, p) => sum + Number(p.stock), 0),
    totalOrders: orders.length,
    stockValue: products.reduce((sum, p) => sum + Number(p.price) * Number(p.stock), 0),
  }
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

onMounted(loadData)
</script>

<style></style>