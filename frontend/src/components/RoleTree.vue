<script setup lang="ts">
import IconComponent from '@/components/IconComponent.vue'
import RoleTree from '@/components/RoleTree.vue'
import type { RoleNode } from '@/types/user'

withDefaults(
  defineProps<{
    nodes: RoleNode[]
    selected: string
    depth?: number
    selectable?: boolean
  }>(),
  {
    depth: 0,
    selectable: true,
  },
)

const emit = defineEmits<{
  select: [role: string]
}>()
</script>

<template>
  <ul
    role="group"
    class="space-y-0.5"
    :class="depth > 0 ? 'ml-3 mt-0.5 border-l border-dark-700 pl-2' : ''"
  >
    <li v-for="(node, index) in nodes" :key="node.role">
      <component
        :is="selectable ? 'button' : 'div'"
        :type="selectable ? 'button' : undefined"
        class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm"
        :class="[
          selectable ? 'transition-colors' : '',
          node.role === selected ? 'bg-dark-700 text-white' : 'text-dark-200',
          selectable ? 'hover:bg-dark-700 hover:text-white' : '',
        ]"
        :role="selectable ? 'option' : undefined"
        :aria-selected="selectable ? node.role === selected : undefined"
        @click="selectable ? emit('select', node.role) : undefined"
      >
        <span class="mr-2 shrink-0 font-mono text-dark-400">
          {{ depth === 0 ? '•' : (index === nodes.length - 1 ? '└' : '├') }}
        </span>
        <span class="min-w-0 truncate">{{ node.label }}</span>
        <IconComponent
          v-if="selectable && node.role === selected"
          name="CheckIcon"
          class="ml-auto h-4 w-4 shrink-0 text-primary-400"
        />
      </component>

      <RoleTree
        v-if="(node.children ?? []).length > 0"
        :nodes="node.children"
        :selected="selected"
        :depth="depth + 1"
        :selectable="selectable"
        @select="emit('select', $event)"
      />
    </li>
  </ul>
</template>
