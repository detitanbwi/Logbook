# WEB Admin/SuperAdmin Test Matrix

## Roles

- SuperAdmin
- Admin

## Coverage Matrix

| Page / Flow | SuperAdmin | Admin | Status |
|---|---|---|---|
| Route access guard (`/superadmin/*`) | Required allow | Required deny | In Progress (unit guard coverage added via `route-guards.test.ts`; e2e route assertions pending) |
| Route access guard (`/admin/*`) | Required allow | Required allow | In Progress (unit guard coverage added via `route-guards.test.ts`; e2e route assertions pending) |
| Route existence (`/superadmin/*`, `/admin/*` planned pages) | Required | Required (shared `/admin/*`) | Baseline created |
| `/superadmin/admins` CRUD Admin accounts | Required | N/A (forbidden) | In Progress (URL-state list + CRUD/reset/delete UI implemented; targeted `usersService` endpoint assertions added) |
| `/superadmin/audit-logs` list/filter | Required | N/A (forbidden) | In Progress (URL-state list/filter/sort/pagination + details modal implemented; `auditService` filtered query-param assertion covered) |
| `/admin/users` list/create/edit/delete/reset | Required | Required | In Progress (manager assignment + admin scope controls + subordinates API test covered) |
| `/admin/kpis` CRUD | Required | Required | In Progress (CRUD UX hardening + KPI service endpoint assertions covered) |
| `/admin/kpi-assignments` assign/remove | Required | Required | In Progress (page UX implemented + KPI assignment service assertions covered) |
| `/admin/logbooks` list/review/revert | Required | Required | In Progress (URL-state list + review/revert actions + endpoint assertions covered) |
| `/admin/staff-performance` summary/drilldown | Required | Required | In Progress (URL date filters + table + staff-performance endpoint query assertion covered) |
| `/admin/reports` export flow | Required | Required | In Progress (export params + method/JSON response handling assertions covered) |
| `/profile` update profile + photo upload | Required | Required | In Progress (profile edit UI aligned + `authService.updateProfile` JSON/FormData assertions covered) |
| `/notifications` list/read/mark-all-read | Required | Required | In Progress (URL filter/query behavior + `notificationService` list/read/read-all endpoint assertions covered) |

## Validation Commands

- `rtk pnpm test`
- `rtk pnpm check`
- `rtk pnpm lint`

### Latest Evidence (Chunk 1 + Chunk 2 baseline)

- `rtk pnpm check` → pass
- `rtk pnpm test -- src/lib/api/__tests__/schema.test.ts src/lib/api/__tests__/api.test.ts src/lib/stores/__tests__/stores.test.ts` → pass (33 tests)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (11 tests, includes `usersService.getSubordinates` assertion)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (15 tests, includes `kpiService` CRUD + query-param assertions for `/admin/kpis` flow)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (18 tests, includes `kpiService.getAssignments`, `kpiService.assignKpi`, `kpiService.deleteAssignment` assertions for `/admin/kpi-assignments` flow)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (20 tests, includes canonical `managerLogbookService.reviewLogbook` and `managerLogbookService.revertLogbook` endpoint assertions for `/admin/logbooks` flow)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (21 tests, includes `analyticsService.getStaffPerformanceSummary` query-param assertion for `/admin/staff-performance` flow)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (22 tests, includes `analyticsService.exportReports` params/method + JSON response handling assertion for `/admin/reports` flow)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (26 tests, includes `/superadmin/admins`-related `usersService` assertions for role query, create/update role payload, and reset-password payload)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (27 tests, includes `/superadmin/audit-logs`-related `auditService.getAuditLogs` filtered query-param assertion)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (29 tests, includes `/profile`-related `authService.updateProfile` JSON + FormData path assertions)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (32 tests, includes `/notifications`-related `notificationService.getNotifications` filtered query params + `read(id)` + `readAll()` endpoint assertions)
- `rtk pnpm test -- src/lib/auth/__tests__/route-guards.test.ts` → pass (6 tests, includes `/superadmin/*`, `/admin/*`, shared routes, and legacy `/admin/audit-logs` redirect matrix)
- `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` → pass (includes service/schema normalization assertions for legacy `name/nip` → canonical `nama/npp` behavior)
- `rtk pnpm check` → pass (`svelte-check found 0 errors and 0 warnings`)
- Full e2e API tests require backend runtime and can fail with `ECONNREFUSED` if backend is not running.

## Notes

- Every completed slice must update this matrix with evidence references (test file names and command output summary).
