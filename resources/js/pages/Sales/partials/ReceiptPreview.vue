<template>
  <div
    class="receipt bg-white text-gray-900 font-bold"
    style="width: 100%; max-width: 48mm; margin-left: 3mm; margin-right: auto; font-family: 'Courier New', monospace; font-size: 13.5px; line-height: 1.4; box-sizing: border-box"
  >
    <div class="text-center mb-2">
      <div style="font-size: 18px; font-weight: 800">{{ companyName }}</div>
      <div style="font-size: 13.5px">{{ t("receipt.title") }}</div>
      <div style="font-size: 12.5px">#{{ order.id }}</div>
      <div style="font-size: 12.5px">{{ formatDate(order.created_at) }}</div>
    </div>

    <div class="dashed-line"></div>

    <div v-for="item in order.items" :key="item.id" class="mb-1.5">
      <div class="break-words">{{ item.name }}</div>
      <div class="flex flex-wrap items-baseline gap-x-1.5">
        <span class="whitespace-nowrap">{{ item.quantity }} x {{ formatPrice(item.price) }}</span>
        <span class="whitespace-nowrap">= {{ formatPrice(item.price * item.quantity) }}</span>
      </div>
    </div>

    <div class="dashed-line"></div>

    <div class="flex flex-wrap items-baseline gap-x-1.5" style="font-size: 14.5px">
      <span class="whitespace-nowrap">{{ t("receipt.total") }}:</span>
      <span class="whitespace-nowrap">{{ formatPrice(order.total) }}</span>
    </div>

    <div class="dashed-line"></div>

    <div class="text-center" style="font-size: 12px; margin-top: 4px">
      {{ t("receipt.thanks") }}
    </div>
    <div class="text-center" style="font-size: 10px; margin-top: 2px; color: #6b7280">
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