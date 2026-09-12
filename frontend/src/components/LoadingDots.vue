<script setup lang="ts">
withDefaults(defineProps<{
  label?: string
}>(), {
  label: 'Ładowanie',
})
</script>

<template>
  <span class="inline-flex items-baseline text-dark-400" role="status" aria-live="polite">
    <span>{{ label }}</span>
    <span class="inline-flex w-[1.15em]" aria-hidden="true">
      <span class="loading-dot">.</span>
      <span class="loading-dot loading-dot-2">.</span>
      <span class="loading-dot loading-dot-3">.</span>
    </span>
  </span>
</template>

<style scoped>
.loading-dot {
  animation: loading-dot 1.2s infinite ease-in-out both;
}

.loading-dot-2 {
  animation-delay: 0.2s;
}

.loading-dot-3 {
  animation-delay: 0.4s;
}

@keyframes loading-dot {
  0%,
  80%,
  100% {
    opacity: 0.2;
  }

  40% {
    opacity: 1;
  }
}
</style>
