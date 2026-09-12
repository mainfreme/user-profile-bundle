<script setup lang="ts">
import { computed } from 'vue'
import IconComponent from './IconComponent.vue'
import { formatPaginationLabel } from '@/utils/pagination'

const props = withDefaults(defineProps<{
  offset: number
  limit: number
  total: number
}>(), {
  offset: 0,
  limit: 20,
  total: 0,
})

const emit = defineEmits<{
  'update:offset': [offset: number]
}>()

const label = computed(() => formatPaginationLabel(props.offset, props.limit, props.total))
const canGoPrev = computed(() => props.offset > 0)
const canGoNext = computed(() => props.offset + props.limit < props.total)

function prevPage(): void {
  if (!canGoPrev.value) {
    return
  }

  emit('update:offset', Math.max(0, props.offset - props.limit))
}

function nextPage(): void {
  if (!canGoNext.value) {
    return
  }

  emit('update:offset', props.offset + props.limit)
}
</script>

<template>
  <div class="flex items-center justify-between px-4 py-3 border-t border-dark-700">
    <span class="text-sm text-dark-400">{{ label }}</span>

    <div class="flex gap-2">
      <button
        type="button"
        class="btn btn-secondary btn-sm"
        :disabled="!canGoPrev"
        @click="prevPage"
      >
        <IconComponent name="ChevronLeftIcon" class="w-4 h-4" />
      </button>

      <button
        type="button"
        class="btn btn-secondary btn-sm"
        :disabled="!canGoNext"
        @click="nextPage"
      >
        <IconComponent name="ChevronRightIcon" class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>
