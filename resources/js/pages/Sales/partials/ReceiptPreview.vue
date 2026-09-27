<template>
  <div class="receipt bg-white text-gray-900">
    <div class="text-center text-lg mb-2">
      <div class="text-2xl font-bold">STOK-TAKİP</div>
      <div>Sipariş Bilgisi</div>
      <div>#{{ order.id }}</div>
      <div>{{ formatDate(order.created_at) }}</div>
    </div>

    <div class="dashed-line"></div>

    <div v-for="item in order.items" :key="item.id" class="mb-4">
      <div class="text-xl font-bold">{{ item.name }}</div>
      <div class="flex justify-between text-lg font-semibold">
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
  width: 64mm;
}
.dashed-line {
  border-top: 1px dashed #9ca3af;
  margin: 8px 0;
}
</style>
