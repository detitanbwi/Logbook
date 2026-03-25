# Web Admin/SuperAdmin Frontend Alignment Design

## Summary

This design aligns the Desktop Web frontend for **Admin** and **SuperAdmin** with the approved plan in `docs/plans/02-WEB-ADMIN-PAGES.md` and the updated backend API contract. Work will be executed incrementally with centralized context so the process remains robust during long-running, multi-agent development.

## Goals

1. Match route structure and role capabilities exactly to the web plan.
2. Connect every Admin/SuperAdmin page end-to-end with canonical backend APIs.
3. Normalize frontend API SDK, schema, and state around canonical fields (`npp`, `nama`) and endpoints.
4. Establish centralized execution docs for compaction-safe progress tracking.

## Non-Goals

1. Redesign mobile staff/manager experience beyond shared SDK adjustments.
2. Major visual overhaul outside planned web admin pages.
3. Backend API behavior changes (already aligned in previous backend work).

## Canonical Role Matrix

- **SuperAdmin**: full Admin capabilities + manage Admin accounts + audit logs.
- **Admin**: operational management only (no admin-account management, no audit logs).

## Canonical Route Map

- `/superadmin/dashboard`
- `/superadmin/admins`
- `/superadmin/audit-logs`
- `/admin/dashboard`
- `/admin/users`
- `/admin/kpis`
- `/admin/kpi-assignments`
- `/admin/logbooks`
- `/admin/staff-performance`
- `/admin/reports`
- `/profile`
- `/notifications`

Legacy/mismatched routes are redirected or blocked via centralized role guards.

## API Contract Alignment

Mandatory frontend support:

- `PUT /api/v1/auth/profile`
- `GET /api/v1/users/{id}/subordinates`
- `GET /api/v1/audit-logs` (SuperAdmin-only UI)
- Canonical logbook review/revert flow (no legacy rate/toggle endpoints)

Mandatory canonical user fields:

- `npp` (not `nip`)
- `nama` (not `name`)

## Frontend Architecture

Layering:

Page/UI → Feature Store (Runes) → API Service (typed) → Schema Validation (Valibot) → Backend

Rules:

1. Pages do not call raw endpoints directly.
2. Role checks are centralized (permissions map + route guard utilities).
3. URL query params are source of truth for table/filter/sort/pagination pages.

## Incremental Delivery Strategy (Hybrid)

1. **Foundation pass**: route normalization + role guards + sidebar visibility + SDK/type baseline.
2. **Vertical slices** (end-to-end per page group):
   - Core admin ops: users, kpis, kpi-assignments
   - Monitoring: logbooks, staff-performance, reports
   - SuperAdmin exclusive: dashboard, admins, audit-logs
   - Cross-cutting: profile, notifications
3. **Hardening**: tests, docs, alignment matrix closure.

## Centralized Context and Documentation Model

Frontend long-running execution docs:

- `frontend/docs/WEB_ADMIN_EXECUTION_HUB.md` (single source of truth)
- `frontend/docs/WEB_ADMIN_API_ALIGNMENT.md`
- `frontend/docs/WEB_ADMIN_TEST_MATRIX.md`
- `frontend/docs/WEB_ADMIN_CHANGELOG.md`

Checkpoint protocol (every meaningful step):

1. Update current phase and next actions in execution hub.
2. Record modified files and validation evidence.
3. Record open decisions/blockers.
4. Record exact next start point for handoff/compaction recovery.

## Multi-Agent Execution Pattern

Parallel agents only for independent tracks:

- Agent A: route/guard/layout
- Agent B: API SDK/schema/type
- Agent C: page implementations
- Agent D: tests/docs consolidation

No overlap on the same files in one batch; each agent writes a handoff block into the execution hub.

## Risks and Mitigations

1. **Field drift (`nip`/`name`)** → canonical type enforcement + boundary mapper removal plan.
2. **Role drift** → single permissions map used by guard + navigation + pages.
3. **Endpoint drift** → API alignment doc as merge gate.
4. **Context loss** → execution hub mandatory updates per checkpoint.

## Acceptance Criteria

1. All planned Admin/SuperAdmin routes exist and enforce role access correctly.
2. All planned pages run end-to-end against updated backend APIs.
3. Frontend SDK/types/stores are canonicalized (`npp`, `nama`, updated endpoints).
4. Legacy endpoint usage removed from active code paths.
5. Test matrix and API alignment docs are complete and current.
