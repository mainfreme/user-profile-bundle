<script setup lang="ts">
import { ref, watch } from 'vue'
import IconComponent from './IconComponent.vue'

const props = withDefaults(
  defineProps<{
    placeholder: string
    label?: string
    ariaLabel?: string
    debounceMs?: number
  }>(),
  {
    debounceMs: 300,
  },
)

const applied = defineModel<string>({ default: '' })
const inputValue = ref(applied.value)
let timer: ReturnType<typeof setTimeout> | null = null

watch(inputValue, (value) => {
  if (timer) {
    clearTimeout(timer)
  }
  timer = setTimeout(() => {
    const next = value.trim()
    if (applied.value !== next) {
      applied.value = next
    }
  }, props.debounceMs)
})

watch(applied, (value) => {
  if (value === '' && inputValue.value !== '') {
    inputValue.value = ''
  }
})

function clear(): void {
  if (timer) {
    clearTimeout(timer)
  }
  inputValue.value = ''
  applied.value = ''
}
</script>

<template>
  <div class="w-full min-w-0">
    <label v-if="label" class="label">{{ label }}</label>
    <div class="relative">
      <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-10 items-center justify-center text-dark-400">
        <IconComponent name="MagnifyingGlassIcon" class="h-4 w-4" />
      </span>
      <input
        v-model="inputValue"
        type="search"
        class="search-field-input input w-full pl-10 pr-10"
        :placeholder="placeholder"
        :aria-label="ariaLabel ?? label ?? 'Szukaj'"
      />
      <button
        v-if="inputValue"
        type="button"
        class="absolute inset-y-0 right-0 z-10 flex w-10 items-center justify-center text-dark-400 hover:text-white"
        aria-label="Wyczyść wyszukiwanie"
        @click="clear"
      >
        <IconComponent name="XMarkIcon" class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

<style scoped>
.search-field-input {
  -webkit-appearance: none;
  appearance: none;
}

.search-field-input::-webkit-search-decoration,
.search-field-input::-webkit-search-cancel-button,
.search-field-input::-webkit-search-results-button,
.search-field-input::-webkit-search-results-decoration {
  -webkit-appearance: none;
  appearance: none;
  display: none;
}
</style>
