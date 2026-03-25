# WEB Admin/SuperAdmin Alignment Changelog

## 2026-03-20

### Planned

- Established design + plan baseline for web desktop alignment:
  - `docs/superpowers/specs/2026-03-20-web-admin-superadmin-frontend-alignment-design.md`
  - `docs/superpowers/plans/2026-03-20-web-admin-superadmin-frontend-alignment.md`
- Locked canonical frontend user identifier field to `npp` (replace `nip`).
- Initialized centralized execution docs:
  - `frontend/docs/WEB_ADMIN_EXECUTION_HUB.md`
  - `frontend/docs/WEB_ADMIN_API_ALIGNMENT.md`
  - `frontend/docs/WEB_ADMIN_TEST_MATRIX.md`
  - `frontend/docs/WEB_ADMIN_CHANGELOG.md`

### Implemented

- Added centralized role/route foundation:
  - `frontend/src/lib/auth/permissions.ts`
  - `frontend/src/lib/auth/route-guards.ts`
- Updated app route guard integration in:
  - `frontend/src/routes/(app)/+layout.ts`
- Updated sidebar navigation to consume centralized permissions:
  - `frontend/src/lib/components/navigation/Sidebar.svelte`
- Verification summary:
  - `pnpm check` passed
  - targeted store tests passed
  - integration API e2e tests require backend runtime and failed with `ECONNREFUSED` in this session

### Implemented (Chunk 2 baseline)

- Canonical contract migration started (`npp`/`nama`):
  - Updated user types: `frontend/src/lib/types/index.ts`
  - Updated auth/user schemas: `frontend/src/lib/api/schemas/auth.schema.ts`, `frontend/src/lib/api/schemas/user.schema.ts`
  - Updated login/profile service: `frontend/src/lib/api/services/authService.ts`
  - Added `usersService.getSubordinates`: `frontend/src/lib/api/services/usersService.ts`
  - Updated auth/login UI and key consumers to `npp`/`nama` compatibility:
    - `frontend/src/routes/(auth)/login/+page.svelte`
    - `frontend/src/lib/stores/auth.svelte.ts`
    - `frontend/src/lib/components/navigation/Navbar.svelte`
    - `frontend/src/routes/(app)/admin/users/+page.svelte`
- Updated tests for new contracts:
  - `frontend/src/lib/api/__tests__/schema.test.ts`
  - `frontend/src/lib/api/__tests__/api.test.ts`
  - `frontend/src/lib/stores/__tests__/stores.test.ts`
  - `frontend/src/lib/api/__tests__/e2e-api.test.ts`
  - `frontend/src/lib/api/__tests__/e2e-api-comprehensive.test.ts`
- Verification summary:
  - `pnpm check` passed
  - targeted unit tests passed (33/33)

### Implemented (Route skeleton coverage)

- Added planned Admin/SuperAdmin route skeleton pages:
  - `/superadmin/dashboard`
  - `/superadmin/admins`
  - `/superadmin/audit-logs`
  - `/admin/kpi-assignments`
  - `/admin/logbooks`
  - `/admin/staff-performance`
  - `/admin/reports`
- Verification summary:
  - `pnpm check` passed

### Implemented (Guard + users flow hardening)

- Non-canonical route handling:
  - Removed legacy page file `frontend/src/routes/(app)/admin/audit-logs/+page.svelte`
  - Added role-based redirect handling in permissions/guard:
    - SuperAdmin redirected to `/superadmin/audit-logs`
    - others redirected to default dashboard
- Admin users page enhancements:
  - Added reset password modal flow
  - Aligned reset-password API payload with backend (`new_password`)
  - Continued canonical field usage (`npp`/`nama`) in page form/table paths
- Verification summary:
  - `pnpm check` passed
  - targeted API/store tests passed (33/33)

### Implemented (Admin users role constraints)

- Updated frontend user schemas and users page role options to follow backend constraints:
  - Removed `Manager` from create/update role picklist in canonical schema and page UI.
  - Current role creation options in `/admin/users`: `Admin`, `Staff`.
- Added schema test assertion ensuring `role: 'Manager'` is rejected at frontend validation layer.
- Verification summary:
  - `pnpm check` passed
  - targeted API/store tests passed (33/33)

### Implemented (`/admin/users` manager assignment + role-safe actions)

- Enhanced `/admin/users` to align with plan requirement "Assign Manager (Subordinates)":
  - Added manager assignment field in create/edit modal (`manager_id`).
  - Loaded manager candidates from Staff users and filtered invalid candidates by querying selected user's subordinates.
  - Displayed current manager column in users table.
- Added role-safe behavior for Admin actor:
  - Admin list queries are forced to `role=Staff`.
  - Role filter UI is shown only for SuperAdmin.
  - Edit/reset/delete actions are blocked for non-Staff records when actor is Admin.
  - Create/edit role select is constrained to Staff for Admin actor.
- Added API integration test coverage for subordinates endpoint call:
  - `frontend/src/lib/api/__tests__/api.test.ts` includes `usersService.getSubordinates` call assertion.
- Verification summary:
  - `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` passed (11/11)
  - `rtk pnpm check` passed

### Implemented (`/admin/kpis` vertical slice hardening)

- Hardened `/admin/kpis` CRUD UX and URL-driven list behavior:
  - Kept URL-driven search/filter/sort/pagination flow, with explicit handlers for search, status filter, and sorting to align with users-page pattern.
  - Added mutation-safe refresh trigger and request sequencing guard to avoid stale list overwrite during rapid URL/mutation updates.
  - Create/edit submit now trims `nama`, blocks empty names with toast feedback, and prevents duplicate submit while request in-flight.
  - Delete flow now refreshes list consistently and auto-navigates to previous page when deleting the last item on a non-first page.
  - Removed unused import/dead state usage in page script.
- Added/expanded KPI API integration assertions:
  - `kpiService.getAllMaster` query parameter pass-through assertion
  - `kpiService.createMaster` endpoint/body assertion
  - `kpiService.updateMaster` endpoint/body assertion
  - `kpiService.deleteMaster` endpoint assertion
- Verification summary:
  - `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` passed (15/15)
  - `rtk pnpm check` passed

### Implemented (`/admin/kpi-assignments` vertical slice)

- Implemented functional `/admin/kpi-assignments` page with URL-shareable selected staff state (`user_id`):
  - Staff selector loaded from `usersService.getAll({ role: 'Staff' })`.
  - Master KPI list loaded from `kpiService.getAllMaster(...)`.
  - Assignment list loaded from `kpiService.getAssignments(...)`.
  - Client-side derivation for selected staff:
    - Assigned KPIs
    - Available (active, not-yet-assigned) KPIs
  - Row-level actions:
    - Assign KPI via `kpiService.assignKpi({ user_id, kpi_id })`
    - Remove assignment via `kpiService.deleteAssignment(assignmentId)`
  - Added loading/error/retry and toast feedback aligned with existing admin page patterns.
- Added targeted API integration assertions for KPI assignment endpoints in:
  - `frontend/src/lib/api/__tests__/api.test.ts`
    - `kpiService.getAssignments`
    - `kpiService.assignKpi`
    - `kpiService.deleteAssignment`
- Verification summary:
  - `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` passed (18/18)
  - `rtk pnpm check` passed

### Implemented (`/admin/logbooks` vertical slice)

- Implemented functional monitoring page at `frontend/src/routes/(app)/admin/logbooks/+page.svelte`:
  - URL-driven list state: `search`, `status`, `date_from`, `date_to`, `page`, `per_page`, `sort_by`, `sort_dir`.
  - Data table with key columns: tanggal/created_at, staff (`nama`/`npp` with compatibility fallbacks), status, rating, created_at.
  - Row actions:
    - Review via modal with rating 1-5.
    - Revert via confirmation dialog.
  - Loading/error/retry and toast feedback aligned with existing admin pages.
- API endpoint alignment:
  - Added canonical method `managerLogbookService.reviewLogbook(...)` using `PUT /logbooks/{id}/review`.
  - Retained `rateLogbook(...)` as deprecated wrapper to avoid breaking older callers while ensuring this slice uses canonical endpoint.
- Added targeted API integration assertions in `frontend/src/lib/api/__tests__/api.test.ts`:
  - Canonical review endpoint `/logbooks/{id}/review`
  - Revert endpoint `/logbooks/{id}/revert`

### Implemented (`/admin/staff-performance` vertical slice)

- Implemented functional `/admin/staff-performance` page:
  - URL-driven date filters (`date_from`, `date_to`) with Apply/Reset controls.
  - Summary loading via `analyticsService.getStaffPerformanceSummary({ date_from, date_to })`.
  - Table columns aligned to contract: `nama`, `npp`, total/accepted/rejected, `target_angka_total`, `capaian_angka_total`, `progress_percent`.
  - Added loading/error/retry states and minimal KPI cards (total staff, total logbooks, average progress).
- API endpoint alignment:
  - Added `analyticsService.getStaffPerformanceSummary(...)` for `GET /summaries/staff-performance`.
- Added targeted API integration assertion in `frontend/src/lib/api/__tests__/api.test.ts`:
  - Ensures `/summaries/staff-performance` is called with `date_from` and `date_to` query params.

### Implemented (`/admin/reports` vertical slice)

- Implemented functional `/admin/reports` page:
  - Added minimal report type selector.
  - Added URL-driven date filters (`date_from`, `date_to`) with explicit Apply/Reset actions.
  - Added generate flow that calls analytics export service and handles loading/error/toast states.
  - Added result rendering for backend response payload and action link/button to open returned `download_url`.
- API contract alignment:
  - Updated `analyticsService.exportReports(...)` to accept params `{ report_type, date_from, date_to }`.
  - Updated response handling to JSON `{ message, download_url }` (no Blob response flow).
- Added targeted API integration assertion in `frontend/src/lib/api/__tests__/api.test.ts`:
  - Ensures `/reports/export` is called with query params and returns expected JSON payload handling.

### Implemented (`/superadmin/admins` vertical slice)

- Implemented functional SuperAdmin-only Admin management page at `frontend/src/routes/(app)/superadmin/admins/+page.svelte`:
  - URL-driven list state: `search`, `page`, `per_page`, `sort_by`, `sort_dir`.
  - Query-level hard scope: all list requests force `role=Admin`.
  - Data table + loading skeleton + empty state + error/retry pattern aligned with existing admin pages.
  - Row actions implemented: Edit, Reset Password, Delete (with confirm dialog).
  - Create/Edit modal fields: `npp`, `nama`, `email`, `password` (create-only), role fixed to `Admin` in UI.
  - Submit-level safety: create/update payloads always enforce `role: 'Admin'`; manager is not assignable from this page.
  - Canonical naming retained with compatibility fallback when reading legacy fields (`nama ?? name`, `npp ?? nip`).
- Added targeted API integration assertions in `frontend/src/lib/api/__tests__/api.test.ts` for this slice behavior:
  - `usersService.getAll(...)` passes role filter query (`role=Admin`) with URL-like params.
  - `usersService.create(...)` sends `role: 'Admin'` payload.
  - `usersService.update(...)` sends `role: 'Admin'` payload.
  - `usersService.resetPassword(...)` sends canonical `{ new_password }` payload.

### Implemented (`/superadmin/audit-logs` vertical slice)

- Implemented functional SuperAdmin audit log page at `frontend/src/routes/(app)/superadmin/audit-logs/+page.svelte`:
  - URL-driven list state: `search`, `action`, `date_from`, `date_to`, `sort_by`, `sort_dir`, `page`, `per_page`.
  - Data table columns aligned with plan: waktu, user, table, action, details button.
  - Added loading/empty/error states with retry and toast feedback, following existing admin page patterns.
  - Added details modal showing:
    - `old_data` and `new_data` in pretty JSON blocks,
    - metadata: `ip_address`, `user_agent`, `performed_at`, plus contextual identifiers.
  - Preserved canonical naming fallbacks on read (`nama ?? name`, `npp ?? nip`).
- API/service alignment:
  - Extended `auditService` params typing with supported backend filters/sort:
    - `action`, `date_from`, `date_to`, `sort_by` (`performed_at|created_at`), plus pagination/search.
  - Endpoint remains unchanged: `GET /api/v1/audit-logs`.
- Added targeted API integration assertion in `frontend/src/lib/api/__tests__/api.test.ts`:
  - `auditService.getAuditLogs(...)` query param pass-through for filtered/sorted paginated calls.

### Implemented (`/profile` hardening + auth profile API alignment)

- Updated `frontend/src/routes/(app)/profile/+page.svelte` with canonical profile update flow:
  - Added editable profile form fields for `nama` and `email`.
  - Added optional photo upload input (`type="file"`, `accept="image/*"`).
  - Submit now calls `authService.updateProfile(...)`:
    - JSON path for `{ nama, email }` when no file selected.
    - FormData path when photo is selected (`nama`, `email`, `foto`).
  - After successful update, refreshes authenticated user state via `auth.fetchMe()`.
  - Introduced isolated loading/message state for profile section, while keeping password-change behavior intact with separate loading/message state.
- Expanded API integration tests in `frontend/src/lib/api/__tests__/api.test.ts`:
  - `authService.updateProfile` JSON payload path assertion (`PUT /auth/profile`).
  - `authService.updateProfile` FormData path assertion (`PUT /auth/profile`, `Content-Type` not forced to JSON).
- Updated profile payload schema in `frontend/src/lib/api/schemas/auth.schema.ts` to include canonical `nama` and `email` fields for JSON validation path.

### Implemented (`/notifications` completion + hardening)

- Completed `/notifications` page behavior with explicit state handling while preserving URL-driven filtering:
  - Kept URL query-driven list state: `is_read`, `type`, `page`, `per_page`.
  - Added explicit load/error/empty rendering states:
    - skeleton loading state,
    - dedicated error panel + retry action,
    - contextual empty message for filtered vs unfiltered results.
  - Added robust user feedback for read actions:
    - toast success/error for `markAsRead` and `markAllAsRead`,
    - inline status alert (`aria-live="polite"`) for visible immediate feedback.
  - Added in-flight action guarding:
    - disables row-level and bulk mark-read buttons while requests are pending,
    - button labels switch to `Memproses...` during mutation.
  - Preserved keyboard/click accessibility behavior for list items (`role="button"`, `tabindex="0"`, Enter/Space activation).
  - Added safe rollback + feedback for notification item open flow when mark-read/navigate sequence fails.
- Store hardening (`frontend/src/lib/stores/notification.svelte.ts`):
  - `markAsRead` and `markAllAsRead` now set error state and rethrow on failure so caller pages can surface clear feedback.
- Added targeted API test coverage in `frontend/src/lib/api/__tests__/api.test.ts`:
  - `notificationService.getNotifications` query-param assertion (`is_read`, `type`, `page`, `per_page`),
  - `notificationService.read(id)` endpoint assertion (`PUT /notifications/{id}/read`),
  - `notificationService.readAll()` endpoint assertion (`PUT /notifications/read-all`).
- Verification summary:
  - `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` passed (32/32)
  - `rtk pnpm check` passed

### Implemented (Componentization refactor: notifications + superadmin audit details)

- Refactored large Svelte page markup into focused reusable components with no behavior changes:
  - Notifications page (`frontend/src/routes/(app)/notifications/+page.svelte`):
    - Extracted notification row rendering into
      `frontend/src/lib/components/notifications/NotificationListItem.svelte`.
    - Extracted loading/error/empty feedback panel into
      `frontend/src/lib/components/notifications/NotificationsStatePanel.svelte`.
  - SuperAdmin audit logs page (`frontend/src/routes/(app)/superadmin/audit-logs/+page.svelte`):
    - Extracted modal details body into
      `frontend/src/lib/components/audit/AuditLogDetailsContent.svelte`.
- Design constraints preserved:
  - URL-driven page state logic remains in page-level route files.
  - Components receive typed props and callbacks for interactions.
  - Existing accessibility behavior preserved for interactive notification rows.
- Verification summary:
  - `rtk pnpm check` passed
  - `rtk pnpm test -- src/lib/api/__tests__/api.test.ts` passed

### Implemented (Route guard tests + canonical drift/normalization hardening sync)

- Added route-access guard unit coverage in:
  - `frontend/src/lib/auth/__tests__/route-guards.test.ts`
  - Coverage includes:
    - unauthenticated redirect,
    - initialization failure redirect,
    - `/superadmin/*` allow/deny behavior,
    - `/admin/*` allow/deny behavior,
    - shared authenticated route coverage (`/profile`, `/notifications`),
    - legacy `/admin/audit-logs` redirect matrix.
- Completed canonical drift sweep follow-through and service/schema hardening:
  - New normalization helper module:
    - `frontend/src/lib/api/schemas/user-normalization.schema.ts`
  - Service-level normalization adoption:
    - `frontend/src/lib/api/services/authService.ts`
    - `frontend/src/lib/api/services/usersService.ts`
    - `frontend/src/lib/api/services/auditService.ts`
    - `frontend/src/lib/api/services/staffLogbookService.ts`
    - `frontend/src/lib/api/services/analyticsService.ts`
  - Updated API tests include normalization behavior assertions:
    - `frontend/src/lib/api/__tests__/api.test.ts`
      (includes legacy-to-canonical actor normalization assertions for audit/user payloads)
- Verification summary (docs checkpoint):
  - `rtk pnpm check` passed
