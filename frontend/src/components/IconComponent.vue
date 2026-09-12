<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue'

const props = defineProps<{
  name: string
}>()

const iconComponents: Record<string, ReturnType<typeof defineAsyncComponent>> = {
  PlusIcon: defineAsyncComponent(() => import('./icons/PlusIcon.vue')),
  PencilIcon: defineAsyncComponent(() => import('./icons/PencilIcon.vue')),
  UserCircleIcon: defineAsyncComponent(() => import('./icons/UserCircleIcon.vue')),
  Bars3Icon: defineAsyncComponent(() => import('./icons/Bars3Icon.vue')),
  BellIcon: defineAsyncComponent(() => import('./icons/BellIcon.vue')),
  MagnifyingGlassIcon: defineAsyncComponent(() => import('./icons/MagnifyingGlassIcon.vue')),
  XMarkIcon: defineAsyncComponent(() => import('./icons/XMarkIcon.vue')),
  CheckIcon: defineAsyncComponent(() => import('./icons/CheckIcon.vue')),
  ExclamationTriangleIcon: defineAsyncComponent(() => import('./icons/ExclamationTriangleIcon.vue')),
  InformationCircleIcon: defineAsyncComponent(() => import('./icons/InformationCircleIcon.vue')),
  ChevronLeftIcon: defineAsyncComponent(() => import('./icons/ChevronLeftIcon.vue')),
  ChevronRightIcon: defineAsyncComponent(() => import('./icons/ChevronRightIcon.vue')),
  ChevronDownIcon: defineAsyncComponent(() => import('./icons/ChevronDownIcon.vue')),
  UserGroupIcon: defineAsyncComponent(() => import('./icons/UserGroupIcon.vue')),
}

const iconComponent = computed(() => iconComponents[props.name] || null)
</script>

<template>
  <span class="inline-flex shrink-0 items-center justify-center" aria-hidden="true">
    <component :is="iconComponent" v-if="iconComponent" class="h-full w-full" />
    <span v-else class="block h-full w-full rounded bg-dark-600" />
  </span>
</template>
