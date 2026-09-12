<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { usersApi } from '@/api/users'
import RoleTreeSelect from '@/components/RoleTreeSelect.vue'
import { useNotificationsStore } from '@/stores/notifications'
import { useRolesStore } from '@/stores/roles'

const router = useRouter()
const notifications = useNotificationsStore()
const roles = useRolesStore()
const saving = ref(false)

const form = ref({
  email: '',
  displayName: '',
  bio: '',
  role: 'ROLE_USER',
  password: '',
})

async function submit(): Promise<void> {
  saving.value = true
  try {
    const user = await usersApi.create({
      email: form.value.email,
      displayName: form.value.displayName,
      bio: form.value.bio,
      role: form.value.role,
      password: form.value.password || undefined,
    })
    notifications.success('Sukces', 'Użytkownik został utworzony')
    await router.push({ name: 'user-profile', params: { id: user.id } })
  } catch (error) {
    notifications.error('Błąd', 'Nie udało się utworzyć użytkownika', error)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  void roles.load()
})
</script>

<template>
  <div class="max-w-2xl">
    <h1 class="text-2xl font-semibold text-white mb-6">Nowy użytkownik</h1>

    <form class="card space-y-6" @submit.prevent="submit">
      <div>
        <label class="label" for="email">
          E-mail <span class="text-red-400">*</span>
        </label>
        <input
          id="email"
          v-model="form.email"
          class="input"
          type="email"
          required
          placeholder="jan@example.com"
        />
      </div>

      <div>
        <label class="label" for="displayName">
          Nazwa wyświetlana <span class="text-red-400">*</span>
        </label>
        <input
          id="displayName"
          v-model="form.displayName"
          class="input"
          type="text"
          required
          minlength="2"
          maxlength="100"
          placeholder="Jan Kowalski"
        />
      </div>

      <div>
        <label class="label" for="bio">Bio</label>
        <textarea
          id="bio"
          v-model="form.bio"
          class="input min-h-[5rem]"
          rows="4"
          maxlength="2000"
          placeholder="Krótki opis profilu"
        />
        <p class="mt-1 text-right text-xs text-dark-400">{{ form.bio.length }}/2000</p>
      </div>

      <RoleTreeSelect id="role" v-model="form.role" label="Rola" expanded />

      <div>
        <label class="label" for="password">Hasło</label>
        <input
          id="password"
          v-model="form.password"
          class="input"
          type="password"
          minlength="8"
          placeholder="opcjonalnie, min. 8 znaków"
        />
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-dark-700">
        <button type="button" class="btn btn-secondary" @click="router.push({ name: 'users' })">
          Anuluj
        </button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          {{ saving ? 'Zapisywanie...' : 'Utwórz użytkownika' }}
        </button>
      </div>
    </form>
  </div>
</template>
