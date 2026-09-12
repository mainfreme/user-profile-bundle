<script setup lang="ts">
import { ref } from 'vue'
import { usersApi } from '@/api/users'
import RoleTreeSelect from '@/components/RoleTreeSelect.vue'
import { useNotificationsStore } from '@/stores/notifications'
import { useRolesStore } from '@/stores/roles'

const emit = defineEmits<{
  created: []
}>()

const notifications = useNotificationsStore()
const roles = useRolesStore()
const saving = ref(false)

const form = ref({
  role: '',
  label: '',
  parentRole: '',
})

async function submit(): Promise<void> {
  saving.value = true
  try {
    const tree = await usersApi.createRole({
      role: form.value.role,
      label: form.value.label,
      parentRole: form.value.parentRole || null,
    })
    roles.applyTree(tree)
    notifications.success('Sukces', 'Rola została dodana')
    form.value = { role: '', label: '', parentRole: '' }
    emit('created')
  } catch (error) {
    notifications.error('Błąd', 'Nie udało się dodać roli', error)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <form class="card space-y-6" @submit.prevent="submit">
    <div>
      <h2 class="text-lg font-medium text-white mb-1">Nowa rola</h2>
      <p class="text-sm text-dark-400">Dodaj rolę do drzewa hierarchii.</p>
    </div>

    <div>
      <label class="label" for="role-code">
        Kod roli <span class="text-red-400">*</span>
      </label>
      <input
        id="role-code"
        v-model="form.role"
        class="input"
        type="text"
        required
        placeholder="ROLE_EDITOR"
      />
    </div>

    <div>
      <label class="label" for="role-label">
        Etykieta <span class="text-red-400">*</span>
      </label>
      <input
        id="role-label"
        v-model="form.label"
        class="input"
        type="text"
        required
        minlength="2"
        maxlength="100"
        placeholder="Redaktor"
      />
    </div>

    <RoleTreeSelect
      id="role-parent"
      v-model="form.parentRole"
      label="Rola nadrzędna"
      allow-empty
      empty-label="Korzeń drzewa"
    />

    <div class="flex justify-end gap-3 pt-4 border-t border-dark-700">
      <button type="submit" class="btn btn-primary" :disabled="saving">
        {{ saving ? 'Zapisywanie...' : 'Dodaj rolę' }}
      </button>
    </div>
  </form>
</template>
