# Project History & Completed Milestones

This document serves as an archive of the original development phases and progress tracking for the initial build of the Logbook & KPI API Backend.

## Initial Development Phases (V1)

**Status**: ALL PHASES COMPLETED AND VERIFIED. The backend API is 100% functional, fully tested (Pest), documented (Scribe), localized to Bahasa Indonesia, and perfectly aligned with the original `SYSTEM_DESIGN.md`.

- [x] **Phase 1: Foundation (Database & Models)**
  - Setup migrations with UUID primary keys.
  - Create Eloquent Models (User, KpiMaster, UserKpiAssignment, Logbook, LogbookKpiDetail, AuditLog, Notification).
  - Write initial Factories.

- [x] **Phase 2: Core Services & Middleware**
  - API token setup (Laravel Sanctum).
  - Middleware for Role-Based Access Control (`Admin`, `Manager`, `Staff`).
  - Auth endpoints (`login`, `logout`, `me`, `change-password`).

- [x] **Phase 3: Admin Features**
  - `UsersController` for employee management.
  - `MasterKpiController` for maintaining KPI dictionary.
  - Automatic `AuditTrail` generation using Eloquent Observers.

- [x] **Phase 4: Manager Features**
  - `KpiAssignmentController` to distribute tasks.
  - `ManagerLogbookController` to rate or revert logbooks submitted by staff.

- [x] **Phase 5: Staff Features**
  - `LogbookController` for daily work execution (start, toggle tasks, submit).
  - Notifications recording for updates.

- [x] **Phase 6: Dashboards & Analytics**
  - Contextual Dashboard endpoints based on Role (Admin metrics, Manager team metrics, Staff personal metrics).

- [x] **Phase 7: Testing, Flow, & Finalization**
  - Implement realistic data with Seeders & localized Faker (`id_ID`).
  - Implement End-to-End System Flow test simulating a full working day loop.

- [x] **Phase 8: Storage & Localization**
  - Linked public storage for image handling (`php artisan storage:link`).
  - Endpoint adaptation for multipart form-data image uploads (`gambar_bukti`).
  - Full JSON response and notification translations to `Bahasa Indonesia`.

- [x] **Phase 9: System Design Validation & API Documentation**
  - Verify route alignment with `SYSTEM_DESIGN.md`.
  - Implement missing `GET /audit-logs` endpoint.
  - Guarantee 100% test coverage with automated Pest tests.
  - Generate complete OpenAPI specification using Scribe.

## Refactoring & Optimization (V1.1)

- [x] **Phase 10: API Pagination Refactoring**
  - **Backend**: Implemented `Illuminate\Http\Resources\Json\JsonResource` for User, Logbook, MasterKpi, and KpiAssignment.
  - **Backend**: Converted `.get()` to `.paginate($perPage)` across all index endpoints.
  - **Frontend SDK**: Standardized `PaginationParams` type in `core/types.ts`.
  - **Frontend SDK**: Deprecated and consolidated duplicate data services (`kpi.ts` -> `kpiService.ts`, `users.ts` -> `usersService.ts`).
  - **Frontend UI**: Created reusable `<Pagination />` Svelte component and integrated it across Admin, Manager, and Staff pages mapping tightly to Laravel's paginated response structure (`data.data` and `data.meta`).
  - **Testing**: Fixed Vitest and Pest Feature tests to accommodate nested JSON payload structures (`assertJsonCount` mapping).
  - **Bug Fix**: Removed unused `SoftDeletes` trait from the `Notification` Eloquent model to resolve a `SQLSTATE[HY000]: General error: 1 no such column: notifications.deleted_at` crash.

## Refactor Milestone (V2.0 - Manual Time & Summary Architecture)

**Status**: COMPLETED AND VERIFIED (Backend)

- [x] **Schema Refactor (Incremental Migration)**
  - Introduced new naming and structure without touching historical migrations.
  - Users: `name/nip` transitioned to `nama/npp` with alias compatibility.
  - Logbook: moved to manual time input (`tanggal`, `start_kerja`, `end_kerja`) and unified `lokasi`.
  - Status domain updated to `DRAFT | SUBMITTED | ACCEPTED | REJECTED`.
  - KPI details changed from boolean completion to numeric progress (`capaian_angka`, `target_angka`) + per-KPI `lampiran_file`.
  - Notification `read_at` removed.

- [x] **Pre-Aggregated Performance Layer**
  - Added `daily_staff_summaries` and `daily_kpi_summaries` tables.
  - Added `v_logbook_work_duration` view with 12:00-13:00 break overlap deduction.
  - Added `DailySummaryService` with `LogbookObserver` and `LogbookKpiDetailObserver` for automatic recalculation.

- [x] **API Refactor + Compatibility Strategy**
  - Core controllers updated to new schema and role semantics.
  - Added Summary API endpoints under `/api/v1/summaries/*`.
  - Kept temporary compatibility aliases for legacy routes and payload fields to reduce frontend breakage during transition.

## Contract Hardening Milestone (V2.1 - Canonical API Only)

**Status**: COMPLETED (Backend Contract)

- [x] Removed backward-compatibility aliases and transitional payload support.
  - Removed route aliases:
    - `PUT /api/v1/logbooks/{logbook}/rate`
    - `POST /api/v1/logbooks/{logbook}/revert`
    - `PATCH /api/v1/logbooks/{logbook}/kpi/{detail}/toggle`
  - Removed transitional auth input alias (`nip`); login now requires `npp`.
  - Removed transitional user payload aliases from API resources (`name`, `nip`); canonical fields are `nama`, `npp`.
  - Removed legacy logbook submit image alias flow (`gambar_bukti`) in favor of per-KPI attachment endpoints.
  - Notification target mapping now strictly uses canonical review types (`LOGBOOK_ACCEPTED`, `LOGBOOK_REJECTED`).

- [x] Updated tests and backend docs for canonical-only contract expectations.

- [x] **Seeder Refactor**
  - `DatabaseSeeder` aligned with V2 schema.
  - Added `MigrateExistingDataSeeder` to backfill summary tables from existing data.

- [x] **Testing & Validation**
  - Updated existing feature tests to new contract.
  - Added new tests for manual-time validation, numeric KPI progress, attachment lifecycle, summary endpoints, and migration/seeder compatibility.
  - Final verification:
    - **55 passed tests**
    - **253 assertions**

### Files Introduced in V2.0 (Backend)
- `app/Models/DailyStaffSummary.php`
- `app/Models/DailyKpiSummary.php`
- `app/Services/DailySummaryService.php`
- `app/Observers/LogbookObserver.php`
- `app/Observers/LogbookKpiDetailObserver.php`
- `app/Http/Controllers/Api/V1/SummaryController.php`
- `database/migrations/2026_03_20_000001_refactor_logbook_schema_for_manual_time_and_summaries.php`
- `database/seeders/MigrateExistingDataSeeder.php`
- `tests/Feature/Api/V1/SummaryEndpointsTest.php`
- `tests/Feature/SeedersAndMigrationCompatibilityTest.php`
