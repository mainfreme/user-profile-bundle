# User Profile Bundle

Bundle Symfony dla użytkownika z edycją profilu (**Bio**) i **przypisaną rolą**.

Pełna instrukcja instalacji, konfiguracji i REST API znajduje się w [README.md](../README.md).

## Model domenowy

- **User** — agregat użytkownika
- **Bio** — treść profilu (max. długość konfigurowalna)
- **Role** — jedna przypisana rola z drzewa hierarchii (`ROLE_ADMIN` → `ROLE_MODERATOR` → `ROLE_USER` domyślnie)
- **Email**, **DisplayName**, **UserId** — value objects

## Operacje

1. Utworzenie użytkownika z opcjonalnym Bio i rolą (domyślnie `ROLE_USER`)
2. Edycja profilu: Bio + nazwa wyświetlana
3. Przypisanie roli z drzewa `role_tree`
4. Dodanie nowej roli do drzewa oraz utworzenie grupy
5. Odczyt profilu, listy użytkowników, hierarchii ról i grup

## Frontend Vue.js

Katalog `frontend/` zawiera SPA: lista użytkowników, edycja Bio i przypisanie roli.

```bash
cd frontend
npm install
npm run dev
```
