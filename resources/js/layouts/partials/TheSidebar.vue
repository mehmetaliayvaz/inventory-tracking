<template>
  <aside
    class="w-64 bg-slate-900 text-white flex flex-col shrink-0 h-screen fixed inset-y-0 left-0 z-40 transition-transform duration-200 lg:sticky lg:top-0 lg:translate-x-0"
    :class="open ? 'translate-x-0' : '-translate-x-full'"
  >
    <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10 shrink-0">
      <div class="w-9 h-9 rounded-lg bg-amber-400 flex items-center justify-center text-slate-900 font-extrabold text-lg shrink-0">{{ appInitial }}</div>
      <div class="min-w-0">
        <div class="font-bold text-lg tracking-tight truncate">{{ t("app.name") }}</div>
        <div v-if="user?.company" class="text-xs text-gray-400 truncate">{{ user.company.name }}</div>
      </div>
    </div>

    <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
      <router-link
        v-for="item in menu"
        :key="item.route"
        :to="item.route"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
        :class="isActive(item.route) ? 'bg-amber-400 text-slate-900' : 'text-gray-300 hover:bg-white/10 hover:text-white'"
        @click="handleNavigate"
      >
        <i :class="['pi', item.icon, 'text-base']"></i>
        <span>{{ t(item.titleKey) }}</span>
      </router-link>
    </nav>

    <div class="p-4 border-t border-white/10 shrink-0">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center font-bold text-sm shrink-0">
          {{ initials }}
        </div>
        <div class="min-w-0">
          <div class="text-sm font-semibold truncate">{{ user?.name }}</div>
          <div class="text-xs text-amber-300">{{ t(isAdmin ? "role.admin" : "role.member") }}</div>
        </div>
      </div>
      <Button
        :label="t('menu.logout')"
        icon="pi pi-sign-out"
        class="w-full p-button-outlined p-button-sm"
        severity="secondary"
        @click="handleLogout"
      />
    </div>
  </aside>
</template>

<script setup>
import { computed } from "vue"
import { useRoute, useRouter } from "vue-router"
import Button from "primevue/button"
import { user, logout } from "../../composables/useAuth"
import { t } from "../../i18n"

const props = defineProps({
  open: { type: Boolean, default: false },
})
const emit = defineEmits(["close"])

const route = useRoute()
const router = useRouter()

const isAdmin = computed(() => user.value?.role === "admin")

const appInitial = computed(() => (t("app.name").trim().charAt(0) || "E").toUpperCase())

const initials = computed(() => {
  const name = user.value?.name || ""
  return name
    .split(" ")
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase()
})

const menu = computed(() => {
  const items = [
    { titleKey: "menu.dashboard", icon: "pi-home", route: "/" },
    { titleKey: "menu.products", icon: "pi-box", route: "/products" },
    { titleKey: "menu.sales", icon: "pi-shopping-cart", route: "/sales" },
    { titleKey: "menu.orders", icon: "pi-receipt", route: "/orders" },
  ]
  if (isAdmin.value) {
    items.push({ titleKey: "menu.categories", icon: "pi-tags", route: "/categories" })
    items.push({ titleKey: "menu.brands", icon: "pi-bookmark", route: "/brands" })
  }
  items.push({ titleKey: "menu.settings", icon: "pi-cog", route: "/settings" })
  return items
})

function isActive(target) {
  if (target === "/") return route.path === "/"
  return route.path.startsWith(target)
}

function handleNavigate() {
  emit("close")
}

async function handleLogout() {
  await logout()
  emit("close")
  router.push({ name: "login" })
}
</script>

<style></style>