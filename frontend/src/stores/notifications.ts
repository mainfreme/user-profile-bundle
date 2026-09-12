import { defineStore } from 'pinia'
import { ref } from 'vue'

export type NotificationType = 'success' | 'error' | 'warning' | 'info'

export interface Notification {
  id: string
  type: NotificationType
  title: string
  message?: string
}

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref<Notification[]>([])
  let counter = 0

  function remove(id: string): void {
    notifications.value = notifications.value.filter((item) => item.id !== id)
  }

  function add(notification: Omit<Notification, 'id'>, duration = 5000): void {
    const id = `n-${++counter}`
    notifications.value.push({ ...notification, id })
    window.setTimeout(() => remove(id), duration)
  }

  function success(title: string, message?: string): void {
    add({ type: 'success', title, message })
  }

  function error(title: string, message?: string, cause?: unknown): void {
    const details = cause instanceof Error ? cause.message : message
    add({ type: 'error', title, message: details }, 8000)
  }

  return { notifications, remove, success, error }
})
