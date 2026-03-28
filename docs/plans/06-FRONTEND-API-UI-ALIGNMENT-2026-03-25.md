# Frontend API + UI Alignment (2026-03-25)

## Scope

This update aligns frontend behavior with the active backend API contract (`/api/v1`), improves role-specific UX for desktop and mobile app shells, and records current verification status.

## Implemented Changes

### 1) API Client Contract Hardening

- File: `frontend/src/lib/api/core/client.ts`
- Added API base URL normalization to reduce env drift:
  - `PUBLIC_API_URL=http://host/api/v1` → used as-is
  - `PUBLIC_API_URL=http://host/api` → auto-normalized to `/api/v1`
  - `PUBLIC_API_URL=http://host` → auto-normalized to `/api/v1`
- `FetchOptions.params` now accepts generic objects (not only `Record<string, unknown>`) and is safely serialized.
- Existing token persistence hardening retained:
  - safe localStorage access
  - fallback clear behavior when `removeItem` is unavailable

### 2) Analytics Schema Alignment

- File: `frontend/src/lib/api/schemas/analytics.schema.ts`
- Added optional fields matching current backend dashboard payloads:
  - `total_active_users`
  - `total_logbooks_this_month`
  - `total_logbooks_last_month`
  - `pending_logbooks_count`
  - `logbook_trend`
  - `logbooks_by_day[]`
  - `logbooks_by_status[]`
  - `users_by_role[]`
  - `personal_kpi_completion_rate`
  - `missed_logbooks_count`

### 3) Tests Updated to Current API/Flow

- Files:
  - `frontend/src/lib/api/__tests__/api.test.ts`
  - `frontend/src/lib/stores/__tests__/stores.test.ts`
  - `frontend/src/lib/api/__tests__/e2e-api.test.ts`
  - `frontend/src/lib/api/__tests__/e2e-api-comprehensive.test.ts`
- Updated test assumptions:
  - base API mock uses `/api/v1`
  - FormData `PUT` path asserts method-spoof behavior (`POST` + `_method=PUT`)
  - profile and user create/update mocks now use envelope `{ data: ... }`
  - E2E credentials updated to `password123`
  - corrected submit condition in E2E staff flow (`submit when not SUBMITTED`)
- Added explicit localStorage mock in API/store tests to avoid brittle environment behavior.

### 4) UX Reliability Improvements (Desktop + Mobile)

- Files:
  - `frontend/src/routes/(app)/manager/dashboard/+page.svelte`
  - `frontend/src/routes/(app)/staff/dashboard/+page.svelte`
- Fixed retry behavior:
  - introduced `fetchTriggered` reactive trigger
  - retry button now reliably re-runs data load
  - each reload resets `loading` + `error` before fetch

## Verification

### Passed

- `pnpm test src/lib/api/__tests__/api.test.ts src/lib/api/__tests__/schema.test.ts src/lib/stores/__tests__/stores.test.ts`
  - Result: **55 passed / 0 failed**
- `pnpm check`
  - Result: **0 errors**, warnings only (pre-existing accessibility label warnings in unrelated files)

### Not Fully Green (Environment/Backend State)

- `pnpm test src/lib/api/__tests__/e2e-api.test.ts src/lib/api/__tests__/e2e-api-comprehensive.test.ts`
  - `e2e-api-comprehensive.test.ts` staff suite failed due to backend runtime/auth state:
    - `Too Many Attempts.`
    - `Unauthenticated.`

These failures are external-state dependent and not caused by frontend compile/type regressions.

## Notes / Follow-up

1. If rate limits are expected in CI/local backend, add deterministic test fixtures/seeding and/or throttle-safe waits for E2E suites.
2. Remaining `pnpm check` warnings are accessibility-label warnings in existing forms and can be cleaned in a dedicated a11y pass.
3. Future API service cleanup should centralize response envelope unwrapping to avoid repeated `(response as any).data ?? response` patterns in pages.
