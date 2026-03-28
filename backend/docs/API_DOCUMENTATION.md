# Logbook API Documentation

> **LLM Context Document** — Optimized for AI-assisted frontend development  
> **Base URL**: `http://localhost:8000/api/v1`  
> **Auth**: Bearer token (Laravel Sanctum)

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Users](#2-users)
3. [KPI Master](#3-kpi-master)
4. [KPI Assignments](#4-kpi-assignments)
5. [Logbooks](#5-logbooks)
6. [Summaries](#6-summaries)
7. [Dashboards](#7-dashboards)
8. [Notifications](#8-notifications)
9. [Audit Logs](#9-audit-logs)
10. [Common Patterns](#10-common-patterns)

---

## Role System

| Role | Description | Capabilities |
|------|-------------|--------------|
| `SuperAdmin` | System administrator | Full access, all users/data |
| `Admin` | Operational admin | Manage Staff users, KPIs, review logbooks |
| `Staff` | Regular employee | Own logbooks, can have subordinates via `manager_id` |

**Manager capability** is determined by `has_subordinates: true`, not a separate role.

---

## 1. Authentication

### POST `/auth/login`

Login with NPP (Nomor Pokok Pegawai).

**Request:**
```json
{
  "npp": "199003032010012003",
  "password": "password123"
}
```

**Response 200:**
```json
{
  "message": "Login successful",
  "access_token": "7|jA58Vg38RCBPQ0cP2x...",
  "token_type": "Bearer",
  "user": {
    "id": "019d11a9-20f0-701c-8c44-1835f1d1c663",
    "npp": "199003032010012003",
    "nama": "Staff Koordinator Lapangan",
    "email": "staff.lead@logbook.com",
    "role": "Staff",
    "manager_id": "019d11a9-20e6-73db-bf66-a8f67c0683eb",
    "has_subordinates": true
  }
}
```

**Error 401:**
```json
{ "message": "Invalid credentials" }
```

---

### GET `/auth/me`

Get current authenticated user's full profile.

**Headers:** `Authorization: Bearer <token>`

**Response 200:**
```json
{
  "user": {
    "id": "019d11a9-20c9-7184-9fac-d94fb153ceb8",
    "npp": "198001012000011001",
    "nama": "Super Admin Sistem",
    "email": "superadmin@logbook.com",
    "role": "SuperAdmin",
    "manager_id": null,
    "foto": null,
    "foto_url": null,
    "tempat_lahir": "Ternate",
    "tanggal_lahir": "1972-12-08",
    "nik": "7885751958220264",
    "npwp": "21.572.549.3-483.152",
    "alamat": "Psr. Suniaraja No. 798, Banjar 18676, Kaltim",
    "status_kawin": "Belum_Kawin",
    "riwayat_pendidikan": [
      {
        "jenjang": "SMA",
        "jurusan": "adipisci",
        "institusi": "Perum Yolanda Tbk",
        "tahun": 2001
      }
    ],
    "riwayat_karir": [
      {
        "jabatan": "Hakim",
        "perusahaan": "PD Rahimah",
        "periode": "2002-1976"
      }
    ],
    "has_subordinates": false,
    "last_password_change": "2026-03-21T18:29:49.000000Z",
    "created_at": "2026-03-21T18:29:49.000000Z",
    "updated_at": "2026-03-21T18:29:49.000000Z"
  }
}
```

---

### POST `/auth/logout`

Revoke current token.

**Response 200:**
```json
{ "message": "Logged out successfully" }
```

---

### PUT `/auth/change-password`

**Request:**
```json
{
  "current_password": "password123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

### PUT `/auth/profile`

Update own profile. Accepts multipart/form-data for photo upload.

**Accepted fields:**
- `foto` (image: jpg/jpeg/png, max 5MB)
- `alamat`, `tempat_lahir`, `tanggal_lahir`
- `nik`, `npwp`, `status_kawin`
- `riwayat_pendidikan` (JSON array)
- `riwayat_karir` (JSON array)

**Protected fields** (cannot be changed): `npp`, `role`, `email`

---

## 2. Users

### GET `/users`

List all users with pagination.

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `per_page` | int | Items per page (default: 15) |
| `page` | int | Page number |
| `search` | string | Filter by nama or npp |
| `role` | string | Filter by role |

**Response 200:**
```json
{
  "data": [
    {
      "id": "019d11a9-20c9-7184-9fac-d94fb153ceb8",
      "npp": "198001012000011001",
      "nama": "Super Admin Sistem",
      "email": "superadmin@logbook.com",
      "role": "SuperAdmin",
      "manager_id": null,
      "foto": null,
      "foto_url": null,
      "tempat_lahir": "Ternate",
      "tanggal_lahir": "1972-12-08",
      "nik": "7885751958220264",
      "npwp": "21.572.549.3-483.152",
      "alamat": "Psr. Suniaraja No. 798, Banjar 18676, Kaltim",
      "status_kawin": "Belum_Kawin",
      "riwayat_pendidikan": [...],
      "riwayat_karir": [...],
      "has_subordinates": false,
      "last_password_change": "2026-03-21T18:29:49.000000Z",
      "created_at": "2026-03-21T18:29:49.000000Z",
      "updated_at": "2026-03-21T18:29:49.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/users?page=1",
    "last": "http://localhost:8000/api/v1/users?page=2",
    "prev": null,
    "next": "http://localhost:8000/api/v1/users?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 2,
    "per_page": 3,
    "to": 3,
    "total": 4
  }
}
```

---

### GET `/users/{id}/subordinates`

Get subordinates of a user (users where `manager_id` = this user).

**Response 200:**
```json
{
  "data": [
    {
      "id": "019d11a9-20f8-70e3-9af9-787b6543af35",
      "npp": "199204142012012004",
      "nama": "Staff Pelaksana Lapangan",
      "email": "staff.subordinate@logbook.com",
      "role": "Staff",
      "manager_id": "019d11a9-20f0-701c-8c44-1835f1d1c663",
      "has_subordinates": false,
      ...
    }
  ],
  "meta": { "current_page": 1, "total": 1, ... }
}
```

---

## 3. KPI Master

### GET `/kpi/master`

List all KPI definitions.

**Response 200:**
```json
{
  "data": [
    {
      "id": "019d11a9-2132-7117-ba37-fd1b59971710",
      "nama": "Respon Keluhan Pelanggan",
      "target_angka": 5,
      "satuan": "tiket",
      "deskripsi": "Penanganan keluhan pelanggan sampai status tindak lanjut jelas.",
      "status_aktif": true,
      "created_at": "2026-03-21T18:29:50.000000Z",
      "updated_at": "2026-03-21T18:29:50.000000Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 2, "total": 6 }
}
```

### POST `/kpi/master`

Create new KPI. **Admin/SuperAdmin only.**

**Request:**
```json
{
  "nama": "New KPI Name",
  "target_angka": 10,
  "satuan": "unit",
  "deskripsi": "KPI description",
  "status_aktif": true
}
```

---

## 4. KPI Assignments

### GET `/kpi/assignments`

List KPI assignments with user and KPI details.

**Response 200:**
```json
{
  "data": [
    {
      "id": "019d11a9-213c-722f-8aee-c8e89584a712",
      "user_id": "019d11a9-20f0-701c-8c44-1835f1d1c663",
      "kpi_id": "019d11a9-2101-73aa-b471-3e7eef7676ff",
      "assigned_by": "019d11a9-20e6-73db-bf66-a8f67c0683eb",
      "user": {
        "id": "019d11a9-20f0-701c-8c44-1835f1d1c663",
        "npp": "199003032010012003",
        "nama": "Staff Koordinator Lapangan",
        "email": "staff.lead@logbook.com",
        "role": "Staff",
        "manager_id": "019d11a9-20e6-73db-bf66-a8f67c0683eb",
        "has_subordinates": true,
        ...
      },
      "kpi": {
        "id": "019d11a9-2101-73aa-b471-3e7eef7676ff",
        "nama": "Meninjau Rencana Kerja Harian",
        "target_angka": 3,
        "satuan": "dokumen",
        "deskripsi": "Memastikan rencana kerja tim harian tervalidasi dengan baik.",
        "status_aktif": true
      },
      "assigner": {
        "id": "019d11a9-20e6-73db-bf66-a8f67c0683eb",
        "nama": "Admin Operasional",
        "email": "admin@logbook.com",
        "role": "Admin"
      },
      "created_at": "2026-03-21T18:29:50.000000Z"
    }
  ],
  "meta": { "total": 6 }
}
```

---

### GET `/kpi/me`

Get current user's assigned KPIs.

**Response 200:** (Array, not paginated)
```json
[
  {
    "id": "019d11a9-213c-722f-8aee-c8e89584a712",
    "user_id": "019d11a9-20f0-701c-8c44-1835f1d1c663",
    "kpi_id": "019d11a9-2101-73aa-b471-3e7eef7676ff",
    "assigned_by": "019d11a9-20e6-73db-bf66-a8f67c0683eb",
    "kpi": {
      "id": "019d11a9-2101-73aa-b471-3e7eef7676ff",
      "nama": "Meninjau Rencana Kerja Harian",
      "target_angka": "3.00",
      "satuan": "dokumen",
      "deskripsi": "Memastikan rencana kerja tim harian tervalidasi dengan baik.",
      "status_aktif": true
    },
    "assigner": { "nama": "Admin Operasional", ... }
  }
]
```

---

## 5. Logbooks

### GET `/logbooks`

List logbooks with filtering.

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `per_page` | int | Items per page |
| `status` | string | `DRAFT`, `SUBMITTED`, `ACCEPTED`, `REJECTED` |
| `date_from` | string | Start date (YYYY-MM-DD) |
| `date_to` | string | End date (YYYY-MM-DD) |
| `user_id` | string | Filter by user (Admin/Manager) |
| `search` | string | Search by nama/npp |
| `sort_by` | string | Field to sort by |
| `sort_dir` | string | `asc` or `desc` |

**Response 200:**
```json
{
  "data": [
    {
      "id": "019d11a9-24b5-70e7-af1a-36de5d0f52c3",
      "user_id": "019d11a9-20f0-701c-8c44-1835f1d1c663",
      "tanggal": "2026-03-21",
      "start_kerja": "07:45:00",
      "end_kerja": "16:30:00",
      "lokasi": "Kantor Operasional Pusat",
      "lokasi_lat": -6.21,
      "lokasi_lng": 106.84513,
      "status": "SUBMITTED",
      "rating": null,
      "reviewed_by": null,
      "reviewed_at": null,
      "reviewer_comment": null,
      "user": {
        "id": "019d11a9-20f0-701c-8c44-1835f1d1c663",
        "npp": "199003032010012003",
        "nama": "Staff Koordinator Lapangan",
        "role": "Staff",
        "manager_id": "019d11a9-20e6-73db-bf66-a8f67c0683eb",
        "has_subordinates": true
      },
      "reviewer": null,
      "details": [
        {
          "id": "019d11a9-24bf-71e3-927a-461c03d75928",
          "kpi_id": "019d11a9-2101-73aa-b471-3e7eef7676ff",
          "kpi_nama": "Meninjau Rencana Kerja Harian",
          "target_angka": 3,
          "satuan": "dokumen",
          "capaian_angka": 1.95,
          "lampiran_file": "logbook-kpi-attachments/today-proof-019d11a9-20f0-701c-8c44-1835f1d1c663.pdf",
          "finished_at": "2026-03-21T14:29:00.000000Z"
        }
      ],
      "created_at": "2026-03-21T18:29:50.000000Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 13, "total": 26 }
}
```

---

### GET `/logbooks/{id}`

Get single logbook with full details.

---

### GET `/logbooks/{id}/duration`

Get work duration breakdown.

**Response 200:**
```json
{
  "logbook_id": "019d11a9-24b5-70e7-af1a-36de5d0f52c3",
  "tanggal": "2026-03-21",
  "start_kerja": "07:45:00",
  "end_kerja": "16:30:00",
  "gross_work_minutes": 525,
  "break_overlap_minutes": 60,
  "net_work_minutes": 465
}
```

---

### POST `/logbooks/start` or POST `/logbooks`

Create new logbook (DRAFT status).

**Request:**
```json
{
  "tanggal": "2026-03-22",
  "start_kerja": "08:00",
  "end_kerja": "17:00",
  "lokasi": "Kantor Pusat"
}
```

**Rules:**
- `start_kerja` must be >= 07:00
- `end_kerja` must be > `start_kerja` if provided
- Multiple logbooks per day allowed

---

### PATCH `/logbooks/{id}/kpi/{detail_id}/progress`

Update KPI progress (DRAFT only).

**Request:**
```json
{
  "capaian_angka": 7.5
}
```

---

### POST `/logbooks/{id}/kpi/{detail_id}/attachment`

Upload KPI attachment (DRAFT only). Multipart form-data.

**Request:** `file` (image or PDF, max 5MB)

---

### POST `/logbooks/{id}/submit`

Submit logbook for review.

**Rules:**
- Only from DRAFT status
- At least one KPI must have `capaian_angka > 0`
- Creates notification for manager

---

### PUT `/logbooks/{id}/review`

Manager/Admin review a SUBMITTED logbook.

**Request:**
```json
{
  "decision": "ACCEPTED",
  "rating": 4,
  "reviewer_comment": "Bagus dan lengkap"
}
```

**Rules:**
- `decision`: `ACCEPTED` or `REJECTED`
- `rating`: 1-5
- `reviewer_comment`: required
- Creates notification for logbook owner

---

## 6. Summaries

Pre-aggregated daily and period summaries. Auto-synced via Observers.

### GET `/summaries/daily`

Daily staff summaries (all users, Admin/SA view).

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `date` | string | Single date |
| `date_from` | string | Start date |
| `date_to` | string | End date |
| `user_id` | string | Filter by user |
| `per_page` | int | Items per page |

**Response 200:**
```json
{
  "data": [
    {
      "id": "019d11c0-a32f-71cf-9f4a-b252dfe2d663",
      "user_id": "019d11a9-20f8-70e3-9af9-787b6543af35",
      "tanggal": "2026-03-22T00:00:00.000000Z",
      "total_logbooks": 1,
      "submitted_logbooks": 1,
      "accepted_logbooks": 0,
      "rejected_logbooks": 0,
      "total_work_minutes": 0,
      "total_kpi": 3,
      "target_angka_total": "23.00",
      "capaian_angka_total": "6.50",
      "progress_percent": "28.26",
      "user": {
        "id": "019d11a9-20f8-70e3-9af9-787b6543af35",
        "nama": "Staff Pelaksana Lapangan",
        "npp": "199204142012012004",
        "role": "Staff",
        "manager_id": "019d11a9-20f0-701c-8c44-1835f1d1c663"
      }
    }
  ],
  "current_page": 1,
  "last_page": 13,
  "total": 26
}
```

---

### GET `/summaries/daily/{user_id}`

Daily summaries for specific user.

---

### GET `/summaries/kpi/daily`

KPI-level daily summaries.

**Response 200:**
```json
{
  "data": [
    {
      "id": "019d11c0-a334-7132-97c8-6adb5724f85c",
      "user_id": "019d11a9-20f8-70e3-9af9-787b6543af35",
      "kpi_id": "019d11a9-2124-7013-97a1-809841208604",
      "tanggal": "2026-03-22T00:00:00.000000Z",
      "kpi_nama": "Eksekusi Pemeriksaan Lapangan",
      "satuan": "unit",
      "target_angka_total": "8.00",
      "capaian_angka_total": "6.50",
      "progress_percent": "81.25",
      "total_lampiran": 0,
      "user": {
        "id": "019d11a9-20f8-70e3-9af9-787b6543af35",
        "nama": "Staff Pelaksana Lapangan",
        "npp": "199204142012012004",
        "manager_id": "019d11a9-20f0-701c-8c44-1835f1d1c663"
      },
      "kpi": {
        "id": "019d11a9-2124-7013-97a1-809841208604",
        "nama": "Eksekusi Pemeriksaan Lapangan"
      }
    }
  ]
}
```

---

### GET `/summaries/kpi/period`

KPI aggregation over date range.

**Query Parameters:** `date_from`, `date_to`

**Response 200:**
```json
{
  "date_from": "2026-03-01",
  "date_to": "2026-03-31",
  "items": [
    {
      "kpi_id": "019d11a9-212b-7214-8f7f-c5a58f72b0b5",
      "kpi_nama": "Dokumentasi Bukti Pekerjaan",
      "satuan": "dokumen",
      "target_angka_total": 110,
      "capaian_angka_total": 73.5,
      "progress_percent": 66.82,
      "total_lampiran": 0
    }
  ]
}
```

---

### GET `/summaries/team/daily`

Team daily summaries (for managers, shows subordinates).

**Response 200:** Same structure as `/summaries/daily` but filtered to subordinates.

---

### GET `/summaries/staff-performance`

Staff performance ranking over period.

**Query Parameters:** `date_from`, `date_to`, `per_page`

**Response 200:**
```json
{
  "date_from": "2026-03-01",
  "date_to": "2026-03-21",
  "items": [
    {
      "user_id": "019d11a9-20f0-701c-8c44-1835f1d1c663",
      "nama": "Staff Koordinator Lapangan",
      "npp": "199003032010012003",
      "total_logbooks": 14,
      "accepted_logbooks": 9,
      "rejected_logbooks": 1,
      "target_angka_total": 154,
      "capaian_angka_total": 118.25,
      "progress_percent": 76.79
    }
  ]
}
```

---

## 7. Dashboards

### GET `/dashboard/admin`

Admin/SuperAdmin dashboard statistics.

**Response 200:**
```json
{
  "data": {
    "total_active_users": 4,
    "total_logbooks_this_month": 26,
    "total_logbooks_last_month": 0,
    "pending_logbooks_count": 10,
    "active_kpis": 6,
    "logbooks_by_day": [
      { "date": "2026-03-21", "count": 26 }
    ],
    "logbooks_by_status": [
      { "status": "ACCEPTED", "count": 14 },
      { "status": "REJECTED", "count": 2 },
      { "status": "SUBMITTED", "count": 10 }
    ],
    "users_by_role": [
      { "role": "Admin", "count": 1 },
      { "role": "Staff", "count": 2 },
      { "role": "SuperAdmin", "count": 1 }
    ],
    "logbook_trend": 100
  }
}
```

---

### GET `/dashboard/staff`

Staff personal dashboard.

**Response 200:**
```json
{
  "data": {
    "personal_kpi_completion_rate": 68.79,
    "total_logbooks": 11,
    "missed_logbooks_count": 4,
    "average_rating": 4.2,
    "kpi_achievements": [
      {
        "nama_kpi": "Dokumentasi Bukti Pekerjaan",
        "total": 11,
        "completed": 0,
        "completion_rate": 0
      }
    ]
  }
}
```

---

### GET `/dashboard/manager`

Manager dashboard (shows subordinate summary).

**Response 200:**
```json
{
  "data": {
    "subordinates": [
      {
        "id": "019d11a9-20f8-70e3-9af9-787b6543af35",
        "nama": "Staff Pelaksana Lapangan",
        "target_angka_total": 253,
        "capaian_angka_total": 174.05,
        "completion_rate": 68.79
      }
    ],
    "pending_logbooks_count": 5
  }
}
```

---

## 8. Notifications

### GET `/notifications`

Get user's notifications.

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `is_read` | bool | Filter by read status |
| `unread_only` | bool | Only unread |
| `per_page` | int | Items per page |

**Response 200:**
```json
{
  "data": [
    {
      "id": "uuid",
      "type": "LOGBOOK_SUBMITTED",
      "message": "Staff X submitted logbook",
      "is_read": false,
      "preview_message": "Short preview",
      "target_path": "/admin/logbooks",
      "target_params": { "id": "logbook-uuid" },
      "created_at": "2026-03-21T18:55:30.000000Z"
    }
  ]
}
```

**Notification Types:**
- `KPI_ASSIGNMENT` — KPI assigned to staff
- `LOGBOOK_SUBMITTED` — Staff submitted logbook (to manager)
- `LOGBOOK_ACCEPTED` — Logbook accepted (to staff)
- `LOGBOOK_REJECTED` — Logbook rejected (to staff)

---

### PUT `/notifications/{id}/read`

Mark single notification as read.

### PUT `/notifications/read-all`

Mark all notifications as read.

---

## 9. Audit Logs

### GET `/audit-logs`

**SuperAdmin only.** View system audit trail.

**Response 200:**
```json
{
  "data": [
    {
      "id": "019d11a9-2136-7115-98a2-a23ac0b57628",
      "event": "created",
      "auditable_type": "kpi_masters",
      "auditable_id": "019d11a9-2132-7117-ba37-fd1b59971710",
      "old_values": [],
      "new_values": "{...}",
      "table_name": "kpi_masters",
      "action": "created",
      "performed_by": null,
      "performed_at": "2026-03-21T18:29:50.000000Z",
      "ip_address": "127.0.0.1",
      "user_agent": "Symfony",
      "user": null
    }
  ],
  "meta": { "total": 10 }
}
```

---

## 10. Common Patterns

### Pagination Format

All paginated endpoints use this structure:

```json
{
  "data": [...],
  "links": {
    "first": "url?page=1",
    "last": "url?page=N",
    "prev": "url?page=X" | null,
    "next": "url?page=Y" | null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": N,
    "per_page": 15,
    "to": 15,
    "total": 100
  }
}
```

---

### Attachment URL Construction

**Backend stores:** `logbook-kpi-attachments/filename.pdf`

**Frontend must construct:**
```typescript
function getAttachmentUrl(filePath: string): string {
  if (!filePath) return '#';
  if (filePath.startsWith('http')) return filePath;
  return `/storage/${filePath}`;
}
```

**File type detection:**
```typescript
function isImageFile(filePath: string): boolean {
  const ext = filePath.split('.').pop()?.toLowerCase() ?? '';
  return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'].includes(ext);
}

function isPdfFile(filePath: string): boolean {
  return filePath.split('.').pop()?.toLowerCase() === 'pdf';
}
```

---

### Date/Time Formats

| Field | Format | Example |
|-------|--------|---------|
| `tanggal` | `YYYY-MM-DD` | `2026-03-21` |
| `start_kerja` | `HH:MM:SS` or `HH:MM` | `07:45:00` |
| `end_kerja` | `HH:MM:SS` or `HH:MM` | `16:30:00` |
| `*_at` timestamps | ISO 8601 | `2026-03-21T18:29:50.000000Z` |

---

### Error Responses

**401 Unauthorized:**
```json
{ "message": "Unauthenticated." }
```

**403 Forbidden:**
```json
{ "message": "This action is unauthorized." }
```

**404 Not Found:**
```json
{ "message": "Record not found" }
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

---

### Status Enum

| Status | Description | Transitions |
|--------|-------------|-------------|
| `DRAFT` | Initial state, editable | → SUBMITTED |
| `SUBMITTED` | Awaiting review | → ACCEPTED, REJECTED |
| `ACCEPTED` | Approved by manager | Final |
| `REJECTED` | Rejected by manager | Final |

---

### Test Credentials

| Role | NPP | Password | Email |
|------|-----|----------|-------|
| SuperAdmin | `198001012000011001` | `password123` | superadmin@logbook.com |
| Admin | `198502022005011002` | `password123` | admin@logbook.com |
| Staff (Lead) | `199003032010012003` | `password123` | staff.lead@logbook.com |
| Staff (Sub) | `199204142012012004` | `password123` | staff.subordinate@logbook.com |

---

### Test User IDs

| Role | ID |
|------|-----|
| SuperAdmin | `019d11a9-20c9-7184-9fac-d94fb153ceb8` |
| Admin | `019d11a9-20e6-73db-bf66-a8f67c0683eb` |
| Staff Lead | `019d11a9-20f0-701c-8c44-1835f1d1c663` |
| Staff Sub | `019d11a9-20f8-70e3-9af9-787b6543af35` |

---

*Last updated: 2026-03-22*
