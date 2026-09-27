<template>
  <div class="flex min-h-screen bg-gray-100">
    <the-sidebar />

    <div class="flex-1 flex flex-col min-w-0">
      <header class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-20">
        <div class="flex items-center justify-between gap-4">
          <div class="min-w-0">
            <h1 class="text-xl font-bold text-gray-800 truncate">{{ title }}</h1>
          </div>
          <div class="flex items-center gap-3 shrink-0">
            <Tag :value="roleLabel" :severity="isAdmin ? 'warn' : 'secondary'" />
            <div class="hidden sm:flex items-center gap-2">
              <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                {{ initials }}
              </div>
              <span class="text-sm font-semibold text-gray-700">{{ user?.name }}</span>
            </div>
          </div>
        </div>
      </header>

      <main class="flex-1 px-6 py-6">
        <Toast />
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue"
import { useRoute } from "vue-router"
import Tag from "primevue/tag"
import Toast from "primevue/toast"
import TheSidebar from "./partials/TheSidebar.vue"
import { user } from "../composables/useAuth"
import { t } from "../i18n"

const route = useRoute()

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