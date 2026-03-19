# Seeded Data for UI Development

To make front-end and PWA development easier, the backend provides a robust seeder (`DatabaseSeeder.php`) that automatically provisions standard test accounts, randomized data, and historical logbooks.

## Getting Started

To reset your database and generate fresh seeded data, run:
```bash
php artisan migrate:fresh --seed
```

---

## 1. Test Accounts

The following static accounts are guaranteed to be created every time you run the seeder. Use these credentials to test the various Role-Based Access Control (RBAC) dashboards and flows.

### Admin
- **Role:** `ADMIN`
- **Name:** Admin System
- **NIP:** `198001012000011001`
- **Password:** `password`
- **Description:** Has full access to manage User Data, Master KPIs, and system-wide Audit Logs. Cannot create their own logbooks.

### Manager
- **Role:** `MANAGER`
- **Name:** Manager Budi
- **NIP:** `198502022005011002`
- **Password:** `password`
- **Description:** Can assign KPIs to their subordinates, review logbooks, and see team performance. Budi acts as the direct manager for "Staff Siti".

### Staff
- **Role:** `STAFF`
- **Name:** Staff Siti
- **NIP:** `199003032010012003`
- **Password:** `password`
- **Description:** Standard operational user. Can check-in, check-out, upload proof images, and view personal KPI metrics. Reports directly to "Manager Budi".

---

## 2. Auto-Generated Volume Data

To help with testing pagination, dashboards, and analytic graphs, the seeder also generates:
- **2 Extra Managers** (Random NIPs, Password: `password`)
- **9 Extra Staff** (Random NIPs, Password: `password`) distributed among the 3 managers.
- **20 Master KPIs** representing standard operational tasks.

---

## 3. Historical Logbook Generation

The seeder automatically simulates **30 days of historical data** for every staff member (including "Staff Siti"). This ensures that as soon as you log in, your Dashboard Analytics will not be empty!

### What's generated in the past 30 days?
- **Randomized Work Days:** Skips some weekends randomly to simulate real work patterns.
- **Randomized Check-in times:** Mostly between 08:00 and 09:59.
- **Randomized Statuses:** 
  - ~60% of past logbooks are completely `REVIEWED` (with random ratings from 1-5).
  - ~20% of past logbooks are left as `SUBMITTED` (Pending Manager Budi's review).
- **Task Toggles:** Random completion rates (`is_finished` true/false) for assigned KPIs on those logbooks.
- **Today's Draft:** Automatically generates a `DRAFT` logbook for "today", simulating that the staff has just checked in at 08:30 AM but hasn't submitted yet.

## Notes for UI Developers
- All endpoints accept authentication via Bearer Token (Laravel Sanctum).
- When writing tests on the UI, default to `password` for the password field.
- If you need to view the full backend API definitions, generate them using `php artisan scribe:generate` and navigate to the `/docs` endpoint.

---

## 4. Contoh Bentuk Data (API Response Examples)

Berikut adalah contoh bentuk data (JSON) yang dihasilkan oleh *seeder* dan *factory*, yang akan diterima oleh Frontend saat memanggil API. Contoh ini sangat berguna untuk pembuatan TypeScript Interfaces, API SDK, atau Mocking di sisi klien.

### A. Contoh Data User (Pegawai / Manager)
Data ini didapatkan ketika memanggil endpoint `GET /api/v1/auth/me` atau `GET /api/v1/users/{id}`.

```json
{
  "id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "nip": "199003032010012003",
  "name": "Staff Siti",
  "email": "staff@logbook.com",
  "role": "STAFF",
  "manager_id": "9b1deb4d-1111-4bad-9bdd-2b0d7b3dcb6d",
  "last_password_change": "2023-10-01T08:00:00.000000Z",
  "created_at": "2023-10-01T08:00:00.000000Z",
  "updated_at": "2023-10-01T08:00:00.000000Z"
}
```

### B. Contoh Data Master KPI
Data yang diambil dari `GET /api/v1/kpi/master`.

```json
{
  "id": "9b1deb4d-2222-4bad-9bdd-2b0d7b3dcb6d",
  "nama": "Menyusun Laporan Keuangan Bulanan",
  "status_aktif": true,
  "created_at": "2023-10-01T08:00:00.000000Z",
  "updated_at": "2023-10-01T08:00:00.000000Z"
}
```

### C. Contoh Data Logbook (Status: REVIEWED)
Data logbook yang sudah disetujui dan dinilai oleh Manager (`GET /api/v1/logbooks/{id}`). Perhatikan bahwa `gambar_bukti` berupa array string (URL), dan terdapat relasi `kpi_details`.

```json
{
  "id": "9b1deb4d-3333-4bad-9bdd-2b0d7b3dcb6d",
  "user_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "start_kerja": "2023-10-15 08:15:00",
  "end_kerja": "2023-10-15 17:05:00",
  "lokasi_start": "-6.200000, 106.816666",
  "lokasi_end": "-6.200000, 106.816666",
  "gambar_bukti": [
    "http://localhost:8000/storage/logbooks/bukti1.jpg",
    "http://localhost:8000/storage/logbooks/bukti2.jpg"
  ],
  "status": "REVIEWED",
  "rating": 4,
  "reviewed_by": "9b1deb4d-1111-4bad-9bdd-2b0d7b3dcb6d",
  "reviewed_at": "2023-10-16T09:00:00.000000Z",
  "created_at": "2023-10-15T01:15:00.000000Z",
  "kpi_details": [
    {
      "id": "9b1deb4d-4444-4bad-9bdd-2b0d7b3dcb6d",
      "logbook_id": "9b1deb4d-3333-4bad-9bdd-2b0d7b3dcb6d",
      "kpi_id": "9b1deb4d-2222-4bad-9bdd-2b0d7b3dcb6d",
      "kpi_nama": "Menyusun Laporan Keuangan Bulanan",
      "is_finished": true,
      "finished_at": "2023-10-15 15:30:00"
    },
    {
      "id": "9b1deb4d-5555-4bad-9bdd-2b0d7b3dcb6d",
      "logbook_id": "9b1deb4d-3333-4bad-9bdd-2b0d7b3dcb6d",
      "kpi_id": "9b1deb4d-6666-4bad-9bdd-2b0d7b3dcb6d",
      "kpi_nama": "Rapat Koordinasi Tim",
      "is_finished": false,
      "finished_at": null
    }
  ]
}
```

### D. Contoh Data Audit Log (Khusus Admin)
Menampilkan riwayat perubahan data pada sistem (`GET /api/v1/audit-logs`).

```json
{
  "id": "9b1deb4d-7777-4bad-9bdd-2b0d7b3dcb6d",
  "table_name": "logbooks",
  "record_id": "9b1deb4d-3333-4bad-9bdd-2b0d7b3dcb6d",
  "action": "UPDATE",
  "old_data": {
    "status": "SUBMITTED",
    "rating": null
  },
  "new_data": {
    "status": "REVIEWED",
    "rating": 4
  },
  "performed_by": "9b1deb4d-1111-4bad-9bdd-2b0d7b3dcb6d",
  "performed_at": "2023-10-16T09:00:00.000000Z",
  "ip_address": "192.168.1.10",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)..."
}
```
