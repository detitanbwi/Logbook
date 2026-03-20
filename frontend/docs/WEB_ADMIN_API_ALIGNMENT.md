# WEB Admin API Alignment Matrix

## Canonical Rules

- User identifier field: **`npp`** (not `nip`)
- User name field: **`nama`** (not `name`)
- Legacy endpoints must not be used in active UI flows:
  - `/logbooks/{id}/rate`
  - `/logbooks/{id}/kpi/{detail}/toggle`

## Route to Endpoint Mapping

| Web Route | Role | Endpoint(s) | Status |
|---|---|---|---|
| `/superadmin/dashboard` | SuperAdmin | `GET /api/v1/dashboard/admin` | Planned |
| `/superadmin/admins` | SuperAdmin | `GET/POST/PUT/DELETE /api/v1/users` (role=Admin), `PUT /api/v1/users/{id}/reset-password` | In Progress (URL-state list + Admin-scoped CRUD/reset/delete implemented; role forced in query + submit payload) |
| `/superadmin/audit-logs` | SuperAdmin | `GET /api/v1/audit-logs` | In Progress (URL-state list + filter/sort/pagination + details modal implemented; auditService query-param + actor-normalization assertions added) |
| `/admin/dashboard` | Admin, SuperAdmin | `GET /api/v1/dashboard/admin` | Existing |
| `/admin/users` | Admin, SuperAdmin | `GET/POST/PUT/DELETE /api/v1/users`, `PUT /api/v1/users/{id}/reset-password`, `GET /api/v1/users/{id}/subordinates` | In Progress (manager assignment UX + admin scope controls aligned; service normalization hardened for canonical `nama/npp`) |
| `/admin/kpis` | Admin, SuperAdmin | `GET/POST/PUT/DELETE /api/v1/kpi/master` | In Progress (CRUD + URL-state hardening aligned to current contract) |
| `/admin/kpi-assignments` | Admin, SuperAdmin | `GET/POST/DELETE /api/v1/kpi/assignments` | In Progress (assign/remove UX + URL `user_id` state + service endpoint assertions aligned) |
| `/admin/logbooks` | Admin, SuperAdmin | `GET /api/v1/logbooks`, `PUT /api/v1/logbooks/{id}/review`, `POST /api/v1/logbooks/{id}/revert` | In Progress (URL-state list + review/revert actions implemented; API assertions added) |
| `/admin/staff-performance` | Admin, SuperAdmin | `GET /api/v1/summaries/staff-performance` | In Progress (URL date filters + summary table + endpoint query assertion aligned) |
| `/admin/reports` | Admin, SuperAdmin | `GET /api/v1/reports/export` | In Progress (export UI + URL date filters + JSON response contract aligned) |
| `/profile` | All authenticated | `GET /api/v1/auth/me`, `PUT /api/v1/auth/profile` | In Progress (profile update form aligned to canonical `nama`/`email` + optional photo FormData path, password-change flow retained) |
| `/notifications` | All authenticated | `GET /api/v1/notifications`, `PUT /api/v1/notifications/{id}/read`, `PUT /api/v1/notifications/read-all` | In Progress (URL-driven filters/pagination preserved; explicit loading/error/empty UX and read/read-all feedback hardening implemented; targeted notification service endpoint assertions added) |

## SDK Required Methods

1. `authService.updateProfile(payload|FormData)` → `PUT /auth/profile`
2. `usersService.getSubordinates(userId)` → `GET /users/{id}/subordinates`
3. Preserve canonical manager review/revert APIs; no legacy `rate` route usage.

### Implementation Status

- `usersService.getSubordinates` ✅ implemented
- `authService.updateProfile` ✅ implemented
- `usersService.resetPassword(id, new_password)` ✅ payload aligned

## Legacy Route Handling

- `/admin/audit-logs` is no longer canonical.
- Guard behavior:
  - SuperAdmin → redirected to `/superadmin/audit-logs`
  - Admin/other roles → redirected to role default dashboard

## Field Contract Tracking (User)

| Contract Field | Backend Canonical | Frontend Status |
|---|---|---|
| User ID | `id` | Existing |
| Employee Number | `npp` | In Progress (types/schemas/login/sdk updated) |
| Display Name | `nama` | In Progress (types/schemas/store/navbar/users page updated) |
| Role | `role` (`Staff`,`Admin`,`SuperAdmin`) | Partial (`Manager` logical via `has_subordinates`) |
| Manager Link | `manager_id` | Existing |

### Normalization Hardening Status

- Canonical normalization helpers are now centralized in:
  - `frontend/src/lib/api/schemas/user-normalization.schema.ts`
- Active service-layer adoption:
  - `authService` (`getMe`, login/user reads)
  - `usersService` (list/detail/subordinates reads)
  - `auditService` (audit actor normalization)
  - `staffLogbookService` (entity user normalization)
  - `analyticsService` (staff/subordinate identity normalization)
- Resulting contract decision:
  - Services should expose canonical `nama/npp` values to UI callers even when backend returns legacy `name/nip` fields.
  - UI-level fallback rendering is retained only where needed for transition safety, not as primary contract handling.

## `/admin/kpis` Contract Notes

- Active frontend contract (implemented and verified against service usage):
  - List: `kpiService.getAllMaster({ page, per_page, search, status_aktif, sort_by, sort_dir })`
  - Create: `kpiService.createMaster({ nama, status_aktif })`
  - Update: `kpiService.updateMaster(id, { nama, status_aktif })`
  - Delete: `kpiService.deleteMaster(id)`
- Deliberate scope decision:
  - Plan mockup includes optional attributes like `target`, `satuan`, `deskripsi`, but current frontend schema/service contract only exposes `nama` and `status_aktif`; page remains aligned to current backend API shape to avoid speculative field drift.

## `/admin/kpi-assignments` Contract Notes

- Active frontend contract (implemented and verified against service usage):
  - Staff options: `usersService.getAll({ role: 'Staff', ... })`
  - Master KPI list: `kpiService.getAllMaster({ ... })`
  - Assignment list: `kpiService.getAssignments({ per_page })`
  - Assign mutation: `kpiService.assignKpi({ user_id, kpi_id })`
  - Remove mutation: `kpiService.deleteAssignment(assignmentId)`
- State handling decision:
  - Selected staff is persisted in URL query param `user_id` for reload-safe and shareable state.
  - Assigned/available KPI lists are derived client-side from loaded assignment + master KPI data, avoiding additional endpoint coupling.

## `/admin/logbooks` Contract Notes

- Active frontend contract (implemented and verified against service usage):
  - List: `staffLogbookService.getLogbooks({ search, status, date_from, date_to, page, per_page, sort_by, sort_dir })`
  - Review mutation (canonical): `managerLogbookService.reviewLogbook(id, { rating })` → `PUT /logbooks/{id}/review`
  - Revert mutation: `managerLogbookService.revertLogbook(id)` → `POST /logbooks/{id}/revert`
- Endpoint alignment decision:
  - `managerLogbookService.rateLogbook(...)` is retained as deprecated compatibility wrapper, internally routed to canonical `reviewLogbook(...)` so active `/admin/logbooks` flow does not call legacy `/rate`.

## `/admin/staff-performance` Contract Notes

- Active frontend contract (implemented and verified against service usage):
  - Summary list: `analyticsService.getStaffPerformanceSummary({ date_from, date_to })`
  - Endpoint: `GET /summaries/staff-performance`
- State handling decision:
  - Date filter state is URL-driven (`date_from`, `date_to`) with explicit Apply/Reset actions to keep navigation and reload behavior deterministic.
  - Response is consumed as-is (`items`) without additional schema overengineering for this slice; formatting and KPI aggregation are handled at page level.

## `/admin/reports` Contract Notes

- Active frontend contract (implemented and verified against service usage):
  - Export action: `analyticsService.exportReports({ report_type, date_from, date_to })`
  - Endpoint: `GET /reports/export`
  - Response shape: `{ message, download_url }`
- State handling decision:
  - Date range filter state is URL-driven (`date_from`, `date_to`) via explicit Apply/Reset actions.
  - `report_type` is local page state with minimal options to match current slice scope.
  - Frontend renders and opens backend-provided `download_url` instead of handling Blob download in the API layer.

## `/superadmin/audit-logs` Contract Notes

- Active frontend contract (implemented and verified against service usage):
  - List query: `auditService.getAuditLogs({ search, action, date_from, date_to, sort_by, sort_dir, per_page, page })`
  - Endpoint: `GET /audit-logs`
  - Supported sort fields: `performed_at`, `created_at`
- State handling decision:
  - Entire list state is URL-driven for reload-safe/shareable behavior (`search`, `action`, `date_from`, `date_to`, `sort_by`, `sort_dir`, `per_page`, `page`).
  - Details modal is local UI state and does not mutate URL.
  - Details view safely pretty-prints `old_data/new_data` whether backend returns JSON object or JSON string payload.

## `/profile` Contract Notes

- Active frontend contract (implemented and verified against service usage):
  - Read current user: `authService.getMe()` (via `auth.fetchMe()` refresh after successful update)
  - Profile update JSON path: `authService.updateProfile({ nama, email })`
  - Profile update FormData path: `authService.updateProfile(formData)` with optional `foto`
  - Password update flow remains unchanged: `authService.changePassword(...)`
- State handling decision:
  - `/profile` uses separate loading/message states per section (`Informasi Profil` vs `Ubah Password`) so requests and feedback are isolated.
  - Canonical field usage enforced in submit payloads (`nama`, `email`) while keeping display fallback compatibility (`nama ?? name`, `npp ?? nip`).

## `/notifications` Contract Notes

- Active frontend contract (implemented and verified against service usage):
  - List query: `notificationService.getNotifications({ is_read, type, page, per_page })`
  - Mark one read: `notificationService.read(id)`
  - Mark all read: `notificationService.readAll()`
  - Endpoints: `GET /notifications`, `PUT /notifications/{id}/read`, `PUT /notifications/read-all`
- State handling decision:
  - Query state remains URL-driven (`is_read`, `type`, `page`, `per_page`) for reload/share-safe behavior.
  - Page now explicitly surfaces loading/error/empty states and mutation feedback (inline + toast) without introducing endpoint or payload changes.
