<template>
  <DefaultLayout v-if="showLayout">
    <router-view />
  </DefaultLayout>
  <router-view v-else />
</template>

<script setup>
import { computed, onMounted, watch } from "vue"
import { useRoute, useRouter } from "vue-router"
import DefaultLayout from "./layouts/DefaultLayout.vue"
import { loadStoredUser, fetchUser } from "./composables/useAuth"
import { t, locale } from "./i18n"

const route = useRoute()
const router = useRouter()

const showLayout = computed(() => !route.meta.public)

function applyTitle() {
  const appName = t("app.name")
  const pageTitle = route.meta.titleKey ? t(route.meta.titleKey) : ""
  document.title = pageTitle && pageTitle !== appName ? `${pageTitle} · ${appName}` : appName
}

watch([locale, route], applyTitle, { immediate: true })

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