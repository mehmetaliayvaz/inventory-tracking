<template>
  <div class="flex min-h-screen bg-gray-100">
    <the-sidebar :open="sidebarOpen" @close="sidebarOpen = false" />

    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black/50 z-30 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <div class="flex-1 flex flex-col min-w-0">
      <header class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3 sm:py-4 sticky top-0 z-20">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-2 min-w-0">
            <Button icon="pi pi-bars" class="lg:hidden p-button-rounded p-button-text p-button-sm" @click="sidebarOpen = true" />
            <h1 class="text-lg sm:text-xl font-bold text-gray-800 truncate">{{ title }}</h1>
          </div>
          <div class="flex items-center gap-3 shrink-0">
            <Tag :value="roleLabel" :severity="isAdmin ? 'warn' : 'secondary'" class="hidden sm:inline-flex" />
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                {{ initials }}
              </div>
              <span class="hidden sm:inline text-sm font-semibold text-gray-700">{{ user?.name }}</span>
            </div>
          </div>
        </div>
      </header>

      <main class="flex-1 px-4 sm:px-6 py-4 sm:py-6">
        <Toast />
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue"
import { useRoute } from "vue-router"
import Button from "primevue/button"
import Tag from "primevue/tag"
import Toast from "primevue/toast"
import TheSidebar from "./partials/TheSidebar.vue"
import { user } from "../composables/useAuth"
import { t } from "../i18n"

const route = useRoute()

const sidebarOpen = ref(false)

const isAdmin = computed(() => user.value?.role === "admin")
const roleLabel = computed(() => t(isAdmin.value ? "role.admin" : "role.member"))
const initials = computed(() => {
  const name = user.value?.name || ""
  return name
    .split(" ")
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase()
})

const title = computed(() => t(route.meta.titleKey || "app.name"))
</script>

<style></style>