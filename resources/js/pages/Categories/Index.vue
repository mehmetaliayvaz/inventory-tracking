<template>
  <div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-gray-700">{{ t("categories.title") }}</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ t("categories.count", { count: categories.length }) }}</p>
      </div>
      <Button :label="t('categories.add')" icon="pi pi-plus" @click="openDialog()" />
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div v-if="loading" class="py-16 text-center text-gray-500">
        <i class="pi pi-spin pi-spinner mr-2"></i>{{ t("common.loading") }}
      </div>

      <DataTable v-else :value="categories" dataKey="id">
        <Column field="id" header="#" style="width: 70px">
          <template #body="{ data }">
            <span class="text-gray-400">{{ data.id }}</span>
          </template>
        </Column>
        <Column field="name" :header="t('categories.name')" />
        <Column field="products_count" :header="t('products.title')" style="width: 120px">
          <template #body="{ data }">
            <Tag :value="String(data.products_count)" severity="secondary" />
          </template>
        </Column>
        <Column :header="t('common.actions')" style="width: 140px">
          <template #body="{ data }">
            <div class="flex gap-1">
              <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="openDialog(data)" />
              <Button icon="pi pi-trash" class="p-button-text p-button-sm p-button-danger" @click="removeCategory(data)" />
            </div>
          </template>
        </Column>
      </DataTable>

      <div v-if="!loading && categories.length === 0" class="py-16 text-center text-gray-500">
        <i class="pi pi-tags text-4xl text-gray-300 block mb-3"></i>
        {{ t("categories.empty") }}
      </div>
    </div>

    <Dialog v-model:visible="dialogVisible" :header="editing ? t('common.edit') : t('categories.add')" modal :style="{ width: '420px' }">
      <form @submit.prevent="saveCategory" class="space-y-4">
        <div>
          <label for="category-name" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("categories.name") }}</label>
          <InputText id="category-name" v-model="form.name" class="w-full" autofocus @keydown.enter.prevent="saveCategory" />
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

const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const dialogVisible = ref(false)
const editing = ref(null)
const form = ref({ name: "" })

async function loadCategories() {
  loading.value = true
  try {
    const { data } = await api.get("/categories")
    categories.value = data.data
  } catch (error) {
    toast.add({ severity: "error", summary: t("categories.title"), detail: t("categories.load-error"), life: 3000 })
  } finally {
    loading.value = false
  }
}

function openDialog(category = null) {
  editing.value = category
  form.value = { name: category?.name || "" }
  dialogVisible.value = true
}

async function saveCategory() {
  if (!form.value.name) {
    toast.add({ severity: "error", summary: t("categories.title"), detail: t("categories.name-required"), life: 3000 })
    return
  }
  saving.value = true
  try {
    if (editing.value) {
      await api.put(`/categories/${editing.value.id}`, form.value)
      toast.add({ severity: "success", summary: t("categories.title"), detail: t("categories.updated"), life: 3000 })
    } else {
      await api.post("/categories", form.value)
      toast.add({ severity: "success", summary: t("categories.title"), detail: t("categories.saved"), life: 3000 })
    }
    dialogVisible.value = false
    loadCategories()
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : t("settings.error")
    toast.add({ severity: "error", summary: t("categories.title"), detail: msg, life: 4000 })
  } finally {
    saving.value = false
  }
}

async function removeCategory(category) {
  try {
    await api.delete(`/categories/${category.id}`)
    toast.add({ severity: "success", summary: t("categories.title"), detail: t("categories.deleted"), life: 3000 })
    loadCategories()
  } catch (error) {
    toast.add({ severity: "error", summary: t("categories.title"), detail: t("settings.error"), life: 3000 })
  }
}

onMounted(loadCategories)
</script>

<style></style>