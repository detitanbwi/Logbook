# Staff Performance Analysis & Full-Page Detail Migration (2026-03-25)

## Problem Summary

1. Admin `/admin/staff-performance` used a right-side drawer for detail, limiting room for KPI/logbook analysis.
2. "Rata-rata bintang" sometimes appeared missing due to strict numeric rendering assumptions in frontend.
3. Some API-backed data looked incomplete in mobile and detail flows due to date-filter handling and pagination differences.

## Root Causes

### A) Detail UX constrained by drawer width
- Previous flow used `StaffDetailDrawer` + `SlideOutDrawer`, causing cramped data presentation for multi-section analysis.

### B) Rating rendering robustness
- Admin list rendered `average_rating.toFixed(1)` directly.
- If upstream payload contains non-number-but-coercible values, stars may fail to display.

### C) Backend summary date filtering gaps
- `SummaryController::kpiDaily()` and `SummaryController::teamDaily()` accepted `tanggal` but not `date_from/date_to`.
- Frontend sent date range filters, but backend ignored them in these methods.

### D) Mobile parity issues
- Mobile team tab fetched paginated `teamDaily` with default page size (risk of partial team).
- Mobile overview average rating included all rated statuses; admin summary rating uses accepted logbooks only.

## Implemented Changes

## 1) Admin Staff Performance moved to page-based detail

- Updated list page:
  - `frontend/src/routes/(app)/admin/staff-performance/+page.svelte`
  - Rows now navigate to `/admin/staff-performance/[user_id]` while preserving date filters in query params.
  - Added explicit **Aksi** column with **Lihat Detail** button.

- Added full detail page:
  - `frontend/src/routes/(app)/admin/staff-performance/[user_id]/+page.svelte`
  - Provides:
    - date filter controls,
    - summary card,
    - daily recap selector,
    - KPI detail section,
    - expandable logbook detail with attachments.

## 2) Backend API date-range support fixed

- File: `backend/app/Http/Controllers/Api/V1/SummaryController.php`
- Added `date_from/date_to` filtering to:
  - `kpiDaily()`
  - `teamDaily()`

This aligns backend behavior with existing frontend query usage.

## 3) Rating normalization + safer display

- Files:
  - `frontend/src/lib/api/services/analyticsService.ts`
  - `frontend/src/lib/api/services/summaryService.ts`
- Added normalization for `average_rating` to ensure number/null contract.

- File:
  - `frontend/src/routes/(app)/admin/staff-performance/+page.svelte`
- Updated rating render guard to display stars only when value is finite numeric after coercion.

## 4) Mobile parity improvements

- File:
  - `frontend/src/routes/(mobile)/m/overview/+page.svelte`
- Average rating now uses accepted logbooks only (parity with backend summary logic).

- File:
  - `frontend/src/lib/components/mobile/TeamPerformanceTab.svelte`
- Added `per_page: 100` to `teamDaily()` calls to reduce truncation risk.
- Stabilized list keying using `member.user?.id || member.user_id`.

- File:
  - `frontend/src/lib/components/mobile/TeamMemberCard.svelte`
- Added robust progress and duration fallback handling (supports `progress_percent` and hour-based fallback fields).
- Name fallback now supports both `member.user?.nama` and top-level `member.nama`.

## Validation Notes

Run from `frontend/`:

```bash
pnpm check
pnpm test src/lib/api/__tests__/api.test.ts src/lib/stores/__tests__/stores.test.ts
```

Run from `backend/` for summary behavior checks:

```bash
php artisan test --filter=Summary
```

## Next Optional Optimization

Consider introducing a dedicated endpoint for staff detail analytics in one response payload
to reduce client fan-out requests on full detail page (`dailyByUser + kpiDaily + logbooks`).
