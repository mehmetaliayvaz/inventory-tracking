<template>
  <div class="w-[250px] bg-primary p-5 min-h-screen text-white flex flex-col justify-between">
    <div>
      <div class="text-xl font-bold py-1.5 px-3">
        <h1 class="">Stok-Takip</h1>
      </div>
      <hr class="mx-3 my-2" />
      <ul class="font-semibold space-y-1">
        <li v-for="menuItem in menu" :key="menuItem.name" class="py-1.5 px-3 rounded-lg" :class="isActive(menuItem.route) ? 'bg-white/20' : ''">
          <router-link :to="menuItem.route">
            {{ menuItem.name }}
          </router-link>
        </li>
      </ul>
    </div>

    <div>
      <div class="px-3 py-2 text-sm text-white/80 truncate" v-if="user">
        {{ user.name }}
      </div>
      <Button label="Çıkış Yap" class="w-full p-button-outlined" severity="secondary" @click="handleLogout" />
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue"
import { useRoute, useRouter } from "vue-router"
import Button from "primevue/button"
import { user, logout } from "../../composables/useAuth"

const route = useRoute()
const router = useRouter()

const menu = [
  {
    name: "Dashboard",
    icon: "fas fa-tachometer-alt",
    route: "/",
  },
  {
    name: "Ürünler",
    icon: "fas fa-box",
    route: "/products",
  },
  {
    name: "Satış Yap",
    icon: "fas fa-shopping-cart",
    route: "/sales",
  },
  {
    name: "Siparişler",
    icon: "fas fa-receipt",
    route: "/orders",
  },
  {
    name: "Ayarlar",
    icon: "fas fa-users",
    route: "/settings",
  },
]

function isActive(target) {
  if (target === "/") return route.path === "/"
  return route.path.startsWith(target)
}

async function handleLogout() {
  await logout()
  router.push({ name: "login" })
}
</script>

<style></style>