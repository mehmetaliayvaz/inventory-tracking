<template>
  <div class="page-wrapper">
    <div class="container">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Satış Yap</h1>

      <div class="grid grid-cols-5 gap-6">
        <!-- Sol: ürün bul -->
        <div class="col-span-3 space-y-5">
          <div class="bg-white shadow rounded-xl p-5">
            <h2 class="font-semibold text-gray-700 mb-3">Ürün Ekle</h2>

            <div>
              <label for="scan" class="block text-sm font-semibold mb-1 text-gray-600">
                Barkod Okut / Yaz
              </label>
              <div class="flex gap-2">
                <InputText id="scan" v-model="scanCode" class="flex-1" placeholder="Barkodu okutun..." @keydown.enter.prevent="scanBarcode" autofocus />
                <Button icon="pi pi-barcode" label="Ekle" @click="scanBarcode" />
              </div>
              <div v-if="scanMessage" class="text-sm mt-2" :class="scanMessage.type === 'error' ? 'text-red-600' : 'text-green-600'">
                {{ scanMessage.text }}
              </div>
            </div>

            <div class="mt-5">
              <label for="productSearch" class="block text-sm font-semibold mb-1 text-gray-600">
                Veya Ürün Ara
              </label>
              <div class="flex gap-2">
                <InputText id="productSearch" v-model="searchQuery" class="flex-1" placeholder="Ürün adı, marka veya barkod ara..." @keydown.enter.prevent="searchProducts" />
                <Button icon="pi pi-search" label="Ara" @click="searchProducts" />
              </div>

              <div v-if="searchResults.length" class="border rounded-lg mt-3 divide-y max-h-72 overflow-auto">
                <button
                  v-for="product in searchResults"
                  :key="product.id"
                  type="button"
                  class="w-full flex items-center gap-3 p-3 hover:bg-gray-50 text-left"
                  @click="addToCart(product)"
                >
                  <img v-if="product.image" :src="product.image" class="w-9 h-9 object-cover rounded" />
                  <div class="flex-1">
                    <div class="font-semibold text-gray-800 text-sm">{{ product.name }}</div>
                    <div class="text-xs text-gray-500">
                      {{ product.barcode || "-" }}
                      <span v-if="product.stock <= 0" class="text-red-500 font-semibold">(Stok yok)</span>
                    </div>
                  </div>
                  <div class="text-sm font-semibold">{{ formatPrice(product.price) }}</div>
                  <i class="pi pi-plus text-blue-500"></i>
                </button>
              </div>
              <div v-else-if="searchSearched" class="text-sm text-gray-500 mt-3">Sonuç bulunamadı.</div>
            </div>
          </div>

          <div class="bg-white shadow rounded-xl p-5">
            <h2 class="font-semibold text-gray-700 mb-3">Sepetteki Ürünler</h2>

            <div v-if="cart.length === 0" class="text-gray-400 text-center py-8">
              Sepet boş. Ürün ekleyin.
            </div>

            <div v-else class="divide-y">
              <div v-for="(item, index) in cart" :key="item.productId" class="flex items-center gap-3 py-3">
                <div class="flex-1">
                  <div class="font-semibold text-gray-800 text-sm">{{ item.name }}</div>
                  <div class="text-xs text-gray-500">
                    {{ formatPrice(item.price) }} x {{ item.quantity }}
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <Button icon="pi pi-minus" class="p-button-text p-button-danger" style="width: 2rem; height: 2rem" @click="decreaseQuantity(index)" />
                  <InputNumber v-model="item.quantity" :min="1" :useGrouping="false" style="width: 4.5rem" @update:modelValue="() => clampStock(index)" />
                  <Button icon="pi pi-plus" class="p-button-text" style="width: 2rem; height: 2rem" @click="increaseQuantity(index)" />
                </div>
                <div class="w-24 text-right font-semibold">{{ formatPrice(item.price * item.quantity) }}</div>
                <Button icon="pi pi-times" class="p-button-text p-button-danger" style="width: 2rem; height: 2rem" @click="removeFromCart(index)" />
              </div>
            </div>

            <div v-if="cart.length" class="flex items-center justify-between border-t pt-4 mt-2">
              <span class="font-bold text-gray-700">Toplam</span>
              <span class="font-bold text-xl text-gray-800">{{ formatPrice(total) }}</span>
            </div>

            <div v-if="cart.length" class="mt-4">
              <Button label="Siparişi Oluştur" icon="pi pi-check" class="w-full" :loading="creating" @click="createOrder" />
            </div>
          </div>
        </div>

        <!-- Sağ: önizleme -->
        <div class="col-span-2">
          <div class="bg-white shadow rounded-xl p-5 sticky top-6">
            <h2 class="font-semibold text-gray-700 mb-3">Fiş Önizleme</h2>
            <receipt-preview v-if="lastOrder" :order="lastOrder" />
            <div v-else class="text-gray-400 text-center py-10 text-sm">
              Sipariş oluşturduktan sonra fiş burada görünecek.
            </div>
            <div v-if="lastOrder" class="mt-4 flex gap-2">
              <Button label="Yazdır" icon="pi pi-print" class="flex-1" @click="printReceipt" />
              <Button label="Yeni Satış" icon="pi pi-plus" outlined class="flex-1" @click="startNewSale" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Yazdırma alanı -->
    <div v-if="lastOrder" id="print-receipt" class="print-receipt">
      <receipt-preview :order="lastOrder" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue"
import Button from "primevue/button"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import { useToast } from "primevue/usetoast"
import api from "../../api"
import ReceiptPreview from "./partials/ReceiptPreview.vue"
import { useBarcodeScanner } from "../../composables/useBarcodeScanner"

const toast = useToast()

const scanCode = ref("")
const scanMessage = ref(null)
const searchQuery = ref("")
const searchResults = ref([])
const searchSearched = ref(false)

const cart = ref([])
const creating = ref(false)
const lastOrder = ref(null)

const total = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0))

function formatPrice(value) {
  return new Intl.NumberFormat("tr-TR", { style: "currency", currency: "TRY" }).format(value)
}

async function scanBarcode() {
  const code = String(scanCode.value || "").trim()
  if (!code) return

  scanMessage.value = null
  try {
    const { data } = await api.get("/products", { params: { q: code } })
    const match = data.data.find((p) => p.barcode === code)
    if (match) {
      addToCart(match)
      scanMessage.value = { type: "success", text: `"${match.name}" sepete eklendi.` }
      scanCode.value = ""
    } else {
      scanMessage.value = { type: "error", text: "Bu barkoda ait ürün sistemde bulunamadı." }
    }
  } catch (error) {
    scanMessage.value = { type: "error", text: "Ürün aranırken hata oluştu." }
  }
}

useBarcodeScanner((code) => {
  scanCode.value = code
  scanBarcode()
})

async function searchProducts() {
  const q = String(searchQuery.value || "").trim()
  if (!q) return
  searchSearched.value = false
  try {
    const { data } = await api.get("/products", { params: { q } })
    searchResults.value = data.data
    searchSearched.value = true
  } catch (error) {
    toast.add({ severity: "error", summary: "Hata", detail: "Arama yapılamadı.", life: 3000 })
  }
}

function addToCart(product) {
  const existing = cart.value.find((item) => item.productId === product.id)
  if (existing) {
    existing.quantity += 1
    clampStockByIndex(cart.value.indexOf(existing))
  } else {
    cart.value.push({
      productId: product.id,
      name: product.name,
      barcode: product.barcode,
      price: Number(product.price),
      quantity: 1,
      maxStock: product.stock,
    })
  }
}

function increaseQuantity(index) {
  const item = cart.value[index]
  if (item.maxStock > 0 && item.quantity >= item.maxStock) {
    toast.add({ severity: "warn", summary: "Uyarı", detail: "Yeterli stok yok.", life: 2500 })
    return
  }
  item.quantity += 1
}

function decreaseQuantity(index) {
  const item = cart.value[index]
  if (item.quantity > 1) {
    item.quantity -= 1
  }
}

function clampStock(index) {
  const item = cart.value[index]
  if (!item) return
  if (item.quantity < 1) item.quantity = 1
  if (item.maxStock > 0 && item.quantity > item.maxStock) {
    item.quantity = item.maxStock
    toast.add({ severity: "warn", summary: "Uyarı", detail: "Yeterli stok yok.", life: 2500 })
  }
}

function clampStockByIndex(index) {
  clampStock(index)
}

function removeFromCart(index) {
  cart.value.splice(index, 1)
}

async function createOrder() {
  creating.value = true
  try {
    const payload = {
      items: cart.value.map((item) => ({
        product_id: item.productId,
        name: item.name,
        barcode: item.barcode,
        price: item.price,
        quantity: item.quantity,
      })),
    }
    const { data } = await api.post("/orders", payload)
    lastOrder.value = data.data
    cart.value = []
    scanCode.value = ""
    searchResults.value = []
    toast.add({ severity: "success", summary: "Başarılı", detail: "Sipariş oluşturuldu.", life: 3000 })
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : "Sipariş oluşturulamadı."
    toast.add({ severity: "error", summary: "Hata", detail: msg, life: 4000 })
  } finally {
    creating.value = false
  }
}

function printReceipt() {
  window.print()
}

function startNewSale() {
  lastOrder.value = null
}
</script>

<style></style>