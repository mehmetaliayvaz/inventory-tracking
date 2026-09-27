<template>
  <div class="max-w-4xl space-y-6">
    <div>
      <h2 class="text-lg font-semibold text-gray-700">{{ t("settings.title") }}</h2>
    </div>

    <!-- Dil seçimi -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <h3 class="font-semibold text-gray-800 mb-1">{{ t("settings.language") }}</h3>
      <p class="text-sm text-gray-500 mb-4">{{ t("settings.language-hint") }}</p>
      <SelectButton v-model="selectedLocale" :options="languageOptions" optionLabel="label" optionValue="value" @change="onLocaleChange" />
    </div>

    <!-- Üye yönetimi (admin) -->
    <div v-if="isAdmin" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-center justify-between mb-1">
        <h3 class="font-semibold text-gray-800">{{ t("settings.team") }}</h3>
        <Button :label="t('settings.member.add')" icon="pi pi-user-plus" @click="openMemberDialog()" />
      </div>
      <p class="text-sm text-gray-500 mb-4">{{ t("settings.team-hint") }}</p>

      <div v-if="loading" class="py-10 text-center text-gray-500">
        <i class="pi pi-spin pi-spinner mr-2"></i>{{ t("common.loading") }}
      </div>

      <DataTable v-else :value="members" dataKey="id" class="p-datatable-sm">
        <Column field="name" :header="t('settings.member.name')">
          <template #body="{ data }">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                {{ initials(data.name) }}
              </div>
              <div>
                <div class="font-semibold text-gray-800">{{ data.name }}</div>
                <div class="text-xs text-gray-400">{{ data.email }}</div>
              </div>
            </div>
          </template>
        </Column>
        <Column :header="t('settings.member.role')" style="width: 160px">
          <template #body="{ data }">
            <Dropdown
              v-model="data.role"
              :options="roleOptions"
              optionLabel="label"
              optionValue="value"
              class="w-full"
              :disabled="data.id === user?.id"
              @change="() => updateRole(data)"
            />
          </template>
        </Column>
        <Column :header="t('common.actions')" style="width: 80px">
          <template #body="{ data }">
            <Button
              icon="pi pi-trash"
              class="p-button-text p-button-sm p-button-danger"
              :disabled="data.id === user?.id"
              @click="removeMember(data)"
            />
          </template>
        </Column>
      </DataTable>

      <div v-if="!loading && members.length === 0" class="py-10 text-center text-gray-500">
        {{ t("settings.member.empty") }}
      </div>
    </div>

    <!-- Üye ekleme dialogu -->
    <Dialog v-model:visible="memberDialogVisible" :header="t('settings.member.add')" modal :style="{ width: '440px' }">
      <form @submit.prevent="saveMember" class="space-y-4">
        <div>
          <label for="member-name" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("settings.member.name") }}</label>
          <InputText id="member-name" v-model="memberForm.name" class="w-full" autofocus />
        </div>
        <div>
          <label for="member-email" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("settings.member.email") }}</label>
          <InputText id="member-email" v-model="memberForm.email" type="email" class="w-full" />
        </div>
        <div>
          <label for="member-password" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("settings.member.password") }}</label>
          <Password id="member-password" v-model="memberForm.password" :feedback="false" class="w-full" toggleMask />
          <div class="text-xs text-gray-400 mt-1">{{ t("settings.member.password-hint") }}</div>
        </div>
        <div>
          <label for="member-role" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("settings.member.role") }}</label>
          <Dropdown id="member-role" v-model="memberForm.role" :options="roleOptions" optionLabel="label" optionValue="value" class="w-full" />
        </div>
        <div class="flex justify-end gap-2 pt-1">
          <Button type="button" :label="t('common.cancel')" class="p-button-outlined p-button-secondary" @click="memberDialogVisible = false" />
          <Button type="submit" :label="t('settings.member.add')" icon="pi pi-user-plus" :loading="savingMember" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import Button from "primevue/button"
import InputText from "primevue/inputtext"
import Password from "primevue/password"
import SelectButton from "primevue/selectbutton"
import Dropdown from "primevue/dropdown"
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Dialog from "primevue/dialog"
import { useToast } from "primevue/usetoast"
import api from "../../api"
import { user } from "../../composables/useAuth"
import { t, setLocale, getLocale } from "../../i18n"

const toast = useToast()

const isAdmin = computed(() => user.value?.role === "admin")

const languageOptions = computed(() => [
  { label: t("settings.language.tr"), value: "tr" },
  { label: t("settings.language.en"), value: "en" },
])
const selectedLocale = ref(getLocale())

function onLocaleChange() {
  setLocale(selectedLocale.value)
}

const roleOptions = computed(() => [
  { label: t("role.admin"), value: "admin" },
  { label: t("role.member"), value: "member" },
])

const members = ref([])
const loading = ref(false)
const memberDialogVisible = ref(false)
const savingMember = ref(false)
const memberForm = ref({ name: "", email: "", password: "", role: "member" })

async function loadMembers() {
  if (!isAdmin.value) return
  loading.value = true
  try {
    const { data } = await api.get("/users")
    members.value = data.data
  } catch (error) {
    toast.add({ severity: "error", summary: t("settings.title"), detail: t("settings.error"), life: 3000 })
  } finally {
    loading.value = false
  }
}

function openMemberDialog() {
  memberForm.value = { name: "", email: "", password: "", role: "member" }
  memberDialogVisible.value = true
}

async function saveMember() {
  savingMember.value = true
  try {
    await api.post("/users", memberForm.value)
    toast.add({ severity: "success", summary: t("settings.title"), detail: t("settings.member.added"), life: 3000 })
    memberDialogVisible.value = false
    loadMembers()
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : t("settings.error")
    toast.add({ severity: "error", summary: t("settings.title"), detail: msg, life: 4000 })
  } finally {
    savingMember.value = false
  }
}

async function updateRole(member) {
  try {
    const { data } = await api.put(`/users/${member.id}`, { role: member.role })
    member.role = data.data.role
    toast.add({ severity: "success", summary: t("settings.title"), detail: t("settings.member.updated"), life: 3000 })
  } catch (error) {
    loadMembers()
    const msg = error.response && error.response.data.message
      ? error.response.data.message
      : t("settings.error")
    toast.add({ severity: "error", summary: t("settings.title"), detail: msg, life: 4000 })
  }
}

async function removeMember(member) {
  try {
    await api.delete(`/users/${member.id}`)
    toast.add({ severity: "success", summary: t("settings.title"), detail: t("settings.member.deleted"), life: 3000 })
    loadMembers()
  } catch (error) {
    const msg = error.response && error.response.data.message
      ? error.response.data.message
      : t("settings.error")
    toast.add({ severity: "error", summary: t("settings.title"), detail: msg, life: 4000 })
  }
}

function initials(name) {
  return name
    .split(" ")
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase()
}

onMounted(loadMembers)
</script>

<style></style>