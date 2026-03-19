# System Context: Manajemen Logbook & KPI Pegawai

## Overview
Aplikasi ini dirancang untuk mendigitalisasi pelaporan kerja harian (Logbook) dan manajemen Key Performance Indicator (KPI) atau tugas karyawan. 

## Roles
- **Admin**: Manage master data (Pegawai, Master KPI, Config). No daily operations.
- **Manager**: Assign KPI to Staff, review daily logbooks, give rating (1-5).
- **Staff**: Start work, check daily tasks, toggle completion, upload proofs, end work.

## Core Entities
- `User` (Admin, Manager, Staff)
- `KpiMaster` (Kamus KPI/Tugas)
- `UserKpiAssignment` (Penugasan KPI ke Staff)
- `Logbook` (Logbook Harian, Status: DRAFT, SUBMITTED, REVIEWED)
- `LogbookKpiDetail` (Detail KPI dalam logbook)
- `AuditLog` (Pelacakan aktivitas sistem)
- `Notification` (Notifikasi sistem)

## Constraints
- Framework: Laravel 12
- PHP: 8.3
- DB: Relational (SQLite/PostgreSQL)
- Auth: Sanctum / JWT
