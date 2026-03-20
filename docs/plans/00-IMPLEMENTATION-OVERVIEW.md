# Implementation Plan Overview

## Logbook & KPI Management System - Full Implementation Guide

**Document Version**: 2.0  
**Last Updated**: March 2026  
**Status**: Planning Phase (Revised)

---

## Executive Summary

Dokumen ini berisi rencana implementasi lengkap untuk sistem Logbook & KPI Management. Sistem ini memiliki 2 target platform:

| Platform | User Type | Technology | Navigation |
|----------|-----------|------------|------------|
| **Web Admin Panel** | SuperAdmin, Admin | SvelteKit + Laravel API | Sidebar navigation |
| **Mobile SPA** | Staff (Pegawai) | SvelteKit PWA + Laravel API | Tabbed dashboard |

### Key Features (Revised)

- **Manual Time Input**: Staff manually inputs `tanggal` (DATE), `start_kerja` (TIME), `end_kerja` (TIME)
- **Multiple Logbooks Per Day**: Staff can create multiple logbook entries for the same day
- **Pre-aggregated Summaries**: `daily_staff_summaries` and `daily_kpi_summaries` tables for fast performance views
- **Event-Driven Updates**: Laravel LogbookObserver triggers summary recalculation on changes
- **Slide-out Drawer**: Detail drill-down views use slide-out drawer pattern

---

## Document Structure

| File | Description |
|------|-------------|
| `00-IMPLEMENTATION-OVERVIEW.md` | Dokumen ini - overview keseluruhan |
| `01-DATA-FLOW-LOGIC.md` | Data flow, business logic, state machines |
| `02-WEB-ADMIN-PAGES.md` | Halaman web untuk SuperAdmin & Admin (with Staff Performance) |
| `03-MOBILE-API-DESIGN.md` | Mobile SPA design & Summary API endpoints |
| `04-MIGRATION-PLAN.md` | Database migration + Summary tables |
| `05-GAP-ANALYSIS.md` | Gap analysis (21 gaps, 113h total) |

---

## Role Hierarchy

```
┌─────────────────────────────────────────────────────────────┐
│                      ROLE HIERARCHY                          │
├─────────────────────────────────────────────────────────────┤
│  SuperAdmin                                                  │
│  ├── Can create Admin accounts                              │
│  ├── All Admin capabilities                                 │
│  └── System-wide access                                     │
├─────────────────────────────────────────────────────────────┤
│  Admin                                                       │
│  ├── Can ONLY create Staff accounts (NOT Admin)             │
│  ├── Assign subordinates/managers (manager_id)              │
│  ├── Assign KPIs to staff                                   │
│  ├── View Staff Performance (all staff)                     │
│  └── Master data management                                 │
├─────────────────────────────────────────────────────────────┤
│  Staff                                                       │
│  ├── Uses Mobile SPA (tabbed dashboard)                     │
│  ├── Manual logbook input (date, time)                      │
│  ├── Multiple logbooks per day allowed                      │
│  └── +Manager capabilities if has subordinates              │
└─────────────────────────────────────────────────────────────┘
```

---

## Platform Distribution

### A. Web Admin Panel (SvelteKit Frontend)

Digunakan oleh **SuperAdmin** dan **Admin** untuk mengelola sistem.
**Navigation**: Sidebar navigation pattern.

```
┌─────────────────────────────────────────────────────────────┐
│                    WEB ADMIN PANEL                          │
│                (SvelteKit + TailwindCSS)                    │
│                 [Sidebar Navigation]                        │
├─────────────────────────────────────────────────────────────┤
│  SuperAdmin                    │  Admin                     │
│  ├── Dashboard                 │  ├── Dashboard             │
│  ├── Manage Admins             │  ├── Manage Users (Staff)  │
│  ├── Manage Users              │  ├── Master KPI            │
│  ├── Master KPI                │  ├── Staff Performance ◄── │
│  ├── Staff Performance ◄──────│  ├── View Logbooks         │
│  ├── Global KPI Monitoring     │  └── Reports & Export      │
│  ├── View All Logbooks         │                            │
│  ├── Audit Logs                │  ◄── Staff Performance:    │
│  └── Reports & Export          │      Aggregated view with  │
│                                │      slide-out drawer      │
└─────────────────────────────────────────────────────────────┘
```

### B. Mobile SPA (SvelteKit PWA)

Digunakan oleh **Staff (Pegawai)** melalui web-based Mobile SPA (Progressive Web App).
**Navigation**: Tabbed dashboard pattern.

```
┌─────────────────────────────────────────────────────────────┐
│                    MOBILE SPA (PWA)                         │
│              (SvelteKit + TailwindCSS + PWA)                │
│                  [Tabbed Dashboard]                         │
├─────────────────────────────────────────────────────────────┤
│  Staff (Pegawai)               │  Staff dengan Bawahan      │
│  ┌─────────────────────────┐   │  ┌─────────────────────────┐│
│  │ [Overview] [Perf] [Log] │   │  │ [Overview][Perf][Log]   ││
│  │ [Profile]               │   │  │ [Team][Profile]         ││
│  └─────────────────────────┘   │  └─────────────────────────┘│
│                                │                             │
│  Tab 1: Overview               │  Tab 1: Overview            │
│  ├── Today's summary           │  ├── Today's summary        │
│  ├── Quick stats               │  ├── Quick stats            │
│  └── Recent activity           │  └── Recent activity        │
│                                │                             │
│  Tab 2: My Performance         │  Tab 2: My Performance      │
│  ├── Daily KPI summary         │  ├── Daily KPI summary      │
│  ├── Period trends             │  ├── Period trends          │
│  └── Drill-down drawer         │  └── Drill-down drawer      │
│                                │                             │
│  Tab 3: Logbook                │  Tab 3: Logbook             │
│  ├── Manual date/time input    │  ├── Manual date/time input │
│  ├── Multiple entries/day      │  ├── Multiple entries/day   │
│  └── KPI progress & bukti      │  └── KPI progress & bukti   │
│                                │                             │
│  Tab 4: Profile                │  Tab 4: Team Performance    │
│  ├── Biodata                   │  ├── Subordinates list      │
│  ├── Settings                  │  ├── Review logbooks        │
│  └── Logout                    │  └── Accept/Reject          │
│                                │                             │
│                                │  Tab 5: Profile             │
│                                │  ├── Biodata & Settings     │
│                                │  └── Logout                 │
└─────────────────────────────────────────────────────────────┘
```

---

## Current Implementation vs Target

### Gap Summary (21 Gaps - 113h Total)

#### Original Gaps (1-11)

| # | Area | Current | Target | Priority | Est. |
|---|------|---------|--------|----------|------|
| 1 | KPI Progress | `is_finished` (Boolean) | `capaian_angka` (Numeric) | HIGH | 5h |
| 2 | Attachments | `gambar_bukti` di logbooks | `lampiran_file` di logbook_kpi_details | HIGH | 8h |
| 3 | Review Fields | `rating` only | `rating` + `reviewer_comment` | MEDIUM | 3h |
| 4 | Status Enum | DRAFT/SUBMITTED/REVIEWED | DRAFT/SUBMITTED/ACCEPTED/REJECTED | HIGH | 4h |
| 5 | Break Time | Manual | DB View auto-calculate (12:00-13:00) | LOW | 2h |
| 6 | User Biodata | Basic fields | Extended (foto, NIK, NPWP, dll) | MEDIUM | 6h |
| 7 | KPI Master | nama only | nama + target_angka + satuan + deskripsi | HIGH | 4h |
| 8 | Role Enum | ADMIN/MANAGER/STAFF | SuperAdmin/Admin/Staff | HIGH | 6h |
| 9 | GPS Location | lokasi_start + lokasi_end | Single `lokasi` field | MEDIUM | 2h |
| 10 | Notifications | is_read + read_at | `is_read` only | LOW | 1h |
| 11 | Start Validation | None | Must be >= 07:00 | MEDIUM | 2h |

**Original Subtotal**: 26h BE + 21h FE = **47h**

#### New Gaps (12-21) - March 2026 Revision

| # | Area | Current | Target | Priority | Est. |
|---|------|---------|--------|----------|------|
| 12 | Summary Tables | None | `daily_staff_summaries` + `daily_kpi_summaries` | HIGH | 3h |
| 13 | Summary Service | None | `DailySummaryService` for aggregation | HIGH | 4h |
| 14 | Event Listeners | None | `LogbookObserver` for auto-recalculation | HIGH | 2h |
| 15 | Summary API | None | 7 new summary endpoints | HIGH | 4h |
| 16 | Manual Time Input | Auto-capture | Manual `tanggal` (DATE) + `start/end_kerja` (TIME) | HIGH | 5h |
| 17 | Multiple Logbooks/Day | One per day | Multiple logbooks per day allowed | MEDIUM | 4h |
| 18 | Performance Tab | None | My Performance tab in Mobile SPA | HIGH | 8h |
| 19 | Team Performance Tab | None | Team Performance tab for managers | HIGH | 6h |
| 20 | Slide-out Drawer | None | Detail drill-down drawer component | MEDIUM | 4h |
| 21 | Staff Performance Page | None | Admin view with aggregated performance | HIGH | 6h |

**New Subtotal**: 13h BE + 33h FE = **46h**

**Grand Total**: 50h BE + 63h FE = **113h** (~14 working days)

---

## Implementation Phases (Revised)

### Phase 1: Database & Core Backend (Week 1-2)
- Create migration files for schema changes
- Create `daily_staff_summaries` table
- Create `daily_kpi_summaries` table
- Update logbooks table (tanggal, TIME fields)
- Implement `DailySummaryService`
- Implement `LogbookObserver`
- Create Summary API endpoints

### Phase 2: Mobile SPA Development (Week 3-4)
- Create tabbed dashboard layout
- Implement Overview tab
- Implement My Performance tab with drill-down
- Implement Logbook tab with manual time input
- Implement Team Performance tab (for managers)
- Implement Profile tab
- Create slide-out drawer component

### Phase 3: Web Admin Updates (Week 5)
- Add Staff Performance page
- Integrate slide-out drawer for details
- Update role permissions (Admin can't create Admin)
- Add summary-based views

### Phase 4: Testing & Optimization (Week 6)
- API endpoint testing
- Mobile SPA responsive testing
- Performance testing with pre-aggregated data
- Integration testing

---

## Database Schema Changes (Summary)

### New Tables

```sql
-- 1. daily_staff_summaries (Pre-aggregated staff daily performance)
CREATE TABLE daily_staff_summaries (
    id UUID PRIMARY KEY,
    user_id UUID REFERENCES users(id),
    date DATE NOT NULL,
    total_work_minutes INTEGER DEFAULT 0,
    logbook_count INTEGER DEFAULT 0,
    accepted_count INTEGER DEFAULT 0,
    rejected_count INTEGER DEFAULT 0,
    pending_count INTEGER DEFAULT 0,
    avg_rating DECIMAL(3,2),
    total_kpi_achieved DECIMAL(10,2) DEFAULT 0,
    total_kpi_target DECIMAL(10,2) DEFAULT 0,
    overall_kpi_percentage DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(user_id, date)
);

-- 2. daily_kpi_summaries (Pre-aggregated KPI daily performance)
CREATE TABLE daily_kpi_summaries (
    id UUID PRIMARY KEY,
    user_id UUID REFERENCES users(id),
    date DATE NOT NULL,
    kpi_id UUID REFERENCES kpi_master(id),
    kpi_nama VARCHAR(255),
    total_achieved DECIMAL(10,2) DEFAULT 0,
    total_target DECIMAL(10,2) DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(user_id, date, kpi_id)
);
```

### Logbooks Table Changes

```sql
-- Modified logbooks table
ALTER TABLE logbooks
    ADD COLUMN tanggal DATE NOT NULL,           -- Manual date input
    ALTER COLUMN start_kerja TYPE TIME,          -- Changed from TIMESTAMP
    ALTER COLUMN end_kerja TYPE TIME;            -- Changed from TIMESTAMP

-- Note: Multiple logbooks per user per day now allowed
-- (No unique constraint on user_id + tanggal)
```

---

## Quick Reference

### Test Accounts
| Role | NPP | Password |
|------|-----|----------|
| SuperAdmin | 197501011995011001 | password |
| Admin | 198001012000011001 | password |
| Staff | 199003032010012003 | password |

### Key Directories
```
backend/
├── app/Http/Controllers/Api/V1/
│   ├── SummaryController.php           # NEW: Summary endpoints
│   └── LogbookController.php           # UPDATED: Manual time input
├── app/Models/
│   ├── DailyStaffSummary.php           # NEW
│   ├── DailyKpiSummary.php             # NEW
│   └── Logbook.php                     # UPDATED
├── app/Observers/
│   └── LogbookObserver.php             # NEW: Event listener
├── app/Services/
│   └── DailySummaryService.php         # NEW: Aggregation logic
├── database/migrations/
│   ├── xxxx_create_daily_staff_summaries_table.php   # NEW
│   └── xxxx_create_daily_kpi_summaries_table.php     # NEW
└── routes/api.php                       # UPDATED: Summary routes

frontend/
├── src/lib/types/                       # TypeScript types
├── src/lib/api/                         # API services
├── src/lib/components/
│   └── SlideoutDrawer.svelte           # NEW: Drawer component
├── src/routes/(app)/admin/
│   └── staff-performance/              # NEW: Staff performance page
└── src/routes/(mobile)/                # NEW: Mobile SPA routes
    ├── +layout.svelte                  # Tabbed layout
    ├── overview/
    ├── performance/
    ├── logbook/
    ├── team/                           # For managers
    └── profile/
```

### API Base URL
```
Development: http://localhost:8000/api/v1
Production: https://api.logbook.example.com/api/v1
```

### New Summary API Endpoints
```
GET /api/v1/summaries/daily           # Daily summary for user(s)
GET /api/v1/summaries/daily/{user_id} # Specific user's daily summary
GET /api/v1/summaries/period          # Period summary (date range)
GET /api/v1/summaries/kpi/daily       # Daily KPI breakdown
GET /api/v1/summaries/kpi/period      # Period KPI breakdown
GET /api/v1/summaries/team/daily      # Team daily summary (Manager)
GET /api/v1/summaries/staff-performance # Staff performance (Admin)
```

---

## Next Steps

1. Review semua dokumen plan di folder ini
2. Mulai dengan `04-MIGRATION-PLAN.md` untuk database changes (termasuk summary tables)
3. Implement `LogbookObserver` dan `DailySummaryService`
4. Build Mobile SPA dengan tabbed dashboard
5. Add Staff Performance page ke Web Admin
6. Ikuti `05-GAP-ANALYSIS.md` untuk tracking progress (21 gaps)

---

**Author**: AI Agent Organizer  
**Reviewed By**: -  
**Approved By**: -
