import { get, post, put } from '@/api/client'
import type {
  CreateRolePayload,
  CreateUserPayload,
  RoleNode,
  RoleTreeResponse,
  UpdateProfilePayload,
  User,
  UserListResponse,
  UserResponse,
} from '@/types/user'

function unwrapUser(response: UserResponse): User {
  if (!response.user) {
    throw new Error(response.errorMessage || 'Brak danych użytkownika.')
  }

  return response.user
}

export const usersApi = {
  async list(role?: string): Promise<User[]> {
    const query = role ? `?role=${encodeURIComponent(role)}` : ''
    const response = await get<UserListResponse>(`/api/users${query}`)
    return response.users
  },

  async get(id: string): Promise<User> {
    return unwrapUser(await get<UserResponse>(`/api/users/${id}`))
  },

  async create(payload: CreateUserPayload): Promise<User> {
    return unwrapUser(await post<UserResponse>('/api/users', payload))
  },

  async updateProfile(id: string, payload: UpdateProfilePayload): Promise<User> {
    return unwrapUser(await put<UserResponse>(`/api/users/${id}/profile`, payload))
  },

  async assignRole(id: string, role: string): Promise<User> {
    return unwrapUser(await put<UserResponse>(`/api/users/${id}/role`, { role }))
  },

  async roleTree(): Promise<RoleNode[]> {
    const response = await get<RoleTreeResponse>('/api/roles')
    return response.roles ?? []
  },

  async createRole(payload: CreateRolePayload): Promise<RoleNode[]> {
    const response = await post<RoleTreeResponse>('/api/roles', payload)
    return response.roles ?? []
  },
}
