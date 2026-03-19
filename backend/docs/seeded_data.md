# Seeded Data for UI Development (V2 Canonical Contract)

To make front-end and PWA development easier, the backend provides a robust seeder (`DatabaseSeeder.php`) that automatically provisions standard test accounts, randomized data, and historical logbooks.

## Getting Started

To reset your database and generate fresh seeded data, run:
```bash
php artisan migrate:fresh --seed
```

---

## 1. Test Accounts

The following static accounts are guaranteed to be created every time you run the seeder.

### Admin
- **Role:** `Admin`
- **Nama:** Admin System
- **NPP:** `198001012000011001`
- **Password:** `password`

### Manager
- **Role:** `Manager`
- **Nama:** Manager Budi
- **NPP:** `198502022005011002`
- **Password:** `password`

### Staff
- **Role:** `Staff`
- **Nama:** Staff Siti
- **NPP:** `199003032010012003`
- **Password:** `password`

---

## 2. Auto-Generated Volume Data

To help with pagination and dashboards, the seeder also generates:
- Extra managers and staff with manager relations.
- Master KPIs with numeric target model (`target_angka`, `satuan`).
- Daily logbooks and KPI detail progress.

---

## 3. Historical Logbook Generation

Seeder generates historical activity for staff users including:
- Manual time fields: `tanggal`, `start_kerja`, `end_kerja`, `lokasi`.
- Status domain: `DRAFT`, `SUBMITTED`, `ACCEPTED`, `REJECTED`.
- KPI detail numeric progress: `target_angka`, `capaian_angka`.
- Per-KPI attachment support via `lampiran_file` (optional).

---

## 4. Canonical API Contract Notes (Breaking Changes)

Canonical contract only (legacy aliases removed):
- Login payload must use `npp` (not `nip`).
- User payloads use `nama` and `npp` (not `name`/`nip`).
- Removed legacy logbook endpoints:
  - `PUT /api/v1/logbooks/{logbook}/rate`
  - `POST /api/v1/logbooks/{logbook}/revert`
  - `PATCH /api/v1/logbooks/{logbook}/kpi/{detail}/toggle`
- Canonical review and KPI progress endpoints:
  - `PUT /api/v1/logbooks/{logbook}/review`
  - `PATCH /api/v1/logbooks/{logbook}/kpi/{detail}/progress`

---

## 5. Example Response Shapes

### A. User (`GET /api/v1/auth/me`)
```json
{
  "id": "uuid",
  "npp": "199003032010012003",
  "nama": "Staff Siti",
  "email": "staff@logbook.com",
  "role": "Staff",
  "manager_id": "uuid|null",
  "last_password_change": "2026-03-20T08:00:00.000000Z",
  "created_at": "2026-03-20T08:00:00.000000Z",
  "updated_at": "2026-03-20T08:00:00.000000Z"
}
```

### B. Logbook (`GET /api/v1/logbooks/{id}`)
```json
{
  "id": "uuid",
  "user_id": "uuid",
  "tanggal": "2026-03-20",
  "start_kerja": "08:15:00",
  "end_kerja": "17:05:00",
  "lokasi": "-6.200000, 106.816666",
  "status": "ACCEPTED",
  "rating": 4,
  "reviewed_by": "uuid",
  "reviewed_at": "2026-03-20T09:00:00.000000Z",
  "reviewer_comment": "Bagus",
  "kpi_details": [
    {
      "id": "uuid",
      "kpi_id": "uuid",
      "kpi_nama": "Menyusun Laporan Keuangan Bulanan",
      "target_angka": 10,
      "satuan": "dokumen",
      "capaian_angka": 8,
      "lampiran_file": "logbook-kpi-attachments/bukti.pdf",
      "finished_at": "2026-03-20T15:30:00.000000Z"
    }
  ]
}
```

### C. Notification (`GET /api/v1/notifications`)
```json
{
  "id": "uuid",
  "title": "Logbook Accepted",
  "message": "Logbook Anda telah diterima dengan rating 4/5.",
  "type": "LOGBOOK_ACCEPTED",
  "reference_id": "uuid",
  "is_read": false,
  "preview_message": "Logbook Anda telah diterima dengan rating 4/5.",
  "target_path": "/staff/history",
  "target_params": {
    "logbook_id": "uuid"
  }
}
```
