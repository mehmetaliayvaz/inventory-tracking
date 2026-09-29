<template>
  <div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
      <Button icon="pi pi-arrow-left" class="p-button-text p-button-rounded" @click="router.push({ name: 'products' })" />
      <h2 class="text-lg font-semibold text-gray-800">{{ t("product.add.title") }}</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div>
          <label for="barcode" class="block text-sm font-semibold mb-1.5 text-gray-700">
            {{ t("product.barcode-label") }}
            <span class="text-xs font-normal text-gray-400">{{ t("product.barcode-hint") }}</span>
          </label>
          <div class="flex gap-2">
            <InputText id="barcode" v-model="form.barcode" class="flex-1" :placeholder="t('product.barcode-label')" autofocus @keydown.enter.prevent="handleLookup" />
            <Button type="button" icon="pi pi-search" :label="t('common.search')" :loading="checking" @click="handleLookup" />
          </div>
          <div v-if="checking" class="text-sm text-gray-400 mt-2">
            <i class="pi pi-spin pi-spinner mr-1"></i> {{ t("product.searching") }}
          </div>
          <div v-else-if="existingProduct" class="text-sm text-blue-600 mt-2 flex items-center gap-1.5">
            <i class="pi pi-info-circle"></i>
            <span>
              {{ t("product.exists", { name: existingProduct.name }) }}
              <router-link :to="{ name: 'products.detail', params: { id: existingProduct.id } }" class="underline font-semibold">
                {{ t("product.go-to") }}
              </router-link>
            </span>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div class="sm:col-span-2">
            <label for="name" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.name-label") }} *</label>
            <InputText id="name" v-model="form.name" class="w-full" required />
          </div>

          <div>
            <label for="brand" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.brand-select") }}</label>
            <div class="flex gap-1.5">
              <Dropdown
                id="brand"
                v-model="form.brand_id"
                :options="brands"
                optionLabel="name"
                optionValue="id"
                :placeholder="t('product.brand-placeholder')"
                class="w-full"
                showClear
              />
              <Button
                type="button"
                icon="pi pi-plus"
                class="p-button-outlined p-button-sm shrink-0"
                :title="t('product.new-brand')"
                :aria-label="t('product.new-brand')"
                @click="openBrandDialog"
              />
            </div>
          </div>

          <div>
            <label for="category" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.category-select") }}</label>
            <div class="flex gap-1.5">
              <Dropdown
                id="category"
                v-model="form.category_id"
                :options="categories"
                optionLabel="name"
                optionValue="id"
                :placeholder="t('product.category-placeholder')"
                class="w-full"
                showClear
              />
              <Button
                type="button"
                icon="pi pi-plus"
                class="p-button-outlined p-button-sm shrink-0"
                :title="t('product.new-category')"
                :aria-label="t('product.new-category')"
                @click="openCategoryDialog"
              />
            </div>
          </div>

          <div>
            <label for="price" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("products.price") }} (₺) *</label>
            <InputNumber id="price" v-model="form.price" :min="0" mode="currency" currency="TRY" locale="tr-TR" class="w-full" />
          </div>

          <div>
            <label for="stock" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("products.stock") }} *</label>
            <InputNumber id="stock" v-model="form.stock" :min="0" :useGrouping="false" class="w-full" />
          </div>

          <div class="sm:col-span-2">
            <label for="description" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.description") }}</label>
            <Textarea id="description" v-model="form.description" rows="3" class="w-full" />
          </div>

          <div class="sm:col-span-2">
            <label class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("product.image-title") }}</label>
            <div class="flex items-start gap-4">
              <product-image :src="form.image" size="lg" />
              <div class="flex-1 space-y-2">
                <div class="flex items-center gap-2">
                  <i class="pi pi-link text-gray-400"></i>
                  <InputText
                    id="image"
                    v-model="form.image"
                    class="flex-1"
                    :placeholder="'https://...'"
                    :aria-label="t('product.image-url')"
                  />
                  <Button
                    v-if="form.image"
                    type="button"
                    icon="pi pi-times"
                    class="p-button-text p-button-rounded p-button-sm"
                    :title="t('product.image-remove')"
                    :aria-label="t('product.image-remove')"
                    @click="form.image = ''"
                  />
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-medium text-gray-400 uppercase">{{ t("product.image-or") }}</span>
                  <Button
                    type="button"
                    icon="pi pi-upload"
                    :label="t('product.image-upload')"
                    outlined
                    severity="secondary"
                    :loading="uploading"
                    @click="imageInput?.click()"
                  />
                  <span v-if="selectedFileName" class="text-xs text-gray-500 truncate max-w-40">{{ selectedFileName }}</span>
                </div>
                <p class="text-xs text-gray-400">{{ t("product.image-limits") }}</p>
                <input
                  ref="imageInput"
                  type="file"
                  accept="image/jpeg,image/png,image/webp,image/gif"
                  class="hidden"
                  @change="handleImageFile"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="pt-2 flex justify-end gap-2">
          <Button type="button" :label="t('common.cancel')" class="p-button-outlined p-button-secondary" @click="router.push({ name: 'products' })" />
          <Button type="submit" :label="t('product.save')" icon="pi pi-check" :loading="saving" />
        </div>
      </form>
    </div>

    <Dialog v-model:visible="brandDialogVisible" :header="t('brands.add')" modal :style="{ width: 'min(420px, 92vw)' }">
      <form @submit.prevent="saveNewBrand" class="space-y-4">
        <div>
          <label for="new-brand-name" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("brands.name") }}</label>
          <InputText id="new-brand-name" v-model="newBrandName" class="w-full" autofocus @keydown.enter.prevent="saveNewBrand" />
        </div>
        <div class="flex justify-end gap-2">
          <Button type="button" :label="t('common.cancel')" class="p-button-outlined p-button-secondary" @click="brandDialogVisible = false" />
          <Button type="submit" :label="t('common.save')" icon="pi pi-check" :loading="savingBrand" />
        </div>
      </form>
    </Dialog>

    <Dialog v-model:visible="categoryDialogVisible" :header="t('categories.add')" modal :style="{ width: 'min(420px, 92vw)' }">
      <form @submit.prevent="saveNewCategory" class="space-y-4">
        <div>
          <label for="new-category-name" class="block text-sm font-semibold mb-1.5 text-gray-700">{{ t("categories.name") }}</label>
          <InputText id="new-category-name" v-model="newCategoryName" class="w-full" autofocus @keydown.enter.prevent="saveNewCategory" />
        </div>
        <div class="flex justify-end gap-2">
          <Button type="button" :label="t('common.cancel')" class="p-button-outlined p-button-secondary" @click="categoryDialogVisible = false" />
          <Button type="submit" :label="t('common.save')" icon="pi pi-check" :loading="savingCategory" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from "vue"
import { useRouter } from "vue-router"
import Button from "primevue/button"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import Dropdown from "primevue/dropdown"
import Textarea from "primevue/textarea"
import Dialog from "primevue/dialog"
import { useToast } from "primevue/usetoast"
import ProductImage from "../../components/ProductImage.vue"
import api from "../../api"
import { useBarcodeScanner } from "../../composables/useBarcodeScanner"
import { t } from "../../i18n"

const router = useRouter()
const toast = useToast()

const categories = ref([])
const brands = ref([])

const form = reactive({
  barcode: "",
  name: "",
  brand_id: null,
  category_id: null,
  price: null,
  stock: null,
  description: "",
  image: "",
})
const saving = ref(false)
const checking = ref(false)
const existingProduct = ref(null)

// Inline "add new brand / category" dialogs
const brandDialogVisible = ref(false)
const categoryDialogVisible = ref(false)
const newBrandName = ref("")
const newCategoryName = ref("")
const savingBrand = ref(false)
const savingCategory = ref(false)

// Image upload
const imageInput = ref(null)
const uploading = ref(false)
const selectedFileName = ref("")

// Security limits for uploaded product images
const MAX_IMAGE_SIZE = 2 * 1024 * 1024 // 2 MB
const ALLOWED_IMAGE_TYPES = ["image/jpeg", "image/png", "image/webp", "image/gif"]
const ALLOWED_IMAGE_EXTENSIONS = ["jpg", "jpeg", "png", "webp", "gif"]

let lookupTimer = null
const lastLookedCode = ref("")

watch(
  () => form.barcode,
  (value) => {
    clearTimeout(lookupTimer)
    const code = String(value || "").trim()
    if (code.length >= 8 && code.length <= 14 && /^\d+$/.test(code) && lastLookedCode.value !== code) {
      lookupTimer = setTimeout(() => {
        if (code === String(form.barcode).trim()) {
          runLookup(code)
        }
      }, 350)
    }
  }
)

async function runLookup(code) {
  if (lastLookedCode.value === code) return
  lastLookedCode.value = code
  checking.value = true
  existingProduct.value = null

  const existing = await findInOwnProducts(code)
  checking.value = false
  if (existing) {
    existingProduct.value = existing
    fillForm(existing)
  }
}

async function findInOwnProducts(code) {
  try {
    const { data } = await api.get("/products", { params: { q: code } })
    return data.data.find((p) => p.barcode === code) || null
  } catch (error) {
    return null
  }
}

function fillForm(source) {
  form.name = form.name || source.name || ""
  form.brand_id = source.brand_id ?? null
  form.category_id = source.category_id ?? null
  form.image = source.image || ""
  if (source.description && !form.description) {
    form.description = source.description
  }
}

function handleLookup() {
  const code = String(form.barcode || "").trim()
  if (!code) return
  runLookup(code)
}

useBarcodeScanner((code) => {
  form.barcode = code
  handleLookup()
})

async function loadOptions() {
  const [catRes, brandRes] = await Promise.all([api.get("/categories"), api.get("/brands")])
  categories.value = catRes.data.data
  brands.value = brandRes.data.data
}

function openBrandDialog() {
  newBrandName.value = ""
  brandDialogVisible.value = true
}

function openCategoryDialog() {
  newCategoryName.value = ""
  categoryDialogVisible.value = true
}

async function saveNewBrand() {
  const name = newBrandName.value.trim()
  if (!name) {
    toast.add({ severity: "error", summary: t("brands.title"), detail: t("brands.name-required"), life: 3000 })
    return
  }
  savingBrand.value = true
  try {
    const { data } = await api.post("/brands", { name })
    brands.value.push(data.data)
    brands.value.sort((a, b) => a.name.localeCompare(b.name))
    form.brand_id = data.data.id
    brandDialogVisible.value = false
    toast.add({ severity: "success", summary: t("brands.title"), detail: t("brands.saved"), life: 3000 })
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : t("settings.error")
    toast.add({ severity: "error", summary: t("brands.title"), detail: msg, life: 4000 })
  } finally {
    savingBrand.value = false
  }
}

async function saveNewCategory() {
  const name = newCategoryName.value.trim()
  if (!name) {
    toast.add({ severity: "error", summary: t("categories.title"), detail: t("categories.name-required"), life: 3000 })
    return
  }
  savingCategory.value = true
  try {
    const { data } = await api.post("/categories", { name })
    categories.value.push(data.data)
    categories.value.sort((a, b) => a.name.localeCompare(b.name))
    form.category_id = data.data.id
    categoryDialogVisible.value = false
    toast.add({ severity: "success", summary: t("categories.title"), detail: t("categories.saved"), life: 3000 })
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : t("settings.error")
    toast.add({ severity: "error", summary: t("categories.title"), detail: msg, life: 4000 })
  } finally {
    savingCategory.value = false
  }
}

async function handleImageFile(event) {
  const file = event.target.files && event.target.files[0]
  event.target.value = "" // allow selecting the same file again later
  if (!file) return

  // Client-side checks for type and size (server re-validates).
  if (!ALLOWED_IMAGE_TYPES.includes(file.type) || !ALLOWED_IMAGE_EXTENSIONS.includes(file.name.split(".").pop().toLowerCase())) {
    toast.add({ severity: "error", summary: t("product.image-title"), detail: t("product.image-type-error"), life: 4000 })
    return
  }
  if (file.size > MAX_IMAGE_SIZE) {
    toast.add({ severity: "error", summary: t("product.image-title"), detail: t("product.image-size-error"), life: 4000 })
    return
  }

  selectedFileName.value = file.name
  uploading.value = true
  try {
    const fd = new FormData()
    fd.append("image", file)
    const { data } = await api.post("/products/upload-image", fd)
    form.image = data.url
    toast.add({ severity: "success", summary: t("product.image-title"), detail: t("product.image-uploaded"), life: 3000 })
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : t("product.image-upload-error")
    toast.add({ severity: "error", summary: t("product.image-title"), detail: msg, life: 4000 })
  } finally {
    uploading.value = false
    selectedFileName.value = ""
  }
}

async function handleSubmit() {
  if (!form.name) {
    toast.add({ severity: "error", summary: t("product.add.title"), detail: t("product.name-required"), life: 3000 })
    return
  }
  if (form.price === null || form.price === undefined) {
    toast.add({ severity: "error", summary: t("product.add.title"), detail: t("product.price-required"), life: 3000 })
    return
  }
  saving.value = true
  try {
    await api.post("/products", form)
    toast.add({ severity: "success", summary: t("product.add.title"), detail: t("product.saved"), life: 3000 })
    router.push({ name: "products" })
  } catch (error) {
    const msg = error.response && error.response.data.errors
      ? Object.values(error.response.data.errors)[0][0]
      : t("settings.error")
    toast.add({ severity: "error", summary: t("product.add.title"), detail: msg, life: 4000 })
  } finally {
    saving.value = false
  }
}

onMounted(loadOptions)
</script>

<style></style>