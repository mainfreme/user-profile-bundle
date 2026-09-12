export function formatPaginationLabel(offset: number, limit: number, total: number): string {
  if (total === 0) {
    return 'Wyświetlanie 0 z 0'
  }

  const from = offset + 1
  const to = Math.min(offset + limit, total)

  return `Wyświetlanie ${from}-${to} z ${total}`
}

export function initials(name: string): string {
  const parts = name.split(' ').filter(Boolean)
  if (parts.length === 0) {
    return '?'
  }
  if (parts.length === 1) {
    return parts[0].charAt(0).toUpperCase()
  }

  return `${parts[0].charAt(0)}${parts[parts.length - 1].charAt(0)}`.toUpperCase()
}
