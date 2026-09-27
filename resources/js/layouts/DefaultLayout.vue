<template>
  <div class="bg-gray-50 flex">
    <the-sidebar />
    <div class="flex-1">
      <Toast />
      <slot />
    </div>
  </div>
</template>

<script setup>
import Toast from "primevue/toast"
import TheSidebar from "./partials/TheSidebar.vue"
</script>