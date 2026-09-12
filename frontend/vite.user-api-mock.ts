import type { IncomingMessage, ServerResponse } from 'node:http'
import type { Plugin } from 'vite'

interface MockUser {
  id: string
  email: string
  displayName: string
  bio: string
  role: string
  createdAt: string
  updatedAt: string
}

type MockRoleNode = {
  role: string
  label: string
  children: MockRoleNode[]
}

let ROLE_TREE: MockRoleNode[] = [
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

function flattenRoleCodes(nodes: MockRoleNode[]): string[] {
  const codes: string[] = []
  for (const node of nodes) {
    codes.push(node.role)
    if (node.children.length > 0) {
      codes.push(...flattenRoleCodes(node.children))
    }
  }
  return codes
}

function allowedRoles(): string[] {
  return flattenRoleCodes(ROLE_TREE)
}

const BIO_MAX_LENGTH = 2000

function nowIso(): string {
  return new Date().toISOString()
}

function seedUsers(): Map<string, MockUser> {
  const users = new Map<string, MockUser>()
  const samples: MockUser[] = [
    {
      id: '550e8400-e29b-41d4-a716-446655440000',
      email: 'jan@example.com',
      displayName: 'Jan Kowalski',
      bio: 'Backend developer. Lubię Symfony, DDD i czyste API.',
      role: 'ROLE_USER',
      createdAt: '2026-09-01T10:00:00+00:00',
      updatedAt: '2026-09-01T10:00:00+00:00',
    },
    {
      id: '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
      email: 'anna@example.com',
      displayName: 'Anna Nowak',
      bio: 'Opiekuję się zespołem i porządkiem ról w aplikacji.',
      role: 'ROLE_ADMIN',
      createdAt: '2026-09-02T08:30:00+00:00',
      updatedAt: '2026-09-02T08:30:00+00:00',
    },
  ]

  for (const user of samples) {
    users.set(user.id, user)
  }

  return users
}

function sendJson(res: ServerResponse, status: number, payload: unknown): void {
  res.statusCode = status
  res.setHeader('Content-Type', 'application/json')
  res.end(JSON.stringify(payload))
}

function readBody(req: IncomingMessage): Promise<string> {
  return new Promise((resolve, reject) => {
    const chunks: Buffer[] = []
    req.on('data', (chunk: Buffer) => chunks.push(chunk))
    req.on('end', () => resolve(Buffer.concat(chunks).toString('utf8')))
    req.on('error', reject)
  })
}

async function parseJsonBody(req: IncomingMessage): Promise<Record<string, unknown> | string> {
  const raw = await readBody(req)
  if (raw.trim() === '') {
    return 'JSON body is required.'
  }

  try {
    const decoded = JSON.parse(raw) as unknown
    if (typeof decoded !== 'object' || decoded === null || Array.isArray(decoded)) {
      return 'JSON body must be an object.'
    }

    return decoded as Record<string, unknown>
  } catch {
    return 'JSON body is invalid.'
  }
}

function stringValue(payload: Record<string, unknown>, key: string): string {
  const value = payload[key]
  return typeof value === 'string' ? value : ''
}

function optionalString(payload: Record<string, unknown>, key: string): string | null {
  if (!(key in payload) || payload[key] === null) {
    return null
  }

  return typeof payload[key] === 'string' ? payload[key] as string : null
}

function validateEmail(email: string): string | null {
  const trimmed = email.trim().toLowerCase()
  if (trimmed === '') {
    return 'Email cannot be empty.'
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
    return `Email "${email}" has an invalid format.`
  }
  return null
}

function validateDisplayName(name: string): string | null {
  const trimmed = name.trim()
  if (trimmed === '') {
    return 'Display name cannot be empty.'
  }
  if (trimmed.length < 2 || trimmed.length > 100) {
    return 'Display name must be between 2 and 100 characters.'
  }
  return null
}

function validateBio(bio: string): string | null {
  if (bio.trim().length > BIO_MAX_LENGTH) {
    return `Bio exceeds maximum allowed length of ${BIO_MAX_LENGTH} characters.`
  }
  return null
}

function validateRole(role: string, mustExist = true): string | null {
  const normalized = role.trim().toUpperCase()
  if (normalized === '') {
    return 'Role cannot be empty.'
  }
  if (!normalized.startsWith('ROLE_')) {
    return `Role "${role}" is invalid. Role must start with "ROLE_".`
  }
  if (mustExist && !allowedRoles().includes(normalized)) {
    return `Role "${normalized}" is not allowed. Allowed roles: ${allowedRoles().join(', ')}.`
  }
  return null
}

function addRoleNode(nodes: MockRoleNode[], role: string, label: string, parentRole: string | null): boolean {
  if (parentRole === null) {
    nodes.push({ role, label, children: [] })
    return true
  }

  for (const node of nodes) {
    if (node.role === parentRole) {
      node.children.push({ role, label, children: [] })
      return true
    }
    if (addRoleNode(node.children, role, label, parentRole)) {
      return true
    }
  }

  return false
}

interface MockGroup {
  id: string
  name: string
  description: string
  role: string | null
  createdAt: string
  updatedAt: string
}

export function userProfileApiMock(): Plugin {
  const users = seedUsers()
  const groups = new Map<string, MockGroup>()

  return {
    name: 'user-profile-api-mock',
    configureServer(server) {
      server.middlewares.use(async (req, res, next) => {
        const method = req.method ?? 'GET'
        const url = new URL(req.url ?? '/', 'http://localhost')
        const path = (url.pathname.replace(/\/$/, '') || '/')

        try {
          if (method === 'GET' && path === '/api/roles') {
            sendJson(res, 200, { status: 'success', errorMessage: null, roles: ROLE_TREE })
            return
          }

          if (method === 'POST' && path === '/api/roles') {
            const body = await parseJsonBody(req)
            if (typeof body === 'string') {
              sendJson(res, 400, { status: 'error', errorMessage: body, roles: [] })
              return
            }

            const role = stringValue(body, 'role').trim().toUpperCase()
            const label = stringValue(body, 'label').trim()
            const parentRaw = optionalString(body, 'parentRole')
            const parentRole = parentRaw && parentRaw.trim() !== '' ? parentRaw.trim().toUpperCase() : null

            const roleError = validateRole(role, false)
            if (roleError) {
              sendJson(res, 400, { status: 'error', errorMessage: roleError, roles: [] })
              return
            }
            if (label === '') {
              sendJson(res, 400, { status: 'error', errorMessage: 'Role label cannot be empty.', roles: [] })
              return
            }
            if (allowedRoles().includes(role)) {
              sendJson(res, 400, { status: 'error', errorMessage: `Role "${role}" already exists.`, roles: [] })
              return
            }
            if (parentRole !== null && !allowedRoles().includes(parentRole)) {
              sendJson(res, 400, {
                status: 'error',
                errorMessage: `Parent role "${parentRole}" was not found.`,
                roles: [],
              })
              return
            }

            addRoleNode(ROLE_TREE, role, label, parentRole)
            sendJson(res, 201, { status: 'success', errorMessage: null, roles: ROLE_TREE })
            return
          }

          if (method === 'GET' && path === '/api/groups') {
            sendJson(res, 200, { status: 'success', errorMessage: null, groups: [...groups.values()] })
            return
          }

          if (method === 'POST' && path === '/api/groups') {
            const body = await parseJsonBody(req)
            if (typeof body === 'string') {
              sendJson(res, 400, { status: 'error', errorMessage: body, group: null })
              return
            }

            const name = stringValue(body, 'name').trim()
            const description = (optionalString(body, 'description') ?? '').trim()
            const roleRaw = optionalString(body, 'role')
            const role = roleRaw && roleRaw.trim() !== '' ? roleRaw.trim().toUpperCase() : null

            if (name === '') {
              sendJson(res, 400, { status: 'error', errorMessage: 'Group name cannot be empty.', group: null })
              return
            }
            if (name.length < 2 || name.length > 100) {
              sendJson(res, 400, {
                status: 'error',
                errorMessage: 'Group name must be between 2 and 100 characters.',
                group: null,
              })
              return
            }
            if (description.length > 500) {
              sendJson(res, 400, {
                status: 'error',
                errorMessage: 'Group description exceeds maximum allowed length of 500 characters.',
                group: null,
              })
              return
            }
            if (role !== null) {
              const roleError = validateRole(role)
              if (roleError) {
                sendJson(res, 400, { status: 'error', errorMessage: roleError, group: null })
                return
              }
            }
            if ([...groups.values()].some((group) => group.name.toLowerCase() === name.toLowerCase())) {
              sendJson(res, 400, {
                status: 'error',
                errorMessage: `Group with name "${name}" already exists.`,
                group: null,
              })
              return
            }

            const timestamp = nowIso()
            const group: MockGroup = {
              id: crypto.randomUUID(),
              name,
              description,
              role,
              createdAt: timestamp,
              updatedAt: timestamp,
            }
            groups.set(group.id, group)
            sendJson(res, 201, { status: 'success', errorMessage: null, group })
            return
          }
        } catch (error) {
          sendJson(res, 500, {
            status: 'error',
            errorMessage: error instanceof Error ? error.message : 'Unexpected mock API error.',
          })
          return
        }

        if (!url.pathname.startsWith('/api/users')) {
          next()
          return
        }

        try {
          const path = url.pathname.replace(/\/$/, '') || '/'

          if (method === 'GET' && path === '/api/users') {
            const role = url.searchParams.get('role')
            const list = [...users.values()].filter((user) => !role || user.role === role.toUpperCase())
            sendJson(res, 200, { status: 'success', errorMessage: null, users: list })
            return
          }

          if (method === 'POST' && path === '/api/users') {
            const body = await parseJsonBody(req)
            if (typeof body === 'string') {
              sendJson(res, 400, { status: 'error', errorMessage: body, user: null })
              return
            }

            const email = stringValue(body, 'email')
            const displayName = stringValue(body, 'displayName')
            const bio = optionalString(body, 'bio') ?? ''
            const role = optionalString(body, 'role') ?? 'ROLE_USER'
            const password = optionalString(body, 'password')

            const emailError = validateEmail(email)
            const nameError = validateDisplayName(displayName)
            const bioError = validateBio(bio)
            const roleError = validateRole(role)
            const passwordError = password && password.length > 0 && password.length < 8
              ? 'Password must be at least 8 characters long.'
              : null

            const error = emailError ?? nameError ?? bioError ?? roleError ?? passwordError
            if (error) {
              sendJson(res, 400, { status: 'error', errorMessage: error, user: null })
              return
            }

            const normalizedEmail = email.trim().toLowerCase()
            if ([...users.values()].some((user) => user.email === normalizedEmail)) {
              sendJson(res, 400, {
                status: 'error',
                errorMessage: `User with email "${normalizedEmail}" already exists.`,
                user: null,
              })
              return
            }

            const timestamp = nowIso()
            const user: MockUser = {
              id: crypto.randomUUID(),
              email: normalizedEmail,
              displayName: displayName.trim(),
              bio: bio.trim(),
              role: role.trim().toUpperCase(),
              createdAt: timestamp,
              updatedAt: timestamp,
            }
            users.set(user.id, user)
            sendJson(res, 201, { status: 'success', errorMessage: null, user })
            return
          }

          const profileMatch = path.match(/^\/api\/users\/([^/]+)\/profile$/)
          const roleMatch = path.match(/^\/api\/users\/([^/]+)\/role$/)
          const showMatch = path.match(/^\/api\/users\/([^/]+)$/)

          if (method === 'PUT' && profileMatch) {
            const user = users.get(profileMatch[1])
            if (!user) {
              sendJson(res, 404, {
                status: 'error',
                errorMessage: `User not found for id "${profileMatch[1]}".`,
                user: null,
              })
              return
            }

            const body = await parseJsonBody(req)
            if (typeof body === 'string') {
              sendJson(res, 400, { status: 'error', errorMessage: body, user: null })
              return
            }

            const bio = stringValue(body, 'bio')
            const displayName = optionalString(body, 'displayName')
            const bioError = validateBio(bio)
            const nameError = displayName ? validateDisplayName(displayName) : null
            const error = bioError ?? nameError
            if (error) {
              sendJson(res, 400, { status: 'error', errorMessage: error, user: null })
              return
            }

            user.bio = bio.trim()
            if (displayName && displayName.trim() !== '') {
              user.displayName = displayName.trim()
            }
            user.updatedAt = nowIso()
            sendJson(res, 200, { status: 'success', errorMessage: null, user })
            return
          }

          if (method === 'PUT' && roleMatch) {
            const user = users.get(roleMatch[1])
            if (!user) {
              sendJson(res, 404, {
                status: 'error',
                errorMessage: `User not found for id "${roleMatch[1]}".`,
                user: null,
              })
              return
            }

            const body = await parseJsonBody(req)
            if (typeof body === 'string') {
              sendJson(res, 400, { status: 'error', errorMessage: body, user: null })
              return
            }

            const role = stringValue(body, 'role')
            const roleError = validateRole(role)
            if (roleError) {
              sendJson(res, 400, { status: 'error', errorMessage: roleError, user: null })
              return
            }

            user.role = role.trim().toUpperCase()
            user.updatedAt = nowIso()
            sendJson(res, 200, { status: 'success', errorMessage: null, user })
            return
          }

          if (method === 'GET' && showMatch && showMatch[1] !== 'users') {
            const user = users.get(showMatch[1])
            if (!user) {
              sendJson(res, 404, {
                status: 'error',
                errorMessage: `User not found for id "${showMatch[1]}".`,
                user: null,
              })
              return
            }

            sendJson(res, 200, { status: 'success', errorMessage: null, user })
            return
          }

          next()
        } catch (error) {
          sendJson(res, 500, {
            status: 'error',
            errorMessage: error instanceof Error ? error.message : 'Unexpected mock API error.',
            user: null,
          })
        }
      })
    },
  }
}
