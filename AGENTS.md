# AGENTS.md — agencia-core

> Actúa como un **Senior Laravel Developer**. Mantén la integridad arquitectónica, escalabilidad y legibilidad del proyecto.

## Stack
- **Backend:** Laravel 13, PHP 8.2+ (Attributes for `#[Fillable]`)
- **Frontend:** Vue 3 (Composition API, `<script setup>`), Inertia v2
- **UI:** shadcn-vue (`components/ui/`), Dark/Light mode (esmeralda/zinc)
- **DB:** PostgreSQL (dev via Sail), SQLite (testing)
- **Auth:** Spatie Laravel Permission v8 (`guard_name: 'web'`, global roles)

## Architecture (DDD)
- **Controllers thin** → validate request, delegate to an `Action`
- **Actions** in `app/Domains/{Domain}/Actions/`
- **Models** in `app/Domains/{Domain}/` or `app/Models/` for global entities
- `Auth/` domain: `Role.php`, `Permission.php` (extend Spatie), `Actions/` (user+role CRUD)
- `Clients/` domain: `Company.php`, `Actions/` (company CRUD)
- **User model:** `App\Models\User` (NOT `App\Domains\Auth\User`)

## Validación
- Usa `FormRequests` para validaciones complejas, o validación directa en el controlador antes de invocar la Action

## Commands
```bash
./vendor/bin/sail up -d                             # Start Docker services
./vendor/bin/sail php artisan serve                 # Dev server
./vendor/bin/sail npm run dev                       # Vite HMR
./vendor/bin/sail npm run build                     # Production build
./vendor/bin/sail php artisan migrate:fresh --seed  # Reset DB + seed
./vendor/bin/sail php artisan test                  # All tests
composer run dev                                    # Alias: server+queue+logs+Vite
# If WSL node is needed (Windows npm fails on UNC paths):
#   wsl -e bash -l -c 'source ~/.nvm/nvm.sh; cd /home/development/agencia-core && npm run build'
```

## Auth / Permissions
- Guard always `'web'` — `Role::findByName($name, 'web')` in Actions
- `super-admin` gets all perms, `agente` only `*.read`
- Permissions: `{module}.{action}` (users/roles/companies × create/read/update/delete)
- Permission has `description` (text) and `group` (unsignedTinyInt 1-3)
- Spatie aliases in `bootstrap/app.php`: `role`, `permission`, `role_or_permission`
- Default admin: `admin@admin.com` / `password123`

## Routes
- Admin at `/admin` with `role:super-admin` middleware
- Always use named routes (e.g. `admin.users.index`, `companies.index`)
- Task routes scoped under company: `admin.companies.tasks.*` (e.g. `admin.companies.tasks.index`)
- Unified tasks entry point: `admin.tasks.index` (`/admin/tasks?company_id=`) — "Centro de Comando" with Kanban/List/Calendar views
- All "Volver" buttons from task Create/Edit/Show point to `route('admin.tasks.index', { company_id: ... })`

## Contexto del Proyecto
- Plataforma de gestión para agencias
- Prioridad de módulos futuros:
  1. Proyectos/Tareas
  2. Tickets de Soporte
  3. CRM (Leads)
  4. Facturación

## Pagination
- Default 10/page, selector 10/20/50/100 via `?perPage=X`
- Fields: `total`, `from`, `to`, `links` at **root level** (no `meta` wrapper)
- `links` is an **array**, each item has `url`, `label`, `active`

## Vue Conventions
- `Pages/Admin/{Roles,Users}/`, `Pages/Companies/`, `Pages/Auth/`
- Route names via Ziggy `route()` helper
- Delete via SweetAlert2; dark mode via `useDark()` from `@vueuse/core`
- Props: `const props = defineProps({...})` (not destructured)
- Paginated iteration: `list.data`, pagination metadata: `list.total`, `list.from`, etc.
- Per-page selector: add `perPage` ref + `changePerPage` via `router.get()` preserving query

## Development Workflow (new module)
1. **Migration** — table, columns, indexes
2. **Model** — relations, fillable, casts
3. **Actions** — Create/Update/Delete in `app/Domains/{Domain}/Actions/`
4. **Controller** — in `app/Http/Controllers/Admin/`
5. **Vue views** — Index, Create, Edit, Show in `resources/js/Pages/Admin/{Module}/`

## Naming
- Classes: `PascalCase`; JS vars: `camelCase`; migrations/tables: `snake_case`
- Use `syncRoles` / `syncPermissions` (Spatie) for many-to-many relations

## Instrucciones para la IA
- Antes de escribir código, verifica si el archivo ya existe
- Mantén el estilo de código existente al modificar
- Si tienes dudas sobre un cambio estructural, pregunta antes de ejecutar
