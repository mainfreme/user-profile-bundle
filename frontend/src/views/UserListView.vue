<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { usersApi } from '@/api/users'
import IconComponent from '@/components/IconComponent.vue'
import LoadingDots from '@/components/LoadingDots.vue'
import PaginationBar from '@/components/PaginationBar.vue'
import RoleBadge from '@/components/RoleBadge.vue'
import RoleTreeSelect from '@/components/RoleTreeSelect.vue'
import SearchField from '@/components/SearchField.vue'
import { useNotificationsStore } from '@/stores/notifications'
import { useRolesStore } from '@/stores/roles'
import type { User } from '@/types/user'
import { initials } from '@/utils/pagination'

const notifications = useNotificationsStore()
const roles = useRolesStore()
const users = ref<User[]>([])
const loading = ref(true)
const search = ref('')
const roleFilter = ref('')
const offset = ref(0)
const limit = 20

const filteredUsers = computed(() => {
  const query = search.value.toLowerCase()
  return users.value.filter((user) => {
    if (query === '') {
      return true
    }

    return [user.displayName, user.email, user.bio].some((value) => value.toLowerCase().includes(query))
  })
})

const pagedUsers = computed(() => filteredUsers.value.slice(offset.value, offset.value + limit))

async function loadUsers(): Promise<void> {
  loading.value = true
  try {
    users.value = await usersApi.list(roleFilter.value || undefined)
  } catch (error) {
    notifications.error('Błąd', 'Nie udało się pobrać użytkowników', error)
  } finally {
    loading.value = false
  }
}

function excerpt(bio: string): string {
  if (bio.trim() === '') {
    return ''
  }
  return bio.length > 80 ? `${bio.slice(0, 80).trim()}…` : bio
}

function formatDate(value: string): string {
  return new Date(value).toLocaleString('pl-PL')
}

watch([search, roleFilter], () => {
  offset.value = 0
})

watch(roleFilter, () => {
  void loadUsers()
})

onMounted(() => {
  void roles.load()
  void loadUsers()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <h1 class="text-xl font-semibold text-white">Użytkownicy</h1>

      <RouterLink to="/users/new" class="btn btn-primary">
        <IconComponent name="PlusIcon" class="w-4 h-4 mr-2" />
        Nowy użytkownik
      </RouterLink>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      <SearchField
        v-model="search"
        placeholder="Szukaj po nazwie, e-mailu lub bio"
        aria-label="Szukaj użytkowników"
      />
      <div class="w-64">
        <RoleTreeSelect v-model="roleFilter" allow-empty empty-label="Wszystkie role" />
      </div>
    </div>

    <div class="card p-0 overflow-hidden">
      <table class="table">
        <thead>
          <tr>
            <th>Użytkownik</th>
            <th>E-mail</th>
            <th>Bio</th>
            <th>Rola</th>
            <th>Aktualizacja</th>
            <th class="w-20"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="text-center py-8">
              <LoadingDots />
            </td>
          </tr>
          <template v-else>
            <tr v-if="pagedUsers.length === 0">
              <td colspan="6" class="text-center py-8 text-dark-400">
                <template v-if="search">
                  Brak użytkowników dla frazy „{{ search }}”
                </template>
                <template v-else>
                  Brak użytkowników do wyświetlenia
                </template>
              </td>
            </tr>
            <tr v-for="user in pagedUsers" :key="user.id">
              <td>
                <RouterLink :to="{ name: 'user-profile', params: { id: user.id } }" class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                    {{ initials(user.displayName) }}
                  </div>
                  <span class="text-primary-400 hover:underline">{{ user.displayName }}</span>
                </RouterLink>
              </td>
              <td>{{ user.email }}</td>
              <td class="text-dark-400">
                <span v-if="user.bio.trim() !== ''">{{ excerpt(user.bio) }}</span>
                <span v-else class="italic text-dark-500">Brak bio</span>
              </td>
              <td>
                <RoleBadge :role="user.role" />
              </td>
              <td class="text-dark-400">{{ formatDate(user.updatedAt) }}</td>
              <td>
                <div class="flex items-center gap-1">
                  <RouterLink
                    :to="{ name: 'user-profile', params: { id: user.id } }"
                    class="p-1.5 rounded hover:bg-dark-600 text-dark-400 hover:text-white"
                  >
                    <IconComponent name="PencilIcon" class="w-4 h-4" />
                  </RouterLink>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <PaginationBar
        :offset="offset"
        :limit="limit"
        :total="filteredUsers.length"
        @update:offset="offset = $event"
      />
    </div>
  </div>
</template>
