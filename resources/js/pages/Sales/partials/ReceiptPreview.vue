<template>
  <div
    class="receipt bg-white text-gray-900"
    style="width: 58mm; font-family: 'Courier New', monospace; font-size: 16px; line-height: 1.5; box-sizing: border-box"
  >
    <div class="text-center mb-2">
      <div style="font-size: 21px; font-weight: bold">{{ companyName }}</div>
      <div style="font-size: 15px">{{ t("receipt.title") }}</div>
      <div style="font-size: 14px">#{{ order.id }}</div>
      <div style="font-size: 14px">{{ formatDate(order.created_at) }}</div>
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

    <div class="flex justify-between font-bold" style="font-size: 16px">
      <span>{{ t("receipt.total") }}</span>
      <span>{{ formatPrice(order.total) }}</span>
    </div>

    <div class="dashed-line"></div>

    <div class="text-center" style="font-size: 13px; margin-top: 4px">
      {{ t("receipt.thanks") }}
    </div>
    <div class="text-center" style="font-size: 11px; margin-top: 2px; color: #6b7280">
      {{ t("receipt.no-cash-value") }}
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue"
import { t, getLocale } from "../../../i18n"

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
})

const companyName = computed(() => props.order?.company?.name || t("app.name"))

function formatPrice(value) {
  return new Intl.NumberFormat(getLocale() === "en" ? "en-US" : "tr-TR", { style: "currency", currency: "TRY" }).format(value)
}

function formatDate(value) {
  const date = new Date(value)
  const options = { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" }
  return new Intl.DateTimeFormat(getLocale() === "en" ? "en-US" : "tr-TR", options).format(date)
}
</script>

<style scoped>
.dashed-line {
  border-top: 1px dashed #9ca3af;
  margin: 8px 0;
}
</style>