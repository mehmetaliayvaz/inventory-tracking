<template>
  <img
    v-if="src"
    :src="src"
    :alt="alt"
    :class="imgClasses"
    @error="showPlaceholder = true"
  />
  <div
    v-else
    :class="['flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400', containerClasses]"
  >
    <i class="pi pi-box" :style="{ fontSize: iconSize }"></i>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue"

const props = defineProps({
  src: { type: String, default: null },
  alt: { type: String, default: "" },
  size: { type: String, default: "md" }, // sm | md | lg
  rounded: { type: Boolean, default: true },
})

const showPlaceholder = ref(false)

watch(
  () => props.src,
  () => {
    showPlaceholder.value = false
  }
)

const sizeMap = {
  sm: { img: "w-8 h-8", container: "w-8 h-8", icon: "0.9rem" },
  md: { img: "w-12 h-12", container: "w-12 h-12", icon: "1.3rem" },
  lg: { img: "w-28 h-28", container: "w-28 h-28", icon: "2.6rem" },
}

const current = computed(() => sizeMap[props.size] || sizeMap.md)
const radiusClass = computed(() => (props.rounded ? "rounded-lg" : ""))

const imgClasses = computed(() => [
  current.value.img,
  radiusClass.value,
  "object-cover",
  "bg-gray-100",
])
const containerClasses = computed(() => [
  current.value.container,
  radiusClass.value,
])
const iconSize = computed(() => current.value.icon)
</script>