<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { usersApi } from '@/api/users'
import RoleBadge from '@/components/RoleBadge.vue'
import RoleTreeSelect from '@/components/RoleTreeSelect.vue'
import { useNotificationsStore } from '@/stores/notifications'
import type { User } from '@/types/user'
import { initials } from '@/utils/pagination'

const props = defineProps<{
  id: string
}>()

const router = useRouter()
const notifications = useNotificationsStore()

const user = ref<User | null>(null)
const loading = ref(true)
const savingProfile = ref(false)
const savingRole = ref(false)

const bio = ref('')
const displayName = ref('')
const role = ref('ROLE_USER')

const avatarInitials = computed(() => initials(user.value?.displayName ?? ''))

async function loadUser(): Promise<void> {
  loading.value = true
  try {
    const loaded = await usersApi.get(props.id)
    user.value = loaded
    bio.value = loaded.bio
    displayName.value = loaded.displayName
    role.value = loaded.role
  } catch (error) {
    notifications.error('Błąd', 'Nie udało się pobrać użytkownika', error)
    await router.push({ name: 'users' })
  } finally {
    loading.value = false
  }
}

async function saveProfile(): Promise<void> {
  if (!user.value) {
    return
  }

  savingProfile.value = true
  try {
    user.value = await usersApi.updateProfile(user.value.id, {
      bio: bio.value,
      displayName: displayName.value,
    })
    notifications.success('Sukces', 'Profil został zaktualizowany')
  } catch (error) {
    notifications.error('Błąd', 'Nie udało się zapisać profilu', error)
  } finally {
    savingProfile.value = false
  }
}

async function saveRole(): Promise<void> {
  if (!user.value) {
    return
  }

  savingRole.value = true
  try {
    user.value = await usersApi.assignRole(user.value.id, role.value)
    notifications.success('Sukces', 'Rola została przypisana')
  } catch (error) {
    notifications.error('Błąd', 'Nie udało się przypisać roli', error)
  } finally {
    savingRole.value = false
  }
}

function formatDate(value: string): string {
  return new Date(value).toLocaleString('pl-PL')
}

watch(() => props.id, loadUser)
onMounted(loadUser)
</script>

<template>
  <div class="space-y-6">
    <div v-if="loading" class="card">
      <div class="space-y-4">
        <div class="h-8 w-1/3 bg-dark-700 rounded animate-pulse" />
        <div class="h-4 w-2/3 bg-dark-700 rounded animate-pulse" />
        <div class="h-16 bg-dark-700 rounded animate-pulse" />
      </div>
    </div>

    <template v-else-if="user">
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 bg-primary-600 rounded-full flex items-center justify-center text-white text-2xl font-medium">
            {{ avatarInitials }}
          </div>
          <div>
            <h1 class="text-2xl font-semibold text-white">{{ user.displayName }}</h1>
            <p class="text-dark-400">{{ user.email }}</p>
          </div>
        </div>
        <RoleBadge :role="user.role" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <form class="card space-y-6" @submit.prevent="saveProfile">
          <div>
            <h2 class="text-lg font-medium text-white mb-1">Profil — Bio</h2>
            <p class="text-sm text-dark-400">Treść widoczna na profilu użytkownika.</p>
          </div>

          <div>
            <label class="label" for="displayName">
              Nazwa wyświetlana <span class="text-red-400">*</span>
            </label>
            <input
              id="displayName"
              v-model="displayName"
              class="input"
              type="text"
              required
              minlength="2"
              maxlength="100"
            />
          </div>

          <div>
            <label class="label" for="bio">Bio</label>
            <textarea
              id="bio"
              v-model="bio"
              class="input min-h-[5rem]"
              rows="4"
              maxlength="2000"
              placeholder="Napisz kilka zdań o sobie"
            />
            <p class="mt-1 text-right text-xs text-dark-400">{{ bio.length }}/2000</p>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-dark-700">
            <button type="submit" class="btn btn-primary" :disabled="savingProfile">
              {{ savingProfile ? 'Zapisywanie...' : 'Zapisz zmiany' }}
            </button>
          </div>
        </form>

        <div class="space-y-6">
          <form class="card space-y-6" @submit.prevent="saveRole">
            <div>
              <h2 class="text-lg font-medium text-white mb-1">Przypisana rola</h2>
              <p class="text-sm text-dark-400">Wybierz rolę z hierarchii — użytkownik ma jedną przypisaną rolę.</p>
            </div>

            <RoleTreeSelect id="role" v-model="role" label="Rola" expanded />

            <div class="flex justify-end gap-3 pt-4 border-t border-dark-700">
              <button type="submit" class="btn btn-primary" :disabled="savingRole">
                {{ savingRole ? 'Zapisywanie...' : 'Zapisz zmiany' }}
              </button>
            </div>
          </form>

          <div class="card">
            <h2 class="text-lg font-medium text-white mb-4">Informacje</h2>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm text-dark-400">Utworzono</dt>
                <dd class="text-dark-200">{{ formatDate(user.createdAt) }}</dd>
              </div>
              <div>
                <dt class="text-sm text-dark-400">Ostatnia aktualizacja</dt>
                <dd class="text-dark-200">{{ formatDate(user.updatedAt) }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
