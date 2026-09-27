import { onMounted, onUnmounted } from "vue"

const handlers = new Set()
let buffer = ""
let lastKeyTime = 0
let listenerInstalled = false

const MIN_LENGTH = 4
const MAX_GAP_MS = 60
const MAX_BUFFER = 64

function installListener() {
  if (listenerInstalled) return
  listenerInstalled = true

  window.addEventListener("keydown", (event) => {
    if (event.ctrlKey || event.metaKey || event.altKey) {
      if (event.key === "Enter") buffer = ""
      return
    }

    if (event.key === "Enter") {
      if (buffer.length >= MIN_LENGTH) {
        const code = buffer
        buffer = ""
        const target = event.target
        const typingInField =
          target && (target.tagName === "INPUT" || target.tagName === "TEXTAREA" || target.isContentEditable)
        if (!typingInField && handlers.size > 0) {
          event.preventDefault()
          handlers.forEach((handler) => handler(code))
        }
      }
      return
    }

    if (event.key === "Tab") {
      buffer = ""
      return
    }

    if (event.key.length === 1) {
      const now = Date.now()
      if (buffer.length > 0 && now - lastKeyTime > MAX_GAP_MS) {
        buffer = ""
      }
      lastKeyTime = now
      buffer += event.key
      if (buffer.length > MAX_BUFFER) buffer = ""
    }
  })
}

export function useBarcodeScanner(handler) {
  installListener()
  onMounted(() => {
    if (handler) handlers.add(handler)
  })
  onUnmounted(() => {
    if (handler) handlers.delete(handler)
  })
}