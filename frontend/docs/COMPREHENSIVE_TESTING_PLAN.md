# STATUS: COMPLETED

**Note: All tests and SDK plans below have been fully executed successfully in Phase 1. 46 tests across 5 suites are passing with zero TypeScript/Svelte-check errors.**

# Comprehensive API SDK & Testing Plan

Dokumen ini merangkum rencana menyeluruh (End-to-End) yang **telah diimplementasikan dan diuji** untuk API SDK di Frontend Svelte 5. Semua kemungkinan, edge cases, dan fitur berdasarkan Endpoint di Laravel Backend telah berhasil dilewati.

## 1. Unit Testing: Valibot Schemas (DTO Validation)

Fokus: Menguji aturan validasi lokal tanpa menyentuh network menggunakan Valibot v1.x (`v.InferOutput`).
**Skenario yang berhasil diuji:**

- **Auth & Profile:**
  - `LoginRequest`: NIP kosong, NIP format salah, Password kosong.
  - `ChangePasswordRequest`: Validasi min-length password, konfirmasi password tidak cocok.
- **Users & KPI (Admin):**
  - `CreateUser`: Role tidak valid (selain ADMIN, MANAGER, STAFF), format email salah, missing fields.
  - `CreateMasterKpi`: Nama KPI kosong, poin / target bersifat negatif.
- **Logbook (Staff & Manager):**
  - `StartLogbook`: Format GPS location salah (tidak mengandung latitude/longitude valid).
  - `SubmitLogbook`: Format array `gambar_bukti` salah (bukan array of strings/files).
  - `RateLogbook`: Rating < 1 atau Rating > 5 (Out of bounds).

## 2. Integration Testing: SDK Methods (Mocked Fetch)

Fokus: Memastikan SDK `fetch` (`ApiClient`) mem-parsing URL, Headers, dan Body Payload dengan benar, serta menangani status HTTP.
**Skenario yang berhasil diuji:**

- **API Client Headers:** Memastikan Bearer token selalu disuntikkan jika ada di `localStorage`.
- **Query Parameters:** Filter URL dibangun dengan benar (contoh: `/users?role=STAFF&page=2`).
- **FormData Uploads:** Memastikan `SubmitLogbook` dengan array of `File` dikonversi dengan benar menjadi `FormData` bukan `JSON.stringify`.
- **Error Handling (Catching HTTP Errors):**
  - `400 Bad Request`: Memastikan error response dari Laravel validation errors ditangkap dan dilempar (throw) dengan benar.
  - `401 Unauthorized`: Memastikan state token di-clear jika API merespon 401.
  - `403 Forbidden`: Memastikan error ditangkap untuk akses di luar role.
  - `404 Not Found`: Menangani endpoint yang tidak ditemukan.
  - `500 Internal Server Error`: Handling graceful failure.

## 3. Flow / E2E Testing (Real Backend via `localhost:8000`)

Fokus: Menyimulasikan urutan interaksi riil User Role terhadap State Machine Backend.

### A. Auth & Profile Flows

1.  Gagal login dengan kredensial salah (Memastikan error text kembali).
2.  Berhasil login dan mendapatkan struktur `user` dan `token`.
3.  Ubah Password berhasil.
4.  Logout membuang sesi pada backend.

### B. Admin Flows (CRUD & Dashboard)

1.  **Users:** GET daftar User (dengan paginasi), POST User baru, PUT (Update) User, Delete/Deactivate User.
2.  **Master KPI:** Create KPI baru, Edit KPI lama, GET semua Master KPI.
3.  **Audit & System:** Menarik `audit-logs` dengan parameter tanggal/waktu.
4.  **Dashboard:** Mengambil data statistik Dashboard Admin (`/dashboard/admin`).

### C. Manager Flows (KPI Assignment & Logbook Review)

1.  **KPI Assignment:** GET daftar Staff bawahannya, POST Assign KPI ke Staff spesifik, DELETE Assignment.
2.  **Review Logbook:**
    - GET logbooks yang `status === 'SUBMITTED'`.
    - **Rate:** PUT `/rate` logbook dengan nilai 4.
    - **Revert:** POST `/revert` logbook agar kembali ke status `DRAFT`.
3.  **Dashboard:** Memuat Dashboard Manager.
4.  **Notifications:** Membaca notifikasi (Mark as read).

### D. Staff Flows (Daily Operations)

1.  **Pagi (Start):** POST `startLogbook` dengan titik GPS. Cek perubahan status menjadi `DRAFT`.
2.  **Siang (Update):** PATCH `toggleKpi` mengubah `is_finished` menjadi true. Coba toggle KPI pada logbook yang bukan miliknya (harus gagal 403).
3.  **Sore (Submit):** POST `submitLogbook` dengan mengirim titik akhir GPS dan melampirkan file foto. Cek perubahan status menjadi `SUBMITTED`.
4.  **Error Edge-case:** Mencoba mensubmit logbook yang sudah disubmit (Harus ditolak API).
5.  **Dashboard & Export:** Mengambil `staffDashboard` dan `reports/export` dengan header `Accept: application/pdf`.

## 4. State Management (Svelte 5 Runes) Integration

Fokus: Menghubungkan SDK response ke reactive stores (`auth.svelte.ts`, `logbook.svelte.ts`).

- Memastikan data memicu _re-render_ melalui Svelte `$state`.
- Memastikan `isLoading` flag aktif selama async requests, lalu berubah false setelah fetch selesai/gagal.
- Memastikan `error` states terisi jika terjadi kegagalan fetching.
- Optimistic UI updates berjalan mulus (contoh: update spesifik array detail KPI tanpa refresh seluruh objek state logbook).
