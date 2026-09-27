<template>
  <div class="page-wrapper">
    <div class="container">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500 text-sm mt-1">Hoş geldiniz, {{ user?.name }}</p>
      </div>

      <div class="grid grid-cols-4 gap-5 mb-8">
        <div class="bg-white shadow rounded-xl p-5">
          <div class="text-sm text-gray-500 mb-1">Toplam Ürün</div>
          <div class="text-2xl font-bold text-gray-800">{{ stats.totalProducts }}</div>
        </div>
        <div class="bg-white shadow rounded-xl p-5">
          <div class="text-sm text-gray-500 mb-1">Toplam Stok</div>
          <div class="text-2xl font-bold text-gray-800">{{ stats.totalStock }}</div>
        </div>
        <div class="bg-white shadow rounded-xl p-5">
          <div class="text-sm text-gray-500 mb-1">Toplam Sipariş</div>
          <div class="text-2xl font-bold text-gray-800">{{ stats.totalOrders }}</div>
        </div>
        <div class="bg-white shadow rounded-xl p-5">
          <div class="text-sm text-gray-500 mb-1">Stok Değeri</div>
          <div class="text-2xl font-bold text-gray-800">{{ formatPrice(stats.stockValue) }}</div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-5">
        <div class="bg-white shadow rounded-xl p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-700">Son Ürünler</h2>
            <router-link to="/products" class="text-blue-600 text-sm hover:underline">Tümünü Gör</router-link>
          </div>
          <div v-if="recentProducts.length === 0" class="text-gray-400 text-center py-6 text-sm">Henüz ürün yok.</div>
          <div v-else class="divide-y">
            <router-link
              v-for="product in recentProducts"
              :key="product.id"
              :to="{ name: 'products.detail', params: { id: product.id } }"
              class="flex items-center gap-3 py-3"
            >
              <img v-if="product.image" :src="product.image" class="w-9 h-9 object-cover rounded" />
              <div class="flex-1">
                <div class="font-semibold text-gray-800 text-sm">{{ product.name }}</div>
                <div class="text-xs text-gray-400">{{ product.barcode || "-" }}</div>
              </div>
              <div class="text-sm font-semibold">{{ formatPrice(product.price) }}</div>
            </router-link>
          </div>
        </div>

        <div class="bg-white shadow rounded-xl p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-700">Son Siparişler</h2>
            <div class="flex gap-3">
              <router-link to="/sales" class="text-blue-600 text-sm hover:underline">Satış Yap</router-link>
              <router-link to="/orders" class="text-blue-600 text-sm hover:underline">Tümünü Gör</router-link>
            </div>
          </div>
          <div v-if="recentOrders.length === 0" class="text-gray-400 text-center py-6 text-sm">Henüz sipariş yok.</div>
          <div v-else class="divide-y">
            <div v-for="order in recentOrders" :key="order.id" class="py-3">
              <div class="flex items-center justify-between">
                <div class="font-semibold text-gray-800 text-sm">#{{ order.id }} - {{ order.items.length }} ürün</div>
                <div class="text-sm font-semibold">{{ formatPrice(order.total) }}</div>
              </div>
              <div class="text-xs text-gray-400 mt-1">{{ formatDate(order.created_at) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { user } from "../../composables/useAuth"
import api from "../../api"

const stats = ref({
  totalProducts: 0,
  totalStock: 0,
  totalOrders: 0,
  stockValue: 0,
})
const recentProducts = ref([])
const recentOrders = ref([])

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
  return new Intl.NumberFormat("tr-TR", { style: "currency", currency: "TRY" }).format(value)
}

function formatDate(value) {
  return new Date(value).toLocaleDateString("tr-TR", { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" })
}

onMounted(loadData)
</script>

<style></style>