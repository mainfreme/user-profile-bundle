import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { usersApi } from '@/api/users'
import {
  DEFAULT_ROLE_TREE,
  flattenRoleTree,
  roleLabelsFromTree,
  type RoleNode,
} from '@/types/user'

export const useRolesStore = defineStore('roles', () => {
  const tree = ref<RoleNode[]>(DEFAULT_ROLE_TREE)

  const options = computed(() => flattenRoleTree(tree.value))
  const labels = computed(() => roleLabelsFromTree(tree.value))
  let inflight: Promise<void> | null = null

  async function load(force = false): Promise<void> {
    if (!force && inflight) {
      return inflight
    }

    inflight = (async () => {
      try {
        const roles = await usersApi.roleTree()
        if (roles.length > 0) {
          tree.value = roles
        }
      } catch {
        if (!force) {
          tree.value = DEFAULT_ROLE_TREE
        }
      }
    })()

    try {
      await inflight
    } finally {
      inflight = null
    }
  }

  function applyTree(nodes: RoleNode[]): void {
    if (nodes.length > 0) {
      tree.value = nodes
    }
  }

  return { tree, options, labels, load, applyTree }
})
