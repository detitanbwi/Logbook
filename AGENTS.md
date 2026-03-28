# PROJECT KNOWLEDGE BASE

**Generated:** 2026-03-25T02:10:22Z
**Commit:** 017edc2
**Branch:** main

## OVERVIEW
Monorepo with a Laravel 12 backend API (`backend/`) and SvelteKit 5 frontend (`frontend/`) for role-based Logbook + KPI workflows.

## STRUCTURE
```text
Logbook/
├── backend/                 # Laravel API v1, Sanctum auth, observers-driven summaries
│   ├── app/Http/            # Requests, API controllers, resources, middleware
│   ├── routes/              # api.php, web.php, console.php
│   └── tests/               # Pest/PHPUnit feature + unit tests
├── frontend/                # SvelteKit app (static adapter, runes enabled)
│   ├── src/routes/          # Route groups: (auth), (app), (mobile)
│   ├── src/lib/api/         # API client, services, valibot schemas
│   └── src/lib/auth/        # Guard + permission matrix + role normalization
└── docs/                    # Plans/specs and architecture docs
```

## WHERE TO LOOK
| Task | Location | Notes |
|---|---|---|
| API entrypoints | `backend/routes/api.php` | All app APIs are under `/api/v1` |
| Auth/session flow | `backend/app/Http/Controllers/Api/V1/AuthController.php`, `frontend/src/lib/stores/auth.svelte.ts` | Sanctum backend + persisted frontend state |
| Route access control | `frontend/src/lib/auth/route-guards.ts`, `frontend/src/lib/auth/permissions.ts` | Centralized role/path authorization |
| KPI + logbook endpoints | `backend/app/Http/Controllers/Api/V1/*` | Business HTTP orchestration layer |
| Summary recalculation | `backend/app/Observers/*`, `backend/app/Services/DailySummaryService.php` | Service invoked by observers, not controllers |
| UI role segmentation | `frontend/src/routes/(app)/*`, `frontend/src/routes/(mobile)/*` | Admin/manager/staff/superadmin + mobile shell |

## CODE MAP
| Symbol | Type | Location | Refs | Role |
|---|---|---|---:|---|
| `Application::configure()` | bootstrap | `backend/bootstrap/app.php` | high | Registers routes/middleware/exceptions |
| `Route::prefix('v1')` | route root | `backend/routes/api.php` | high | API v1 boundary |
| `enforceAppRouteGuard()` | function | `frontend/src/lib/auth/route-guards.ts` | high | Guards app/mobile route groups |
| `canAccessPath()` | function | `frontend/src/lib/auth/permissions.ts` | high | Role/path permission matrix |
| `auth` | store singleton | `frontend/src/lib/stores/auth.svelte.ts` | high | Auth state + revalidation |
| `DailySummaryService` | service | `backend/app/Services/DailySummaryService.php` | medium | Derived summary sync logic |

## CONVENTIONS
- Treat `backend/` and `frontend/` as independent build/test units.
- Backend route contract is versioned (`/api/v1`) and protected by Sanctum for non-login paths.
- Frontend uses Svelte 5 runes and static adapter (`ssr=false`, `prerender=true` at root layout).
- Route groups are semantic (layout/domain), not URL path modifiers.

## ANTI-PATTERNS (THIS PROJECT)
- Do not use Svelte 4 reactivity (`$:`/`export let`) in frontend code.
- Do not call remote `/auth/logout` from unauthorized revalidation paths.
- Do not bypass `enforceAppRouteGuard()` with page-level ad-hoc role checks.
- Do not document or reason from generated artifacts (`.svelte-kit`, storage/framework views/cache).

## UNIQUE STYLES
- Frontend role matrix includes a legacy redirect rule from `/admin/audit-logs` to `/superadmin/audit-logs` for superadmins.
- Backend summary consistency uses model observers to trigger recalculation service methods.

## COMMANDS
```bash
# backend
cd backend && composer dev
cd backend && composer test

# frontend
cd frontend && pnpm dev
cd frontend && pnpm test
cd frontend && pnpm check && pnpm lint
```

## NOTES
- Ignore broken/generated symlink noise (e.g. `backend/public/storage`) during structural scans.
- Existing `backend/AGENTS.md` and `frontend/AGENTS.md` are active scope files; keep child AGENTS concise and non-duplicative.
