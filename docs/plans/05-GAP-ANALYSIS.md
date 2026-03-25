# Gap Analysis - Current vs Target Implementation

## Overview

Dokumen ini menganalisis gap antara implementasi saat ini dengan target design sesuai SYSTEM_DESIGN.md. Setiap gap diidentifikasi dengan prioritas dan estimasi effort.

**Revision Note:** This document has been updated to include gaps 12-21 for the new features:
- Manual time input (tanggal, start_kerja, end_kerja as separate fields)
- Multiple logbooks per day allowed
- Pre-aggregated summary tables
- LogbookObserver for automatic summary recalculation
- Staff Mobile SPA with Performance tabs

---

## 1. Gap Summary Matrix

### Original Gaps (1-11)

| # | Area | Gap Type | Priority | Backend Effort | Frontend Effort | Total |
|---|------|----------|----------|----------------|-----------------|-------|
| 1 | KPI Progress | Schema + Logic | HIGH | 4h | 4h | 8h |
| 2 | Attachments | Schema + Logic + UI | HIGH | 6h | 6h | 12h |
| 3 | Review Comment | Schema + Logic + UI | MEDIUM | 2h | 3h | 5h |
| 4 | Status Enum | Schema + Logic | HIGH | 3h | 2h | 5h |
| 5 | Break Time | Database View | LOW | 3h | 1h | 4h |
| 6 | User Biodata | Schema + UI | MEDIUM | 4h | 6h | 10h |
| 7 | KPI Master Fields | Schema + UI | HIGH | 2h | 3h | 5h |
| 8 | Role Enum | Schema + Logic | HIGH | 4h | 4h | 8h |
| 9 | GPS Location | Schema + Logic | MEDIUM | 2h | 2h | 4h |
| 10 | Notifications | Schema cleanup | LOW | 1h | 1h | 2h |
| 11 | Start Validation | Logic + UI | MEDIUM | 2h | 2h | 4h |
| **Subtotal** | | | | **33h** | **34h** | **67h** |

### New Gaps (12-21) - Revision Features

| # | Area | Gap Type | Priority | Backend Effort | Frontend Effort | Total |
|---|------|----------|----------|----------------|-----------------|-------|
| 12 | Summary Tables Migration | Schema | HIGH | 3h | - | 3h |
| 13 | DailySummaryService | Logic | HIGH | 4h | - | 4h |
| 14 | LogbookObserver Events | Logic | HIGH | 2h | - | 2h |
| 15 | Summary API Endpoints | API | HIGH | 4h | - | 4h |
| 16 | Manual Time Input | Schema + Logic + UI | HIGH | 2h | 3h | 5h |
| 17 | Multiple Logbooks/Day | Logic + UI | MEDIUM | 2h | 2h | 4h |
| 18 | Performance Tab (Mobile) | UI | HIGH | - | 8h | 8h |
| 19 | Team Performance Tab | UI | HIGH | - | 6h | 6h |
| 20 | Slide-out Drawer Component | UI | MEDIUM | - | 4h | 4h |
| 21 | Staff Performance Page (Web) | UI | HIGH | - | 6h | 6h |
| **Subtotal** | | | | **17h** | **29h** | **46h** |

### Grand Total

| Category | Backend | Frontend | Total |
|----------|---------|----------|-------|
| Original (1-11) | 33h | 34h | 67h |
| New (12-21) | 17h | 29h | 46h |
| **Grand Total** | **50h** | **63h** | **113h** | |

---

## 2. Detailed Gap Analysis

### GAP-01: KPI Progress Type

**Current Implementation:**
```php
// logbook_kpi_details migration
$table->boolean('is_finished')->default(false);

// LogbookController.php
$detail->update([
    'is_finished' => $request->is_finished,
    'finished_at' => $request->is_finished ? now() : null,
]);
```

**Target Implementation:**
```php
// Migration
$table->decimal('capaian_angka', 10, 2)->default(0);
$table->decimal('target_angka', 10, 2);
$table->string('satuan');

// Controller
$detail->update([
    'capaian_angka' => $request->capaian_angka,
    'finished_at' => $request->capaian_angka >= $detail->target_angka ? now() : null,
]);
```

**Backend Changes:**
- [ ] Create migration to add `capaian_angka`, `target_angka`, `satuan`
- [ ] Create migration to remove `is_finished`
- [ ] Update `LogbookKpiDetail` model
- [ ] Update `LogbookController::toggleKpi()` → `updateProgress()`
- [ ] Update API route from `toggle` to `PATCH` with numeric input
- [ ] Add data migration script

**Frontend Changes:**
- [ ] Update `LogbookKpiDetail` TypeScript type
- [ ] Replace boolean toggle with numeric input
- [ ] Add progress display (e.g., "2/3 dokumen")
- [ ] Update validation schemas

**Files to Modify:**
```
Backend:
- database/migrations/xxxx_update_logbook_kpi_details.php (NEW)
- app/Models/LogbookKpiDetail.php
- app/Http/Controllers/Api/V1/LogbookController.php
- routes/api.php

Frontend:
- src/lib/types/index.ts
- src/routes/(app)/staff/logbook/+page.svelte
- src/lib/api/schemas/logbook.schema.ts
```

---

### GAP-02: Attachment Location

**Current Implementation:**
```php
// logbooks table
$table->jsonb('gambar_bukti')->nullable();

// Submit in LogbookController
$logbook->update([
    'gambar_bukti' => $proofs, // Array of URLs
]);
```

**Target Implementation:**
```php
// logbook_kpi_details table
$table->jsonb('lampiran_file')->nullable();

// Per-KPI attachment upload
$detail->update([
    'lampiran_file' => array_merge($detail->lampiran_file ?? [], [$newFileUrl])
]);
```

**Backend Changes:**
- [ ] Add `lampiran_file` column to logbook_kpi_details
- [ ] Remove `gambar_bukti` from logbooks
- [ ] Create attachment upload endpoint per KPI detail
- [ ] Create attachment delete endpoint
- [ ] Migrate existing attachments

**Frontend Changes:**
- [ ] Remove logbook-level attachment upload
- [ ] Add per-KPI attachment upload component
- [ ] Display attachments grouped by KPI
- [ ] Update submit flow

**New API Endpoints:**
```
POST /logbooks/{id}/kpi/{detail_id}/attachments
DELETE /logbooks/{id}/kpi/{detail_id}/attachments/{index}
```

---

### GAP-03: Review Comment Field

**Current Implementation:**
```php
// logbooks table - rating only
$table->integer('rating')->nullable();

// ManagerLogbookController
$logbook->update([
    'status' => 'REVIEWED',
    'rating' => $request->rating,
]);
```

**Target Implementation:**
```php
// logbooks table
$table->integer('rating')->nullable();
$table->text('reviewer_comment')->nullable();

// Controller
$logbook->update([
    'status' => $request->decision, // 'ACCEPTED' or 'REJECTED'
    'rating' => $request->rating,
    'reviewer_comment' => $request->reviewer_comment,
]);
```

**Backend Changes:**
- [ ] Add `reviewer_comment` column to logbooks
- [ ] Update `ManagerLogbookController::rate()` → `review()`
- [ ] Add validation for `reviewer_comment`
- [ ] Update notification message to include comment

**Frontend Changes:**
- [ ] Add textarea for reviewer comment in review form
- [ ] Display reviewer comment in logbook history
- [ ] Update review modal UI

---

### GAP-04: Status Enum Values

**Current Implementation:**
```php
// Migration
$table->enum('status', ['DRAFT', 'SUBMITTED', 'REVIEWED'])->default('DRAFT');

// Controller checks
if ($logbook->status !== 'SUBMITTED') { ... }
$logbook->update(['status' => 'REVIEWED']);
```

**Target Implementation:**
```php
// Migration
$table->enum('status', ['DRAFT', 'SUBMITTED', 'ACCEPTED', 'REJECTED'])->default('DRAFT');

// Controller
$logbook->update(['status' => $request->decision]); // 'ACCEPTED' or 'REJECTED'
```

**Backend Changes:**
- [ ] Modify enum values in migration
- [ ] Update all status checks in controllers
- [ ] Update notification types
- [ ] Migrate existing 'REVIEWED' → 'ACCEPTED'

**Frontend Changes:**
- [ ] Update `LogbookStatus` TypeScript type
- [ ] Update status display (colors, labels)
- [ ] Update filter options

**Current TypeScript:**
```typescript
export type LogbookStatus = 'DRAFT' | 'SUBMITTED' | 'REVIEWED' | 'REVERTED';
```

**Target TypeScript:**
```typescript
export type LogbookStatus = 'DRAFT' | 'SUBMITTED' | 'ACCEPTED' | 'REJECTED';
```

---

### GAP-05: Break Time Auto-Calculation

**Current Implementation:**
- Manual calculation or none

**Target Implementation:**
```sql
CREATE VIEW v_logbook_work_duration AS
SELECT 
    logbook_id,
    total_minutes,
    break_minutes,  -- Auto-detected 12:00-13:00 overlap
    effective_work_minutes
FROM logbooks;
```

**Backend Changes:**
- [ ] Create database view migration
- [ ] Add endpoint `GET /logbooks/{id}/duration`
- [ ] Add accessor in Logbook model

**Frontend Changes:**
- [ ] Display effective duration in logbook detail
- [ ] Show break time deduction info

---

### GAP-06: Extended User Biodata

**Current Implementation:**
```php
// users table
$table->string('name');
$table->string('email')->unique();
$table->string('nip')->unique();
// No extended biodata
```

**Target Implementation:**
```php
// users table additions
$table->string('foto')->nullable();
$table->string('tempat_lahir')->nullable();
$table->date('tanggal_lahir')->nullable();
$table->string('nik', 16)->unique()->nullable();
$table->string('npwp')->nullable();
$table->text('alamat')->nullable();
$table->enum('status_kawin', [...]);
$table->jsonb('riwayat_pendidikan')->nullable();
$table->jsonb('riwayat_karir')->nullable();
```

**Backend Changes:**
- [ ] Add biodata columns to users table
- [ ] Update User model fillable
- [ ] Update AuthController for profile update
- [ ] Add photo upload handling
- [ ] Update user creation/update validation

**Frontend Changes:**
- [ ] Extend User TypeScript type
- [ ] Create biodata form components
- [ ] Add tabbed interface in user edit modal
- [ ] Add profile photo upload
- [ ] Add education/career history management

---

### GAP-07: KPI Master Enhancement

**Current Implementation:**
```php
// kpi_masters table
$table->string('nama');
$table->boolean('status_aktif')->default(true);
```

**Target Implementation:**
```php
// kpi_masters table
$table->string('nama');
$table->decimal('target_angka', 10, 2);
$table->string('satuan');
$table->text('deskripsi')->nullable();
$table->boolean('status_aktif')->default(true);
```

**Backend Changes:**
- [ ] Add columns to kpi_masters
- [ ] Update KpiMaster model
- [ ] Update validation in MasterKpiController
- [ ] Copy target to logbook_kpi_details on logbook start

**Frontend Changes:**
- [ ] Update KpiMaster TypeScript type
- [ ] Add form fields in KPI management
- [ ] Display target and unit in KPI list

---

### GAP-08: Role Enum Changes

**Current Implementation:**
```php
// User model uses string role
// Values: 'ADMIN', 'MANAGER', 'STAFF'

// Authorization checks
if ($user->role === 'MANAGER') { ... }
```

**Target Implementation:**
```php
// Values: 'SuperAdmin', 'Admin', 'Staff'
// Manager determined by: User::where('manager_id', $user->id)->exists()

// Authorization checks
if ($user->hasSubordinates()) { ... }
```

**Backend Changes:**
- [ ] Update role enum values
- [ ] Add `hasSubordinates()` method to User model
- [ ] Update all authorization checks
- [ ] Update middleware/gates
- [ ] Migrate existing role values

**Frontend Changes:**
- [ ] Update UserRole TypeScript type
- [ ] Update role checks in components
- [ ] Update navigation/sidebar logic

**Current Frontend:**
```typescript
export type UserRole = 'Admin' | 'Manager' | 'Staff';
```

**Target Frontend:**
```typescript
export type UserRole = 'SuperAdmin' | 'Admin' | 'Staff';

interface User {
    role: UserRole;
    has_subordinates?: boolean; // New field from API
}
```

---

### GAP-09: GPS Location Simplification

**Current Implementation:**
```php
// logbooks table
$table->string('lokasi_start');
$table->string('lokasi_end')->nullable();

// API
'gps_location_start' => 'required|string',
'gps_location_end' => 'required|string',
```

**Target Implementation:**
```php
// logbooks table
$table->string('lokasi'); // Single field at check-in

// API
'lokasi' => 'required|string', // Only at start
```

**Backend Changes:**
- [ ] Add `lokasi` column
- [ ] Migrate data from `lokasi_start`
- [ ] Remove `lokasi_start` and `lokasi_end`
- [ ] Update controller logic

**Frontend Changes:**
- [ ] Update Logbook TypeScript type
- [ ] Remove end location capture
- [ ] Update map display

---

### GAP-10: Notification Simplification

**Current Implementation:**
```php
// notifications table
$table->boolean('is_read')->default(false);
$table->timestamp('read_at')->nullable();
```

**Target Implementation:**
```php
// notifications table
$table->boolean('is_read')->default(false);
// No read_at column
```

**Backend Changes:**
- [ ] Remove `read_at` column
- [ ] Update NotificationController

**Frontend Changes:**
- [ ] Remove `read_at` from Notification type
- [ ] Update notification display

---

### GAP-11: Start Time Validation

**Current Implementation:**
- No validation for check-in time

**Target Implementation:**
```php
// LogbookController::start()
$startTime = now();
if ($startTime->format('H:i') < '07:00') {
    return response()->json([
        'message' => 'Check-in tidak diperbolehkan sebelum pukul 07:00'
    ], 400);
}
```

**Backend Changes:**
- [ ] Add time validation in `LogbookController::start()`
- [ ] Return appropriate error message

**Frontend Changes:**
- [ ] Show current time on dashboard
- [ ] Disable check-in button before 07:00
- [ ] Display message about check-in availability

---

### GAP-12: Summary Tables Migration

**Current Implementation:**
- No pre-aggregated summary tables
- Performance views calculated on-the-fly

**Target Implementation:**
```sql
-- daily_staff_summaries table
CREATE TABLE daily_staff_summaries (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL,
    date DATE NOT NULL,
    total_work_minutes INTEGER,
    logbook_count INTEGER,
    accepted_count INTEGER,
    rejected_count INTEGER,
    pending_count INTEGER,
    avg_rating DECIMAL(3,2),
    total_kpi_achieved DECIMAL(10,2),
    total_kpi_target DECIMAL(10,2),
    overall_kpi_percentage DECIMAL(5,2),
    UNIQUE(user_id, date)
);

-- daily_kpi_summaries table
CREATE TABLE daily_kpi_summaries (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL,
    date DATE NOT NULL,
    kpi_id UUID NOT NULL,
    kpi_nama VARCHAR(255),
    total_achieved DECIMAL(10,2),
    total_target DECIMAL(10,2),
    percentage DECIMAL(5,2),
    UNIQUE(user_id, date, kpi_id)
);
```

**Backend Changes:**
- [ ] Create migration for `daily_staff_summaries`
- [ ] Create migration for `daily_kpi_summaries`
- [ ] Create `DailyStaffSummary` model
- [ ] Create `DailyKpiSummary` model
- [ ] Add indexes for performance

**Files to Modify:**
```
Backend:
- database/migrations/xxxx_create_daily_staff_summaries_table.php (NEW)
- database/migrations/xxxx_create_daily_kpi_summaries_table.php (NEW)
- app/Models/DailyStaffSummary.php (NEW)
- app/Models/DailyKpiSummary.php (NEW)
```

---

### GAP-13: DailySummaryService

**Current Implementation:**
- No service for aggregating daily data

**Target Implementation:**
```php
<?php
// app/Services/DailySummaryService.php

class DailySummaryService
{
    public function recalculate(string $userId, string $date): void
    {
        // Aggregate all logbooks for user+date
        $logbooks = Logbook::where('user_id', $userId)
            ->where('tanggal', $date)
            ->with('details')
            ->get();
        
        // Calculate totals
        $summary = [
            'total_work_minutes' => $logbooks->sum('duration_minutes'),
            'logbook_count' => $logbooks->count(),
            'accepted_count' => $logbooks->where('status', 'ACCEPTED')->count(),
            // ... etc
        ];
        
        // Upsert summary
        DailyStaffSummary::updateOrCreate(
            ['user_id' => $userId, 'date' => $date],
            $summary
        );
        
        // Also update KPI summaries
        $this->recalculateKpiSummaries($userId, $date, $logbooks);
    }
}
```

**Backend Changes:**
- [ ] Create `DailySummaryService` class
- [ ] Implement `recalculate()` method
- [ ] Implement `recalculateKpiSummaries()` method
- [ ] Add unit tests

**Files to Modify:**
```
Backend:
- app/Services/DailySummaryService.php (NEW)
- tests/Unit/Services/DailySummaryServiceTest.php (NEW)
```

---

### GAP-14: LogbookObserver Events

**Current Implementation:**
- No observers for logbook changes

**Target Implementation:**
```php
<?php
// app/Observers/LogbookObserver.php

class LogbookObserver
{
    public function created(Logbook $logbook): void
    {
        $this->summaryService->recalculate($logbook->user_id, $logbook->tanggal);
    }

    public function updated(Logbook $logbook): void
    {
        $this->summaryService->recalculate($logbook->user_id, $logbook->tanggal);
        
        // Handle date change - recalculate old date too
        if ($logbook->isDirty('tanggal')) {
            $this->summaryService->recalculate(
                $logbook->user_id, 
                $logbook->getOriginal('tanggal')
            );
        }
    }

    public function deleted(Logbook $logbook): void
    {
        $this->summaryService->recalculate($logbook->user_id, $logbook->tanggal);
    }
}

// Also need LogbookKpiDetailObserver for detail changes
```

**Backend Changes:**
- [ ] Create `LogbookObserver` class
- [ ] Create `LogbookKpiDetailObserver` class
- [ ] Register observers in `AppServiceProvider`
- [ ] Add integration tests

**Files to Modify:**
```
Backend:
- app/Observers/LogbookObserver.php (NEW)
- app/Observers/LogbookKpiDetailObserver.php (NEW)
- app/Providers/AppServiceProvider.php
- tests/Feature/Observers/LogbookObserverTest.php (NEW)
```

---

### GAP-15: Summary API Endpoints

**Current Implementation:**
- No endpoints for pre-aggregated summaries

**Target Implementation:**
```php
// New routes
Route::prefix('summaries')->group(function () {
    Route::get('/daily', [SummaryController::class, 'daily']);
    Route::get('/daily/{user_id}', [SummaryController::class, 'userDaily']);
    Route::get('/period', [SummaryController::class, 'period']);
    Route::get('/kpi/daily', [SummaryController::class, 'kpiDaily']);
    Route::get('/kpi/period', [SummaryController::class, 'kpiPeriod']);
    Route::get('/team/daily', [SummaryController::class, 'teamDaily']);
    Route::get('/staff-performance', [SummaryController::class, 'staffPerformance']);
});
```

**Backend Changes:**
- [ ] Create `SummaryController`
- [ ] Implement all summary endpoints
- [ ] Add request validation
- [ ] Add authorization (hierarchical access)
- [ ] Add pagination for list endpoints

**Files to Modify:**
```
Backend:
- app/Http/Controllers/Api/V1/SummaryController.php (NEW)
- app/Http/Requests/SummaryRequest.php (NEW)
- routes/api.php
- tests/Feature/Api/SummaryControllerTest.php (NEW)
```

---

### GAP-16: Manual Time Input

**Current Implementation:**
```php
// logbooks table
$table->timestamp('start_kerja');
$table->timestamp('end_kerja');

// Auto-captured on check-in/check-out
$logbook->start_kerja = now();
```

**Target Implementation:**
```php
// logbooks table
$table->date('tanggal');
$table->time('start_kerja');
$table->time('end_kerja');

// Manual input from staff
$logbook->tanggal = $request->tanggal;
$logbook->start_kerja = $request->start_kerja;
$logbook->end_kerja = $request->end_kerja;
```

**Backend Changes:**
- [ ] Add `tanggal` column (DATE)
- [ ] Change `start_kerja` to TIME type
- [ ] Change `end_kerja` to TIME type
- [ ] Update `LogbookController::store()` for manual input
- [ ] Update validation rules
- [ ] Migrate existing data

**Frontend Changes:**
- [ ] Add date picker for `tanggal`
- [ ] Add time picker for `start_kerja`
- [ ] Add time picker for `end_kerja`
- [ ] Show calculated duration
- [ ] Update validation

**Files to Modify:**
```
Backend:
- database/migrations/xxxx_update_logbooks_manual_time.php
- app/Http/Controllers/Api/V1/LogbookController.php
- app/Http/Requests/LogbookRequest.php

Frontend:
- src/lib/types/index.ts
- src/routes/(app)/staff/logbook/+page.svelte
- src/lib/components/LogbookForm.svelte (NEW/MODIFY)
```

---

### GAP-17: Multiple Logbooks Per Day

**Current Implementation:**
```php
// Validation prevents multiple logbooks per day
if (Logbook::where('user_id', $user->id)
    ->whereDate('start_kerja', today())
    ->exists()) {
    throw new ValidationException('Already has logbook for today');
}
```

**Target Implementation:**
```php
// Multiple logbooks per day allowed
// No unique constraint on user_id + date
// User can create multiple logbooks for same day
```

**Backend Changes:**
- [ ] Remove unique constraint validation
- [ ] Update `LogbookController` to allow multiple per day
- [ ] Update queries to handle multiple logbooks
- [ ] Update dashboard to show list instead of single

**Frontend Changes:**
- [ ] Update dashboard to show logbook list for today
- [ ] Update logbook list view grouped by date
- [ ] Allow creating new logbook even if one exists

**Files to Modify:**
```
Backend:
- app/Http/Controllers/Api/V1/LogbookController.php
- app/Http/Controllers/Api/V1/DashboardController.php

Frontend:
- src/routes/(app)/staff/+page.svelte
- src/routes/(app)/staff/logbook/+page.svelte
```

---

### GAP-18: Performance Tab (Mobile SPA)

**Current Implementation:**
- No dedicated performance view for staff

**Target Implementation:**
```
┌─────────────────────────────────────────────────────────────┐
│ My Performance Tab                                          │
├─────────────────────────────────────────────────────────────┤
│ View: [Daily ▼] [Period ▼]  │  Date: [20 Mar 2026 ▼]       │
│                                                             │
│ Summary Stats                                               │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│ │Logbooks  │ │ Duration │ │ KPI %    │ │ Rating   │       │
│ │    2     │ │  8h 30m  │ │   95%    │ │ ⭐ 4.2   │       │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘       │
│                                                             │
│ KPI Breakdown                                               │
│ ├── Buat Laporan: 3/3 ██████████ 100%                      │
│ ├── Kunjungan: 4/5 ████████░░ 80%                          │
│ └── Data Entry: 95/100 ████████░░ 95%                      │
└─────────────────────────────────────────────────────────────┘
```

**Frontend Changes:**
- [ ] Create `PerformanceTab.svelte` component
- [ ] Create `DailyPerformanceView.svelte` component
- [ ] Create `PeriodPerformanceView.svelte` component
- [ ] Create `KpiProgressBar.svelte` component
- [ ] Create `PerformanceSummaryCard.svelte` component
- [ ] Integrate with Summary API endpoints
- [ ] Add date/period picker
- [ ] Add trend chart for period view

**Files to Create:**
```
Frontend:
- src/lib/components/mobile/PerformanceTab.svelte
- src/lib/components/mobile/DailyPerformanceView.svelte
- src/lib/components/mobile/PeriodPerformanceView.svelte
- src/lib/components/mobile/KpiProgressBar.svelte
- src/lib/components/mobile/PerformanceSummaryCard.svelte
```

---

### GAP-19: Team Performance Tab (Manager)

**Current Implementation:**
- Basic subordinate list only

**Target Implementation:**
```
┌─────────────────────────────────────────────────────────────┐
│ Team Performance Tab (Manager Only)                         │
├─────────────────────────────────────────────────────────────┤
│ Pending Reviews: 3 logbooks                      [Review →] │
│                                                             │
│ View: [Daily ▼]  │  Date: [20 Mar 2026 ▼]                  │
│                                                             │
│ Team Members (5)                                            │
│ ├── John Doe    │ 2 logs │ 8h30m │ 95% │ ⭐4.2 │ [Detail] │
│ ├── Alice Wang  │ 1 log  │ 6h00m │ 72% │ ⭐3.5 │ [Detail] │
│ └── Bob Smith   │ 3 logs │ 9h15m │ 100%│ ⭐4.8 │ [Detail] │
│                                                             │
│ Team Summary                                                │
│ Total: 8 logs │ Avg KPI: 89% │ Avg Rating: 4.2 │ Pending: 3│
└─────────────────────────────────────────────────────────────┘
```

**Frontend Changes:**
- [ ] Create `TeamPerformanceTab.svelte` component
- [ ] Create `TeamMemberCard.svelte` component
- [ ] Create `TeamSummaryCard.svelte` component
- [ ] Integrate with team summary API
- [ ] Add pending reviews banner
- [ ] Add drill-down to staff detail

**Files to Create:**
```
Frontend:
- src/lib/components/mobile/TeamPerformanceTab.svelte
- src/lib/components/mobile/TeamMemberCard.svelte
- src/lib/components/mobile/TeamSummaryCard.svelte
```

---

### GAP-20: Slide-out Drawer Component

**Current Implementation:**
- Modal dialogs for detail views

**Target Implementation:**
```
┌────────────────────────────────────────────────────────────────────────────┐
│ Main Content                          │◀─────── Slide-out Drawer ────────▶│
│                                       │ ┌────────────────────────────────┐ │
│                                       │ │ ← Staff Detail        [Close] │ │
│                                       │ ├────────────────────────────────┤ │
│  (Content dims when drawer open)      │ │ 👤 John Doe                    │ │
│                                       │ │ NPP: 199003032010012003        │ │
│                                       │ │                                │ │
│                                       │ │ Daily Summary                  │ │
│                                       │ │ KPI Breakdown                  │ │
│                                       │ │ Logbook List                   │ │
│                                       │ │ - #1 08:00-12:00 [Detail]      │ │
│                                       │ │ - #2 13:00-17:30 [Detail]      │ │
│                                       │ └────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────────┘
```

**Frontend Changes:**
- [ ] Create `SlideOutDrawer.svelte` generic component
- [ ] Create `StaffDetailDrawer.svelte` component
- [ ] Create `LogbookDetailDrawer.svelte` component
- [ ] Add transition animations
- [ ] Add backdrop overlay
- [ ] Handle escape key / outside click

**Files to Create:**
```
Frontend:
- src/lib/components/ui/SlideOutDrawer.svelte
- src/lib/components/drawers/StaffDetailDrawer.svelte
- src/lib/components/drawers/LogbookDetailDrawer.svelte
```

---

### GAP-21: Staff Performance Page (Web Admin)

**Current Implementation:**
- No dedicated staff performance page for Admin

**Target Implementation:**
```
┌─────────────────────────────────────────────────────────────┐
│ Staff Performance                            Admin Panel    │
├─────────────────────────────────────────────────────────────┤
│ View: [Daily ▼] [Period ▼]  │  Date: [20 Mar 2026 ▼]       │
│ Search: [____________]      │  Sort: [Name ▼] [Asc ▼]      │
├─────────────────────────────────────────────────────────────┤
│ Name         │ Manager  │ Logs │ Duration │ KPI % │ Rating │
│──────────────┼──────────┼──────┼──────────┼───────┼────────│
│ John Doe     │ Jane M.  │  2   │ 8h 30m   │  95%  │ ⭐4.2  │
│ Alice Wang   │ Jane M.  │  1   │ 6h 00m   │  72%  │ ⭐3.5  │
│ Bob Smith    │ Tom L.   │  3   │ 9h 15m   │ 100%  │ ⭐4.8  │
├─────────────────────────────────────────────────────────────┤
│ [◀ Prev]                 Page 1 of 5              [Next ▶] │
└─────────────────────────────────────────────────────────────┘

Click row → Opens StaffDetailDrawer
```

**Frontend Changes:**
- [ ] Create `StaffPerformancePage.svelte`
- [ ] Create `StaffPerformanceTable.svelte`
- [ ] Add view toggle (Daily/Period)
- [ ] Add date/period picker
- [ ] Add search and sort
- [ ] Add pagination
- [ ] Integrate StaffDetailDrawer

**Files to Create:**
```
Frontend:
- src/routes/(app)/admin/staff-performance/+page.svelte
- src/lib/components/admin/StaffPerformanceTable.svelte
```

---

## 3. Implementation Priority Order

### Phase 1: Critical (Week 1) - Must Have
1. **GAP-04: Status Enum** - Breaks existing flow
2. **GAP-08: Role Enum** - Affects authorization
3. **GAP-07: KPI Master Fields** - Required for progress tracking
4. **GAP-01: KPI Progress Type** - Core feature change
5. **GAP-12: Summary Tables Migration** - Required for new features
6. **GAP-16: Manual Time Input** - Core UX change

### Phase 2: High (Week 2) - Important
7. **GAP-02: Attachments** - Major UX change
8. **GAP-03: Review Comment** - Improves feedback
9. **GAP-09: GPS Location** - Simplification
10. **GAP-13: DailySummaryService** - Required for performance views
11. **GAP-14: LogbookObserver Events** - Required for auto-updates
12. **GAP-15: Summary API Endpoints** - Required for frontend

### Phase 3: Medium (Week 3) - UI Implementation
13. **GAP-17: Multiple Logbooks/Day** - Enables multiple entries
14. **GAP-18: Performance Tab (Mobile)** - Staff performance view
15. **GAP-19: Team Performance Tab** - Manager performance view
16. **GAP-20: Slide-out Drawer** - Detail view component
17. **GAP-21: Staff Performance Page** - Admin view

### Phase 4: Low (Week 4) - Polish
18. **GAP-06: User Biodata** - Extended features
19. **GAP-11: Start Validation** - Business rule
20. **GAP-05: Break Time** - Auto calculation
21. **GAP-10: Notifications** - Cleanup

---

## 4. Risk Assessment

| Gap | Risk | Mitigation |
|-----|------|------------|
| GAP-01 | Data loss during migration | Backup + data migration script |
| GAP-02 | Existing attachments lost | Copy to first KPI detail before drop |
| GAP-04 | Existing REVIEWED status | Map to ACCEPTED |
| GAP-08 | Authorization breaks | Comprehensive testing |
| GAP-09 | Location data loss | Copy lokasi_start to lokasi |

---

## 5. Testing Checklist

### Backend Tests
- [ ] Unit tests for new model methods
- [ ] Feature tests for updated endpoints
- [ ] Migration rollback tests
- [ ] Authorization tests for new role logic

### Frontend Tests
- [ ] Component tests for new forms
- [ ] E2E tests for critical flows
- [ ] Type checking (TypeScript)

### Integration Tests
- [ ] Login/auth flow
- [ ] Logbook create → submit → review flow
- [ ] KPI assignment → progress → completion flow

---

## 6. Files Changed Summary

### Backend (Laravel)

**Migrations (New):**
```
database/migrations/
├── 2026_03_21_000001_add_extended_biodata_to_users.php
├── 2026_03_21_000002_add_target_fields_to_kpi_masters.php
├── 2026_03_21_000003_update_logbooks_schema.php
├── 2026_03_21_000004_update_logbook_kpi_details_schema.php
├── 2026_03_21_000005_update_user_role_enum.php
├── 2026_03_21_000006_remove_read_at_from_notifications.php
├── 2026_03_21_000007_create_logbook_work_duration_view.php
├── 2026_03_21_000008_add_performance_indexes.php
├── 2026_03_21_000009_create_daily_staff_summaries_table.php   # NEW
└── 2026_03_21_000010_create_daily_kpi_summaries_table.php     # NEW
```

**Models (New):**
```
app/Models/
├── DailyStaffSummary.php    # NEW
└── DailyKpiSummary.php      # NEW
```

**Models (Modified):**
```
app/Models/
├── User.php           # Extended fillable, hasSubordinates()
├── KpiMaster.php      # New fields
├── Logbook.php        # New fields (tanggal, time fields), removed fields
├── LogbookKpiDetail.php  # capaian_angka, lampiran_file
└── Notification.php   # Remove read_at
```

**Services (New):**
```
app/Services/
└── DailySummaryService.php   # NEW - handles summary recalculation
```

**Observers (New):**
```
app/Observers/
├── LogbookObserver.php           # NEW - triggers summary updates
└── LogbookKpiDetailObserver.php  # NEW - triggers summary updates
```

**Controllers (New):**
```
app/Http/Controllers/Api/V1/
└── SummaryController.php   # NEW - summary API endpoints
```

**Controllers (Modified):**
```
app/Http/Controllers/Api/V1/
├── LogbookController.php      # Progress update, attachment endpoints, manual time
├── ManagerLogbookController.php  # Review with comment, accept/reject
├── UsersController.php        # Extended biodata
├── MasterKpiController.php    # New fields validation
├── AuthController.php         # Profile update with biodata
└── DashboardController.php    # Support multiple logbooks
```

**Routes:**
```
routes/api.php  # New summary endpoints, modified existing
```

### Frontend (SvelteKit)

**Types:**
```
src/lib/types/index.ts  # Updated all types + new summary types
```

**Components (New) - Mobile SPA:**
```
src/lib/components/mobile/
├── PerformanceTab.svelte           # My Performance tab
├── DailyPerformanceView.svelte     # Daily performance view
├── PeriodPerformanceView.svelte    # Period performance view
├── KpiProgressBar.svelte           # KPI progress visualization
├── PerformanceSummaryCard.svelte   # Summary stats cards
├── TeamPerformanceTab.svelte       # Team Performance tab (Manager)
├── TeamMemberCard.svelte           # Team member performance card
└── TeamSummaryCard.svelte          # Team summary stats
```

**Components (New) - Shared:**
```
src/lib/components/ui/
└── SlideOutDrawer.svelte    # Generic slide-out drawer

src/lib/components/drawers/
├── StaffDetailDrawer.svelte    # Staff detail view drawer
└── LogbookDetailDrawer.svelte  # Logbook detail view drawer
```

**Components (New) - Admin:**
```
src/lib/components/admin/
└── StaffPerformanceTable.svelte   # Staff performance list table
```

**Components (Modified):**
```
src/lib/components/
├── KpiProgressInput.svelte    # Numeric input for progress
├── KpiAttachmentUpload.svelte # Per-KPI file upload
├── BiodataForm.svelte         # Extended user form
├── ReviewForm.svelte          # Rating + comment form
└── LogbookForm.svelte         # Manual time input
```

**Pages (New):**
```
src/routes/(app)/admin/
└── staff-performance/+page.svelte   # Admin staff performance page
```

**Pages (Modified):**
```
src/routes/(app)/
├── admin/users/+page.svelte    # Extended biodata
├── admin/kpis/+page.svelte     # New fields
├── staff/+page.svelte          # Multiple logbooks, performance tab
├── staff/logbook/+page.svelte  # Manual time input, attachments
├── staff/history/+page.svelte  # Review comment display
└── manager/reviews/+page.svelte # Accept/reject with comment
```

**API Services (Modified):**
```
src/lib/api/
├── logbook.ts     # New summary endpoints
├── summary.ts     # NEW - Summary API service
└── schemas/       # Updated validation schemas
```

---

## 7. Definition of Done

Each gap is complete when:

- [ ] Migration created and tested
- [ ] Model updated with new fields/methods
- [ ] Controller logic updated
- [ ] API endpoint working (tested via Postman/tests)
- [ ] Frontend types updated
- [ ] UI components created/modified
- [ ] Integration tested end-to-end
- [ ] Code reviewed
- [ ] Documentation updated

---

**Document Complete**

Return to `00-IMPLEMENTATION-OVERVIEW.md` for next steps.
