# System Context: Manajemen Logbook & KPI Pegawai

## Overview
Aplikasi ini dirancang untuk mendigitalisasi pelaporan kerja harian (Logbook) dan manajemen Key Performance Indicator (KPI) atau tugas karyawan. 

## Roles (Current)
- **SuperAdmin**: Full system access, manage Admin and Staff.
- **Admin**: Manage master data, users (primarily Staff), KPI, reports, monitoring.
- **Staff**: Submit manual logbook and KPI progress.
- **Manager Capability**: Determined by subordinate relation (`users.manager_id`), not a dedicated role enum.

## Core Entities (Current)
- `User` (`nama`, `npp`, role: `SuperAdmin|Admin|Staff`)
- `KpiMaster` (`target_angka`, `satuan`, `deskripsi`)
- `UserKpiAssignment`
- `Logbook` (manual time input: `tanggal`, `start_kerja`, `end_kerja`, `lokasi`; status: `DRAFT|SUBMITTED|ACCEPTED|REJECTED`)
- `LogbookKpiDetail` (`capaian_angka`, `target_angka`, `lampiran_file`)
- `DailyStaffSummary` (pre-aggregated daily staff performance)
- `DailyKpiSummary` (pre-aggregated daily KPI performance)
- `AuditLog`
- `Notification`

## Refactor Context (March 2026)

### Implemented Backend Scope
- ✅ Migration refactor delivered in incremental migration (`2026_03_20_000001_refactor_logbook_schema_for_manual_time_and_summaries.php`)
- ✅ Seeder updates delivered:
  - `DatabaseSeeder` aligned to new schema/role semantics
  - `MigrateExistingDataSeeder` backfills daily summary tables
- ✅ Summary service and observers delivered:
  - `DailySummaryService`
  - `LogbookObserver`
  - `LogbookKpiDetailObserver`
- ✅ Summary API delivered:
  - `/api/v1/summaries/daily`
  - `/api/v1/summaries/daily/{user_id}`
  - `/api/v1/summaries/period`
  - `/api/v1/summaries/kpi/daily`
  - `/api/v1/summaries/kpi/period`
  - `/api/v1/summaries/team/daily`
  - `/api/v1/summaries/staff-performance`

### Breaking Changes (V2 Canonical Contract)
- Alias input has been removed:
  - Login now only accepts `npp` (not `nip`)
  - User payloads now use `nama` and `npp` only (not `name`/`nip`)
- Legacy endpoint aliases have been removed:
  - ❌ `PUT /api/v1/logbooks/{logbook}/rate`
  - ❌ `POST /api/v1/logbooks/{logbook}/revert`
  - ❌ `PATCH /api/v1/logbooks/{logbook}/kpi/{detail}/toggle`
- Canonical replacements:
  - ✅ `PUT /api/v1/logbooks/{logbook}/review` with `{ decision: ACCEPTED|REJECTED, rating, reviewer_comment? }`
  - ✅ `PATCH /api/v1/logbooks/{logbook}/kpi/{detail}/progress` with `{ capaian_angka }`

### Quality Verification
- Full backend test suite status after latest hardening:
  - **85 tests passed**, **329 assertions**
- Additional edge-case coverage now includes:
  - review requires `reviewer_comment`
  - non-manager without subordinates cannot review
  - attachment update is blocked for non-DRAFT logbooks
  - summary date filtering and period zero-target fallback
  - team daily access behavior for admin vs staff
  - submit requires at least one KPI progress before `SUBMITTED`
  - submit sends manager notification (`LOGBOOK_SUBMITTED`)
  - duration endpoint payload (`gross/break/net` minutes)
  - **summary recalculation when logbook date changes** (both old and new dates)
  - start time exactly at 07:00 boundary accepted
  - end time earlier than start time rejected
  - rating below 1 or above 5 rejected
  - invalid decision value "REVIEWED" rejected (only ACCEPTED/REJECTED)
  - attachment operations blocked in ACCEPTED/REJECTED status
  - KPI progress update blocked in SUBMITTED/ACCEPTED status
  - negative capaian_angka rejected
  - cannot re-submit already submitted/accepted logbooks
  - cannot review DRAFT or already-reviewed logbooks

## Notification Data Contract (Backend → Frontend)

Endpoint notifikasi (`GET /api/v1/notifications`) mengembalikan field existing + metadata tambahan untuk UX frontend:

- `preview_message` (string): ringkasan pesan notifikasi (truncated)
- `target_path` (string|null): path tujuan saat notifikasi diklik
- `target_params` (object|null): query params tambahan (opsional)

Filter query yang didukung:

- `is_read=true|false`
- `unread_only=true` (memaksa hanya notifikasi belum dibaca)

Mapping route default saat metadata target digunakan:

- `KPI_ASSIGNMENT` → `/staff/logbook` (+ `assignment_id`)
- `LOGBOOK_SUBMITTED` → `/manager/reviews` (+ `logbook_id`)
- `LOGBOOK_REJECTED` / `LOGBOOK_ACCEPTED` → `/staff/history` (+ `logbook_id`)

Endpoint read bersifat idempotent:

- `PUT /api/v1/notifications/{notification}/read`
- aman dipanggil berulang; status `is_read` tetap konsisten.

## Constraints
- Framework: Laravel 12
- PHP: 8.3
- DB: Relational (SQLite/PostgreSQL)
- Auth: Sanctum / JWT

## API Documentation
- Canonical API reference is documented at: `backend/docs/api_reference.md`
