<template>
  <div class="receipt bg-white text-gray-900">
    <div class="text-center mb-2">
      <div style="font-size: 22px; font-weight: bold">STOK-TAKİP</div>
      <div>Satış Fişi</div>
      <div>#{{ order.id }}</div>
      <div>{{ formatDate(order.created_at) }}</div>
    </div>

    <div class="dashed-line"></div>

    <div v-for="item in order.items" :key="item.id" class="mb-1">
      <div class="font-semibold">{{ item.name }}</div>
      <div class="flex justify-between">
        <span>{{ item.quantity }} x {{ formatPrice(item.price) }}</span>
        <span>{{ formatPrice(item.price * item.quantity) }}</span>
      </div>
    </div>

    <div class="dashed-line"></div>

    <div class="flex justify-between font-bold" style="font-size: 20px">
      <span>TOPLAM</span>
      <span>{{ formatPrice(order.total) }}</span>
    </div>

    <div class="dashed-line"></div>

    <div class="text-center" style="font-size: 16px; margin-top: 4px">Bizi tercih ettiğiniz için teşekkürler!</div>
  </div>
</template>

<script setup>
defineProps({
  order: {
    type: Object,
    required: true,
  },
})

function formatPrice(value) {
  return new Intl.NumberFormat("tr-TR", { style: "currency", currency: "TRY" }).format(value)
}

function formatDate(value) {
  const date = new Date(value)
  const options = { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" }
  return new Intl.DateTimeFormat("tr-TR", options).format(date)
}
</script>

<style scoped>
.receipt {
  width: 100%;
}
.dashed-line {
  border-top: 1px dashed #9ca3af;
  margin: 8px 0;
}
</style>
