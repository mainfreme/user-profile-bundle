<script setup lang="ts">
import { useRolesStore } from '@/stores/roles'

defineProps<{
  role: string
}>()

const roles = useRolesStore()

function label(role: string): string {
  return roles.labels[role] ?? role.replace('ROLE_', '')
}

function badgeClass(role: string): string {
  if (role === 'ROLE_ADMIN') {
    return 'badge-primary'
  }
  if (role === 'ROLE_MODERATOR') {
    return 'badge-warning'
  }

  return 'badge-success'
}
</script>

<template>
  <span class="badge" :class="badgeClass(role)">
    {{ label(role) }}
  </span>
</template>
