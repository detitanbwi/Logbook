# Backend API Reference (V2 Canonical Contract)

Dokumen ini merangkum kontrak API backend saat ini berdasarkan implementasi aktif dan pengujian.

## Base & Auth

- Base path: `/api/v1`
- Auth: `Authorization: Bearer <token>` (Sanctum) untuk semua endpoint selain login.

---

## 1) Authentication

### POST `/auth/login`

Login menggunakan **npp** (bukan `nip`).

Request:
```json
{
  "npp": "199003032010012003",
  "password": "password123"
}
```

Response 200:
```json
{
  "message": "Login successful",
  "access_token": "...",
  "token_type": "Bearer",
  "user": {
    "id": "uuid",
    "npp": "199003032010012003",
    "nama": "Staff Siti",
    "email": "staff@example.com",
    "role": "Staff",
    "manager_id": "uuid|null",
    "has_subordinates": false
  }
}
```

Edge cases:
- `nip` payload tidak didukung lagi → 422.
- kredensial salah → 401.

### POST `/auth/logout`
### GET `/auth/me`
### PUT `/auth/change-password`
### PUT `/auth/profile`

Update profil pengguna saat login.

Accepted fields:
- `foto` (image: jpg/jpeg/png, max 5MB)
- `alamat`
- `tempat_lahir`
- `tanggal_lahir`
- `nik`
- `npwp`
- `status_kawin`
- `riwayat_pendidikan` (array)
- `riwayat_karir` (array)

Catatan:
- Field protected seperti `npp` dan `role` tidak diubah lewat endpoint ini.

---

## 2) Users

`Route::apiResource('users', ...)` + `PUT /users/{user}/reset-password`

Additional endpoint:
- `GET /users/{user}/subordinates`

Canonical fields:
- `nama` (required create)
- `npp` (required create)
- legacy `name/nip` sudah dihapus dari kontrak API.

Role semantics:
- `SuperAdmin`, `Admin`, `Staff`
- manager capability berbasis relasi bawahan (`manager_id`), bukan enum role khusus.

---

## 3) KPI Master & Assignment

### KPI Master
- `GET /kpi/master`
- `POST /kpi/master`
- `GET /kpi/master/{id}`
- `PUT /kpi/master/{id}`
- `DELETE /kpi/master/{id}`

Field KPI master:
- `nama`
- `target_angka`
- `satuan`
- `deskripsi`
- `status_aktif`

### KPI Assignment
- `GET /kpi/assignments`
- `POST /kpi/assignments`
- `DELETE /kpi/assignments/{assignment}`
- `GET /kpi/me`

Behavior:
- assignment hanya untuk subordinate (kecuali Admin/SuperAdmin).
- notifikasi `KPI_ASSIGNMENT` dibuat untuk staff.

---

## 4) Logbooks (Manual Time + Numeric KPI)

### Create/start
- `POST /logbooks/start`
- `POST /logbooks` (alias canonical ke start handler)

Request:
```json
{
  "tanggal": "2026-03-20",
  "start_kerja": "08:00",
  "end_kerja": "12:00",
  "lokasi": "-6.2,106.8"
}
```

Rules:
- `start_kerja >= 07:00`
- jika `end_kerja` ada, harus `> start_kerja`
- multiple logbooks per day allowed.

### Read
- `GET /logbooks`
- `GET /logbooks/{logbook}`
- `PATCH /logbooks/{logbook}` (update DRAFT milik sendiri)
- `DELETE /logbooks/{logbook}` (hapus DRAFT milik sendiri)

Filter umum:
- `status`
- `date_from`, `date_to`
- `search` (`nama`/`npp`)

### KPI Progress
- `PATCH /logbooks/{logbook}/kpi/{detail}/progress`

Request:
```json
{
  "capaian_angka": 7.5
}
```

### KPI Attachment
- `POST /logbooks/{logbook}/kpi/{detail}/attachment`
- `DELETE /logbooks/{logbook}/kpi/{detail}/attachment`

Rules:
- hanya owner logbook
- hanya saat status `DRAFT`

### Submit
- `POST /logbooks/{logbook}/submit`

Rules:
- hanya owner
- hanya dari `DRAFT`
- minimal satu KPI harus memiliki `capaian_angka > 0`
- jika user memiliki `manager_id`, sistem membuat notifikasi `LOGBOOK_SUBMITTED` ke manager

### Duration breakdown
- `GET /logbooks/{logbook}/duration`

Response:
```json
{
  "logbook_id": "uuid",
  "tanggal": "2026-03-20",
  "start_kerja": "08:00:00",
  "end_kerja": "17:00:00",
  "gross_work_minutes": 540,
  "break_overlap_minutes": 60,
  "net_work_minutes": 480
}
```

### Review (Manager/Admin)
- `PUT /logbooks/{logbook}/review`

Request:
```json
{
  "decision": "ACCEPTED",
  "rating": 4,
  "reviewer_comment": "Bagus dan lengkap"
}
```

Rules:
- hanya untuk status `SUBMITTED`
- `reviewer_comment` required
- `decision` in `ACCEPTED|REJECTED`
- notifikasi dikirim ke pemilik logbook (`LOGBOOK_ACCEPTED/LOGBOOK_REJECTED`).

Removed endpoints:
- `PUT /logbooks/{logbook}/rate`
- `PATCH /logbooks/{logbook}/kpi/{detail}/toggle`

Current canonical endpoint:
- `POST /logbooks/{logbook}/revert`

---

## 5) Summaries (Pre-aggregated)

Endpoint group:
- `GET /summaries/daily`
- `GET /summaries/daily/{user_id}`
- `GET /summaries/period`
- `GET /summaries/kpi/daily`
- `GET /summaries/kpi/period`
- `GET /summaries/team/daily`
- `GET /summaries/staff-performance`

Data sources:
- `daily_staff_summaries`
- `daily_kpi_summaries`

### Auto-sync Behavior

Summaries are automatically recalculated via Eloquent Observers:

1. **LogbookObserver**: Triggers on logbook create/update/delete/restore
   - Recalculates summaries for the logbook's date
   - When `tanggal` changes, also recalculates the OLD date's summary
   - If no logbooks remain on a date, the summary record is deleted

2. **LogbookKpiDetailObserver**: Triggers on KPI detail create/update/delete
   - Recalculates both staff and KPI summaries for the parent logbook's date

Important notes:
- `team/daily` untuk admin saat ini mengembalikan data kosong bila tanpa subordinate relation langsung.
- period endpoints meng-handle `target_angka_total=0` dengan `progress_percent=0`.

Field contract (staff summary):
- `total_logbooks`
- `submitted_logbooks`
- `accepted_logbooks`
- `rejected_logbooks`
- `total_work_minutes`
- `total_kpi`
- `target_angka_total`
- `capaian_angka_total`
- `progress_percent`

---

## 6) Notifications

- `GET /notifications`
- `PUT /notifications/{notification}/read`
- `PUT /notifications/read-all`

Additional metadata:
- `preview_message`
- `target_path`
- `target_params`

Filter:
- `is_read=true|false`
- `unread_only=true`

---

## 7) Audit & Analytics

Audit:
- `GET /audit-logs`

Analytics:
- `GET /dashboard/admin`
- `GET /dashboard/manager`
- `GET /dashboard/manager/locations`
- `GET /dashboard/staff`
- `GET /users/{user}/kpi-achievements`
- `GET /reports/export`

---

## Role & Policy Notes

- Canonical role set: `SuperAdmin | Admin | Staff`
- Capability manager ditentukan via relasi bawahan (`has_subordinates`), bukan role enum terpisah.
- Policy users endpoint:
  - `Admin` tidak boleh membuat/menetapkan role `Admin` atau `SuperAdmin`.
  - `Admin` hanya boleh membuat/menetapkan role `Staff`.

---

## Edge Cases Tested (Current)

### Authentication & User Management
- login with `nip` rejected (422)
- Admin can only create Staff accounts (not Admin/SuperAdmin)

### Logbook Time Validation
- start before 07:00 rejected (422)
- start at exactly 07:00 accepted
- end time <= start time rejected (422)
- multiple logbooks same day allowed

### KPI Progress & Attachments
- KPI progress numeric update in draft only
- KPI progress update blocked in SUBMITTED status
- KPI progress update blocked in ACCEPTED status
- negative capaian_angka rejected (422)
- attachment upload/delete only in draft
- attachment operations blocked in ACCEPTED status
- attachment operations blocked in REJECTED status

### Logbook Workflow
- cannot submit already submitted logbook
- cannot submit already accepted logbook
- submit requires at least one KPI with capaian_angka > 0

### Manager Review
- manager review accept/reject with required comment
- reviewer_comment is required
- decision must be ACCEPTED or REJECTED (REVIEWED is invalid)
- rating must be between 1 and 5
- cannot review DRAFT logbook (must be SUBMITTED)
- cannot review already accepted logbook
- cannot review already rejected logbook

### Summary System
- summary access control baseline
- summary period fallback when total target = 0
- summary recalculation when logbook date changes (both old and new dates)
- DailyStaffSummary auto-deleted when no logbooks remain on a date
- DailyKpiSummary auto-deleted when no KPI details remain on a date

### Removed Endpoints
- removed legacy endpoints return not found (routing level)

---

## Change Policy

Dokumen ini adalah kontrak canonical. Jika endpoint/field berubah, update:
1. `backend/docs/api_reference.md`
2. `backend/docs/context.md`
3. test feature terkait endpoint tersebut.
