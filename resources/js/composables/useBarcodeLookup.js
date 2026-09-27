import { ref } from "vue"

const lookupBaseUrl = "https://world.openfoodfacts.org/api/v0/product"

export function useBarcodeLookup() {
  const loading = ref(false)
  const notFound = ref(false)
  const error = ref("")

  async function lookupBarcode(barcode) {
    const code = String(barcode || "").trim()
    if (!code) return null

    loading.value = true
    notFound.value = false
    error.value = ""

    try {
      const response = await fetch(`${lookupBaseUrl}/${code}.json`)
      const data = await response.json()

      if (data && data.status === 1 && data.product) {
        const p = data.product
        const result = {
          name: p.product_name || null,
          brand: p.brands || null,
          image: p.image_url || p.image_front_url || null,
          category: p.categories ? p.categories.split(",")[0].trim() : null,
          quantity: p.quantity || null,
        }
        return result
      }

      notFound.value = true
      return null
    } catch (err) {
      error.value = "Barkod servisine ulaşılamadı."
      return null
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    notFound,
    error,
    lookupBarcode,
  }
}