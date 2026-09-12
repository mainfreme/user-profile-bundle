import { get, post } from '@/api/client'
import type { CreateGroupPayload, Group, GroupListResponse, GroupResponse } from '@/types/user'

function unwrapGroup(response: GroupResponse): Group {
  if (!response.group) {
    throw new Error(response.errorMessage || 'Brak danych grupy.')
  }

  return response.group
}

export const groupsApi = {
  async list(): Promise<Group[]> {
    const response = await get<GroupListResponse>('/api/groups')
    return response.groups
  },

  async create(payload: CreateGroupPayload): Promise<Group> {
    return unwrapGroup(await post<GroupResponse>('/api/groups', payload))
  },
}
