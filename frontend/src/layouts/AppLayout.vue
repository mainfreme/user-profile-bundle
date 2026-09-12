<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import { useRolesStore } from '@/stores/roles'
import AppSidebar from './AppSidebar.vue'
import AppHeader from './AppHeader.vue'
import NotificationToast from '@/components/NotificationToast.vue'

const appStore = useAppStore()
const roles = useRolesStore()

onMounted(() => {
  void roles.load()
})

const mainClass = computed(() => ({
  'ml-64': appStore.sidebarOpen && !appStore.sidebarCollapsed,
  'ml-20': appStore.sidebarOpen && appStore.sidebarCollapsed,
  'ml-0': !appStore.sidebarOpen,
}))
</script>

<template>
  <div class="min-h-screen bg-dark-900">
    <AppSidebar
      v-if="appStore.sidebarOpen"
      :collapsed="appStore.sidebarCollapsed"
    />

    <div
      class="transition-all duration-300"
      :class="mainClass"
    >
      <AppHeader @toggle-sidebar="appStore.toggleSidebar" />

      <main class="p-6">
        <slot />
      </main>
    </div>

    <NotificationToast />
  </div>
</template>
