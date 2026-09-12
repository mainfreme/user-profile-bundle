export interface User {
  id: string
  email: string
  displayName: string
  bio: string
  role: string
  createdAt: string
  updatedAt: string
}

export interface UserResponse {
  status: 'success' | 'error'
  errorMessage: string | null
  user: User | null
}

export interface UserListResponse {
  status: 'success' | 'error'
  errorMessage: string | null
  users: User[]
}

export interface RoleNode {
  role: string
  label: string
  children: RoleNode[]
}

export interface RoleTreeResponse {
  status: 'success' | 'error'
  errorMessage: string | null
  roles: RoleNode[]
}

export interface RoleOption {
  role: string
  label: string
  depth: number
  isLast: boolean
  hierarchyLabel: string
  path: string[]
}

export interface CreateUserPayload {
  email: string
  displayName: string
  bio?: string
  role?: string
  password?: string
}

export interface UpdateProfilePayload {
  bio: string
  displayName?: string
}

export interface CreateRolePayload {
  role: string
  label: string
  parentRole?: string | null
}

export interface Group {
  id: string
  name: string
  description: string
  role: string | null
  createdAt: string
  updatedAt: string
}

export interface GroupResponse {
  status: 'success' | 'error'
  errorMessage: string | null
  group: Group | null
}

export interface GroupListResponse {
  status: 'success' | 'error'
  errorMessage: string | null
  groups: Group[]
}

export interface CreateGroupPayload {
  name: string
  description?: string
  role?: string | null
}

export const DEFAULT_ROLE_TREE: RoleNode[] = [
  {
    role: 'ROLE_ADMIN',
    label: 'Administrator',
    children: [
      {
        role: 'ROLE_MODERATOR',
        label: 'Moderator',
        children: [
          {
            role: 'ROLE_USER',
            label: 'Użytkownik',
            children: [],
          },
        ],
      },
    ],
  },
]

export const ALLOWED_ROLES = ['ROLE_ADMIN', 'ROLE_MODERATOR', 'ROLE_USER'] as const

export type AllowedRole = (typeof ALLOWED_ROLES)[number]

export const ROLE_LABELS: Record<string, string> = {
  ROLE_USER: 'Użytkownik',
  ROLE_MODERATOR: 'Moderator',
  ROLE_ADMIN: 'Administrator',
}

export function flattenRoleTree(
  nodes: RoleNode[],
  depth = 0,
  ancestors: string[] = [],
): RoleOption[] {
  const options: RoleOption[] = []

  nodes.forEach((node, index) => {
    const isLast = index === nodes.length - 1
    const indent = '\u00A0\u00A0'.repeat(depth)
    const glyph = depth === 0 ? '' : `${isLast ? '└─' : '├─'} `
    const children = node.children ?? []

    options.push({
      role: node.role,
      label: node.label,
      depth,
      isLast,
      hierarchyLabel: `${indent}${glyph}${node.label}`,
      path: [...ancestors, node.label],
    })

    if (children.length > 0) {
      options.push(...flattenRoleTree(children, depth + 1, [...ancestors, node.label]))
    }
  })

  return options
}

export function roleLabelsFromTree(nodes: RoleNode[]): Record<string, string> {
  const labels: Record<string, string> = { ...ROLE_LABELS }

  for (const option of flattenRoleTree(nodes)) {
    labels[option.role] = option.label
  }

  return labels
}
