# Staff Mobile SPA - API & UI Design

## Overview

Dokumen ini mendefinisikan **Staff Mobile SPA (Single Page Application)** yang diakses melalui browser mobile. Staff menggunakan **tabbed dashboard** untuk navigasi antar fitur.

**Platform:** Web-based Mobile SPA (responsive)  
**Technology Stack:**
- Frontend: SvelteKit + Svelte 5 (Runes) + TailwindCSS
- API: Laravel REST API + Sanctum
- State: Svelte Stores

**Base URL:** `/api/v1`  
**Authentication:** Bearer Token (Laravel Sanctum)  
**Content-Type:** `application/json`

---

## Staff Mobile SPA - Tab Structure

### Tab Navigation

```
┌─────────────────────────────────────────────────────────────┐
│                    Staff Mobile SPA                         │
│                                                             │
│  [Content Area - varies by tab]                             │
│                                                             │
│                                                             │
│                                                             │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  [🏠]      [📊]       [📝]       [👥]       [👤]           │
│ Overview   My Perf   Logbook   Team Perf*  Profile         │
└─────────────────────────────────────────────────────────────┘

* Team Performance tab only visible for Staff with subordinates (Managers)
```

### Tab Structure by Role

| Tab | Staff | Staff with Subordinates (Manager) |
|-----|-------|-----------------------------------|
| Overview | ✅ Dashboard utama | ✅ Dashboard + pending reviews |
| My Performance | ✅ Performa pribadi | ✅ Performa pribadi |
| Logbook | ✅ Input/kelola logbook | ✅ Input/kelola logbook |
| Team Performance | ❌ Hidden | ✅ Performa tim |
| Profile | ✅ Profil & settings | ✅ Profil & settings |

---

## Mobile SPA Screen Designs

### 1. Overview Tab (Dashboard)

```
┌─────────────────────────────────────────────────────────────┐
│ ☰  Logbook Management               🔔3  [👤]              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Selamat Pagi, John Doe! 👋                                │
│  Jumat, 20 Maret 2026                                       │
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Today's Summary                                         ││
│  │ ┌──────────┐ ┌──────────┐ ┌──────────┐                 ││
│  │ │Logbooks  │ │ Duration │ │ KPI %    │                 ││
│  │ │    2     │ │  6h 30m  │ │   85%    │                 ││
│  │ └──────────┘ └──────────┘ └──────────┘                 ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ [+ Buat Logbook Baru]                                   ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Logbook Hari Ini                                        ││
│  │ ├─────────────────────────────────────────────────────┐ ││
│  │ │ #1 08:00 - 12:00  │ ✅ ACCEPTED │ ⭐4.5 │ [Detail] │ ││
│  │ ├─────────────────────────────────────────────────────┤ ││
│  │ │ #2 13:00 - 17:30  │ 🟡 SUBMITTED│  -   │ [Detail] │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ KPI Progress Hari Ini                                   ││
│  │ ├── Laporan: 2/3 ████████░░ 67%                        ││
│  │ ├── Kunjungan: 5/5 ██████████ 100%                     ││
│  │ └── Data Entry: 80/100 ████████░░ 80%                  ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  [🏠]      [📊]       [📝]       [👥]       [👤]           │
│ Overview◀  My Perf   Logbook   Team Perf  Profile          │
└─────────────────────────────────────────────────────────────┘
```

### 2. My Performance Tab

```
┌─────────────────────────────────────────────────────────────┐
│ ☰  My Performance                    🔔3  [👤]              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ View: [Daily ▼]  │  Tanggal: [20 Mar 2026 ▼]           ││
│  │       [Period]   │  Dari - Sampai                       ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  === Daily View (20 Mar 2026) ===                          │
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Summary Stats                                           ││
│  │ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐   ││
│  │ │Logbooks  │ │ Duration │ │ KPI %    │ │ Rating   │   ││
│  │ │    2     │ │  8h 30m  │ │   95%    │ │ ⭐ 4.2   │   ││
│  │ └──────────┘ └──────────┘ └──────────┘ └──────────┘   ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ KPI Breakdown                                           ││
│  │ ┌─────────────────────────────────────────────────────┐││
│  │ │ 📋 Buat Laporan                                     │││
│  │ │ Target: 3 dokumen  │  Capaian: 3  │  100%           │││
│  │ │ ████████████████████ ✅                             │││
│  │ └─────────────────────────────────────────────────────┘││
│  │ ┌─────────────────────────────────────────────────────┐││
│  │ │ 🚗 Kunjungan Lapangan                               │││
│  │ │ Target: 5 lokasi   │  Capaian: 4  │  80%            │││
│  │ │ ████████████████░░░░ ⏳                             │││
│  │ └─────────────────────────────────────────────────────┘││
│  │ ┌─────────────────────────────────────────────────────┐││
│  │ │ 💾 Input Data                                       │││
│  │ │ Target: 100 record │  Capaian: 95 │  95%            │││
│  │ │ ███████████████████░ ⏳                             │││
│  │ └─────────────────────────────────────────────────────┘││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Logbooks on This Date                     [View All →] ││
│  │ ├── #1: 08:00-12:00 │ ACCEPTED │ ⭐4.5               │ ││
│  │ └── #2: 13:00-17:30 │ ACCEPTED │ ⭐4.0               │ ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  [🏠]      [📊]       [📝]       [👥]       [👤]           │
│ Overview   My Perf◀  Logbook   Team Perf  Profile          │
└─────────────────────────────────────────────────────────────┘
```

### 3. My Performance Tab - Period View

```
┌─────────────────────────────────────────────────────────────┐
│ ☰  My Performance                    🔔3  [👤]              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ View: [Period ▼]  │  Dari: [01 Mar] Sampai: [20 Mar]   ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  === Period View (01 - 20 Mar 2026) ===                    │
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Period Summary                                          ││
│  │ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐   ││
│  │ │ Days     │ │Logbooks  │ │Avg KPI % │ │Avg Rating│   ││
│  │ │   15     │ │    28    │ │   92%    │ │ ⭐ 4.3   │   ││
│  │ └──────────┘ └──────────┘ └──────────┘ └──────────┘   ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ KPI Summary (Accumulated)                               ││
│  │ ┌─────────────────────────────────────────────────────┐││
│  │ │ 📋 Buat Laporan        │  45/45  │  100% ✅         │││
│  │ │ 🚗 Kunjungan Lapangan  │  72/75  │   96% ⏳         │││
│  │ │ 💾 Input Data          │ 1850/2000│  93% ⏳         │││
│  │ └─────────────────────────────────────────────────────┘││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 📈 Daily Trend                                          ││
│  │  100%│    ▄▄  ▄▄  ▄▄                                   ││
│  │   75%│▄▄  ██  ██  ██  ▄▄  ▄▄                           ││
│  │   50%│██  ██  ██  ██  ██  ██  ▄▄  ▄▄                   ││
│  │   25%│██  ██  ██  ██  ██  ██  ██  ██                   ││
│  │      └────────────────────────────────                  ││
│  │       1   5  10  15  20                                 ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  [🏠]      [📊]       [📝]       [👥]       [👤]           │
│ Overview   My Perf◀  Logbook   Team Perf  Profile          │
└─────────────────────────────────────────────────────────────┘
```

### 4. Logbook Tab (Create/Manage)

```
┌─────────────────────────────────────────────────────────────┐
│ ☰  Logbook                           🔔3  [👤]              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ [+ Buat Logbook Baru]                                   ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Filter: [All Dates ▼]  [All Status ▼]                  ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 📅 20 Mar 2026                                          ││
│  │ ├─────────────────────────────────────────────────────┐ ││
│  │ │ #1  08:00 - 12:00                                   │ ││
│  │ │ Status: ✅ ACCEPTED  │  Rating: ⭐⭐⭐⭐⭐           │ ││
│  │ │ KPI: 3/3 complete    │  [View Detail →]             │ ││
│  │ ├─────────────────────────────────────────────────────┤ ││
│  │ │ #2  13:00 - 17:30                                   │ ││
│  │ │ Status: 🟡 SUBMITTED │  Waiting review...           │ ││
│  │ │ KPI: 2/3 complete    │  [View Detail →]             │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 📅 19 Mar 2026                                          ││
│  │ ├─────────────────────────────────────────────────────┐ ││
│  │ │ #1  08:15 - 17:00                                   │ ││
│  │ │ Status: ✅ ACCEPTED  │  Rating: ⭐⭐⭐⭐             │ ││
│  │ │ KPI: 4/4 complete    │  [View Detail →]             │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  [Load More...]                                             │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  [🏠]      [📊]       [📝]       [👥]       [👤]           │
│ Overview   My Perf   Logbook◀  Team Perf  Profile          │
└─────────────────────────────────────────────────────────────┘
```

### 5. Create New Logbook (Manual Time Input)

```
┌─────────────────────────────────────────────────────────────┐
│ ←  Buat Logbook Baru                                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Tanggal Kerja *                                         ││
│  │ ┌─────────────────────────────────────────────────────┐ ││
│  │ │ 📅  20 Maret 2026                            [📅]  │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Waktu Kerja *                                           ││
│  │ ┌───────────────────┐   ┌───────────────────┐          ││
│  │ │ Mulai: 08:00  [⏰]│   │ Selesai: 12:00 [⏰]│          ││
│  │ └───────────────────┘   └───────────────────┘          ││
│  │ Durasi: 4 jam 0 menit                                   ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Lokasi (opsional)                                       ││
│  │ ┌─────────────────────────────────────────────────────┐ ││
│  │ │ 📍 -6.2088, 106.8456                    [📍 GPS]   │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ KPI yang akan dikerjakan:                               ││
│  │ ┌─────────────────────────────────────────────────────┐ ││
│  │ │ ☑ Buat Laporan Bulanan (Target: 3 dokumen)         │ ││
│  │ │ ☑ Kunjungan Lapangan (Target: 5 lokasi)            │ ││
│  │ │ ☑ Input Data (Target: 100 record)                  │ ││
│  │ │ ☐ Verifikasi Berkas (Target: 20 berkas)            │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  │ Note: KPI akan auto-selected dari assignment Anda       ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │                  [Buat Logbook]                         ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 6. Logbook Detail / Edit (DRAFT status)

```
┌─────────────────────────────────────────────────────────────┐
│ ←  Logbook Detail                      [🗑 Hapus]           │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Status: 📝 DRAFT                                           │
│  Tanggal: 20 Mar 2026  │  Waktu: 08:00 - 12:00             │
│  Durasi: 4 jam 0 menit │  Lokasi: Jakarta                  │
│                                                             │
│  ═══════════════════════════════════════════════════════   │
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 📋 Buat Laporan Bulanan                                 ││
│  │ Target: 3 dokumen                                       ││
│  │ ┌─────────────────────────────────────────────────────┐ ││
│  │ │ Capaian: [  2  ] dokumen         ████████░░ 67%    │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  │ Lampiran:                                               ││
│  │ ┌─────────────────────────────────────────────────────┐ ││
│  │ │ 📎 laporan_1.pdf                            [🗑]    │ ││
│  │ │ 📎 bukti_laporan.jpg                        [🗑]    │ ││
│  │ │ [+ Tambah Lampiran]                                 │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 🚗 Kunjungan Lapangan                                   ││
│  │ Target: 5 lokasi                                        ││
│  │ ┌─────────────────────────────────────────────────────┐ ││
│  │ │ Capaian: [  5  ] lokasi          ██████████ 100%   │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  │ Lampiran:                                               ││
│  │ ┌─────────────────────────────────────────────────────┐ ││
│  │ │ 📎 foto_kunjungan_1.jpg                     [🗑]    │ ││
│  │ │ 📎 foto_kunjungan_2.jpg                     [🗑]    │ ││
│  │ │ [+ Tambah Lampiran]                                 │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │           [💾 Simpan Draft]  [📤 Submit]                ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 7. Team Performance Tab (Manager Only)

```
┌─────────────────────────────────────────────────────────────┐
│ ☰  Team Performance                  🔔3  [👤]              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Pending Reviews: 3 logbooks                [Review →]  ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ View: [Daily ▼]  │  Tanggal: [20 Mar 2026 ▼]           ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Team Members (5)                                        ││
│  │ ┌─────────────────────────────────────────────────────┐ ││
│  │ │ 👤 John Doe                                         │ ││
│  │ │ Logbooks: 2 │ Duration: 8h30m │ KPI: 95% │ ⭐4.2   │ ││
│  │ │                                       [Detail →]    │ ││
│  │ ├─────────────────────────────────────────────────────┤ ││
│  │ │ 👤 Alice Wang                                       │ ││
│  │ │ Logbooks: 1 │ Duration: 6h00m │ KPI: 72% │ ⭐3.5   │ ││
│  │ │                                       [Detail →]    │ ││
│  │ ├─────────────────────────────────────────────────────┤ ││
│  │ │ 👤 Bob Smith                                        │ ││
│  │ │ Logbooks: 3 │ Duration: 9h15m │ KPI: 100%│ ⭐4.8   │ ││
│  │ │                                       [Detail →]    │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Team Summary                                            ││
│  │ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐   ││
│  │ │ Total    │ │ Avg KPI  │ │ Avg      │ │ Pending  │   ││
│  │ │Logbooks  │ │    %     │ │ Rating   │ │ Reviews  │   ││
│  │ │    8     │ │   89%    │ │ ⭐ 4.2   │ │    3     │   ││
│  │ └──────────┘ └──────────┘ └──────────┘ └──────────┘   ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  [🏠]      [📊]       [📝]       [👥]       [👤]           │
│ Overview   My Perf   Logbook   Team Perf◀ Profile          │
└─────────────────────────────────────────────────────────────┘
```

### 8. Review Logbook Screen (Manager)

```
┌─────────────────────────────────────────────────────────────┐
│ ←  Review Logbook                                           │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 👤 John Doe                                             ││
│  │ NPP: 199003032010012003                                 ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  Tanggal: 20 Mar 2026  │  Waktu: 13:00 - 17:30             │
│  Durasi: 4 jam 30 menit │  Status: 🟡 SUBMITTED            │
│                                                             │
│  ═══════════════════════════════════════════════════════   │
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 📋 Buat Laporan Bulanan                                 ││
│  │ Capaian: 3/3 dokumen ██████████ 100% ✅                 ││
│  │ Lampiran: 📎 laporan.pdf, 📎 bukti.jpg                  ││
│  │           [📷 View] [📥 Download]                       ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 🚗 Kunjungan Lapangan                                   ││
│  │ Capaian: 4/5 lokasi ████████░░ 80%                      ││
│  │ Lampiran: 📎 foto1.jpg, 📎 foto2.jpg                    ││
│  │           [📷 View] [📥 Download]                       ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ═══════════════════════════════════════════════════════   │
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Rating *                                                ││
│  │ [⭐] [⭐] [⭐] [⭐] [☆]  4/5                            ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Komentar *                                              ││
│  │ ┌─────────────────────────────────────────────────────┐ ││
│  │ │ Pekerjaan bagus, dokumentasi lengkap. Lanjutkan!   │ ││
│  │ │                                                     │ ││
│  │ └─────────────────────────────────────────────────────┘ ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │    [❌ Reject]              [✅ Accept]                 ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 9. Profile Tab

```
┌─────────────────────────────────────────────────────────────┐
│ ☰  Profile                           🔔3  [👤]              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │      ┌──────────────┐                                   ││
│  │      │     📷       │     John Doe                      ││
│  │      │   [foto]     │     NPP: 199003032010012003       ││
│  │      │  [Edit]      │     Staff                         ││
│  │      └──────────────┘     Manager: Jane Manager         ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 📋 Informasi Pribadi                           [Edit] ││
│  │ ├── Email: john@example.com                            ││
│  │ ├── Tempat Lahir: Jakarta                              ││
│  │ ├── Tanggal Lahir: 03 Maret 1990                       ││
│  │ ├── NIK: 3171030303900001                              ││
│  │ ├── NPWP: 12.345.678.9-012.000                         ││
│  │ ├── Alamat: Jl. Sudirman No. 123                       ││
│  │ └── Status: Kawin                                       ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 🎓 Riwayat Pendidikan                                   ││
│  │ ├── S1 - Teknik Informatika - UI - 2012                ││
│  │ └── SMA - SMAN 1 Jakarta - 2008                         ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 💼 Riwayat Karir                                        ││
│  │ └── Staff IT - PT ABC - 2015-2020                       ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 🔐 [Ubah Password]                                      ││
│  │ 🚪 [Logout]                                             ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  [🏠]      [📊]       [📝]       [👥]       [👤]           │
│ Overview   My Perf   Logbook   Team Perf  Profile◀         │
└─────────────────────────────────────────────────────────────┘
```

---

## 1. Authentication Endpoints

### 1.1 Login

```
POST /auth/login
```

**Request Body:**
```json
{
  "npp": "199003032010012003",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": "uuid",
      "npp": "199003032010012003",
      "nama": "John Doe",
      "role": "Staff",
      "manager_id": "uuid-manager",
      "foto": "/storage/photos/john.jpg",
      "has_subordinates": false
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

**Error Response (401):**
```json
{
  "message": "NPP atau password salah"
}
```

### 1.2 Logout

```
POST /auth/logout
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "Logout berhasil"
}
```

### 1.3 Get Current User

```
GET /auth/me
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "data": {
    "id": "uuid",
    "npp": "199003032010012003",
    "nama": "John Doe",
    "email": "john@example.com",
    "role": "Staff",
    "manager_id": "uuid",
    "manager": {
      "id": "uuid",
      "nama": "Jane Manager"
    },
    "foto": "/storage/photos/john.jpg",
    "tempat_lahir": "Jakarta",
    "tanggal_lahir": "1990-03-03",
    "nik": "3171030303900001",
    "npwp": "12.345.678.9-012.000",
    "alamat": "Jl. Sudirman No. 123",
    "status_kawin": "Kawin",
    "riwayat_pendidikan": [
      {"jenjang": "S1", "jurusan": "Teknik Informatika", "institusi": "UI", "tahun": 2012}
    ],
    "riwayat_karir": [
      {"jabatan": "Staff IT", "perusahaan": "PT ABC", "periode": "2015-2020"}
    ],
    "has_subordinates": false,
    "last_password_change": "2026-01-15T10:00:00Z"
  }
}
```

### 1.4 Change Password

```
PUT /auth/change-password
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "current_password": "oldpassword",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Success Response (200):**
```json
{
  "message": "Password berhasil diubah"
}
```

### 1.5 Update Profile

```
PUT /auth/profile
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
foto: [file]
alamat: "Jl. Baru No. 456"
tempat_lahir: "Bandung"
... (other biodata fields)
```

**Success Response (200):**
```json
{
  "message": "Profil berhasil diperbarui",
  "data": { /* updated user object */ }
}
```

---

## 2. KPI Endpoints

### 2.1 Get My KPI Assignments

```
GET /kpi/me
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "data": [
    {
      "id": "assignment-uuid-1",
      "kpi_id": "kpi-uuid-1",
      "kpi": {
        "id": "kpi-uuid-1",
        "nama": "Buat Laporan Bulanan",
        "target_angka": 3,
        "satuan": "dokumen",
        "deskripsi": "Membuat laporan bulanan departemen"
      },
      "assigned_by": "manager-uuid",
      "assigner": {
        "nama": "Jane Manager"
      },
      "created_at": "2026-03-01T08:00:00Z"
    },
    {
      "id": "assignment-uuid-2",
      "kpi_id": "kpi-uuid-2",
      "kpi": {
        "id": "kpi-uuid-2",
        "nama": "Kunjungan Lapangan",
        "target_angka": 5,
        "satuan": "lokasi",
        "deskripsi": "Melakukan kunjungan ke lokasi kerja"
      },
      "assigned_by": "manager-uuid",
      "assigner": {
        "nama": "Jane Manager"
      },
      "created_at": "2026-03-01T08:00:00Z"
    }
  ]
}
```

---

## 3. Logbook Endpoints

### 3.1 Start Work (Check-in)

```
POST /logbooks/start
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "lokasi": "-6.2088,106.8456"
}
```

**Validation Rules:**
- Current time must be >= 07:00
- User must not have active DRAFT logbook for today

**Success Response (201):**
```json
{
  "message": "Logbook berhasil dimulai",
  "data": {
    "id": "logbook-uuid",
    "user_id": "user-uuid",
    "start_kerja": "2026-03-20T08:00:00Z",
    "lokasi": "-6.2088,106.8456",
    "status": "DRAFT",
    "details": [
      {
        "id": "detail-uuid-1",
        "kpi_id": "kpi-uuid-1",
        "kpi_nama": "Buat Laporan Bulanan",
        "target_angka": 3,
        "satuan": "dokumen",
        "capaian_angka": 0,
        "lampiran_file": []
      },
      {
        "id": "detail-uuid-2",
        "kpi_id": "kpi-uuid-2",
        "kpi_nama": "Kunjungan Lapangan",
        "target_angka": 5,
        "satuan": "lokasi",
        "capaian_angka": 0,
        "lampiran_file": []
      }
    ]
  }
}
```

**Error Response (400) - Before 07:00:**
```json
{
  "message": "Check-in tidak diperbolehkan sebelum pukul 07:00"
}
```

**Error Response (400) - Already has DRAFT:**
```json
{
  "message": "Anda masih memiliki logbook yang belum diselesaikan"
}
```

### 3.2 List My Logbooks

```
GET /logbooks
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 15 | Items per page (max 100) |
| status | string | - | Filter by status (DRAFT/SUBMITTED/ACCEPTED/REJECTED) |
| date_from | date | - | Filter from date (YYYY-MM-DD) |
| date_to | date | - | Filter to date (YYYY-MM-DD) |
| sort_by | string | created_at | Sort field |
| sort_dir | string | desc | Sort direction (asc/desc) |

**Success Response (200):**
```json
{
  "data": [
    {
      "id": "logbook-uuid-1",
      "user_id": "user-uuid",
      "start_kerja": "2026-03-20T08:00:00Z",
      "end_kerja": "2026-03-20T17:00:00Z",
      "lokasi": "-6.2088,106.8456",
      "status": "ACCEPTED",
      "rating": 4,
      "reviewer_comment": "Bagus, lanjutkan!",
      "reviewed_by": "manager-uuid",
      "reviewed_at": "2026-03-20T18:00:00Z"
    },
    {
      "id": "logbook-uuid-2",
      "user_id": "user-uuid",
      "start_kerja": "2026-03-19T08:30:00Z",
      "end_kerja": null,
      "lokasi": "-6.2088,106.8456",
      "status": "DRAFT",
      "rating": null,
      "reviewer_comment": null
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

### 3.3 Get Logbook Detail

```
GET /logbooks/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "data": {
    "id": "logbook-uuid",
    "user_id": "user-uuid",
    "start_kerja": "2026-03-20T08:00:00Z",
    "end_kerja": "2026-03-20T17:00:00Z",
    "lokasi": "-6.2088,106.8456",
    "status": "ACCEPTED",
    "rating": 4,
    "reviewer_comment": "Pekerjaan bagus, dokumentasi lengkap.",
    "reviewed_by": "manager-uuid",
    "reviewed_at": "2026-03-20T18:00:00Z",
    "reviewer": {
      "id": "manager-uuid",
      "nama": "Jane Manager"
    },
    "details": [
      {
        "id": "detail-uuid-1",
        "kpi_id": "kpi-uuid-1",
        "kpi_nama": "Buat Laporan Bulanan",
        "target_angka": 3,
        "satuan": "dokumen",
        "capaian_angka": 3,
        "lampiran_file": [
          "/storage/proofs/2026/03/laporan_maret.pdf",
          "/storage/proofs/2026/03/bukti_1.jpg"
        ],
        "finished_at": "2026-03-20T15:00:00Z"
      },
      {
        "id": "detail-uuid-2",
        "kpi_id": "kpi-uuid-2",
        "kpi_nama": "Kunjungan Lapangan",
        "target_angka": 5,
        "satuan": "lokasi",
        "capaian_angka": 5,
        "lampiran_file": [
          "/storage/proofs/2026/03/foto_lokasi1.jpg",
          "/storage/proofs/2026/03/foto_lokasi2.jpg"
        ],
        "finished_at": "2026-03-20T16:00:00Z"
      }
    ]
  }
}
```

### 3.4 Get Work Duration (with break calculation)

```
GET /logbooks/{id}/duration
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "data": {
    "logbook_id": "logbook-uuid",
    "start_kerja": "2026-03-20T08:00:00Z",
    "end_kerja": "2026-03-20T17:00:00Z",
    "total_minutes": 540,
    "break_minutes": 60,
    "effective_work_minutes": 480,
    "formatted": {
      "total": "9 jam 0 menit",
      "break": "1 jam 0 menit",
      "effective": "8 jam 0 menit"
    }
  }
}
```

### 3.5 Update KPI Progress

```
PATCH /logbooks/{id}/kpi/{detail_id}
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "capaian_angka": 2
}
```

**Validation Rules:**
- Logbook must be in DRAFT status
- capaian_angka must be numeric (>= 0)
- User must own the logbook

**Success Response (200):**
```json
{
  "message": "Progress KPI berhasil diperbarui",
  "data": {
    "id": "detail-uuid",
    "kpi_nama": "Buat Laporan Bulanan",
    "target_angka": 3,
    "satuan": "dokumen",
    "capaian_angka": 2,
    "lampiran_file": [],
    "finished_at": null
  }
}
```

### 3.6 Upload KPI Attachment

```
POST /logbooks/{id}/kpi/{detail_id}/attachments
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```
file: [binary file]
```

**Validation Rules:**
- Logbook must be in DRAFT status
- Max file size: 5MB
- Allowed types: jpg, jpeg, png, pdf, doc, docx

**Success Response (201):**
```json
{
  "message": "File berhasil diupload",
  "data": {
    "file_url": "/storage/proofs/2026/03/20/abc123.jpg",
    "file_name": "bukti_laporan.jpg",
    "file_size": 245678,
    "uploaded_at": "2026-03-20T10:30:00Z"
  }
}
```

### 3.7 Delete KPI Attachment

```
DELETE /logbooks/{id}/kpi/{detail_id}/attachments/{file_index}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "File berhasil dihapus"
}
```

### 3.8 Submit Logbook (Check-out)

```
POST /logbooks/{id}/submit
Authorization: Bearer {token}
```

**Request Body:**
```json
{}
```

**Validation Rules:**
- Logbook must be in DRAFT status
- At least one KPI must have progress > 0

**Success Response (200):**
```json
{
  "message": "Logbook berhasil disubmit",
  "data": {
    "id": "logbook-uuid",
    "status": "SUBMITTED",
    "end_kerja": "2026-03-20T17:00:00Z",
    "duration": {
      "effective_work_minutes": 480
    }
  }
}
```

---

## 4. Manager Endpoints (Staff with Subordinates)

These endpoints are available for Staff who have subordinates (manager capability).

### 4.1 Get Subordinates

```
GET /users/{id}/subordinates
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "data": [
    {
      "id": "staff-uuid-1",
      "npp": "199003032010012003",
      "nama": "John Doe",
      "foto": "/storage/photos/john.jpg"
    },
    {
      "id": "staff-uuid-2",
      "npp": "199004042011012004",
      "nama": "Bob Smith",
      "foto": "/storage/photos/bob.jpg"
    }
  ]
}
```

### 4.2 List KPI Assignments (for subordinates)

```
GET /kpi/assignments
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| user_id | uuid | Filter by specific subordinate |

**Success Response (200):**
```json
{
  "data": [
    {
      "id": "assignment-uuid",
      "user_id": "staff-uuid",
      "user": {
        "id": "staff-uuid",
        "nama": "John Doe"
      },
      "kpi_id": "kpi-uuid",
      "kpi": {
        "nama": "Buat Laporan",
        "target_angka": 3,
        "satuan": "dokumen"
      },
      "assigned_by": "manager-uuid"
    }
  ]
}
```

### 4.3 Assign KPI to Subordinate

```
POST /kpi/assignments
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "user_id": "staff-uuid",
  "kpi_id": "kpi-uuid"
}
```

**Validation Rules:**
- user_id must be a direct subordinate (manager_id = current user)
- KPI must be active

**Success Response (201):**
```json
{
  "message": "KPI berhasil ditugaskan",
  "data": {
    "id": "assignment-uuid",
    "user_id": "staff-uuid",
    "kpi_id": "kpi-uuid",
    "assigned_by": "manager-uuid"
  }
}
```

### 4.4 Remove KPI Assignment

```
DELETE /kpi/assignments/{id}
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "Penugasan KPI berhasil dihapus"
}
```

### 4.5 List Team Logbooks (Pending Review)

```
GET /logbooks
Authorization: Bearer {token}
Query: ?status=SUBMITTED
```

Returns logbooks from subordinates that need review.

### 4.6 Review Logbook (Accept/Reject)

```
PUT /logbooks/{id}/review
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "rating": 4,
  "reviewer_comment": "Pekerjaan bagus, dokumentasi lengkap.",
  "decision": "ACCEPTED"
}
```

**Validation Rules:**
- rating: required, integer 1-5
- reviewer_comment: required, string
- decision: required, enum (ACCEPTED, REJECTED)
- Logbook must be in SUBMITTED status
- Logbook owner must be subordinate of current user

**Success Response (200):**
```json
{
  "message": "Logbook berhasil direview",
  "data": {
    "id": "logbook-uuid",
    "status": "ACCEPTED",
    "rating": 4,
    "reviewer_comment": "Pekerjaan bagus, dokumentasi lengkap.",
    "reviewed_by": "manager-uuid",
    "reviewed_at": "2026-03-20T18:00:00Z"
  }
}
```

### 4.7 Revert Logbook to DRAFT

```
POST /logbooks/{id}/revert
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "reason": "Bukti kunjungan kurang lengkap, tambahkan foto lokasi."
}
```

**Success Response (200):**
```json
{
  "message": "Logbook dikembalikan ke DRAFT",
  "data": {
    "id": "logbook-uuid",
    "status": "DRAFT"
  }
}
```

---

## 5. Dashboard Endpoints

### 5.1 Staff Dashboard

```
GET /dashboard/staff
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "data": {
    "personal_kpi_completion_rate": 85.5,
    "missed_logbooks_count": 2,
    "average_rating": 4.2,
    "today_logbook": {
      "id": "logbook-uuid",
      "status": "DRAFT",
      "start_kerja": "2026-03-20T08:00:00Z",
      "kpi_completed": 2,
      "kpi_total": 4
    },
    "recent_logbooks": [
      {
        "id": "logbook-uuid-1",
        "date": "2026-03-19",
        "status": "ACCEPTED",
        "rating": 4
      }
    ]
  }
}
```

### 5.2 Manager Dashboard

```
GET /dashboard/manager
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "data": {
    "subordinates": [
      {
        "id": "staff-uuid-1",
        "name": "John Doe",
        "total_kpi": 4,
        "completed_kpi": 3,
        "completion_rate": 75.0
      },
      {
        "id": "staff-uuid-2",
        "name": "Bob Smith",
        "total_kpi": 3,
        "completed_kpi": 3,
        "completion_rate": 100.0
      }
    ],
    "pending_logbooks_count": 3,
    "team_average_rating": 4.1
  }
}
```

---

## 6. Notification Endpoints

### 6.1 List Notifications

```
GET /notifications
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| page | int | 1 | Page number |
| per_page | int | 20 | Items per page |
| is_read | bool | - | Filter by read status |

**Success Response (200):**
```json
{
  "data": [
    {
      "id": "notif-uuid-1",
      "title": "KPI Baru Ditugaskan",
      "message": "Anda ditugaskan KPI baru: Buat Laporan Bulanan",
      "type": "KPI_ASSIGNMENT",
      "reference_id": "assignment-uuid",
      "is_read": false,
      "created_at": "2026-03-20T08:00:00Z"
    },
    {
      "id": "notif-uuid-2",
      "title": "Logbook Diterima",
      "message": "Logbook tanggal 19 Maret 2026 telah diterima dengan rating 4/5",
      "type": "LOGBOOK_ACCEPTED",
      "reference_id": "logbook-uuid",
      "is_read": true,
      "created_at": "2026-03-19T18:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 15,
    "unread_count": 3
  }
}
```

### 6.2 Mark Notification as Read

```
PUT /notifications/{id}/read
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "Notifikasi ditandai sudah dibaca"
}
```

### 6.3 Mark All as Read

```
PUT /notifications/read-all
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "Semua notifikasi ditandai sudah dibaca"
}
```

---

## 7. KPI Achievement Endpoints

### 7.1 Get My KPI Achievements

```
GET /users/{id}/kpi-achievements
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| period | string | Month in YYYY-MM format (default: current month) |

**Success Response (200):**
```json
{
  "data": {
    "period": "2026-03",
    "achievements": [
      {
        "kpi_id": "kpi-uuid-1",
        "kpi_nama": "Buat Laporan Bulanan",
        "target_angka": 3,
        "satuan": "dokumen",
        "total_capaian": 9,
        "total_target": 9,
        "completion_rate": 100.0,
        "logbook_count": 3
      },
      {
        "kpi_id": "kpi-uuid-2",
        "kpi_nama": "Kunjungan Lapangan",
        "target_angka": 5,
        "satuan": "lokasi",
        "total_capaian": 12,
        "total_target": 15,
        "completion_rate": 80.0,
        "logbook_count": 3
      }
    ],
    "overall_completion_rate": 90.0
  }
}
```

---

## 7. Error Response Format

All error responses follow this format:

```json
{
  "message": "Human readable error message",
  "errors": {
    "field_name": ["Validation error 1", "Validation error 2"]
  }
}
```

### Common HTTP Status Codes

| Code | Meaning | When |
|------|---------|------|
| 200 | OK | Request successful |
| 201 | Created | Resource created |
| 400 | Bad Request | Invalid request/business rule violation |
| 401 | Unauthorized | Invalid/missing token |
| 403 | Forbidden | Not allowed to access resource |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Server Error | Internal server error |

---

## 8. Summary Endpoints (Pre-aggregated Data)

Summary endpoints provide pre-aggregated data from `daily_staff_summaries` and `daily_kpi_summaries` tables for fast performance views.

### 8.1 Get Daily Summary

```
GET /summaries/daily
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| date | date | today | Date in YYYY-MM-DD format |
| user_id | uuid | current user | Specific user (Admin/Manager only) |

**Success Response (200):**
```json
{
  "data": {
    "user_id": "user-uuid",
    "date": "2026-03-20",
    "total_work_minutes": 510,
    "logbook_count": 2,
    "accepted_count": 1,
    "rejected_count": 0,
    "pending_count": 1,
    "avg_rating": 4.5,
    "total_kpi_achieved": 8,
    "total_kpi_target": 10,
    "overall_kpi_percentage": 80.0,
    "formatted": {
      "work_duration": "8 jam 30 menit"
    }
  }
}
```

### 8.2 Get User Daily Summary

```
GET /summaries/daily/{user_id}
Authorization: Bearer {token}
```

**Authorization:**
- Staff: Can only view own summary
- Manager: Can view own + subordinates' summaries
- Admin/SuperAdmin: Can view all summaries

**Success Response (200):**
```json
{
  "data": {
    "user_id": "staff-uuid",
    "user": {
      "id": "staff-uuid",
      "nama": "John Doe",
      "npp": "199003032010012003",
      "foto": "/storage/photos/john.jpg"
    },
    "date": "2026-03-20",
    "total_work_minutes": 510,
    "logbook_count": 2,
    "accepted_count": 2,
    "rejected_count": 0,
    "pending_count": 0,
    "avg_rating": 4.5,
    "total_kpi_achieved": 10,
    "total_kpi_target": 10,
    "overall_kpi_percentage": 100.0,
    "logbooks": [
      {
        "id": "logbook-uuid-1",
        "start_kerja": "08:00",
        "end_kerja": "12:00",
        "status": "ACCEPTED",
        "rating": 4
      },
      {
        "id": "logbook-uuid-2",
        "start_kerja": "13:00",
        "end_kerja": "17:30",
        "status": "ACCEPTED",
        "rating": 5
      }
    ]
  }
}
```

### 8.3 Get Period Summary

```
GET /summaries/period
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Required | Description |
|-------|------|----------|-------------|
| date_from | date | yes | Start date (YYYY-MM-DD) |
| date_to | date | yes | End date (YYYY-MM-DD) |
| user_id | uuid | no | Specific user (Admin/Manager only) |

**Success Response (200):**
```json
{
  "data": {
    "user_id": "user-uuid",
    "period": {
      "from": "2026-03-01",
      "to": "2026-03-20"
    },
    "days_worked": 15,
    "total_work_minutes": 7200,
    "total_logbooks": 28,
    "accepted_count": 25,
    "rejected_count": 1,
    "pending_count": 2,
    "avg_rating": 4.3,
    "total_kpi_achieved": 142,
    "total_kpi_target": 150,
    "overall_kpi_percentage": 94.67,
    "daily_trend": [
      { "date": "2026-03-01", "kpi_percentage": 100.0, "logbook_count": 2 },
      { "date": "2026-03-02", "kpi_percentage": 85.0, "logbook_count": 1 },
      { "date": "2026-03-03", "kpi_percentage": 90.0, "logbook_count": 2 }
    ],
    "formatted": {
      "total_work_duration": "120 jam 0 menit",
      "avg_daily_work": "8 jam 0 menit"
    }
  }
}
```

### 8.4 Get Daily KPI Summary

```
GET /summaries/kpi/daily
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| date | date | today | Date in YYYY-MM-DD format |
| user_id | uuid | current user | Specific user (Admin/Manager only) |

**Success Response (200):**
```json
{
  "data": {
    "user_id": "user-uuid",
    "date": "2026-03-20",
    "kpi_breakdown": [
      {
        "kpi_id": "kpi-uuid-1",
        "kpi_nama": "Buat Laporan Bulanan",
        "total_achieved": 3,
        "total_target": 3,
        "percentage": 100.0,
        "status": "completed"
      },
      {
        "kpi_id": "kpi-uuid-2",
        "kpi_nama": "Kunjungan Lapangan",
        "total_achieved": 4,
        "total_target": 5,
        "percentage": 80.0,
        "status": "in_progress"
      },
      {
        "kpi_id": "kpi-uuid-3",
        "kpi_nama": "Input Data",
        "total_achieved": 95,
        "total_target": 100,
        "percentage": 95.0,
        "status": "in_progress"
      }
    ],
    "overall_percentage": 91.67
  }
}
```

### 8.5 Get Period KPI Summary

```
GET /summaries/kpi/period
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Required | Description |
|-------|------|----------|-------------|
| date_from | date | yes | Start date (YYYY-MM-DD) |
| date_to | date | yes | End date (YYYY-MM-DD) |
| user_id | uuid | no | Specific user (Admin/Manager only) |

**Success Response (200):**
```json
{
  "data": {
    "user_id": "user-uuid",
    "period": {
      "from": "2026-03-01",
      "to": "2026-03-20"
    },
    "kpi_breakdown": [
      {
        "kpi_id": "kpi-uuid-1",
        "kpi_nama": "Buat Laporan Bulanan",
        "total_achieved": 45,
        "total_target": 45,
        "percentage": 100.0,
        "daily_breakdown": [
          { "date": "2026-03-01", "achieved": 3, "target": 3 },
          { "date": "2026-03-02", "achieved": 2, "target": 3 },
          { "date": "2026-03-03", "achieved": 3, "target": 3 }
        ]
      },
      {
        "kpi_id": "kpi-uuid-2",
        "kpi_nama": "Kunjungan Lapangan",
        "total_achieved": 72,
        "total_target": 75,
        "percentage": 96.0,
        "daily_breakdown": [
          { "date": "2026-03-01", "achieved": 5, "target": 5 },
          { "date": "2026-03-02", "achieved": 4, "target": 5 },
          { "date": "2026-03-03", "achieved": 5, "target": 5 }
        ]
      }
    ],
    "overall_percentage": 98.0
  }
}
```

### 8.6 Get Team Daily Summary (Manager Only)

```
GET /summaries/team/daily
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| date | date | today | Date in YYYY-MM-DD format |

**Success Response (200):**
```json
{
  "data": {
    "manager_id": "manager-uuid",
    "date": "2026-03-20",
    "team_summary": {
      "total_members": 5,
      "total_logbooks": 8,
      "total_work_minutes": 2400,
      "avg_kpi_percentage": 89.5,
      "avg_rating": 4.2,
      "pending_reviews": 3
    },
    "members": [
      {
        "user_id": "staff-uuid-1",
        "nama": "John Doe",
        "foto": "/storage/photos/john.jpg",
        "logbook_count": 2,
        "work_minutes": 510,
        "kpi_percentage": 95.0,
        "avg_rating": 4.5
      },
      {
        "user_id": "staff-uuid-2",
        "nama": "Alice Wang",
        "foto": "/storage/photos/alice.jpg",
        "logbook_count": 1,
        "work_minutes": 360,
        "kpi_percentage": 72.0,
        "avg_rating": 3.5
      },
      {
        "user_id": "staff-uuid-3",
        "nama": "Bob Smith",
        "foto": "/storage/photos/bob.jpg",
        "logbook_count": 3,
        "work_minutes": 555,
        "kpi_percentage": 100.0,
        "avg_rating": 4.8
      }
    ]
  }
}
```

### 8.7 Get Staff Performance List (Admin/SuperAdmin)

```
GET /summaries/staff-performance
Authorization: Bearer {token}
```

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| view | string | daily | View type: "daily" or "period" |
| date | date | today | For daily view |
| date_from | date | - | For period view |
| date_to | date | - | For period view |
| sort_by | string | nama | Sort field |
| sort_dir | string | asc | Sort direction |
| search | string | - | Search by name/NPP |

**Success Response (200) - Daily View:**
```json
{
  "data": {
    "view": "daily",
    "date": "2026-03-20",
    "staff": [
      {
        "user_id": "staff-uuid-1",
        "nama": "John Doe",
        "npp": "199003032010012003",
        "foto": "/storage/photos/john.jpg",
        "manager_nama": "Jane Manager",
        "logbook_count": 2,
        "work_minutes": 510,
        "kpi_percentage": 95.0,
        "avg_rating": 4.5,
        "status_summary": { "accepted": 1, "pending": 1, "rejected": 0 }
      }
    ],
    "meta": {
      "total": 50,
      "page": 1,
      "per_page": 20
    }
  }
}
```

---

## 9. Logbook Endpoints (Updated for Multiple Logbooks/Day)

### 9.1 Create New Logbook (Manual Time Input)

```
POST /logbooks
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "tanggal": "2026-03-20",
  "start_kerja": "08:00",
  "end_kerja": "12:00",
  "lokasi": "-6.2088,106.8456"
}
```

**Validation Rules:**
- tanggal: required, date format YYYY-MM-DD
- start_kerja: required, time format HH:MM
- end_kerja: required, time format HH:MM, must be after start_kerja
- User can create multiple logbooks for the same day

**Success Response (201):**
```json
{
  "message": "Logbook berhasil dibuat",
  "data": {
    "id": "logbook-uuid",
    "user_id": "user-uuid",
    "tanggal": "2026-03-20",
    "start_kerja": "08:00",
    "end_kerja": "12:00",
    "lokasi": "-6.2088,106.8456",
    "status": "DRAFT",
    "duration_minutes": 240,
    "details": [
      {
        "id": "detail-uuid-1",
        "kpi_id": "kpi-uuid-1",
        "kpi_nama": "Buat Laporan Bulanan",
        "target_angka": 3,
        "satuan": "dokumen",
        "capaian_angka": 0,
        "lampiran_file": []
      }
    ]
  }
}
```

### 9.2 Update Logbook Time (DRAFT status only)

```
PATCH /logbooks/{id}
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "tanggal": "2026-03-20",
  "start_kerja": "08:30",
  "end_kerja": "12:30"
}
```

**Validation Rules:**
- Logbook must be in DRAFT status
- User must own the logbook

**Success Response (200):**
```json
{
  "message": "Logbook berhasil diperbarui",
  "data": {
    "id": "logbook-uuid",
    "tanggal": "2026-03-20",
    "start_kerja": "08:30",
    "end_kerja": "12:30",
    "duration_minutes": 240
  }
}
```

### 9.3 Delete Logbook (DRAFT status only)

```
DELETE /logbooks/{id}
Authorization: Bearer {token}
```

**Validation Rules:**
- Logbook must be in DRAFT status
- User must own the logbook

**Success Response (200):**
```json
{
  "message": "Logbook berhasil dihapus"
}
```

---

## 10. Mobile App Screens Mapping (Updated)

| Screen | Primary API | Additional APIs |
|--------|-------------|-----------------|
| Login | POST /auth/login | - |
| Home/Dashboard | GET /dashboard/staff | GET /logbooks (today's) |
| Check-in | POST /logbooks/start | GET /kpi/me |
| Work Session | GET /logbooks/{id} | PATCH kpi progress, POST attachments |
| Check-out | POST /logbooks/{id}/submit | - |
| History | GET /logbooks | GET /logbooks/{id} |
| KPI Achievement | GET /users/{id}/kpi-achievements | - |
| Profile | GET /auth/me | PUT /auth/profile |
| Notifications | GET /notifications | PUT read |
| Manager: Team | GET /users/{id}/subordinates | GET /dashboard/manager |
| Manager: Assign KPI | GET /kpi/master, POST /kpi/assignments | - |
| Manager: Review | GET /logbooks?status=SUBMITTED | PUT /logbooks/{id}/review |
| My Performance (Daily) | GET /summaries/daily | GET /summaries/kpi/daily |
| My Performance (Period) | GET /summaries/period | GET /summaries/kpi/period |
| Team Performance | GET /summaries/team/daily | GET /summaries/daily/{user_id} |
| Staff Performance (Admin) | GET /summaries/staff-performance | GET /summaries/daily/{user_id} |

---

## 11. Summary Tables Schema

### 11.1 daily_staff_summaries

Pre-aggregated daily staff performance data, updated via LogbookObserver.

```sql
CREATE TABLE daily_staff_summaries (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id),
    date DATE NOT NULL,
    total_work_minutes INTEGER DEFAULT 0,
    logbook_count INTEGER DEFAULT 0,
    accepted_count INTEGER DEFAULT 0,
    rejected_count INTEGER DEFAULT 0,
    pending_count INTEGER DEFAULT 0,
    avg_rating DECIMAL(3,2) DEFAULT NULL,
    total_kpi_achieved DECIMAL(10,2) DEFAULT 0,
    total_kpi_target DECIMAL(10,2) DEFAULT 0,
    overall_kpi_percentage DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, date)
);
```

### 11.2 daily_kpi_summaries

Pre-aggregated daily KPI performance per user, updated via LogbookObserver.

```sql
CREATE TABLE daily_kpi_summaries (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id),
    date DATE NOT NULL,
    kpi_id UUID NOT NULL REFERENCES kpi_masters(id),
    kpi_nama VARCHAR(255) NOT NULL,
    total_achieved DECIMAL(10,2) DEFAULT 0,
    total_target DECIMAL(10,2) DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, date, kpi_id)
);
```

### 11.3 LogbookObserver Event Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    LogbookObserver                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Events: created, updated, deleted                          │
│                                                             │
│  On Logbook Change:                                         │
│  ├── 1. Get logbook.user_id and logbook.tanggal            │
│  ├── 2. Call DailySummaryService::recalculate(user, date)  │
│  │       ├── Query all logbooks for user+date              │
│  │       ├── Aggregate work minutes, counts, ratings       │
│  │       ├── Aggregate KPI achievements                    │
│  │       └── Upsert daily_staff_summaries                  │
│  └── 3. Call DailyKpiSummaryService::recalculate(user,date)│
│          ├── Query all logbook_kpi_details for user+date   │
│          ├── Group by kpi_id                               │
│          ├── Sum achieved and target per KPI               │
│          └── Upsert daily_kpi_summaries                    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

**Next Document**: `04-MIGRATION-PLAN.md` - Database migration dari current → target
