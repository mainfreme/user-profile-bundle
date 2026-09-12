<script setup lang="ts">
import { useRoute } from 'vue-router'
import IconComponent from '@/components/IconComponent.vue'

const emit = defineEmits<{
  toggleSidebar: []
}>()

const route = useRoute()
</script>

<template>
  <header class="h-16 bg-dark-800 border-b border-dark-700 flex items-center justify-between px-6">
    <div class="flex items-center gap-4">
      <button
        type="button"
        class="p-2 rounded-lg text-dark-400 hover:bg-dark-700 hover:text-white transition-colors"
        @click="emit('toggleSidebar')"
      >
        <IconComponent name="Bars3Icon" class="w-5 h-5" />
      </button>

      <h1 class="text-lg font-medium text-white">
        {{ route.meta.title || 'Użytkownicy' }}
      </h1>
    </div>

    <div class="flex items-center gap-3">
      <button
        type="button"
        class="p-2 rounded-lg text-dark-400 hover:bg-dark-700 hover:text-white transition-colors"
      >
        <IconComponent name="BellIcon" class="w-5 h-5" />
      </button>

      <button
        type="button"
        class="p-2 rounded-lg text-dark-400 hover:bg-dark-700 hover:text-white transition-colors"
      >
        <IconComponent name="MagnifyingGlassIcon" class="w-5 h-5" />
      </button>

      <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
        U
      </div>
    </div>
  </header>
</template>
