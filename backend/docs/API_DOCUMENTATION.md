# Logbook API Documentation (Backend Source of Truth)

> **Base URL**: `http://localhost:8000/api/v1`  
> **Authentication**: Laravel Sanctum Bearer token (`Authorization: Bearer <token>`) for all endpoints except login.

---

## Table of Contents

1. [Role & Access Model](#1-role--access-model)
2. [Authentication](#2-authentication)
3. [Users](#3-users)
4. [KPI Master](#4-kpi-master)
5. [KPI Assignments](#5-kpi-assignments)
6. [Logbooks](#6-logbooks)
7. [Summaries](#7-summaries)
8. [Notifications](#8-notifications)
9. [Audit Logs](#9-audit-logs)
10. [Analytics & Dashboards](#10-analytics--dashboards)
11. [Response Shape Conventions](#11-response-shape-conventions)
12. [Observers & Derived Summary Behavior](#12-observers--derived-summary-behavior)
13. [Error Responses](#13-error-responses)

---

## 1. Role & Access Model

### Roles

| Role | Meaning |
|---|---|
| `SuperAdmin` | Full privileged access |
| `Admin` | Privileged operational access |
| `Staff` | Regular user; can also act as manager if they have subordinates |

### Manager Capability

There is no dedicated `Manager` role. A user is treated as manager when:

- `role = Staff`, and
- the user has subordinates (`hasSubordinates()` returns true).

### Common Access Rules in Controllers

- **Privileged** (`isPrivileged`) = `Admin` or `SuperAdmin`
- **Regular staff** (`isStaff && !hasSubordinates`) is frequently restricted to own data.
- **Manager-like staff** (`hasSubordinates`) can access direct subordinate data in specific endpoints.

---

## 2. Authentication

### POST `/auth/login`

Login using NPP and password.

- Middleware: `throttle:5,1`
- No auth token required

**Request**
```json
{
  "npp": "199003032010012003",
  "password": "password123"
}
```

**Validation rules**
- `npp`: required, string
- `password`: required, string

**Response 200**
```json
{
  "message": "Login successful",
  "access_token": "7|...",
  "token_type": "Bearer",
  "user": {
    "id": "uuid",
    "npp": "199003032010012003",
    "nama": "...",
    "email": "...",
    "role": "Staff",
    "manager_id": "uuid-or-null",
    "foto": null,
    "foto_url": null,
    "tempat_lahir": null,
    "tanggal_lahir": null,
    "nik": null,
    "npwp": null,
    "alamat": null,
    "status_kawin": null,
    "riwayat_pendidikan": null,
    "riwayat_karir": null,
    "has_subordinates": false,
    "manager": null,
    "last_password_change": null,
    "created_at": "2026-03-21T18:29:49.000000Z",
    "updated_at": "2026-03-21T18:29:49.000000Z"
  }
}
```

**Response 401**
```json
{ "message": "Kredensial tidak valid" }
```

---

### POST `/auth/logout`

Invalidate current access token.

**Response 200**
```json
{ "message": "Logged out successfully" }
```

---

### GET `/auth/me`

Get authenticated user profile.

**Response 200**
```json
{
  "user": {
    "id": "uuid",
    "npp": "...",
    "nama": "...",
    "email": "...",
    "role": "SuperAdmin",
    "manager_id": null,
    "foto": null,
    "foto_url": null,
    "tempat_lahir": "...",
    "tanggal_lahir": "YYYY-MM-DD",
    "nik": "...",
    "npwp": "...",
    "alamat": "...",
    "status_kawin": "...",
    "riwayat_pendidikan": [],
    "riwayat_karir": [],
    "has_subordinates": false,
    "manager": null,
    "last_password_change": "2026-03-21T18:29:49.000000Z",
    "created_at": "2026-03-21T18:29:49.000000Z",
    "updated_at": "2026-03-21T18:29:49.000000Z"
  }
}
```

---

### PUT `/auth/profile`

Update authenticated profile.

Supports `multipart/form-data` for `foto`.

**Validated fields**
- `foto`: sometimes, nullable, image, mimes `jpg,jpeg,png,webp`, max 5 MB
- `alamat`: sometimes, nullable, string, max 2000
- `tempat_lahir`: sometimes, nullable, string, max 255
- `tanggal_lahir`: sometimes, nullable, date
- `nik`: sometimes, nullable, string, max 32
- `npwp`: sometimes, nullable, string, max 32
- `status_kawin`: sometimes, nullable, string, max 32
- `riwayat_pendidikan`: sometimes, nullable, array
- `riwayat_karir`: sometimes, nullable, array

**Response 200**
```json
{
  "message": "Profil berhasil diperbarui",
  "data": {
    "id": "uuid",
    "npp": "...",
    "nama": "...",
    "email": "...",
    "role": "Staff",
    "manager_id": "uuid-or-null",
    "foto": "profile-photos/filename.jpg",
    "foto_url": "/storage/profile-photos/filename.jpg",
    "tempat_lahir": "...",
    "tanggal_lahir": "YYYY-MM-DD",
    "nik": "...",
    "npwp": "...",
    "alamat": "...",
    "status_kawin": "...",
    "riwayat_pendidikan": [],
    "riwayat_karir": [],
    "has_subordinates": false,
    "manager": null,
    "last_password_change": "...",
    "created_at": "...",
    "updated_at": "..."
  }
}
```

---

### PUT `/auth/change-password`

Change authenticated user password.

**Request**
```json
{
  "old_password": "current-password",
  "new_password": "new-password-123",
  "new_password_confirmation": "new-password-123"
}
```

**Validation rules**
- `old_password`: required, string
- `new_password`: required, string, min:8, confirmed

**Response 200**
```json
{ "message": "Password updated successfully" }
```

**Response 400**
```json
{ "message": "Current password does not match" }
```

---

## 3. Users

All endpoints require auth.

### GET `/users`

List users with role-based filtering.

**Query params**
- `search` (nama/email/npp)
- `role`
- `sort_by`: `nama|email|npp|role|created_at`
- `sort_dir`: `asc|desc`
- `per_page` (max 100)

**Access**
- Staff without subordinates: `403 Forbidden`
- Non-privileged manager-like staff: auto-filtered to own subordinates

**Response**
- Resource pagination: `data + links + meta`

---

### POST `/users`

Create user.

**Validation rules**
- `nama`: required, string, max:255
- `email`: required, email, max:255, unique
- `npp`: required, string, max:255, unique
- `role`: required, one of `SuperAdmin|Admin|Staff|ADMIN|STAFF`
- `password`: required, string, min:8
- `manager_id`: nullable, uuid, exists:users,id

**Access**
- Privileged only (`Admin`/`SuperAdmin`)
- Additional rule: `Admin` cannot create `Admin` or `SuperAdmin`

**Response 201**
- Single `UserResource` (`{ "data": { ... } }`)

---

### GET `/users/{user}`

View user detail.

**Access**
- Allowed if requester can manage target user, or target is requester

**Response**
- Single `UserResource`

---

### PUT/PATCH `/users/{user}`

Update user profile/account fields.

**Validation (all `sometimes`)**
- `nama`: required|string|max:255
- `npp`: required|string|max:255|unique(ignore current)
- `role`: required|in(`SuperAdmin,Admin,Staff,ADMIN,STAFF`)
- `manager_id`: nullable|uuid|exists
- `email`: required|email|max:255|unique(ignore current)
- `foto`: nullable image `jpg,jpeg,png,webp`, max 5 MB
- `tempat_lahir`: nullable string max:255
- `tanggal_lahir`: nullable date
- `nik`: nullable string max:32
- `npwp`: nullable string max:32
- `alamat`: nullable string max:2000
- `status_kawin`: nullable string max:32
- `riwayat_pendidikan`: nullable array
- `riwayat_karir`: nullable array

**Access**
- Privileged only
- `Admin` cannot assign role `Admin` or `SuperAdmin`

**Response**
- Single `UserResource`

---

### DELETE `/users/{user}`

Delete user and revoke user tokens.

**Access**: privileged only

**Response 200**
```json
{ "message": "Pengguna berhasil dihapus" }
```

---

### PUT `/users/{user}/reset-password`

Reset user password.

**Validation**
- `new_password`: required|string|min:8

**Access**: privileged only

**Response 200**
```json
{ "message": "Password reset successfully" }
```

---

### GET `/users/{user}/subordinates`

List subordinates for a user.

**Access**
- Privileged: may view subordinates of any user
- Non-privileged: only for own user id and must actually have subordinates

**Response**
- Resource pagination (`UserResource` collection)

---

## 4. KPI Master

Resource routes from `Route::apiResource('kpi/master', MasterKpiController::class)`.

### GET `/kpi/master`

List master KPIs.

**Query params**
- `search` (by `nama`)
- `status_aktif` (boolean)
- `sort_by`: `nama|created_at`
- `sort_dir`: `asc|desc`
- `per_page` (max 100)

**Access**
- Staff without subordinates: forbidden

**Response**
- Resource pagination (`MasterKpiResource`)

---

### POST `/kpi/master`

Create KPI.

**Validation**
- `nama`: required|string|max:255
- `target_angka`: nullable|numeric|min:0
- `satuan`: nullable|string|max:100
- `deskripsi`: nullable|string
- `status_aktif`: boolean

**Access**: privileged only

**Response 201**
- Single `MasterKpiResource`

---

### GET `/kpi/master/{kpi}`

Show KPI detail.

**Access**
- Staff without subordinates: forbidden

**Response**
- Single `MasterKpiResource`

---

### PUT/PATCH `/kpi/master/{kpi}`

Update KPI.

**Validation**
- Same as create, with `nama` as `sometimes|required`

**Access**: privileged only

**Response**
- Single `MasterKpiResource`

---

### DELETE `/kpi/master/{kpi}`

Delete KPI.

**Access**: privileged only

**Response 200**
```json
{ "message": "KPI deleted successfully" }
```

---

## 5. KPI Assignments

### GET `/kpi/assignments`

List assignments.

**Query params**
- `per_page` (default 15)

**Access**
- Staff without subordinates: forbidden
- Non-privileged manager-like users: results limited to subordinate users

**Response**
- Resource pagination (`KpiAssignmentResource`)

---

### POST `/kpi/assignments`

Assign KPI to user.

**Validation**
- `user_id`: required|exists:users,id
- `kpi_id`: required|exists:kpi_masters,id

**Access**
- Staff without subordinates: forbidden
- Non-privileged manager-like users can assign only to direct subordinates

**Side effect**
- Creates `Notification` of type `KPI_ASSIGNMENT` for assignee.

**Response 201**
- Single `KpiAssignmentResource`

---

### DELETE `/kpi/assignments/{assignment}`

Delete assignment.

**Access**
- Staff without subordinates: forbidden
- Non-privileged manager-like users only for direct subordinate assignments

**Response 204**
- No content

---

### GET `/kpi/me`

Current user assignments.

**Response 200**
- Raw array of assignment models with loaded relations (`kpi`, `assigner`)  
  (not wrapped with `KpiAssignmentResource`)

---

## 6. Logbooks

### POST `/logbooks/start`
### POST `/logbooks`

Both create a new logbook (`/logbooks` delegates to `start()` internally).

**Validation**
- `tanggal`: required|date
- `start_kerja`: required|date_format:H:i
- `end_kerja`: nullable|date_format:H:i
- `lokasi`: required|string|max:1000
- `lokasi_lat`: nullable|numeric|between:-90,90
- `lokasi_lng`: nullable|numeric|between:-180,180

**Business checks**
- Only `Staff` can create
- `start_kerja >= 07:00`
- if `end_kerja` present, must be greater than `start_kerja`
- user must have assigned KPIs

**Important implementation behavior**
- New logbook is created with status `SUBMITTED`.
- KPI details are auto-generated from user KPI assignments.

**Response 201**
- Single `LogbookResource`

---

### GET `/logbooks`

List logbooks with role-aware visibility.

**Query params**
- `user_id`
- `search` (owner `nama`/`npp`)
- `status`: only `SUBMITTED|ACCEPTED|REJECTED` are applied
- `date_from`
- `date_to`
- `sort_by`: `tanggal|start_kerja|end_kerja|status|created_at`
- `sort_dir`: `asc|desc`
- `per_page` (max 100)

**Access/filtering**
- Staff without subordinates: own data only
- Non-privileged manager-like users: own + direct subordinates

**Response**
- Resource pagination (`LogbookResource`)

---

### GET `/logbooks/{logbook}`

Get single logbook.

**Access**
- Privileged, or owner, or manager of owner

**Response**
- Single `LogbookResource` (includes loaded `user`, `reviewer`, `details`)

---

### PATCH `/logbooks/{logbook}`

Update logbook basic fields.

**Validation (`sometimes`)**
- `tanggal`: date
- `start_kerja`: date_format:H:i
- `end_kerja`: nullable|date_format:H:i
- `lokasi`: string|max:1000
- `lokasi_lat`: nullable|numeric|between:-90,90
- `lokasi_lng`: nullable|numeric|between:-180,180

**Business checks**
- Owner only
- Not allowed when status = `ACCEPTED`
- If `start_kerja` provided, must be `>= 07:00`
- Effective `end_kerja` must be greater than effective `start_kerja`

**Response**
- Single `LogbookResource`

---

### DELETE `/logbooks/{logbook}`

Delete logbook.

**Business checks**
- Owner only
- Not allowed when status = `ACCEPTED`

**Response 200**
```json
{ "message": "Logbook berhasil dihapus" }
```

---

### GET `/logbooks/{logbook}/duration`

Get computed work duration.

**Access**
- Privileged, owner, or manager of owner

**Response 200**
```json
{
  "logbook_id": "uuid",
  "tanggal": "2026-03-21",
  "start_kerja": "07:45:00",
  "end_kerja": "16:30:00",
  "gross_work_minutes": 525,
  "break_overlap_minutes": 60,
  "net_work_minutes": 465
}
```

---

### PATCH `/logbooks/{logbook}/kpi/{detail}/progress`

Update KPI progress on a logbook detail.

**Validation**
- `capaian_angka`: required|numeric|min:0

**Business checks**
- Owner only
- `detail` must belong to `logbook`
- Not allowed when status = `ACCEPTED`

**Response 200**
```json
{
  "id": "uuid",
  "capaian_angka": 7.5,
  "target_angka": 10,
  "satuan": "unit",
  "finished_at": "2026-03-21T14:29:00.000000Z"
}
```

---

### POST `/logbooks/{logbook}/kpi/{detail}/attachment`

Upload attachment for KPI detail.

**Validation**
- `lampiran_file`: required|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx

**Business checks**
- Owner only
- `detail` must belong to `logbook`
- Not allowed when status = `ACCEPTED`

**Response 200**
```json
{
  "message": "Lampiran KPI berhasil diunggah",
  "data": {
    "id": "uuid",
    "lampiran_file": "logbook-kpi-attachments/filename.pdf"
  }
}
```

---

### DELETE `/logbooks/{logbook}/kpi/{detail}/attachment`

Delete attachment for KPI detail.

**Business checks**
- Owner only
- `detail` must belong to `logbook`
- Not allowed when status = `ACCEPTED`

**Response 200**
```json
{
  "message": "Lampiran KPI berhasil dihapus",
  "data": {
    "id": "uuid",
    "lampiran_file": null
  }
}
```

---

### POST `/logbooks/{logbook}/submit`

Submit (or re-submit) logbook.

**Business checks**
- Owner only
- Not allowed when status = `ACCEPTED`
- `end_kerja` must be filled
- At least one KPI detail must have `capaian_angka > 0`

**Side effect**
- If owner has manager, creates `Notification` type `LOGBOOK_SUBMITTED` for manager.

**Response**
- Single `LogbookResource`

---

### PUT `/logbooks/{logbook}/review`

Review submitted logbook (manager/admin/superadmin flow).

**Validation**
- `decision`: required|string|in:`ACCEPTED,REJECTED`
- `rating`: required|integer|min:1|max:5
- `reviewer_comment`: required|string|max:5000

**Business checks**
- Privileged users can review any logbook
- Non-privileged reviewer must have subordinates and target must be direct subordinate
- Logbook must be `SUBMITTED`
- Uses DB transaction with row lock to avoid double-review race

**Side effect**
- Creates notification for logbook owner with type:
  - `LOGBOOK_ACCEPTED` or
  - `LOGBOOK_REJECTED`

**Response 200**
```json
{
  "message": "Logbook review berhasil disimpan",
  "data": {
    "id": "uuid",
    "status": "ACCEPTED",
    "rating": 4,
    "reviewer_comment": "Bagus",
    "reviewed_by": "uuid",
    "reviewed_at": "2026-03-21T18:29:50.000000Z",
    "user": { "id": "...", "nama": "..." },
    "reviewer": { "id": "...", "nama": "..." },
    "details": []
  }
}
```

---

## 7. Summaries

> Summary endpoints are derived/aggregated outputs. Several endpoints return **raw paginator JSON** (not API Resource pagination).

### GET `/summaries/daily`

Daily staff summary rows.

**Query params**
- `tanggal`
- `date_from`
- `date_to`
- `per_page` (max 100)

**Access/filtering**
- Staff without subordinates: own summary rows only
- Non-privileged manager-like users: only subordinate rows

**Response**
- Raw Laravel paginator (`current_page`, `data`, `first_page_url`, `last_page`, `links[]`, etc.)

---

### GET `/summaries/daily/{user_id}`

Daily summaries by specific user.

**Access**
- Requester must manage target user or be the target user

**Query params**
- `date_from`
- `date_to`
- `per_page` (max 100)

**Response**
- Raw paginator

---

### GET `/summaries/period`

Aggregated period summary.

**Query params**
- `date_from` (default start of current month)
- `date_to` (default today)

**Response 200**
```json
{
  "date_from": "2026-03-01",
  "date_to": "2026-03-31",
  "total_logbooks": 26,
  "submitted_logbooks": 10,
  "accepted_logbooks": 14,
  "rejected_logbooks": 2,
  "total_work_minutes": 12345,
  "total_kpi": 100,
  "target_angka_total": 250,
  "capaian_angka_total": 190,
  "progress_percent": 76
}
```

---

### GET `/summaries/kpi/daily`

KPI-level daily summary rows.

**Query params**
- `tanggal`
- `per_page`

**Response**
- Raw paginator

---

### GET `/summaries/kpi/period`

KPI aggregation over period.

**Query params**
- `date_from` (default start of current month)
- `date_to` (default today)
- `user_id` (optional)

**Response 200**
```json
{
  "date_from": "2026-03-01",
  "date_to": "2026-03-31",
  "items": [
    {
      "kpi_id": "uuid",
      "kpi_nama": "...",
      "satuan": "unit",
      "target_angka_total": 100,
      "capaian_angka_total": 81.5,
      "progress_percent": 81.5,
      "total_lampiran": 10
    }
  ]
}
```

---

### GET `/summaries/team/daily`

Daily subordinate summary rows.

**Access**
- Requires privileged user OR user with subordinates

**Query params**
- `tanggal`
- `per_page`

**Response**
- Raw paginator

---

### GET `/summaries/staff-performance`

Staff performance ranking over period.

**Access**
- Staff without subordinates: forbidden

**Query params**
- `date_from` (default start of month)
- `date_to` (default today)

**Response 200**
```json
{
  "date_from": "2026-03-01",
  "date_to": "2026-03-21",
  "items": [
    {
      "user_id": "uuid",
      "nama": "...",
      "npp": "...",
      "total_logbooks": 14,
      "accepted_logbooks": 9,
      "rejected_logbooks": 1,
      "total_days_worked": 14,
      "total_work_hours": 111.97,
      "average_rating": 3.33,
      "progress_percent": 76.79
    }
  ]
}
```

---

## 8. Notifications

### GET `/notifications`

Get authenticated user's notifications.

**Query params**
- `is_read` (boolean)
- `unread_only` (boolean, overrides `is_read` to false)
- `type`
- `sort_by` (`created_at`)
- `sort_dir` (`asc|desc`)
- `per_page` (max 100)

**Response shape**
- Raw paginator
- Each item contains notification model fields plus appended fields:
  - `preview_message`
  - `target_path`
  - `target_params`

**Example item**
```json
{
  "id": "uuid",
  "user_id": "uuid",
  "title": "Logbook Submitted",
  "message": "Terdapat logbook baru yang menunggu review.",
  "type": "LOGBOOK_SUBMITTED",
  "reference_id": "uuid",
  "is_read": false,
  "created_at": "2026-03-21T18:55:30.000000Z",
  "updated_at": "2026-03-21T18:55:30.000000Z",
  "preview_message": "Terdapat logbook baru yang menunggu review.",
  "target_path": "/manager/reviews",
  "target_params": { "logbook_id": "uuid" }
}
```

---

### PUT `/notifications/{notification}/read`

Mark one notification as read.

**Access**
- Notification must belong to authenticated user

**Response 200**
- Raw notification model JSON

---

### PUT `/notifications/read-all`

Mark all authenticated user's unread notifications as read.

**Response 200**
```json
{ "message": "All notifications marked as read" }
```

---

## 9. Audit Logs

### GET `/audit-logs`

List audit logs.

**Access**
- `SuperAdmin` only

**Query params**
- `search` (table name)
- `action`: `created|updated|deleted`
- `date_from`
- `date_to`
- `sort_by`: `performed_at|created_at`
- `sort_dir`: `asc|desc`
- `per_page` (max 100)

**Response**
- Resource pagination (`AuditLogResource`)

**`AuditLogResource` item highlights**
- Canonical aliases: `event`, `auditable_type`, `auditable_id`, `old_values`, `new_values`
- Backward-compatible fields also included: `table_name`, `record_id`, `action`, `old_data`, `new_data`
- Nested `user` (if loaded): `id`, `nama`, `email`, `npp`, `role`

---

## 10. Analytics & Dashboards

### GET `/dashboard/admin`

Admin/SuperAdmin dashboard.

**Access**
- Privileged only

**Response**
```json
{
  "data": {
    "total_active_users": 4,
    "total_logbooks_this_month": 26,
    "total_logbooks_last_month": 0,
    "pending_logbooks_count": 10,
    "active_kpis": 6,
    "logbooks_by_day": [{ "date": "2026-03-21", "count": 26 }],
    "logbooks_by_status": [{ "status": "ACCEPTED", "count": 14 }],
    "users_by_role": [{ "role": "Admin", "count": 1 }],
    "logbook_trend": 100
  }
}
```

---

### GET `/dashboard/manager`

Manager dashboard.

**Access**
- User must have subordinates

**Response**
```json
{
  "data": {
    "subordinates": [
      {
        "id": "uuid",
        "nama": "...",
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

### GET `/dashboard/manager/locations`

Get today's subordinate locations.

**Access**
- Privileged OR manager with subordinates

**Response**
```json
{
  "data": [
    {
      "lat": -6.21,
      "lng": 106.84,
      "title": "Staff Name",
      "status": "SUBMITTED"
    }
  ]
}
```

---

### GET `/dashboard/staff`

Staff personal dashboard.

**Access**
- Must be staff without subordinates

**Response**
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

### GET `/users/{user}/kpi-achievements`

Monthly KPI achievement snapshot for a specific user.

**Access**
- Privileged OR requester's id equals target user's `manager_id`

**Response**
```json
{
  "data": {
    "user_id": "uuid",
    "user_nama": "...",
    "total_kpi_details": 20,
    "completed_kpi_details": 14,
    "completion_rate": 70
  }
}
```

---

### GET `/reports/export`

Initiate export report.

**Access**
- Staff without subordinates: forbidden

**Response 200**
```json
{
  "message": "Export initiated",
  "download_url": "http://localhost:8000/api/v1/exports/report-1710000000.pdf"
}
```

---

## 11. Response Shape Conventions

### A. Resource Pagination Shape

Used by endpoints returning `Resource::collection($query->paginate())`.

```json
{
  "data": [ ...resource items... ],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 45
  }
}
```

### B. Raw Paginator Shape

Used by endpoints returning `response()->json($paginator)`.

```json
{
  "current_page": 1,
  "data": [ ... ],
  "first_page_url": "...",
  "from": 1,
  "last_page": 3,
  "last_page_url": "...",
  "links": [ ... ],
  "path": "...",
  "per_page": 15,
  "to": 15,
  "total": 45
}
```

### C. Embedded Resource in Custom JSON

Some endpoints embed `new Resource(...)` under a custom key:

```json
{ "user": { ...resource fields... } }
```

or

```json
{ "message": "...", "data": { ...resource fields... } }
```

---

## 12. Observers & Derived Summary Behavior

Summary synchronization is **observer-driven**, not explicitly called from controllers.

- `LogbookObserver`
  - On `created/updated/deleted/restored/forceDeleted`: calls `DailySummaryService::syncForLogbook($logbook)`
  - On `tanggal` change: also rebuilds old date staff and KPI summaries

- `LogbookKpiDetailObserver`
  - On `created/updated/deleted/restored`: calls `DailySummaryService::syncForDetail($detail)`

Implication for clients:
- Summary endpoints can change immediately after logbook or KPI-detail writes.

---

## 13. Error Responses

### 401 Unauthenticated
```json
{ "message": "Unauthenticated." }
```

### 403 Forbidden
Controller-specific variants include:
```json
{ "message": "Forbidden" }
```
or abort-driven messages such as:
- `Unauthorized.`
- `Unauthorized action.`
- `You can only review your direct subordinates' logbooks`

### 404 Not Found
Default Laravel model binding or explicit aborts, e.g.:
```json
{ "message": "No query results for model [...]" }
```
or
```json
{ "message": "Logbook owner not found" }
```

### 409 Conflict
Used in review race condition:
```json
{ "message": "Logbook sudah direview atau tidak tersedia." }
```

### 422 Validation / Business Rule Errors
Validation format (Laravel):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["..."]
  }
}
```

Business-rule 422 examples used by controllers:
```json
{ "message": "Jam mulai minimal 07:00" }
```
```json
{ "message": "Jam selesai harus lebih besar dari jam mulai" }
```
```json
{ "message": "Anda belum memiliki KPI yang ditugaskan. Hubungi manager Anda." }
```
```json
{ "message": "Minimal satu KPI harus memiliki capaian lebih dari 0 sebelum submit" }
```

---

## 14. Live Verification Matrix (Executed Against Running API)

This matrix was generated from a full live flow run (real HTTP calls against `http://127.0.0.1:8000/api/v1`) after `migrate:fresh --seed`.

- Total live calls: **74**
- Passed expected assertions: **74**
- Failed expected assertions: **0**
- Covered: auth, users, KPI master, KPI assignments, logbook lifecycle, summaries, notifications, audit logs, analytics/dashboard, reports.

| Method | Path | Success status (observed) | Edge status (observed) | Roles tested |
|---|---|---|---|---|
| GET | `/audit-logs` | 200 | 403 | admin, superadmin |
| PUT | `/auth/change-password` | 200 | 400 | sub |
| POST | `/auth/login` | 200 | - | - |
| POST | `/auth/logout` | 200 | - | sub |
| GET | `/auth/me` | 200 | 401 | superadmin |
| PUT | `/auth/profile` | 200 | 422 | sub |
| GET | `/dashboard/admin` | 200 | 403 | sub, superadmin |
| GET | `/dashboard/manager` | 200 | 403 | lead, sub |
| GET | `/dashboard/manager/locations` | 200 | - | lead |
| GET | `/dashboard/staff` | 200 | 403 | lead, sub |
| GET | `/kpi/assignments` | 200 | - | admin |
| POST | `/kpi/assignments` | 201 | 403 | lead, sub |
| DELETE | `/kpi/assignments/{assignment}` | 204 | - | lead |
| GET | `/kpi/master` | 200 | - | admin |
| POST | `/kpi/master` | 201 | - | admin |
| DELETE | `/kpi/master/{kpi}` | 200 | - | admin |
| GET | `/kpi/master/{kpi}` | 200 | - | admin |
| PUT | `/kpi/master/{kpi}` | 200 | - | admin |
| GET | `/kpi/me` | 200 | - | sub |
| GET | `/logbooks` | 200 | - | sub |
| POST | `/logbooks` | 201 | - | sub |
| POST | `/logbooks/start` | 201 | - | sub |
| DELETE | `/logbooks/{logbook}` | 200 | 400 | sub |
| GET | `/logbooks/{logbook}` | 200 | - | sub |
| PATCH | `/logbooks/{logbook}` | 200 | 400, 422 | sub |
| GET | `/logbooks/{logbook}/duration` | 200 | - | sub |
| DELETE | `/logbooks/{logbook}/kpi/{detail}/attachment` | 200 | - | sub |
| POST | `/logbooks/{logbook}/kpi/{detail}/attachment` | 200 | 400 | sub |
| PATCH | `/logbooks/{logbook}/kpi/{detail}/progress` | 200 | 422 | sub |
| PUT | `/logbooks/{logbook}/review` | 200 | 400 | lead |
| POST | `/logbooks/{logbook}/submit` | 200 | - | sub |
| GET | `/notifications` | 200 | - | sub |
| PUT | `/notifications/read-all` | 200 | - | sub |
| PUT | `/notifications/{notification}/read` | 200 | 403 | lead, sub |
| GET | `/reports/export` | 200 | 403 | admin, sub |
| GET | `/summaries/daily` | 200 | - | admin |
| GET | `/summaries/daily/{user_id}` | 200 | - | lead |
| GET | `/summaries/kpi/daily` | 200 | - | admin |
| GET | `/summaries/kpi/period` | 200 | - | admin |
| GET | `/summaries/period` | 200 | - | admin |
| GET | `/summaries/staff-performance` | 200 | 403 | admin, sub |
| GET | `/summaries/team/daily` | 200 | - | lead |
| GET | `/users` | 200 | - | lead, superadmin |
| POST | `/users` | 201 | 403 | admin |
| DELETE | `/users/{user}` | 200 | - | admin |
| GET | `/users/{user}` | 200 | - | lead |
| PUT | `/users/{user}` | 200 | - | admin |
| GET | `/users/{user}/kpi-achievements` | 200 | 403 | lead, sub |
| PUT | `/users/{user}/reset-password` | 200 | - | admin |
| GET | `/users/{user}/subordinates` | 200 | 403 | lead |

---

## 15. Real Request/Response Samples (Captured Live)

### A. Login success (SuperAdmin)

**Request**
```json
{
  "npp": "198001012000011001",
  "password": "password123"
}
```

**Observed response (200)**
```json
{
  "message": "Login successful",
  "access_token": "21|...",
  "token_type": "Bearer",
  "user": {
    "id": "019d22f4-4c1c-73aa-8e08-8845d971a472",
    "npp": "198001012000011001",
    "nama": "Super Admin Sistem",
    "email": "superadmin@logbook.com",
    "role": "SuperAdmin"
  }
}
```

### B. Create user success (Admin)

**Request**
```json
{
  "nama": "Temp Staff API",
  "email": "temp.staff.api.1774408388770@example.com",
  "npp": "1774408388770",
  "role": "Staff",
  "password": "password123",
  "manager_id": "019d22f4-4c58-72b1-95b9-2f2aef9c4ad9"
}
```

**Observed response (201)**
```json
{
  "data": {
    "id": "019d22fb-5163-73aa-adad-a30cbfe25487",
    "npp": "1774408388770",
    "nama": "Temp Staff API",
    "email": "temp.staff.api.1774408388770@example.com",
    "role": "Staff",
    "manager_id": "019d22f4-4c58-72b1-95b9-2f2aef9c4ad9"
  }
}
```

### C. Logbook validation edge (`start_kerja` too early)

**Request**
```json
{
  "start_kerja": "06:59"
}
```

**Observed response (422)**
```json
{ "message": "Jam mulai minimal 07:00" }
```

### D. Review flow success (Lead reviews subordinate logbook)

**Request**
```json
{
  "decision": "ACCEPTED",
  "rating": 4,
  "reviewer_comment": "Reviewed via live API run"
}
```

**Observed response (200)**
```json
{
  "message": "Logbook review berhasil disimpan",
  "data": {
    "id": "019d22fb-5354-730e-aea6-c2a1086e73bf",
    "status": "ACCEPTED",
    "rating": 4,
    "reviewed_by": "019d22f4-4c58-72b1-95b9-2f2aef9c4ad9"
  }
}
```

### E. Notification ownership edge (forbidden)

`PUT /notifications/{notification}/read` by non-owner observed:

```json
{
  "message": ""
}
```

with HTTP status **403**.

### F. Audit logs forbidden for non-superadmin

`GET /audit-logs` by `Admin` observed:

```json
{ "message": "Forbidden" }
```

with HTTP status **403**.

---

*Last synchronized with backend source: 2026-03-25*
