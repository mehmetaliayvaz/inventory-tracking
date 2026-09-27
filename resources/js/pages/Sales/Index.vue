<template>
  <div>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
      <!-- Sol: ürün bul -->
      <div class="lg:col-span-3 space-y-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="pi pi-barcode text-amber-500"></i> {{ t("sales.add-product") }}
          </h3>

          <div>
            <label for="scan" class="block text-sm font-semibold mb-1.5 text-gray-600">{{ t("sales.scan-label") }}</label>
            <div class="flex gap-2">
              <InputText id="scan" v-model="scanCode" class="flex-1" :placeholder="t('sales.scan-placeholder')" @keydown.enter.prevent="scanBarcode" autofocus />
              <Button icon="pi pi-barcode" :label="t('sales.add')" @click="scanBarcode" />
            </div>
            <div v-if="scanMessage" class="text-sm mt-2" :class="scanMessage.type === 'error' ? 'text-red-600' : 'text-green-600'">
              <i :class="['pi mr-1', scanMessage.type === 'error' ? 'pi-times' : 'pi-check']"></i>{{ scanMessage.text }}
            </div>
          </div>

          <div class="mt-5">
            <label for="productSearch" class="block text-sm font-semibold mb-1.5 text-gray-600">{{ t("sales.search") }}</label>
            <div class="flex gap-2">
              <InputText id="productSearch" v-model="searchQuery" class="flex-1" :placeholder="t('sales.search-placeholder')" @keydown.enter.prevent="searchProducts" />
              <Button icon="pi pi-search" :label="t('common.search')" @click="searchProducts" />
            </div>

            <div v-if="searchResults.length" class="border border-gray-100 rounded-lg mt-3 divide-y max-h-72 overflow-auto">
              <button
                v-for="product in searchResults"
                :key="product.id"
                type="button"
                class="w-full flex items-center gap-3 p-3 hover:bg-gray-50 text-left transition"
                @click="addToCart(product)"
              >
                <product-image :src="product.image" size="sm" />
                <div class="flex-1 min-w-0">
                  <div class="font-semibold text-gray-800 text-sm truncate">{{ product.name }}</div>
                  <div class="text-xs text-gray-500">
                    {{ product.barcode || "—" }}
                    <span v-if="product.stock <= 0" class="text-red-500 font-semibold">({{ t("sales.stock-warning") }})</span>
                  </div>
                </div>
                <div class="text-sm font-semibold text-gray-700">{{ formatPrice(product.price) }}</div>
                <i class="pi pi-plus text-amber-500"></i>
              </button>
            </div>
            <div v-else-if="searchSearched" class="text-sm text-gray-500 mt-3">{{ t("sales.search-results") }}</div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <h3 class="font-semibold text-gray-800 mb-4">{{ t("sales.cart") }}</h3>

          <div v-if="cart.length === 0" class="text-gray-400 text-center py-10">
            <i class="pi pi-shopping-cart text-4xl text-gray-300 block mb-2"></i>
            {{ t("sales.cart-empty") }}
          </div>

          <div v-else class="divide-y divide-gray-100">
            <div v-for="(item, index) in cart" :key="item.productId" class="flex items-center gap-3 py-3">
              <div class="flex-1 min-w-0">
                <div class="font-semibold text-gray-800 text-sm truncate">{{ item.name }}</div>
                <div class="text-xs text-gray-500">{{ formatPrice(item.price) }} x {{ item.quantity }}</div>
              </div>
              <div class="flex items-center gap-1">
                <Button icon="pi pi-minus" class="p-button-text p-button-danger p-button-sm" @click="decreaseQuantity(index)" />
                <InputNumber v-model="item.quantity" :min="1" :useGrouping="false" style="width: 4rem" @update:modelValue="() => clampStock(index)" />
                <Button icon="pi pi-plus" class="p-button-text p-button-sm" @click="increaseQuantity(index)" />
              </div>
              <div class="w-24 text-right font-semibold text-gray-800">{{ formatPrice(item.price * item.quantity) }}</div>
              <Button icon="pi pi-times" class="p-button-text p-button-danger p-button-sm" @click="removeFromCart(index)" />
            </div>
          </div>

          <div v-if="cart.length" class="flex items-center justify-between border-t border-gray-100 pt-4 mt-2">
            <span class="font-bold text-gray-700">{{ t("sales.total") }}</span>
            <span class="font-bold text-2xl text-gray-800">{{ formatPrice(total) }}</span>
          </div>

          <div v-if="cart.length" class="mt-4">
            <Button :label="t('sales.create-order')" icon="pi pi-check" class="w-full" :loading="creating" @click="createOrder" />
          </div>
        </div>
      </div>

      <!-- Sağ: fiş önizleme -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 lg:sticky lg:top-24">
          <h3 class="font-semibold text-gray-800 mb-4">{{ t("sales.receipt-preview") }}</h3>
          <div class="flex justify-center border border-dashed border-gray-200 rounded-lg p-4 bg-gray-50">
            <receipt-preview v-if="lastOrder" :order="lastOrder" />
            <div v-else class="text-gray-400 text-center py-10 text-sm">
              <i class="pi pi-receipt text-3xl text-gray-300 block mb-2"></i>
              {{ t("sales.receipt-empty") }}
            </div>
          </div>
          <div v-if="lastOrder" class="mt-4 flex gap-2">
            <Button :label="t('common.print')" icon="pi pi-print" class="flex-1" @click="printReceipt" />
            <Button :label="t('sales.new-sale')" icon="pi pi-plus" outlined class="flex-1" @click="startNewSale" />
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
import ProductImage from "../../components/ProductImage.vue"
import api from "../../api"
import ReceiptPreview from "./partials/ReceiptPreview.vue"
import { useBarcodeScanner } from "../../composables/useBarcodeScanner"
import { t, getLocale } from "../../i18n"

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
  return new Intl.NumberFormat(getLocale() === "en" ? "en-US" : "tr-TR", { style: "currency", currency: "TRY" }).format(value)
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
      scanMessage.value = { type: "success", text: t("sales.added-to-cart", { name: match.name }) }
      scanCode.value = ""
    } else {
      scanMessage.value = { type: "error", text: t("sales.not-in-system") }
    }
  } catch (error) {
    scanMessage.value = { type: "error", text: t("sales.product-error") }
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
    toast.add({ severity: "error", summary: t("sales.title"), detail: t("sales.search-error"), life: 3000 })
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
    toast.add({ severity: "warn", summary: t("sales.title"), detail: t("sales.stock-warning"), life: 2500 })
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
    toast.add({ severity: "warn", summary: t("sales.title"), detail: t("sales.stock-warning"), life: 2500 })
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
    toast.add({ severity: "success", summary: t("sales.title"), detail: t("sales.order-created"), life: 3000 })
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : t("sales.order-error")
    toast.add({ severity: "error", summary: t("sales.title"), detail: msg, life: 4000 })
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