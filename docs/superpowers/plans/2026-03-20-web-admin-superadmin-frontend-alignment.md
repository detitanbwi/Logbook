# Web Admin/SuperAdmin Frontend Alignment Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver full end-to-end Desktop Web Admin/SuperAdmin feature parity with `docs/plans/02-WEB-ADMIN-PAGES.md`, connected to updated backend APIs with canonical frontend SDK/types/stores.

**Architecture:** Use a hybrid execution model: first normalize route/permission/API foundations, then implement vertical page slices end-to-end. All progress and decisions must be centralized in frontend docs to survive context compaction and support multi-agent handoffs.

**Tech Stack:** SvelteKit, Svelte 5 (Runes), TypeScript, Valibot, TailwindCSS, Vitest.

---

## Chunk 1: Centralized Docs & Foundation Contracts

### Task 1: Create long-running execution docs hub

**Files:**
- Create: `frontend/docs/WEB_ADMIN_EXECUTION_HUB.md`
- Create: `frontend/docs/WEB_ADMIN_API_ALIGNMENT.md`
- Create: `frontend/docs/WEB_ADMIN_TEST_MATRIX.md`
- Create: `frontend/docs/WEB_ADMIN_CHANGELOG.md`

- [ ] **Step 1: Write docs skeletons with mandatory sections**
  - Execution hub: current phase, done/next, blockers, handoff note
  - API alignment: route→endpoint→schema→role matrix
  - Test matrix: role x page x critical flow
  - Changelog: append-only slice updates

- [ ] **Step 2: Add checkpoint protocol to execution hub**
  - Every checkpoint must include changed files, validation commands, and next-start instructions.

- [ ] **Step 3: Validate markdown lint/style conventions if available**

- [ ] **Step 4: Commit**
  - `feat(frontend-docs): add web admin execution hub and alignment matrices`

### Task 2: Add centralized permissions and guard utilities

**Files:**
- Create: `frontend/src/lib/auth/permissions.ts`
- Create: `frontend/src/lib/auth/route-guards.ts`
- Modify: `frontend/src/routes/(app)/+layout.svelte`
- Modify: `frontend/src/lib/components/navigation/Sidebar.svelte`

- [ ] **Step 1: Write failing guard tests (if guard tests exist or add new)**
  - SuperAdmin allowed for `/superadmin/*`
  - Admin forbidden for `/superadmin/admins` and `/superadmin/audit-logs`
  - Admin + SuperAdmin allowed for `/admin/*`

- [ ] **Step 2: Implement centralized permission map and route predicates**

- [ ] **Step 3: Wire guard behavior into app layout and sidebar visibility**

- [ ] **Step 4: Run tests and app type checks**
  - `rtk pnpm test`
  - `rtk pnpm check`

- [ ] **Step 5: Commit**
  - `feat(frontend-auth): centralize admin/superadmin route permissions`

---

## Chunk 2: API SDK, Types, and Store Canonicalization (`npp` + updated endpoints)

### Task 3: Canonicalize user contract to `npp`/`nama`

**Files:**
- Modify: `frontend/src/lib/types/index.ts`
- Modify: `frontend/src/lib/api/schemas/user.schema.ts`
- Modify: `frontend/src/lib/api/services/usersService.ts`
- Modify: `frontend/src/lib/stores/auth.svelte.ts`
- Optional Create (temporary): `frontend/src/lib/api/mappers/user.mapper.ts`

- [ ] **Step 1: Write failing schema/type tests for `npp` and `nama`**

- [ ] **Step 2: Update types and schemas to canonical fields**

- [ ] **Step 3: Introduce temporary mapper only where legacy UI still depends on old keys**

- [ ] **Step 4: Remove direct `nip` usage from core admin/superadmin SDK paths**

- [ ] **Step 5: Run API/schema tests**
  - `rtk pnpm test src/lib/api`

- [ ] **Step 6: Commit**
  - `refactor(frontend-api): canonicalize user fields to npp and nama`

### Task 4: Add missing frontend API methods aligned to backend

**Files:**
- Modify: `frontend/src/lib/api/services/authService.ts`
- Modify: `frontend/src/lib/api/services/usersService.ts`
- Modify: `frontend/src/lib/api/index.ts`
- Modify: `frontend/src/lib/api/schemas/auth.schema.ts`

- [ ] **Step 1: Write failing tests for new methods**
  - `updateProfile` (`PUT /auth/profile`, multipart)
  - `getSubordinates(userId)` (`GET /users/{id}/subordinates`)

- [ ] **Step 2: Implement methods with request/response typing**

- [ ] **Step 3: Add validation/error normalization path in services**

- [ ] **Step 4: Run service/API tests**

- [ ] **Step 5: Commit**
  - `feat(frontend-api): add profile update and subordinates endpoints`

---

## Chunk 3: Route Normalization and Page Scaffolding

### Task 5: Normalize route tree to plan

**Files:**
- Create: `frontend/src/routes/(app)/superadmin/dashboard/+page.svelte`
- Create: `frontend/src/routes/(app)/superadmin/admins/+page.svelte`
- Create: `frontend/src/routes/(app)/superadmin/audit-logs/+page.svelte`
- Create: `frontend/src/routes/(app)/admin/kpi-assignments/+page.svelte`
- Create: `frontend/src/routes/(app)/admin/logbooks/+page.svelte`
- Create: `frontend/src/routes/(app)/admin/staff-performance/+page.svelte`
- Create: `frontend/src/routes/(app)/admin/reports/+page.svelte`
- Modify/redirect legacy mismatches (e.g. `admin/audit-logs`)

- [ ] **Step 1: Add placeholder pages with strict role guards and canonical titles**

- [ ] **Step 2: Add redirects/blocking for deprecated/mismatched routes**

- [ ] **Step 3: Validate navigation and sidebar links**

- [ ] **Step 4: Run route smoke tests/type checks**

- [ ] **Step 5: Commit**
  - `feat(frontend-routes): normalize admin and superadmin desktop route map`

---

## Chunk 4: Core Admin Vertical Slices (End-to-End)

### Task 6: `/admin/users` end-to-end modernization

**Files:**
- Modify: `frontend/src/routes/(app)/admin/users/+page.svelte`
- Modify: related local components used by this page
- Modify: user store/service/schema if needed

- [ ] **Step 1: Write failing UI/service tests for canonical `npp`/`nama` flow**

- [ ] **Step 2: Replace legacy `nip`/`name` bindings with canonical fields**

- [ ] **Step 3: Ensure create/edit/reset/delete all function with backend contract**

- [ ] **Step 4: Validate query param state sync (search/filter/sort/page)**

- [ ] **Step 5: Update docs hub + API alignment + test matrix**

- [ ] **Step 6: Commit**
  - `feat(frontend-admin): complete users page with canonical backend contract`

### Task 7: `/admin/kpis` end-to-end

**Files:**
- Modify: `frontend/src/routes/(app)/admin/kpis/+page.svelte`
- Modify: `frontend/src/lib/api/services/kpiService.ts`
- Modify: KPI schemas/types/tests as required

- [ ] **Step 1: Add failing tests for CRUD + filters/sort/pagination**
- [ ] **Step 2: Implement full page flow with validation and errors
`
- [ ] **Step 3: Verify role behavior for Admin + SuperAdmin**
- [ ] **Step 4: Update docs and commit**

### Task 8: `/admin/kpi-assignments` end-to-end

**Files:**
- Create/Modify: `frontend/src/routes/(app)/admin/kpi-assignments/+page.svelte`
- Modify: KPI assignment service/schema/store

- [ ] **Step 1: Add failing tests for assign/list/remove flows**
- [ ] **Step 2: Implement full assignment workflow with user/KPI selectors**
- [ ] **Step 3: Run tests/check, update docs, commit**

---

## Chunk 5: Monitoring Slices (End-to-End)

### Task 9: `/admin/logbooks` page

**Files:**
- Create/Modify: `frontend/src/routes/(app)/admin/logbooks/+page.svelte`
- Modify: manager/admin logbook services and schemas

- [ ] **Step 1: Add tests for list/filter/detail/review/revert actions**
- [ ] **Step 2: Implement page and action handlers**
- [ ] **Step 3: Verify canonical endpoints only (no rate/toggle legacy)**
- [ ] **Step 4: Update docs and commit**

### Task 10: `/admin/staff-performance` page

**Files:**
- Create/Modify: `frontend/src/routes/(app)/admin/staff-performance/+page.svelte`
- Modify: analytics/summary services/schemas

- [ ] **Step 1: Add tests for summary/drill-down and filters**
- [ ] **Step 2: Implement page with pagination/filter controls and charts/tables**
- [ ] **Step 3: Update docs and commit**

### Task 11: `/admin/reports` page

**Files:**
- Create/Modify: `frontend/src/routes/(app)/admin/reports/+page.svelte`
- Modify: report export service/schema

- [ ] **Step 1: Add tests for export request and download/error states**
- [ ] **Step 2: Implement full report/export flow**
- [ ] **Step 3: Update docs and commit**

---

## Chunk 6: SuperAdmin Exclusive Slices (End-to-End)

### Task 12: `/superadmin/dashboard`

**Files:**
- Create/Modify: `frontend/src/routes/(app)/superadmin/dashboard/+page.svelte`
- Modify: analytics/dashboard service usage

- [ ] **Step 1: Add tests for dashboard data load and cards**
- [ ] **Step 2: Implement page and guard checks**
- [ ] **Step 3: Update docs and commit**

### Task 13: `/superadmin/admins`

**Files:**
- Create/Modify: `frontend/src/routes/(app)/superadmin/admins/+page.svelte`
- Reuse users service with role filter and superadmin constraints

- [ ] **Step 1: Add tests for admin account CRUD/reset flows**
- [ ] **Step 2: Implement page with strict superadmin gating**
- [ ] **Step 3: Update docs and commit**

### Task 14: `/superadmin/audit-logs`

**Files:**
- Create/Modify: `frontend/src/routes/(app)/superadmin/audit-logs/+page.svelte`
- Modify: `frontend/src/lib/api/services/auditService.ts` (if needed)

- [ ] **Step 1: Add tests for list/filter/sort/pagination behavior**
- [ ] **Step 2: Implement page and forbidden fallback UX**
- [ ] **Step 3: Ensure `/admin/audit-logs` is no longer exposed as canonical route**
- [ ] **Step 4: Update docs and commit**

---

## Chunk 7: Cross-Cutting Pages + Hardening

### Task 15: `/profile` align with `PUT /auth/profile`

**Files:**
- Modify: `frontend/src/routes/(app)/profile/+page.svelte`
- Modify: auth/profile schemas/services/stores

- [ ] **Step 1: Add tests for profile update + photo upload + error states**
- [ ] **Step 2: Implement multipart profile update flow**
- [ ] **Step 3: Update docs and commit**

### Task 16: `/notifications` align with backend payload

**Files:**
- Modify: `frontend/src/routes/(app)/notifications/+page.svelte`
- Modify: notification service/schema/store/types

- [ ] **Step 1: Add tests for list/read/mark-all-read flows**
- [ ] **Step 2: Implement payload-aligned rendering and actions**
- [ ] **Step 3: Update docs and commit**

### Task 17: Final hardening and closure

**Files:**
- Modify: `frontend/docs/WEB_ADMIN_*` files
- Modify: relevant test files under `frontend/src/**/__tests__`

- [ ] **Step 1: Run full frontend verification**
  - `rtk pnpm test`
  - `rtk pnpm check`
  - `rtk pnpm lint`

- [ ] **Step 2: Confirm API alignment matrix is fully green**

- [ ] **Step 3: Confirm test matrix includes Admin and SuperAdmin critical flows**

- [ ] **Step 4: Commit**
  - `chore(frontend): finalize web admin and superadmin alignment`

---

## Execution Notes for Subagent-Driven Development

1. Execute by chunk; do not start next chunk until current chunk passes tests and docs checkpoint updates.
2. Use one implementer subagent per task, then spec-reviewer, then code-quality reviewer.
3. Maintain centralized context in `frontend/docs/WEB_ADMIN_EXECUTION_HUB.md` at every checkpoint.
4. Prefer parallel subagents only when file ownership does not overlap.
