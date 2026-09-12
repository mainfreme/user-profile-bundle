<script setup lang="ts">
import { useRoute, RouterLink } from 'vue-router'
import IconComponent from '@/components/IconComponent.vue'

defineProps<{
  collapsed: boolean
}>()

const route = useRoute()

const navigation = [
  { name: 'Użytkownicy', to: '/', icon: 'UserCircleIcon' },
  { name: 'Role i grupy', to: '/roles', icon: 'UserGroupIcon' },
]

function isActive(to: string): boolean {
  if (to === '/') {
    return route.path === '/' || route.path.startsWith('/users')
  }

  return route.path === to || route.path.startsWith(`${to}/`)
}
</script>

<template>
  <aside
    class="fixed left-0 top-0 h-screen bg-dark-800 border-r border-dark-700 transition-all duration-300 z-40"
    :class="collapsed ? 'w-20' : 'w-64'"
  >
    <div class="flex flex-col h-full">
      <div class="h-16 flex items-center px-4 border-b border-dark-700">
        <RouterLink to="/" class="flex items-center gap-3">
          <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
            <IconComponent name="UserCircleIcon" class="w-6 h-6 text-white" />
          </div>
          <span v-if="!collapsed" class="text-lg font-semibold text-white">Sales Tracker</span>
        </RouterLink>
      </div>

      <nav class="flex-1 py-4 overflow-y-auto">
        <ul class="space-y-1 px-3">
          <li v-for="item in navigation" :key="item.to">
            <RouterLink
              :to="item.to"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-dark-300 hover:bg-dark-700 hover:text-white transition-colors"
              :class="{ 'bg-dark-700 text-white': isActive(item.to) }"
            >
              <IconComponent :name="item.icon" class="w-5 h-5 flex-shrink-0" />
              <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
            </RouterLink>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
</template>
