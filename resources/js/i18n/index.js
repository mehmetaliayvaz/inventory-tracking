import { ref } from "vue"
import tr from "./tr.json"
import en from "./en.json"

const dictionaries = { tr, en }

const locale = ref(localStorage.getItem("locale") || "tr")

export function t(key, params) {
  const dict = dictionaries[locale.value] || dictionaries.tr
  let text = dict[key] ?? key
  if (params) {
    for (const [paramKey, paramValue] of Object.entries(params)) {
      text = text.replaceAll(`{${paramKey}}`, String(paramValue))
    }
  }
  return text
}

export function setLocale(localeCode) {
  if (dictionaries[localeCode]) {
    locale.value = localeCode
    localStorage.setItem("locale", localeCode)
    document.documentElement.lang = localeCode
  }
}

export function getLocale() {
  return locale.value
}

export { locale }