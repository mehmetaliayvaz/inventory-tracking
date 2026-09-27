<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h2 class="text-lg font-semibold text-gray-700">{{ t("brands.title") }}</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ t("brands.count", { count: brands.length }) }}</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <Button :label="t('brands.add')" icon="pi pi-plus" class="w-full sm:w-auto" @click="openDialog()" />
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div v-if="loading" class="py-16 text-center text-gray-500">
        <i class="pi pi-spin pi-spinner mr-2"></i>{{ t("common.loading") }}
      </div>

      <div v-else class="overflow-x-auto">
        <DataTable :value="brands" dataKey="id">
        <Column field="id" header="#" style="width: 70px">
          <template #body="{ data }">
            <span class="text-gray-400">{{ data.id }}</span>
          </template>
        </Column>
        <Column field="name" :header="t('brands.name')" />
        <Column field="products_count" :header="t('products.title')" style="width: 120px">
          <template #body="{ data }">
            <Tag :value="String(data.products_count)" severity="secondary" />
          </template>
        </Column>
        <Column :header="t('common.actions')" style="width: 140px">
          <template #body="{ data }">
            <div class="flex gap-1">
              <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="openDialog(data)" />
              <Button icon="pi pi-trash" class="p-button-text p-button-sm p-button-danger" @click="removeBrand(data)" />
            </div>
          </template>
        </Column>
        </DataTable>
      </div>

      <div v-if="!loading && brands.length === 0" class="py-16 text-center text-gray-500">
        <i class="pi pi-bookmark text-4xl text-gray-300 block mb-3"></i>
        {{ t("brands.empty") }}
      </div>
    </div>

    <Dialog v-model:visible="dialogVisible" :header="editing ? t('common.edit') : t('brands.add')" modal :style="{ width: 'min(420px, 92vw)' }">
      <form @submit.prevent="saveBrand" class="space-y-4">
        <div>
          <label for="brand-name" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("brands.name") }}</label>
          <InputText id="brand-name" v-model="form.name" class="w-full" autofocus @keydown.enter.prevent="saveBrand" />
        </div>
        <div class="flex justify-end gap-2">
          <Button type="button" :label="t('common.cancel')" class="p-button-outlined p-button-secondary" @click="dialogVisible = false" />
          <Button type="submit" :label="t('common.save')" icon="pi pi-check" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import Button from "primevue/button"
import InputText from "primevue/inputtext"
import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Tag from "primevue/tag"
import Dialog from "primevue/dialog"
import { useToast } from "primevue/usetoast"
import api from "../../api"
import { t } from "../../i18n"

const toast = useToast()

const brands = ref([])
const loading = ref(false)
const saving = ref(false)
const dialogVisible = ref(false)
const editing = ref(null)
const form = ref({ name: "" })

async function loadBrands() {
  loading.value = true
  try {
    const { data } = await api.get("/brands")
    brands.value = data.data
  } catch (error) {
    toast.add({ severity: "error", summary: t("brands.title"), detail: t("brands.load-error"), life: 3000 })
  } finally {
    loading.value = false
  }
}

function openDialog(brand = null) {
  editing.value = brand
  form.value = { name: brand?.name || "" }
  dialogVisible.value = true
}

async function saveBrand() {
  if (!form.value.name) {
    toast.add({ severity: "error", summary: t("brands.title"), detail: t("brands.name-required"), life: 3000 })
    return
  }
  saving.value = true
  try {
    if (editing.value) {
      await api.put(`/brands/${editing.value.id}`, form.value)
      toast.add({ severity: "success", summary: t("brands.title"), detail: t("brands.updated"), life: 3000 })
    } else {
      await api.post("/brands", form.value)
      toast.add({ severity: "success", summary: t("brands.title"), detail: t("brands.saved"), life: 3000 })
    }
    dialogVisible.value = false
    loadBrands()
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : t("settings.error")
    toast.add({ severity: "error", summary: t("brands.title"), detail: msg, life: 4000 })
  } finally {
    saving.value = false
  }
}

async function removeBrand(brand) {
  try {
    await api.delete(`/brands/${brand.id}`)
    toast.add({ severity: "success", summary: t("brands.title"), detail: t("brands.deleted"), life: 3000 })
    loadBrands()
  } catch (error) {
    toast.add({ severity: "error", summary: t("brands.title"), detail: t("settings.error"), life: 3000 })
  }
}

onMounted(loadBrands)
</script>

<style></style>