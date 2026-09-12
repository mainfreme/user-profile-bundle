<script setup lang="ts">
import { ref } from 'vue'
import { groupsApi } from '@/api/groups'
import RoleTreeSelect from '@/components/RoleTreeSelect.vue'
import { useNotificationsStore } from '@/stores/notifications'

const emit = defineEmits<{
  created: []
}>()

const notifications = useNotificationsStore()
const saving = ref(false)

const form = ref({
  name: '',
  description: '',
  role: '',
})

async function submit(): Promise<void> {
  saving.value = true
  try {
    await groupsApi.create({
      name: form.value.name,
      description: form.value.description,
      role: form.value.role || null,
    })
    notifications.success('Sukces', 'Grupa została utworzona')
    form.value = { name: '', description: '', role: '' }
    emit('created')
  } catch (error) {
    notifications.error('Błąd', 'Nie udało się utworzyć grupy', error)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <form class="card space-y-6" @submit.prevent="submit">
    <div>
      <h2 class="text-lg font-medium text-white mb-1">Nowa grupa</h2>
      <p class="text-sm text-dark-400">Utwórz grupę i opcjonalnie przypisz jej rolę.</p>
    </div>

    <div>
      <label class="label" for="group-name">
        Nazwa <span class="text-red-400">*</span>
      </label>
      <input
        id="group-name"
        v-model="form.name"
        class="input"
        type="text"
        required
        minlength="2"
        maxlength="100"
        placeholder="Redakcja"
      />
    </div>

    <div>
      <label class="label" for="group-description">Opis</label>
      <textarea
        id="group-description"
        v-model="form.description"
        class="input min-h-[5rem]"
        rows="3"
        maxlength="500"
        placeholder="Krótki opis grupy"
      />
      <p class="mt-1 text-right text-xs text-dark-400">{{ form.description.length }}/500</p>
    </div>

    <RoleTreeSelect
      id="group-role"
      v-model="form.role"
      label="Przypisana rola"
      allow-empty
      empty-label="Bez roli"
    />

    <div class="flex justify-end gap-3 pt-4 border-t border-dark-700">
      <button type="submit" class="btn btn-primary" :disabled="saving">
        {{ saving ? 'Zapisywanie...' : 'Dodaj grupę' }}
      </button>
    </div>
  </form>
</template>
