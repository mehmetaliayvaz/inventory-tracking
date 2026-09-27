<template>
  <DefaultLayout v-if="showLayout">
    <router-view />
  </DefaultLayout>
  <router-view v-else />
</template>

<script setup>
import { computed, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import DefaultLayout from "./layouts/DefaultLayout.vue"
import { loadStoredUser, fetchUser } from "./composables/useAuth"

const route = useRoute()
const router = useRouter()

const showLayout = computed(() => !route.meta.public)

onMounted(() => {
  loadStoredUser()
  if (localStorage.getItem("auth_token")) {
    fetchUser().catch(() => {})
  }
  window.addEventListener("auth:unauthorized", () => {
    if (!route.meta.public) {
      router.push({ name: "login" })
    }
  })
})
</script>

<style></style>