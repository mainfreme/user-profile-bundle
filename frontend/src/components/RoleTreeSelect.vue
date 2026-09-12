<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue'
import IconComponent from '@/components/IconComponent.vue'
import RoleTree from '@/components/RoleTree.vue'
import { useRolesStore } from '@/stores/roles'

const props = withDefaults(
  defineProps<{
    id?: string
    label?: string
    allowEmpty?: boolean
    emptyLabel?: string
    expanded?: boolean
    disabled?: boolean
  }>(),
  {
    allowEmpty: false,
    emptyLabel: 'Wszystkie role',
    expanded: false,
    disabled: false,
  },
)

const selected = defineModel<string>({ default: '' })
const roles = useRolesStore()
const open = ref(false)
const root = ref<HTMLElement | null>(null)
const trigger = ref<HTMLButtonElement | null>(null)

const selectedOption = computed(() => roles.options.find((option) => option.role === selected.value) ?? null)

const displayLabel = computed(() => {
  if (selected.value === '' && props.allowEmpty) {
    return props.emptyLabel
  }

  return selectedOption.value?.label ?? selected.value
})

const displayPath = computed(() => selectedOption.value?.path.join(' › ') ?? '')

function selectRole(role: string): void {
  selected.value = role
  open.value = false
}

function toggle(): void {
  if (props.disabled || props.expanded) {
    return
  }

  open.value = !open.value
}

function onDocumentPointer(event: MouseEvent): void {
  if (!open.value || props.expanded) {
    return
  }

  const target = event.target
  if (!(target instanceof Node) || !root.value?.contains(target)) {
    open.value = false
  }
}

function onKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') {
    open.value = false
    return
  }

  if ((event.key === 'Enter' || event.key === ' ') && document.activeElement === trigger.value) {
    event.preventDefault()
    toggle()
  }
}

onMounted(async () => {
  await roles.load()
  await nextTick()
  document.addEventListener('mousedown', onDocumentPointer)
  document.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
  document.removeEventListener('mousedown', onDocumentPointer)
  document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
  <div>
    <label v-if="label" class="label" :for="id">{{ label }}</label>

    <div ref="root" class="relative">
      <button
        v-if="!expanded"
        :id="id"
        ref="trigger"
        type="button"
        class="input flex items-center justify-between text-left"
        :disabled="disabled"
        :aria-expanded="open"
        aria-haspopup="listbox"
        @click="toggle"
      >
        <span class="min-w-0">
          <span class="block truncate text-dark-100">{{ displayLabel }}</span>
          <span v-if="selectedOption && selectedOption.path.length > 1" class="block truncate text-xs text-dark-400">
            {{ displayPath }}
          </span>
        </span>
        <IconComponent name="ChevronDownIcon" class="ml-2 h-4 w-4 shrink-0 text-dark-400" />
      </button>

      <div
        v-if="expanded || open"
        :id="expanded ? id : undefined"
        class="rounded-lg border border-dark-600 bg-dark-800 py-1"
        :class="expanded ? '' : 'absolute z-20 mt-2 w-full shadow-xl'"
        role="listbox"
      >
        <button
          v-if="allowEmpty"
          type="button"
          class="flex w-full items-center px-3 py-2 text-left text-sm transition-colors"
          :class="selected === '' ? 'bg-dark-700 text-white' : 'text-dark-200 hover:bg-dark-700 hover:text-white'"
          role="option"
          :aria-selected="selected === ''"
          @click="selectRole('')"
        >
          {{ emptyLabel }}
        </button>

        <RoleTree :nodes="roles.tree" :selected="selected" @select="selectRole" />
      </div>
    </div>
  </div>
</template>
