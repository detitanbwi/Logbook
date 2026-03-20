# Web Admin Panel - Pages & Views Design

## Overview

Web Admin Panel digunakan oleh **SuperAdmin** dan **Admin** untuk mengelola sistem melalui browser desktop dengan **sidebar navigation**.

**Technology Stack:**
- Frontend: SvelteKit + Svelte 5 (Runes)
- Styling: TailwindCSS
- State: Svelte Stores
- API: Fetch + Valibot validation

---

## Role Capabilities Matrix

| Feature | SuperAdmin | Admin |
|---------|------------|-------|
| Create/Manage Admins | ✅ | ❌ |
| Create/Manage Staff | ✅ | ✅ |
| Assign Manager (Subordinates) | ✅ | ✅ |
| Manage Master KPI | ✅ | ✅ |
| Assign KPI to Staff | ✅ | ✅ |
| View All Logbooks | ✅ | ✅ |
| View Staff Performance | ✅ | ✅ |
| View Audit Logs | ✅ | ❌ |
| System Reports & Export | ✅ | ✅ |

---

## 1. Route Structure

```
frontend/src/routes/
├── (auth)/
│   └── login/
│       └── +page.svelte          # Login page (public)
│
├── (app)/                         # Protected routes
│   ├── +layout.svelte            # Main layout with sidebar
│   │
│   ├── superadmin/               # SuperAdmin ONLY
│   │   ├── dashboard/
│   │   │   └── +page.svelte      # SuperAdmin dashboard
│   │   ├── admins/
│   │   │   └── +page.svelte      # Manage Admin accounts (SuperAdmin only!)
│   │   └── audit-logs/
│   │       └── +page.svelte      # View all audit logs
│   │
│   ├── admin/                    # Admin & SuperAdmin (shared)
│   │   ├── dashboard/
│   │   │   └── +page.svelte      # Admin dashboard
│   │   ├── users/
│   │   │   └── +page.svelte      # Staff management (Admin can only create Staff)
│   │   ├── kpis/
│   │   │   └── +page.svelte      # Master KPI management
│   │   ├── kpi-assignments/
│   │   │   └── +page.svelte      # Assign KPIs to Staff
│   │   ├── logbooks/
│   │   │   └── +page.svelte      # View all logbooks
│   │   ├── staff-performance/
│   │   │   └── +page.svelte      # Staff performance with drill-down
│   │   └── reports/
│   │       └── +page.svelte      # Reports & export
│   │
│   ├── profile/
│   │   └── +page.svelte          # User profile (all roles)
│   │
│   └── notifications/
│       └── +page.svelte          # Notification center
```

---

## 2. Authentication Pages

### 2.1 Login Page

**Route:** `/login`  
**Access:** Public  
**API:** `POST /api/v1/auth/login`

```
┌─────────────────────────────────────────────────────────────┐
│                         LOGIN                                │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│                    [LOGO INSTANSI]                          │
│                                                             │
│              Aplikasi Logbook & KPI Management              │
│                                                             │
│     ┌─────────────────────────────────────────────┐        │
│     │ NPP / Username                              │        │
│     └─────────────────────────────────────────────┘        │
│                                                             │
│     ┌─────────────────────────────────────────────┐        │
│     │ Password                              [👁]  │        │
│     └─────────────────────────────────────────────┘        │
│                                                             │
│     ┌─────────────────────────────────────────────┐        │
│     │              MASUK                          │        │
│     └─────────────────────────────────────────────┘        │
│                                                             │
│     [Error message area - tampil jika login gagal]         │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Component Logic:**
```typescript
// Form state
let npp = $state('');
let password = $state('');
let loading = $state(false);
let error = $state('');

async function handleLogin() {
  loading = true;
  error = '';
  
  const response = await authService.login(npp, password);
  
  if (response.success) {
    // Redirect based on role
    if (response.user.role === 'SuperAdmin') {
      goto('/superadmin/dashboard');
    } else if (response.user.role === 'Admin') {
      goto('/admin/dashboard');
    }
  } else {
    error = response.message;
  }
  
  loading = false;
}
```

---

## 3. SuperAdmin Pages

### 3.1 SuperAdmin Dashboard

**Route:** `/superadmin/dashboard`  
**Access:** SuperAdmin only  
**API:** `GET /api/v1/dashboard/admin`

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 SuperAdmin ▼] │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Dashboard SuperAdmin                                      │
│                │                                                            │
│  📊 Dashboard  │  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐       │
│  👥 Admins     │  │ Total Users  │ │ Total Admins │ │ Total Staff  │       │
│  👤 Users      │  │     156      │ │      5       │ │     150      │       │
│  📋 Master KPI │  └──────────────┘ └──────────────┘ └──────────────┘       │
│  📈 Monitoring │                                                            │
│  📝 Audit Logs │  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐       │
│  📊 Reports    │  │ Logbooks     │ │ Pending      │ │ Active KPIs  │       │
│                │  │ This Month   │ │ Reviews      │ │              │       │
│                │  │     432      │ │      23      │ │     18       │       │
│                │  └──────────────┘ └──────────────┘ └──────────────┘       │
│                │                                                            │
│                │  ┌────────────────────────────────────────────────────┐   │
│                │  │ Recent Audit Logs                                  │   │
│                │  ├────────────────────────────────────────────────────┤   │
│                │  │ Admin created user "John Doe" - 5 min ago          │   │
│                │  │ Staff submitted logbook - 10 min ago               │   │
│                │  │ Admin updated KPI "Laporan Bulanan" - 1 hour ago   │   │
│                │  └────────────────────────────────────────────────────┘   │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘
```

### 3.2 Admin Management

**Route:** `/superadmin/admins`  
**Access:** SuperAdmin only  
**API:** `GET/POST/PUT/DELETE /api/v1/users` (filtered by role=Admin)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 SuperAdmin ▼] │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Manajemen Admin                     [+ Tambah Admin]      │
│                │                                                            │
│  📊 Dashboard  │  ┌─────────────────────────────────────────────────────┐  │
│  👥 Admins ◀   │  │ 🔍 Cari admin...                                    │  │
│  👤 Users      │  └─────────────────────────────────────────────────────┘  │
│  📋 Master KPI │                                                            │
│  📈 Monitoring │  ┌─────────────────────────────────────────────────────┐  │
│  📝 Audit Logs │  │ NPP          │ Nama         │ Email       │ Aksi    │  │
│  📊 Reports    │  ├─────────────────────────────────────────────────────┤  │
│                │  │ 198001...001 │ Admin Satu   │ admin1@...  │ ✏️ 🔑 🗑 │  │
│                │  │ 198001...002 │ Admin Dua    │ admin2@...  │ ✏️ 🔑 🗑 │  │
│                │  │ 198001...003 │ Admin Tiga   │ admin3@...  │ ✏️ 🔑 🗑 │  │
│                │  └─────────────────────────────────────────────────────┘  │
│                │                                                            │
│                │  [< Prev] Page 1 of 1 [Next >]                            │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘

Actions:
✏️ = Edit Admin
🔑 = Reset Password
🗑 = Delete Admin (soft delete)
```

### 3.3 Audit Logs

**Route:** `/superadmin/audit-logs`  
**Access:** SuperAdmin only  
**API:** `GET /api/v1/audit-logs`

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 SuperAdmin ▼] │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Audit Logs                                                │
│                │                                                            │
│  📊 Dashboard  │  ┌──────────┬───────────────┬─────────────────────────┐   │
│  👥 Admins     │  │ Filter:  │ [All Tables ▼]│ [All Actions ▼]        │   │
│  👤 Users      │  └──────────┴───────────────┴─────────────────────────┘   │
│  📋 Master KPI │                                                            │
│  📈 Monitoring │  ┌───────────────────────────────────────────────────────┐ │
│  📝 Audit ◀    │  │ Waktu    │ User    │ Table   │ Action │ Details      │ │
│  📊 Reports    │  ├───────────────────────────────────────────────────────┤ │
│                │  │ 10:30:15 │ Admin1  │ users   │ CREATE │ [View ▶]     │ │
│                │  │ 10:25:00 │ Admin1  │ kpi_m.. │ UPDATE │ [View ▶]     │ │
│                │  │ 10:20:45 │ Admin2  │ users   │ UPDATE │ [View ▶]     │ │
│                │  │ 09:15:30 │ System  │ logbo.. │ DELETE │ [View ▶]     │ │
│                │  └───────────────────────────────────────────────────────┘ │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘

[View ▶] opens modal with old_data and new_data comparison
```

---

## 4. Admin Pages

### 4.1 Admin Dashboard

**Route:** `/admin/dashboard`  
**Access:** Admin, SuperAdmin  
**API:** `GET /api/v1/dashboard/admin`

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 Admin ▼]      │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Dashboard Admin                                           │
│                │                                                            │
│  📊 Dashboard  │  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐       │
│  👤 Users      │  │ Total Staff  │ │ Logbooks     │ │ Active KPIs  │       │
│  📋 Master KPI │  │              │ │ Bulan Ini    │ │              │       │
│  📈 Monitoring │  │     150      │ │     432      │ │     18       │       │
│  📝 Logbooks   │  └──────────────┘ └──────────────┘ └──────────────┘       │
│  📊 Reports    │                                                            │
│                │  ┌────────────────────────────────────────────────────┐   │
│                │  │ Logbook Trend (Last 7 Days)                        │   │
│                │  │ [Bar Chart Visualization]                          │   │
│                │  └────────────────────────────────────────────────────┘   │
│                │                                                            │
│                │  ┌───────────────────┐ ┌───────────────────┐              │
│                │  │ By Status         │ │ Quick Actions     │              │
│                │  │ ● DRAFT: 45       │ │ [+ Add User]      │              │
│                │  │ ● SUBMITTED: 23   │ │ [+ Add KPI]       │              │
│                │  │ ● ACCEPTED: 350   │ │ [📊 Export]       │              │
│                │  │ ● REJECTED: 14    │ │                   │              │
│                │  └───────────────────┘ └───────────────────┘              │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘
```

### 4.2 User Management (Staff Only for Admin)

**Route:** `/admin/users`  
**Access:** Admin, SuperAdmin  
**API:** `GET/POST/PUT/DELETE /api/v1/users`

**Important:** Admin can ONLY create Staff accounts. SuperAdmin can create both Admin and Staff.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 Admin ▼]      │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Manajemen Staff                       [+ Tambah Staff]    │
│                │                                                            │
│  📊 Dashboard  │  ┌──────────────────────────────────────────────────────┐ │
│  👤 Staff ◀    │  │ 🔍 Cari...  │ [Status: All ▼] │ [Manager: All ▼]    │ │
│  📋 Master KPI │  └──────────────────────────────────────────────────────┘ │
│  📎 Assign KPI │                                                            │
│  📝 Logbooks   │  ┌───────────────────────────────────────────────────────┐│
│  👥 Staff Perf │  │ NPP      │ Nama     │ Manager   │ KPIs │ Aksi        ││
│  📊 Reports    │  ├───────────────────────────────────────────────────────┤│
│                │  │ 1990...03│ John Doe │ Jane M.   │  4   │ ✏️ 👁 🔑 🗑  ││
│                │  │ 1985...02│ Jane M.  │ -         │  3   │ ✏️ 👁 🔑 🗑  ││
│                │  │ 1988...04│ Bob S.   │ Jane M.   │  5   │ ✏️ 👁 🔑 🗑  ││
│                │  └───────────────────────────────────────────────────────┘│
│                │                                                            │
│                │  [< Prev] Page 1 of 10 [Next >]                           │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘

Actions:
✏️ = Edit User (termasuk biodata, assign manager)
👁 = View Detail + Biodata
🔑 = Reset Password
🗑 = Delete User (soft delete)
```

**Add/Edit Staff Modal:**

```
┌──────────────────────────────────────────────────────────────┐
│ Tambah/Edit Staff                                        [X] │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Tab: [Informasi Dasar] [Biodata] [Riwayat]                 │
│                                                              │
│  === Informasi Dasar ===                                     │
│                                                              │
│  NPP*                      Nama Lengkap*                     │
│  ┌──────────────────┐      ┌──────────────────┐             │
│  │ 199003032010...  │      │ John Doe         │             │
│  └──────────────────┘      └──────────────────┘             │
│                                                              │
│  Email*                    Role (readonly for Admin)         │
│  ┌──────────────────┐      ┌──────────────────┐             │
│  │ john@example.com │      │ Staff          ▼ │ (disabled)  │
│  └──────────────────┘      └──────────────────┘             │
│                                                              │
│  Atasan (Manager)*         Password (jika baru)              │
│  ┌──────────────────┐      ┌──────────────────┐             │
│  │ Jane Manager   ▼ │      │ ••••••••         │             │
│  └──────────────────┘      └──────────────────┘             │
│                                                              │
│  Note: Atasan dropdown shows other Staff who can be manager  │
│                                                              │
│  === Biodata (Tab 2) ===                                     │
│  ... (same as before)                                        │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│                            [Batal]  [Simpan]                 │
└──────────────────────────────────────────────────────────────┘
```

### 4.3 KPI Assignment Page

**Route:** `/admin/kpi-assignments`  
**Access:** Admin, SuperAdmin  
**API:** `GET/POST/DELETE /api/v1/kpi/assignments`

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 Admin ▼]      │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Penugasan KPI ke Staff                                    │
│                │                                                            │
│  📊 Dashboard  │  ┌──────────────────────────────────────────────────────┐ │
│  👤 Staff      │  │ Staff: [Pilih Staff ▼]  │ [+ Assign KPI]             │ │
│  📋 Master KPI │  └──────────────────────────────────────────────────────┘ │
│  📎 Assign ◀   │                                                            │
│  📝 Logbooks   │  Selected: John Doe (NPP: 199003032010012003)             │
│  👥 Staff Perf │                                                            │
│  📊 Reports    │  ┌───────────────────────────────────────────────────────┐│
│                │  │ KPI Ditugaskan                                        ││
│                │  ├───────────────────────────────────────────────────────┤│
│                │  │ ✅ Buat Laporan Bulanan    │ 3 dokumen │ [Hapus]     ││
│                │  │ ✅ Kunjungan Lapangan      │ 5 lokasi  │ [Hapus]     ││
│                │  │ ✅ Input Data Harian       │ 100 record│ [Hapus]     ││
│                │  │ ✅ Verifikasi Berkas       │ 20 berkas │ [Hapus]     ││
│                │  └───────────────────────────────────────────────────────┘│
│                │                                                            │
│                │  ┌───────────────────────────────────────────────────────┐│
│                │  │ KPI Tersedia (belum ditugaskan)                       ││
│                │  ├───────────────────────────────────────────────────────┤│
│                │  │ ☐ Presentasi Mingguan    │ 2 presentasi │ [Assign]   ││
│                │  │ ☐ Koordinasi Tim         │ 4 meeting    │ [Assign]   ││
│                │  └───────────────────────────────────────────────────────┘│
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘
```

### 4.4 Master KPI Management

**Route:** `/admin/kpis`  
**Access:** Admin, SuperAdmin  
**API:** `GET/POST/PUT/DELETE /api/v1/kpi/master`

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 Admin ▼]      │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Master Data KPI                       [+ Tambah KPI]      │
│                │                                                            │
│  📊 Dashboard  │  ┌──────────────────────────────────────────────────────┐ │
│  👤 Users      │  │ 🔍 Cari KPI...          │ [Status: All ▼]           │ │
│  📋 KPI ◀      │  └──────────────────────────────────────────────────────┘ │
│  📈 Monitoring │                                                            │
│  📝 Logbooks   │  ┌───────────────────────────────────────────────────────┐│
│  📊 Reports    │  │ Nama KPI        │ Target │ Satuan  │ Status │ Aksi   ││
│                │  ├───────────────────────────────────────────────────────┤│
│                │  │ Buat Laporan    │   3    │ dokumen │ ✅ Aktif│ ✏️ 🗑  ││
│                │  │ Kunjungan       │   5    │ lokasi  │ ✅ Aktif│ ✏️ 🗑  ││
│                │  │ Input Data      │  100   │ record  │ ❌ Non  │ ✏️ 🗑  ││
│                │  │ Verifikasi      │   20   │ berkas  │ ✅ Aktif│ ✏️ 🗑  ││
│                │  └───────────────────────────────────────────────────────┘│
│                │                                                            │
│                │  [< Prev] Page 1 of 2 [Next >]                            │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘
```

**Add/Edit KPI Modal:**

```
┌──────────────────────────────────────────────────────────────┐
│ Tambah/Edit Master KPI                                  [X] │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Nama KPI*                                                   │
│  ┌──────────────────────────────────────────────┐           │
│  │ Buat Laporan Bulanan                         │           │
│  └──────────────────────────────────────────────┘           │
│                                                              │
│  Target Angka*             Satuan*                           │
│  ┌──────────────────┐      ┌──────────────────┐             │
│  │ 3                │      │ dokumen          │             │
│  └──────────────────┘      └──────────────────┘             │
│                                                              │
│  Deskripsi                                                   │
│  ┌──────────────────────────────────────────────┐           │
│  │ Membuat laporan bulanan departemen yang      │           │
│  │ mencakup pencapaian target dan evaluasi.     │           │
│  └──────────────────────────────────────────────┘           │
│                                                              │
│  Status                                                      │
│  ┌──────────────────┐                                        │
│  │ [✓] Aktif        │                                        │
│  └──────────────────┘                                        │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│                            [Batal]  [Simpan]                 │
└──────────────────────────────────────────────────────────────┘
```

### 4.5 Logbook Monitoring

**Route:** `/admin/logbooks`  
**Access:** Admin, SuperAdmin  
**API:** `GET /api/v1/logbooks`

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 Admin ▼]      │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Monitoring Logbook                                        │
│                │                                                            │
│  📊 Dashboard  │  ┌──────────────────────────────────────────────────────┐ │
│  👤 Users      │  │ 🔍 Cari staff... │[Status▼]│[Dari]│[Sampai]│[Filter]│ │
│  📋 Master KPI │  └──────────────────────────────────────────────────────┘ │
│  📈 Monitoring │                                                            │
│  📝 Logbooks ◀ │  ┌───────────────────────────────────────────────────────┐│
│  📊 Reports    │  │ Tanggal   │ Staff    │ Durasi  │ Status   │ Rating   ││
│                │  ├───────────────────────────────────────────────────────┤│
│                │  │ 20 Mar 26 │ John Doe │ 7h 30m  │ ACCEPTED │ ⭐⭐⭐⭐  ││
│                │  │ 20 Mar 26 │ Jane M.  │ 8h 00m  │ SUBMITTED│ -        ││
│                │  │ 19 Mar 26 │ Bob S.   │ 6h 45m  │ REJECTED │ ⭐⭐     ││
│                │  │ 19 Mar 26 │ John Doe │ 7h 15m  │ ACCEPTED │ ⭐⭐⭐⭐⭐││
│                │  └───────────────────────────────────────────────────────┘│
│                │                                                            │
│                │  Click row to view detail →                               │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘
```

**Logbook Detail Modal:**

```
┌──────────────────────────────────────────────────────────────┐
│ Detail Logbook - John Doe                               [X] │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Tanggal: 20 Maret 2026                                      │
│  Status: ACCEPTED                                            │
│  Rating: ⭐⭐⭐⭐ (4/5)                                        │
│                                                              │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Check-in  : 08:00:15                                    ││
│  │ Check-out : 17:00:30                                    ││
│  │ Lokasi    : -6.2088, 106.8456 (Jakarta)                 ││
│  │ Durasi    : 8h 00m (Break: 1h)                          ││
│  │ Efektif   : 7h 00m                                      ││
│  └─────────────────────────────────────────────────────────┘│
│                                                              │
│  === Progress KPI ===                                        │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ ✅ Buat Laporan: 3/3 dokumen                            ││
│  │    📎 laporan_maret.pdf, bukti_1.jpg                    ││
│  │                                                         ││
│  │ ✅ Kunjungan: 5/5 lokasi                                ││
│  │    📎 foto_kunjungan1.jpg, foto_kunjungan2.jpg          ││
│  │                                                         ││
│  │ ⏳ Input Data: 80/100 record                            ││
│  │    📎 screenshot_data.png                               ││
│  └─────────────────────────────────────────────────────────┘│
│                                                              │
│  === Review ===                                              │
│  Reviewer: Jane Manager                                      │
│  Komentar: "Bagus, lanjutkan kinerja seperti ini."          │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│                                          [Tutup]            │
└──────────────────────────────────────────────────────────────┘
```

### 4.6 KPI Monitoring (Legacy - to be replaced by Staff Performance)

**Note:** This section is being replaced by the new Staff Performance page (Section 7) which provides better aggregated views using pre-calculated summary tables.

**Route:** `/admin/monitoring` (deprecated, redirect to `/admin/staff-performance`)  
**Access:** Admin, SuperAdmin  
**API:** `GET /api/v1/users/{id}/kpi-achievements`

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 Admin ▼]      │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Monitoring Pencapaian KPI                                 │
│                │                                                            │
│  📊 Dashboard  │  ┌──────────────────────────────────────────────────────┐ │
│  👤 Users      │  │ Periode: [Maret 2026 ▼]  │ Departemen: [Semua ▼]    │ │
│  📋 Master KPI │  └──────────────────────────────────────────────────────┘ │
│  📈 Monitor ◀  │                                                            │
│  📝 Logbooks   │  ┌───────────────────────────────────────────────────────┐│
│  📊 Reports    │  │ Staff       │ KPI              │ Target │ Capaian    ││
│                │  ├───────────────────────────────────────────────────────┤│
│                │  │ John Doe    │ Buat Laporan     │ 3      │ 3 (100%)   ││
│                │  │             │ Kunjungan        │ 5      │ 5 (100%)   ││
│                │  │             │ Input Data       │ 100    │ 80 (80%)   ││
│                │  ├───────────────────────────────────────────────────────┤│
│                │  │ Jane M.     │ Buat Laporan     │ 3      │ 2 (67%)    ││
│                │  │             │ Verifikasi       │ 20     │ 18 (90%)   ││
│                │  └───────────────────────────────────────────────────────┘│
│                │                                                            │
│                │  [📊 Export to Excel]  [📄 Export to PDF]                 │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘
```

### 4.7 Reports & Export

**Route:** `/admin/reports`  
**Access:** Admin, SuperAdmin  
**API:** `GET /api/v1/reports/export`

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 Admin ▼]      │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Laporan & Ekspor                                          │
│                │                                                            │
│  📊 Dashboard  │  ┌─────────────────────────────────────────────────────┐  │
│  👤 Users      │  │ Jenis Laporan                                       │  │
│  📋 Master KPI │  │ ┌─────────────────────────────────────────────────┐ │  │
│  📈 Monitoring │  │ │ ○ Rekap Logbook Bulanan                         │ │  │
│  📝 Logbooks   │  │ │ ○ Pencapaian KPI per Staff                      │ │  │
│  📊 Reports ◀  │  │ │ ○ Summary Kehadiran                             │ │  │
│                │  │ │ ○ Rating & Review History                       │ │  │
│                │  │ └─────────────────────────────────────────────────┘ │  │
│                │  └─────────────────────────────────────────────────────┘  │
│                │                                                            │
│                │  ┌─────────────────────────────────────────────────────┐  │
│                │  │ Filter                                              │  │
│                │  │ Periode: [Maret 2026 ▼]  Staff: [Semua ▼]          │  │
│                │  │ Format:  ○ Excel  ○ PDF  ○ CSV                      │  │
│                │  └─────────────────────────────────────────────────────┘  │
│                │                                                            │
│                │  [📥 Generate & Download Report]                          │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘
```

---

## 5. Common Components

### 5.1 Sidebar Navigation

```svelte
<!-- Sidebar.svelte -->
<script lang="ts">
  import { page } from '$app/stores';
  import { authStore } from '$lib/stores/auth.svelte';
  
  const user = $derived(authStore.user);
  const isSuperAdmin = $derived(user?.role === 'SuperAdmin');
</script>

<aside class="w-64 bg-gray-800 text-white">
  <nav>
    {#if isSuperAdmin}
      <a href="/superadmin/dashboard">Dashboard</a>
      <a href="/superadmin/admins">Kelola Admin</a>
    {/if}
    
    <a href="/admin/dashboard">Dashboard</a>
    <a href="/admin/users">Pengguna</a>
    <a href="/admin/kpis">Master KPI</a>
    <a href="/admin/monitoring">Monitoring KPI</a>
    <a href="/admin/logbooks">Logbook</a>
    <a href="/admin/reports">Laporan</a>
    
    {#if isSuperAdmin}
      <a href="/superadmin/audit-logs">Audit Logs</a>
    {/if}
  </nav>
</aside>
```

### 5.2 Data Table Component

```svelte
<!-- DataTable.svelte -->
<script lang="ts">
  interface Props {
    columns: Array<{key: string, label: string, sortable?: boolean}>;
    data: Array<Record<string, any>>;
    loading?: boolean;
    onRowClick?: (row: Record<string, any>) => void;
  }
  
  let { columns, data, loading = false, onRowClick }: Props = $props();
</script>

<table class="w-full">
  <thead>
    <tr>
      {#each columns as col}
        <th>{col.label}</th>
      {/each}
    </tr>
  </thead>
  <tbody>
    {#if loading}
      <tr><td colspan={columns.length}>Loading...</td></tr>
    {:else}
      {#each data as row}
        <tr onclick={() => onRowClick?.(row)}>
          {#each columns as col}
            <td>{row[col.key]}</td>
          {/each}
        </tr>
      {/each}
    {/if}
  </tbody>
</table>
```

### 5.3 Pagination Component

```svelte
<!-- Pagination.svelte -->
<script lang="ts">
  interface Props {
    currentPage: number;
    totalPages: number;
    onPageChange: (page: number) => void;
  }
  
  let { currentPage, totalPages, onPageChange }: Props = $props();
</script>

<div class="flex justify-center gap-2">
  <button 
    disabled={currentPage === 1}
    onclick={() => onPageChange(currentPage - 1)}
  >
    Previous
  </button>
  
  <span>Page {currentPage} of {totalPages}</span>
  
  <button 
    disabled={currentPage === totalPages}
    onclick={() => onPageChange(currentPage + 1)}
  >
    Next
  </button>
</div>
```

---

## 6. API Integration

### 6.1 Service Pattern

```typescript
// src/lib/api/services/users.service.ts
import { apiClient } from '../client';
import type { User, PaginatedResponse } from '$lib/types';

export const usersService = {
  async list(params?: {
    page?: number;
    per_page?: number;
    search?: string;
    role?: string;
  }): Promise<PaginatedResponse<User>> {
    return apiClient.get('/users', { params });
  },
  
  async create(data: CreateUserDto): Promise<User> {
    return apiClient.post('/users', data);
  },
  
  async update(id: string, data: UpdateUserDto): Promise<User> {
    return apiClient.put(`/users/${id}`, data);
  },
  
  async delete(id: string): Promise<void> {
    return apiClient.delete(`/users/${id}`);
  },
  
  async resetPassword(id: string, password: string): Promise<void> {
    return apiClient.put(`/users/${id}/reset-password`, { password });
  }
};
```

---

## 7. Staff Performance Page (Admin/SuperAdmin)

### 7.1 Overview

Admin dan SuperAdmin dapat melihat performa seluruh staff melalui halaman Staff Performance. Halaman ini menampilkan data agregat dari `daily_staff_summaries` dan `daily_kpi_summaries` tables.

**Route:** `/admin/staff-performance`  
**Access:** Admin, SuperAdmin  
**API:** `GET /api/v1/summaries/daily`, `GET /api/v1/summaries/period`

### 7.2 Staff Performance List Page

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [≡] Logbook Management                              [🔔] [👤 Admin ▼]      │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Staff Performance                                         │
│                │                                                            │
│  📊 Dashboard  │  ┌──────────────────────────────────────────────────────┐ │
│  👤 Users      │  │ View: [Daily ▼] │ Tanggal: [20 Mar 2026] │ [Filter] │ │
│  📋 Master KPI │  │       [Period]  │ Dari - Sampai          │           │ │
│  📈 Monitoring │  └──────────────────────────────────────────────────────┘ │
│  📝 Logbooks   │                                                            │
│  👥 Staff      │  ┌───────────────────────────────────────────────────────┐│
│     Performance│  │ Staff       │ Logbooks │ Duration │ KPI %  │ Rating  ││
│     ◀          │  ├───────────────────────────────────────────────────────┤│
│  📊 Reports    │  │ 🔗John Doe  │    2     │ 8h 30m   │ 95%    │ ⭐ 4.2  ││
│                │  │ 🔗Jane M.   │    1     │ 7h 45m   │ 88%    │ ⭐ 4.5  ││
│                │  │ 🔗Bob S.    │    3     │ 9h 15m   │ 100%   │ ⭐ 4.8  ││
│                │  │ 🔗Alice W.  │    1     │ 6h 00m   │ 72%    │ ⭐ 3.5  ││
│                │  │ 🔗Charlie X.│    2     │ 8h 00m   │ 85%    │ ⭐ 4.0  ││
│                │  └───────────────────────────────────────────────────────┘│
│                │                                                            │
│                │  Klik nama staff untuk melihat detail →                   │
│                │                                                            │
│                │  [< Prev] Page 1 of 5 [Next >]                            │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘

Legend:
🔗 = Clickable name, opens slide-out drawer with detail view
```

### 7.3 Slide-out Drawer - Staff Detail View

Ketika Admin mengklik nama staff, muncul slide-out drawer dari kanan dengan detail performa.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ Staff Performance (dimmed background)                                       │
├─────────────────────────────────────────────────────┬───────────────────────┤
│ (Previous page content - dimmed)                    │                       │
│                                                     │  ╔═══════════════════╗│
│                                                     │  ║ Staff Detail   [X]║│
│                                                     │  ╠═══════════════════╣│
│                                                     │  ║ 📷 John Doe       ║│
│                                                     │  ║ NPP: 199003...003 ║│
│                                                     │  ║ Manager: Jane M.  ║│
│                                                     │  ╠═══════════════════╣│
│                                                     │  ║ Summary (20 Mar)  ║│
│                                                     │  ║ ┌───────────────┐ ║│
│                                                     │  ║ │ Logbooks: 2   │ ║│
│                                                     │  ║ │ Duration: 8h30│ ║│
│                                                     │  ║ │ KPI: 95%      │ ║│
│                                                     │  ║ │ Rating: 4.2   │ ║│
│                                                     │  ║ └───────────────┘ ║│
│                                                     │  ╠═══════════════════╣│
│                                                     │  ║ KPI Breakdown     ║│
│                                                     │  ║ ┌───────────────┐ ║│
│                                                     │  ║ │ Laporan: 3/3  │ ║│
│                                                     │  ║ │   ████████ 100%│ ║│
│                                                     │  ║ │ Kunjungan: 4/5│ ║│
│                                                     │  ║ │   ██████░░ 80% │ ║│
│                                                     │  ║ │ Data: 95/100  │ ║│
│                                                     │  ║ │   █████████ 95%│ ║│
│                                                     │  ║ └───────────────┘ ║│
│                                                     │  ╠═══════════════════╣│
│                                                     │  ║ Logbooks Today    ║│
│                                                     │  ║ ┌───────────────┐ ║│
│                                                     │  ║ │ #1 08:00-12:00│ ║│
│                                                     │  ║ │ Status: ✅    │ ║│
│                                                     │  ║ │ [View Detail] │ ║│
│                                                     │  ║ ├───────────────┤ ║│
│                                                     │  ║ │ #2 13:00-17:30│ ║│
│                                                     │  ║ │ Status: ✅    │ ║│
│                                                     │  ║ │ [View Detail] │ ║│
│                                                     │  ║ └───────────────┘ ║│
│                                                     │  ╚═══════════════════╝│
└─────────────────────────────────────────────────────┴───────────────────────┘
```

### 7.4 Logbook Detail Modal (from Drawer)

Clicking "View Detail" on a logbook opens a modal with full logbook detail including attachments (reusing the existing Logbook Detail Modal from section 4.4).

### 7.5 Period View (Cross-Day Performance)

When user selects "Period" view type:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ Staff Performance - Period View                                             │
├────────────────┬────────────────────────────────────────────────────────────┤
│                │                                                            │
│  MENU          │  Staff Performance                                         │
│                │                                                            │
│  📊 Dashboard  │  ┌──────────────────────────────────────────────────────┐ │
│  👤 Users      │  │ View: [Period ▼] │ Dari: [01 Mar] Sampai: [20 Mar]  │ │
│  📋 Master KPI │  └──────────────────────────────────────────────────────┘ │
│  📈 Monitoring │                                                            │
│  📝 Logbooks   │  ┌───────────────────────────────────────────────────────┐│
│  👥 Staff      │  │ Staff       │ Days │ Logbooks │ Avg KPI │ Avg Rating ││
│     Perf. ◀    │  ├───────────────────────────────────────────────────────┤│
│  📊 Reports    │  │ 🔗John Doe  │  15  │    28    │  92%    │ ⭐ 4.3     ││
│                │  │ 🔗Jane M.   │  14  │    25    │  88%    │ ⭐ 4.5     ││
│                │  │ 🔗Bob S.    │  16  │    32    │  95%    │ ⭐ 4.7     ││
│                │  └───────────────────────────────────────────────────────┘│
│                │                                                            │
│                │  [📊 Export to Excel]  [📄 Export to PDF]                 │
│                │                                                            │
└────────────────┴────────────────────────────────────────────────────────────┘
```

---

## 8. Slide-out Drawer Component

### 8.1 Component Design

```svelte
<!-- SlideOutDrawer.svelte -->
<script lang="ts">
  import { fly, fade } from 'svelte/transition';
  
  interface Props {
    isOpen: boolean;
    title: string;
    onClose: () => void;
    width?: 'sm' | 'md' | 'lg' | 'xl';
  }
  
  let { isOpen, title, onClose, width = 'md' }: Props = $props();
  
  const widthClasses = {
    sm: 'w-80',
    md: 'w-96',
    lg: 'w-[32rem]',
    xl: 'w-[40rem]'
  };
  
  function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') onClose();
  }
</script>

<svelte:window onkeydown={handleKeydown} />

{#if isOpen}
  <!-- Backdrop -->
  <div 
    class="fixed inset-0 bg-black/50 z-40"
    transition:fade={{ duration: 200 }}
    onclick={onClose}
    role="presentation"
  ></div>
  
  <!-- Drawer -->
  <aside 
    class="fixed right-0 top-0 h-full bg-white shadow-xl z-50 {widthClasses[width]} flex flex-col"
    transition:fly={{ x: 400, duration: 300 }}
  >
    <!-- Header -->
    <header class="flex items-center justify-between p-4 border-b">
      <h2 class="text-lg font-semibold">{title}</h2>
      <button 
        onclick={onClose}
        class="p-2 hover:bg-gray-100 rounded"
        aria-label="Close"
      >
        ✕
      </button>
    </header>
    
    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-4">
      {@render children?.()}
    </div>
  </aside>
{/if}
```

### 8.2 Staff Detail Drawer Component

```svelte
<!-- StaffDetailDrawer.svelte -->
<script lang="ts">
  import SlideOutDrawer from './SlideOutDrawer.svelte';
  import type { DailyStaffSummary, DailyKpiSummary } from '$lib/types';
  
  interface Props {
    isOpen: boolean;
    staffId: string | null;
    date: string;
    onClose: () => void;
    onViewLogbook: (logbookId: string) => void;
  }
  
  let { isOpen, staffId, date, onClose, onViewLogbook }: Props = $props();
  
  let summary = $state<DailyStaffSummary | null>(null);
  let kpiBreakdown = $state<DailyKpiSummary[]>([]);
  let loading = $state(false);
  
  $effect(() => {
    if (isOpen && staffId) {
      loadStaffDetail();
    }
  });
  
  async function loadStaffDetail() {
    loading = true;
    try {
      const [summaryRes, kpiRes] = await Promise.all([
        fetch(`/api/v1/summaries/daily/${staffId}?date=${date}`),
        fetch(`/api/v1/summaries/kpi/daily?user_id=${staffId}&date=${date}`)
      ]);
      summary = await summaryRes.json();
      kpiBreakdown = await kpiRes.json();
    } finally {
      loading = false;
    }
  }
</script>

<SlideOutDrawer {isOpen} title="Staff Detail" {onClose} width="lg">
  {#if loading}
    <div class="flex items-center justify-center h-32">
      <span class="loading loading-spinner"></span>
    </div>
  {:else if summary}
    <!-- Staff Info -->
    <div class="flex items-center gap-4 mb-6">
      <img 
        src={summary.user.foto || '/default-avatar.png'} 
        alt={summary.user.nama}
        class="w-16 h-16 rounded-full object-cover"
      />
      <div>
        <h3 class="font-semibold text-lg">{summary.user.nama}</h3>
        <p class="text-sm text-gray-500">NPP: {summary.user.npp}</p>
        {#if summary.user.manager}
          <p class="text-sm text-gray-500">Manager: {summary.user.manager.nama}</p>
        {/if}
      </div>
    </div>
    
    <!-- Summary Stats -->
    <div class="grid grid-cols-2 gap-4 mb-6">
      <div class="bg-blue-50 p-4 rounded-lg">
        <p class="text-sm text-blue-600">Logbooks</p>
        <p class="text-2xl font-bold text-blue-800">{summary.logbook_count}</p>
      </div>
      <div class="bg-green-50 p-4 rounded-lg">
        <p class="text-sm text-green-600">Duration</p>
        <p class="text-2xl font-bold text-green-800">{formatMinutes(summary.total_work_minutes)}</p>
      </div>
      <div class="bg-purple-50 p-4 rounded-lg">
        <p class="text-sm text-purple-600">KPI Achievement</p>
        <p class="text-2xl font-bold text-purple-800">{summary.overall_kpi_percentage}%</p>
      </div>
      <div class="bg-yellow-50 p-4 rounded-lg">
        <p class="text-sm text-yellow-600">Avg Rating</p>
        <p class="text-2xl font-bold text-yellow-800">⭐ {summary.avg_rating?.toFixed(1) || '-'}</p>
      </div>
    </div>
    
    <!-- KPI Breakdown -->
    <div class="mb-6">
      <h4 class="font-semibold mb-3">KPI Breakdown</h4>
      <div class="space-y-3">
        {#each kpiBreakdown as kpi}
          <div class="border rounded-lg p-3">
            <div class="flex justify-between items-center mb-1">
              <span class="font-medium">{kpi.kpi_nama}</span>
              <span class="text-sm">{kpi.total_achieved}/{kpi.total_target}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-blue-600 h-2 rounded-full"
                style="width: {kpi.percentage}%"
              ></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">{kpi.percentage}% achieved</p>
          </div>
        {/each}
      </div>
    </div>
    
    <!-- Logbooks List -->
    <div>
      <h4 class="font-semibold mb-3">Logbooks on {date}</h4>
      <div class="space-y-2">
        {#each summary.logbooks as logbook, index}
          <div class="border rounded-lg p-3 hover:bg-gray-50">
            <div class="flex justify-between items-center">
              <div>
                <p class="font-medium">#{index + 1} {formatTime(logbook.start_kerja)} - {formatTime(logbook.end_kerja)}</p>
                <p class="text-sm text-gray-500">Status: {logbook.status}</p>
              </div>
              <button 
                onclick={() => onViewLogbook(logbook.id)}
                class="text-blue-600 hover:text-blue-800 text-sm"
              >
                View Detail →
              </button>
            </div>
          </div>
        {/each}
      </div>
    </div>
  {/if}
</SlideOutDrawer>

<script context="module" lang="ts">
  function formatMinutes(minutes: number): string {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}h ${mins}m`;
  }
  
  function formatTime(timestamp: string): string {
    return new Date(timestamp).toLocaleTimeString('id-ID', { 
      hour: '2-digit', 
      minute: '2-digit' 
    });
  }
</script>
```

### 8.3 Usage in Staff Performance Page

```svelte
<!-- /admin/staff-performance/+page.svelte -->
<script lang="ts">
  import StaffDetailDrawer from '$lib/components/StaffDetailDrawer.svelte';
  import LogbookDetailModal from '$lib/components/LogbookDetailModal.svelte';
  
  let selectedStaffId = $state<string | null>(null);
  let selectedLogbookId = $state<string | null>(null);
  let selectedDate = $state(new Date().toISOString().split('T')[0]);
  
  function openStaffDrawer(staffId: string) {
    selectedStaffId = staffId;
  }
  
  function closeStaffDrawer() {
    selectedStaffId = null;
  }
  
  function openLogbookModal(logbookId: string) {
    selectedLogbookId = logbookId;
  }
</script>

<!-- Staff list table with clickable names -->
<table>
  <!-- ... -->
  <tbody>
    {#each staffList as staff}
      <tr>
        <td>
          <button 
            onclick={() => openStaffDrawer(staff.user_id)}
            class="text-blue-600 hover:underline"
          >
            {staff.user_nama}
          </button>
        </td>
        <!-- ... other columns -->
      </tr>
    {/each}
  </tbody>
</table>

<!-- Staff Detail Drawer -->
<StaffDetailDrawer 
  isOpen={selectedStaffId !== null}
  staffId={selectedStaffId}
  date={selectedDate}
  onClose={closeStaffDrawer}
  onViewLogbook={openLogbookModal}
/>

<!-- Logbook Detail Modal (reused from existing) -->
<LogbookDetailModal 
  isOpen={selectedLogbookId !== null}
  logbookId={selectedLogbookId}
  onClose={() => selectedLogbookId = null}
/>
```

---

## 9. Updated Route Structure

Updated route structure with new Staff Performance page:

```
frontend/src/routes/
├── (auth)/
│   └── login/
│       └── +page.svelte          # Login page (public)
│
├── (app)/                         # Protected routes
│   ├── +layout.svelte            # Main layout with sidebar
│   │
│   ├── superadmin/               # SuperAdmin only
│   │   ├── dashboard/
│   │   │   └── +page.svelte      # SuperAdmin dashboard
│   │   ├── admins/
│   │   │   └── +page.svelte      # Manage Admin accounts
│   │   └── audit-logs/
│   │       └── +page.svelte      # View all audit logs
│   │
│   ├── admin/                    # Admin & SuperAdmin
│   │   ├── dashboard/
│   │   │   └── +page.svelte      # Admin dashboard
│   │   ├── users/
│   │   │   └── +page.svelte      # User management
│   │   ├── kpis/
│   │   │   └── +page.svelte      # Master KPI management
│   │   ├── logbooks/
│   │   │   └── +page.svelte      # View all logbooks
│   │   ├── staff-performance/    # NEW: Staff Performance page
│   │   │   └── +page.svelte      # Daily/Period performance view
│   │   └── reports/
│   │       └── +page.svelte      # Reports & export
│   │
│   ├── profile/
│   │   └── +page.svelte          # User profile (all roles)
│   │
│   └── notifications/
│       └── +page.svelte          # Notification center
```

---

## 10. Updated Sidebar Navigation

```svelte
<!-- Sidebar.svelte (updated) -->
<script lang="ts">
  import { page } from '$app/stores';
  import { authStore } from '$lib/stores/auth.svelte';
  
  const user = $derived(authStore.user);
  const isSuperAdmin = $derived(user?.role === 'SuperAdmin');
</script>

<aside class="w-64 bg-gray-800 text-white">
  <nav>
    {#if isSuperAdmin}
      <a href="/superadmin/dashboard">📊 Dashboard</a>
      <a href="/superadmin/admins">👥 Kelola Admin</a>
    {/if}
    
    <a href="/admin/dashboard">📊 Dashboard</a>
    <a href="/admin/users">👤 Pengguna</a>
    <a href="/admin/kpis">📋 Master KPI</a>
    <a href="/admin/logbooks">📝 Logbook</a>
    
    <!-- NEW: Staff Performance menu item -->
    <a href="/admin/staff-performance">👥 Staff Performance</a>
    
    <a href="/admin/reports">📊 Laporan</a>
    
    {#if isSuperAdmin}
      <a href="/superadmin/audit-logs">📝 Audit Logs</a>
    {/if}
  </nav>
</aside>
```

---

**Next Document**: `03-MOBILE-API-DESIGN.md` - API endpoints untuk Staff (mobile app)
