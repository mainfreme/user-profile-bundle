<script setup lang="ts">
import IconComponent from './IconComponent.vue'
import { useNotificationsStore, type NotificationType } from '@/stores/notifications'

const notifications = useNotificationsStore()

function iconName(type: NotificationType): string {
  const icons: Record<NotificationType, string> = {
    success: 'CheckIcon',
    error: 'XMarkIcon',
    warning: 'ExclamationTriangleIcon',
    info: 'InformationCircleIcon',
  }
  return icons[type]
}

function typeClasses(type: NotificationType): string {
  const classes: Record<NotificationType, string> = {
    success: 'bg-green-600/10 border-green-600/30 text-green-400',
    error: 'bg-red-600/10 border-red-600/30 text-red-400',
    warning: 'bg-yellow-600/10 border-yellow-600/30 text-yellow-400',
    info: 'bg-primary-600/10 border-primary-600/30 text-primary-400',
  }
  return classes[type]
}
</script>

<template>
  <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-lg">
    <TransitionGroup name="toast">
      <div
        v-for="item in notifications.notifications"
        :key="item.id"
        class="flex items-start gap-3 p-4 rounded-lg border backdrop-blur-sm"
        :class="typeClasses(item.type)"
      >
        <IconComponent :name="iconName(item.type)" class="w-5 h-5 flex-shrink-0 mt-0.5" />

        <div class="flex-1 min-w-0">
          <p class="font-medium text-white">{{ item.title }}</p>
          <p v-if="item.message" class="text-sm opacity-80 mt-0.5">{{ item.message }}</p>
        </div>

        <button
          type="button"
          class="flex-shrink-0 p-1 rounded hover:bg-white/10 transition-colors"
          @click="notifications.remove(item.id)"
        >
          <IconComponent name="XMarkIcon" class="w-4 h-4" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>
