<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { groupsApi } from '@/api/groups'
import GroupCreateForm from '@/components/GroupCreateForm.vue'
import LoadingDots from '@/components/LoadingDots.vue'
import RoleBadge from '@/components/RoleBadge.vue'
import RoleCreateForm from '@/components/RoleCreateForm.vue'
import RoleTree from '@/components/RoleTree.vue'
import { useNotificationsStore } from '@/stores/notifications'
import { useRolesStore } from '@/stores/roles'
import type { Group } from '@/types/user'

const notifications = useNotificationsStore()
const roles = useRolesStore()
const groups = ref<Group[]>([])
const loading = ref(true)

async function loadGroups(): Promise<void> {
  loading.value = true
  try {
    groups.value = await groupsApi.list()
  } catch (error) {
    notifications.error('Błąd', 'Nie udało się pobrać grup', error)
  } finally {
    loading.value = false
  }
}

function formatDate(value: string): string {
  return new Date(value).toLocaleString('pl-PL')
}

onMounted(() => {
  void roles.load()
  void loadGroups()
})
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-xl font-semibold text-white">Role i grupy</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="space-y-6">
        <div class="card space-y-4">
          <div>
            <h2 class="text-lg font-medium text-white mb-1">Hierarchia ról</h2>
            <p class="text-sm text-dark-400">Aktualne drzewo ról w systemie.</p>
          </div>
          <RoleTree :nodes="roles.tree" selected="" :selectable="false" />
        </div>

        <RoleCreateForm @created="void roles.load(true)" />
      </div>

      <div class="space-y-6">
        <GroupCreateForm @created="void loadGroups()" />
      </div>
    </div>

    <div class="card p-0 overflow-hidden">
      <table class="table">
        <thead>
          <tr>
            <th>Grupa</th>
            <th>Opis</th>
            <th>Rola</th>
            <th>Utworzono</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="4" class="text-center py-8">
              <LoadingDots />
            </td>
          </tr>
          <template v-else>
            <tr v-if="groups.length === 0">
              <td colspan="4" class="text-center py-8 text-dark-400">
                Brak grup do wyświetlenia
              </td>
            </tr>
            <tr v-for="group in groups" :key="group.id">
              <td class="text-white font-medium">{{ group.name }}</td>
              <td class="text-dark-400">
                <span v-if="group.description.trim() !== ''">{{ group.description }}</span>
                <span v-else class="italic text-dark-500">Brak opisu</span>
              </td>
              <td>
                <RoleBadge v-if="group.role" :role="group.role" />
                <span v-else class="italic text-dark-500">Brak roli</span>
              </td>
              <td class="text-dark-400">{{ formatDate(group.createdAt) }}</td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>
