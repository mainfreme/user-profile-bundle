# user-profile-bundle

Symfony bundle do zarządzania **użytkownikiem**: edycja profilu (**Bio**) oraz **przypisana rola**. Implementacja w architekturze **DDD** i **CQRS** (Symfony Messenger) z **REST API** i dokumentacją **Swagger/OpenAPI**.

## Wymagania

- PHP >= 8.4
- Symfony 8.1+

## Instalacja

```bash
composer require swh/user-profile-bundle:^1.0
```

Zarejestruj routing w `config/routes.yaml`:

```yaml
user_profile:
    resource: '@UserProfileBundle/Resources/config/routes.yaml'
```

Bundle automatycznie rejestruje też trasy Swagger UI (`/api/doc`, `/api/doc.json`).

W aplikacji hostującej włącz assets (wymagane przez Swagger UI):

```yaml
# config/packages/framework.yaml
framework:
    assets: {}
```

## Konfiguracja

Utwórz plik `config/packages/user_profile.yaml`:

### Zapis na dysku (domyślnie)

```yaml
user_profile:
    storage:
        driver: filesystem
        filesystem:
            root: '%kernel.project_dir%/var/users'
    bio_max_length: 2000
    default_role: ROLE_USER
    role_tree:
        - role: ROLE_ADMIN
          label: Administrator
          children:
              - role: ROLE_MODERATOR
                label: Moderator
                children:
                    - role: ROLE_USER
                      label: Użytkownik
```

`allowed_roles` jest wyprowadzane z `role_tree`. Płaską listę `allowed_roles` ustawiasz tylko wtedy, gdy `role_tree` jest puste.

### Magazyn w pamięci (testy)

```yaml
user_profile:
    storage:
        driver: memory
```

## REST API

| Metoda | Endpoint | Opis |
|--------|----------|------|
| `GET` | `/api/roles` | Drzewo hierarchii ról |
| `POST` | `/api/roles` | Dodanie roli (`role`, `label`, opcjonalnie `parentRole`) |
| `GET` | `/api/groups` | Lista grup |
| `POST` | `/api/groups` | Utworzenie grupy (`name`, opcjonalnie `description`, `role`) |
| `GET` | `/api/users` | Lista użytkowników (`?role=ROLE_ADMIN` opcjonalnie) |
| `POST` | `/api/users` | Utworzenie użytkownika |
| `GET` | `/api/users/{id}` | Profil użytkownika (Bio + rola) |
| `PUT` | `/api/users/{id}/profile` | Edycja profilu (Bio, nazwa wyświetlana) |
| `PUT` | `/api/users/{id}/role` | Przypisanie roli |

### Przykłady

```bash
# Utworzenie użytkownika
curl -X POST http://localhost/api/users \
  -H "Content-Type: application/json" \
  -d '{"email":"jan@example.com","displayName":"Jan Kowalski","bio":"Lubię Symfony","role":"ROLE_USER","password":"secret123"}'

# Edycja Bio
curl -X PUT http://localhost/api/users/{id}/profile \
  -H "Content-Type: application/json" \
  -d '{"bio":"Nowe bio","displayName":"Jan Nowak"}'

# Przypisanie roli
curl -X PUT http://localhost/api/users/{id}/role \
  -H "Content-Type: application/json" \
  -d '{"role":"ROLE_ADMIN"}'

# Profil
curl http://localhost/api/users/{id}

# Lista
curl http://localhost/api/users

# Drzewo ról
curl http://localhost/api/roles

# Dodanie roli
curl -X POST http://localhost/api/roles \
  -H "Content-Type: application/json" \
  -d '{"role":"ROLE_EDITOR","label":"Redaktor","parentRole":"ROLE_MODERATOR"}'

# Lista grup
curl http://localhost/api/groups

# Utworzenie grupy
curl -X POST http://localhost/api/groups \
  -H "Content-Type: application/json" \
  -d '{"name":"Redakcja","description":"Zespół redakcyjny","role":"ROLE_MODERATOR"}'
```

### Odpowiedź create / show / update (sukces)

```json
{
  "status": "success",
  "errorMessage": null,
  "user": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "email": "jan@example.com",
    "displayName": "Jan Kowalski",
    "bio": "Lubię Symfony",
    "role": "ROLE_USER",
    "createdAt": "2026-09-10T19:00:00+00:00",
    "updatedAt": "2026-09-10T19:00:00+00:00"
  }
}
```

### Kody HTTP

| Kod | Znaczenie |
|-----|-----------|
| `200` | Sukces (GET, PUT) |
| `201` | Utworzono (POST) |
| `400` | Błąd walidacji |
| `404` | Użytkownik nie istnieje |
| `500` | Błąd serwera |

## Swagger / OpenAPI

Po instalacji bundle dokumentacja jest dostępna pod:

- **UI:** `/api/doc`
- **JSON:** `/api/doc.json`

Dokumentacja generowana jest przez [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle) z atrybutów OpenAPI w kontrolerze.

## Architektura

```
REST Controller
    → CommandBus / QueryBus (CQRS)
        → Handlers (CreateUser, UpdateProfile, AssignRole, CreateRole, CreateGroup, GetUser, ListUsers, GetRoleTree, ListGroups)
            → Domain (User, Bio, Role, RoleTree, Group)
            → UserRepositoryInterface (port)
                → InMemory / JSON filesystem
```

Własną persistencję (np. Doctrine) podłączasz implementując `UserRepositoryInterface`.

## Frontend Vue.js

Aplikacja Vue 3 (Vite + Vue Router + Pinia + Tailwind) do:

- listy użytkowników (filtr po roli z hierarchii)
- tworzenia użytkownika
- **edycji profilu (Bio i nazwa)**
- **przypisania roli z drzewa hierarchii**
- **dodawania ról i grup**

### Uruchomienie

```bash
cd frontend
npm install
npm run dev
```

Interfejs: [http://127.0.0.1:5173](http://127.0.0.1:5173)

Domyślnie Vite serwuje mock REST API zgodny z bundlem (seed: Jan Kowalski, Anna Nowak), żeby frontend działał bez hostującej aplikacji Symfony.

### Podłączenie do prawdziwego API Symfony

```bash
VITE_API_MOCK=false npm run dev
```

Vite proxy kieruje `/api` na `http://127.0.0.1:8080` (nadpiszesz przez `VITE_DEV_PROXY_TARGET`).

## Testy

```bash
composer install
vendor/bin/phpunit
```

## Jakość kodu (dev)

```bash
composer phpstan      # analiza statyczna (level 8)
composer cs-fix       # automatyczne formatowanie
composer cs-check     # sprawdzenie stylu bez zmian
composer quality      # cs-check + phpstan + phpunit
```


### Jeśli pakiet nie jest na Packagist, w composer.json hosta:
```bash
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:mainfreme/user-profile-bundel.git"
    }
  ]
}
```