# Database Migration Plan

## Overview

Dokumen ini menjelaskan langkah-langkah migrasi database dari skema saat ini ke skema target sesuai SYSTEM_DESIGN.md yang telah direvisi.

**Key Changes in This Revision:**
- Manual time input: `tanggal` (DATE), `start_kerja` (TIME), `end_kerja` (TIME)
- Multiple logbooks per day allowed
- Pre-aggregated summary tables: `daily_staff_summaries`, `daily_kpi_summaries`
- LogbookObserver for automatic summary recalculation

---

## 1. Current vs Target Schema

### 1.1 Users Table

| Field | Current | Target | Action |
|-------|---------|--------|--------|
| id | uuid PK | uuid PK | - |
| npp | varchar unique | varchar unique | Rename from 'nip' |
| nama | varchar | varchar | Rename from 'name' |
| password | varchar | varchar | - |
| email | varchar unique | varchar unique | - |
| role | varchar (Admin/Manager/Staff) | enum (SuperAdmin/Admin/Staff) | Change enum values |
| manager_id | uuid FK nullable | uuid FK nullable | - |
| last_password_change | timestamp | timestamp | - |
| **foto** | - | varchar | **ADD** |
| **tempat_lahir** | - | varchar | **ADD** |
| **tanggal_lahir** | - | date | **ADD** |
| **nik** | - | varchar unique | **ADD** |
| **npwp** | - | varchar | **ADD** |
| **alamat** | - | text | **ADD** |
| **status_kawin** | - | enum | **ADD** |
| **riwayat_pendidikan** | - | jsonb | **ADD** |
| **riwayat_karir** | - | jsonb | **ADD** |
| created_at | timestamp | timestamp | - |
| updated_at | timestamp | timestamp | - |
| deleted_at | timestamp | timestamp | - |

### 1.2 KPI Masters Table

| Field | Current | Target | Action |
|-------|---------|--------|--------|
| id | uuid PK | uuid PK | - |
| nama | varchar | varchar | - |
| status_aktif | boolean | boolean | - |
| **target_angka** | - | numeric | **ADD** |
| **satuan** | - | varchar | **ADD** |
| **deskripsi** | - | text | **ADD** |
| created_at | timestamp | timestamp | - |
| updated_at | timestamp | timestamp | - |
| deleted_at | timestamp | timestamp | - |

### 1.3 Logbooks Table

| Field | Current | Target | Action |
|-------|---------|--------|--------|
| id | uuid PK | uuid PK | - |
| user_id | uuid FK | uuid FK | - |
| **tanggal** | - | date | **ADD** (manual date input) |
| start_kerja | timestamp | time | **CHANGE** type to TIME |
| end_kerja | timestamp nullable | time nullable | **CHANGE** type to TIME |
| lokasi_start | varchar | - | **REMOVE** |
| lokasi_end | varchar nullable | - | **REMOVE** |
| **lokasi** | - | varchar | **ADD** (merge from lokasi_start) |
| gambar_bukti | jsonb | - | **REMOVE** (move to kpi_details) |
| status | enum (DRAFT/SUBMITTED/REVIEWED) | enum (DRAFT/SUBMITTED/ACCEPTED/REJECTED) | **CHANGE** |
| rating | integer nullable | integer nullable | - |
| **reviewer_comment** | - | text nullable | **ADD** |
| reviewed_by | uuid FK nullable | uuid FK nullable | - |
| reviewed_at | timestamp nullable | timestamp nullable | - |
| created_at | timestamp | timestamp | - |
| updated_at | timestamp | timestamp | - |
| deleted_at | timestamp | timestamp | - |

**Note:** Multiple logbooks per user per day are now allowed (no unique constraint on user_id + tanggal)

### 1.4 Logbook KPI Details Table

| Field | Current | Target | Action |
|-------|---------|--------|--------|
| id | uuid PK | uuid PK | - |
| logbook_id | uuid FK | uuid FK | - |
| kpi_id | uuid FK | uuid FK | - |
| kpi_nama | varchar | varchar | - |
| **target_angka** | - | numeric | **ADD** |
| **satuan** | - | varchar | **ADD** |
| is_finished | boolean | - | **REMOVE** |
| **capaian_angka** | - | numeric default 0 | **ADD** (replaces is_finished) |
| **lampiran_file** | - | jsonb | **ADD** (from logbooks.gambar_bukti) |
| finished_at | timestamp nullable | timestamp nullable | - |
| created_at | timestamp | timestamp | - |
| updated_at | timestamp | timestamp | - |
| deleted_at | timestamp | timestamp | - |

### 1.5 Notifications Table

| Field | Current | Target | Action |
|-------|---------|--------|--------|
| id | uuid PK | uuid PK | - |
| user_id | uuid FK | uuid FK | - |
| title | varchar | varchar | - |
| message | text | text | - |
| type | varchar | varchar | - |
| reference_id | uuid nullable | uuid nullable | - |
| is_read | boolean default false | boolean default false | - |
| read_at | timestamp nullable | - | **REMOVE** |
| created_at | timestamp | timestamp | - |

### 1.6 NEW: Daily Staff Summaries Table

| Field | Type | Description |
|-------|------|-------------|
| id | uuid PK | Primary key |
| user_id | uuid FK | References users(id) |
| date | date | Summary date |
| total_work_minutes | integer | Sum of work duration for the day |
| logbook_count | integer | Number of logbooks |
| accepted_count | integer | Number of accepted logbooks |
| rejected_count | integer | Number of rejected logbooks |
| pending_count | integer | Number of pending (SUBMITTED) logbooks |
| avg_rating | decimal(3,2) | Average rating for the day |
| total_kpi_achieved | decimal(10,2) | Sum of KPI achievements |
| total_kpi_target | decimal(10,2) | Sum of KPI targets |
| overall_kpi_percentage | decimal(5,2) | (achieved/target) * 100 |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Unique Constraint:** (user_id, date)

### 1.7 NEW: Daily KPI Summaries Table

| Field | Type | Description |
|-------|------|-------------|
| id | uuid PK | Primary key |
| user_id | uuid FK | References users(id) |
| date | date | Summary date |
| kpi_id | uuid FK | References kpi_masters(id) |
| kpi_nama | varchar | Denormalized KPI name |
| total_achieved | decimal(10,2) | Sum of capaian for this KPI on this day |
| total_target | decimal(10,2) | Sum of target for this KPI on this day |
| percentage | decimal(5,2) | (achieved/target) * 100 |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Unique Constraint:** (user_id, date, kpi_id)

---

## 2. Migration Files

### Migration 1: Add Extended User Biodata

```php
<?php
// 2026_03_21_000001_add_extended_biodata_to_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Extended biodata fields
            $table->string('foto')->nullable()->after('manager_id');
            $table->string('tempat_lahir')->nullable()->after('foto');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('nik', 16)->nullable()->unique()->after('tanggal_lahir');
            $table->string('npwp')->nullable()->after('nik');
            $table->text('alamat')->nullable()->after('npwp');
            $table->enum('status_kawin', ['Belum_Kawin', 'Kawin', 'Cerai_Hidup', 'Cerai_Mati'])
                  ->nullable()->after('alamat');
            $table->jsonb('riwayat_pendidikan')->nullable()->after('status_kawin');
            $table->jsonb('riwayat_karir')->nullable()->after('riwayat_pendidikan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'foto', 'tempat_lahir', 'tanggal_lahir', 'nik', 
                'npwp', 'alamat', 'status_kawin', 
                'riwayat_pendidikan', 'riwayat_karir'
            ]);
        });
    }
};
```

### Migration 2: Enhance KPI Master Table

```php
<?php
// 2026_03_21_000002_add_target_fields_to_kpi_masters.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_masters', function (Blueprint $table) {
            $table->decimal('target_angka', 10, 2)->default(1)->after('nama');
            $table->string('satuan')->default('unit')->after('target_angka');
            $table->text('deskripsi')->nullable()->after('satuan');
        });
    }

    public function down(): void
    {
        Schema::table('kpi_masters', function (Blueprint $table) {
            $table->dropColumn(['target_angka', 'satuan', 'deskripsi']);
        });
    }
};
```

### Migration 3: Update Logbooks Table (Manual Time Input)

```php
<?php
// 2026_03_21_000003_update_logbooks_schema.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new columns
        Schema::table('logbooks', function (Blueprint $table) {
            $table->date('tanggal')->nullable()->after('user_id');
            $table->string('lokasi')->nullable()->after('end_kerja');
            $table->text('reviewer_comment')->nullable()->after('rating');
        });

        // Step 2: Migrate data - extract date from start_kerja, copy lokasi_start to lokasi
        DB::statement("UPDATE logbooks SET tanggal = DATE(start_kerja) WHERE start_kerja IS NOT NULL");
        DB::statement('UPDATE logbooks SET lokasi = lokasi_start WHERE lokasi_start IS NOT NULL');

        // Step 3: Convert timestamp columns to time
        // Create temp columns, copy data, drop old, rename
        Schema::table('logbooks', function (Blueprint $table) {
            $table->time('start_kerja_time')->nullable()->after('tanggal');
            $table->time('end_kerja_time')->nullable()->after('start_kerja_time');
        });

        DB::statement("UPDATE logbooks SET start_kerja_time = start_kerja::time WHERE start_kerja IS NOT NULL");
        DB::statement("UPDATE logbooks SET end_kerja_time = end_kerja::time WHERE end_kerja IS NOT NULL");

        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn(['start_kerja', 'end_kerja']);
        });

        Schema::table('logbooks', function (Blueprint $table) {
            $table->renameColumn('start_kerja_time', 'start_kerja');
            $table->renameColumn('end_kerja_time', 'end_kerja');
        });

        // Step 4: Update status enum
        DB::statement("ALTER TABLE logbooks DROP CONSTRAINT IF EXISTS logbooks_status_check");
        DB::statement("ALTER TABLE logbooks ALTER COLUMN status TYPE VARCHAR(20)");
        DB::statement("UPDATE logbooks SET status = 'ACCEPTED' WHERE status = 'REVIEWED'");
        DB::statement("ALTER TABLE logbooks ADD CONSTRAINT logbooks_status_check CHECK (status IN ('DRAFT', 'SUBMITTED', 'ACCEPTED', 'REJECTED'))");

        // Step 5: Drop old columns (after data migration)
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn(['lokasi_start', 'lokasi_end', 'gambar_bukti']);
        });

        // Step 6: Make tanggal required
        Schema::table('logbooks', function (Blueprint $table) {
            $table->date('tanggal')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->timestamp('start_kerja_ts')->nullable();
            $table->timestamp('end_kerja_ts')->nullable();
            $table->string('lokasi_start')->nullable();
            $table->string('lokasi_end')->nullable();
            $table->jsonb('gambar_bukti')->nullable();
        });

        DB::statement("UPDATE logbooks SET start_kerja_ts = tanggal + start_kerja WHERE start_kerja IS NOT NULL");
        DB::statement("UPDATE logbooks SET end_kerja_ts = tanggal + end_kerja WHERE end_kerja IS NOT NULL");
        DB::statement('UPDATE logbooks SET lokasi_start = lokasi');

        DB::statement("ALTER TABLE logbooks DROP CONSTRAINT IF EXISTS logbooks_status_check");
        DB::statement("UPDATE logbooks SET status = 'REVIEWED' WHERE status IN ('ACCEPTED', 'REJECTED')");
        DB::statement("ALTER TABLE logbooks ADD CONSTRAINT logbooks_status_check CHECK (status IN ('DRAFT', 'SUBMITTED', 'REVIEWED'))");

        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn(['tanggal', 'start_kerja', 'end_kerja', 'lokasi', 'reviewer_comment']);
            $table->renameColumn('start_kerja_ts', 'start_kerja');
            $table->renameColumn('end_kerja_ts', 'end_kerja');
        });
    }
};
```

### Migration 4: Update Logbook KPI Details

```php
<?php
// 2026_03_21_000004_update_logbook_kpi_details_schema.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbook_kpi_details', function (Blueprint $table) {
            // Add new columns
            $table->decimal('target_angka', 10, 2)->default(1)->after('kpi_nama');
            $table->string('satuan')->default('unit')->after('target_angka');
            $table->decimal('capaian_angka', 10, 2)->default(0)->after('satuan');
            $table->jsonb('lampiran_file')->nullable()->after('capaian_angka');
        });

        // Migrate is_finished to capaian_angka
        // If is_finished = true, set capaian_angka = target_angka (assume 1 if no target)
        DB::statement("
            UPDATE logbook_kpi_details 
            SET capaian_angka = CASE 
                WHEN is_finished = true THEN COALESCE(target_angka, 1) 
                ELSE 0 
            END
        ");

        // Copy target_angka and satuan from kpi_masters
        DB::statement("
            UPDATE logbook_kpi_details lkd
            SET target_angka = km.target_angka,
                satuan = km.satuan
            FROM kpi_masters km
            WHERE lkd.kpi_id = km.id
        ");

        // Drop is_finished column
        Schema::table('logbook_kpi_details', function (Blueprint $table) {
            $table->dropColumn('is_finished');
        });
    }

    public function down(): void
    {
        Schema::table('logbook_kpi_details', function (Blueprint $table) {
            $table->boolean('is_finished')->default(false);
        });

        DB::statement("
            UPDATE logbook_kpi_details 
            SET is_finished = (capaian_angka >= target_angka)
        ");

        Schema::table('logbook_kpi_details', function (Blueprint $table) {
            $table->dropColumn(['target_angka', 'satuan', 'capaian_angka', 'lampiran_file']);
        });
    }
};
```

### Migration 5: Update Role Enum

```php
<?php
// 2026_03_21_000005_update_user_role_enum.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update role values
        DB::statement("UPDATE users SET role = 'SuperAdmin' WHERE role = 'SUPERADMIN'");
        DB::statement("UPDATE users SET role = 'Admin' WHERE role = 'ADMIN'");
        DB::statement("UPDATE users SET role = 'Staff' WHERE role IN ('STAFF', 'MANAGER')");
        
        // Note: MANAGER role is removed - manager capability determined by manager_id relation
    }

    public function down(): void
    {
        // Revert role values (approximate, can't perfectly restore MANAGER)
        DB::statement("UPDATE users SET role = 'ADMIN' WHERE role = 'Admin'");
        DB::statement("UPDATE users SET role = 'STAFF' WHERE role = 'Staff'");
    }
};
```

### Migration 6: Remove read_at from Notifications

```php
<?php
// 2026_03_21_000006_remove_read_at_from_notifications.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable();
        });
    }
};
```

### Migration 7: Create Work Duration View

```php
<?php
// 2026_03_21_000007_create_logbook_work_duration_view.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_logbook_work_duration AS
            SELECT 
                l.id AS logbook_id,
                l.user_id,
                l.start_kerja,
                l.end_kerja,
                l.lokasi,
                l.status,
                
                -- Total raw duration in minutes
                EXTRACT(EPOCH FROM (l.end_kerja - l.start_kerja)) / 60 AS total_minutes,
                
                -- Calculate break overlap (12:00-13:00)
                CASE 
                    WHEN l.end_kerja IS NULL THEN 0
                    WHEN l.start_kerja::date = l.end_kerja::date THEN
                        GREATEST(0, 
                            LEAST(
                                EXTRACT(EPOCH FROM (
                                    LEAST(l.end_kerja, (l.start_kerja::date + INTERVAL '13 hours')) -
                                    GREATEST(l.start_kerja, (l.start_kerja::date + INTERVAL '12 hours'))
                                )) / 60,
                                60
                            )
                        )
                    ELSE 0
                END AS break_minutes,
                
                -- Effective work duration (total - break)
                CASE 
                    WHEN l.end_kerja IS NULL THEN NULL
                    ELSE 
                        (EXTRACT(EPOCH FROM (l.end_kerja - l.start_kerja)) / 60) -
                        CASE 
                            WHEN l.start_kerja::date = l.end_kerja::date THEN
                                GREATEST(0, 
                                    LEAST(
                                        EXTRACT(EPOCH FROM (
                                            LEAST(l.end_kerja, (l.start_kerja::date + INTERVAL '13 hours')) -
                                            GREATEST(l.start_kerja, (l.start_kerja::date + INTERVAL '12 hours'))
                                        )) / 60,
                                        60
                                    )
                                )
                            ELSE 0
                        END
                END AS effective_work_minutes
                
            FROM logbooks l
            WHERE l.deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_logbook_work_duration");
    }
};
```

### Migration 8: Add Database Indexes

```php
<?php
// 2026_03_21_000008_add_performance_indexes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_logbooks_user_status');
            $table->index(['user_id', 'tanggal'], 'idx_logbooks_user_tanggal');
            $table->index('tanggal', 'idx_logbooks_tanggal');
        });

        Schema::table('logbook_kpi_details', function (Blueprint $table) {
            $table->index('logbook_id', 'idx_kpi_details_logbook');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('manager_id', 'idx_users_manager');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'idx_notifications_user_read');
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropIndex('idx_logbooks_user_status');
            $table->dropIndex('idx_logbooks_user_tanggal');
            $table->dropIndex('idx_logbooks_tanggal');
        });

        Schema::table('logbook_kpi_details', function (Blueprint $table) {
            $table->dropIndex('idx_kpi_details_logbook');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_manager');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_read');
        });
    }
};
```

### Migration 9: Create Daily Staff Summaries Table

```php
<?php
// 2026_03_21_000009_create_daily_staff_summaries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_staff_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->date('date');
            $table->integer('total_work_minutes')->default(0);
            $table->integer('logbook_count')->default(0);
            $table->integer('accepted_count')->default(0);
            $table->integer('rejected_count')->default(0);
            $table->integer('pending_count')->default(0);
            $table->decimal('avg_rating', 3, 2)->nullable();
            $table->decimal('total_kpi_achieved', 10, 2)->default(0);
            $table->decimal('total_kpi_target', 10, 2)->default(0);
            $table->decimal('overall_kpi_percentage', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'date'], 'unique_user_date_summary');
            $table->index('date', 'idx_daily_summaries_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_staff_summaries');
    }
};
```

### Migration 10: Create Daily KPI Summaries Table

```php
<?php
// 2026_03_21_000010_create_daily_kpi_summaries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_kpi_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->date('date');
            $table->uuid('kpi_id');
            $table->string('kpi_nama');
            $table->decimal('total_achieved', 10, 2)->default(0);
            $table->decimal('total_target', 10, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kpi_id')->references('id')->on('kpi_masters')->onDelete('cascade');
            $table->unique(['user_id', 'date', 'kpi_id'], 'unique_user_date_kpi_summary');
            $table->index(['user_id', 'date'], 'idx_kpi_summaries_user_date');
            $table->index('date', 'idx_kpi_summaries_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_kpi_summaries');
    }
};
```

---

## 3. Data Migration Script

For existing production data, run this script after migrations:

```php
<?php
// database/seeders/MigrateExistingDataSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateExistingDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Migrate attachments from logbooks to logbook_kpi_details
        $this->migrateAttachments();
        
        // 2. Update existing KPI masters with default target
        $this->setDefaultKpiTargets();
        
        // 3. Copy KPI targets to existing logbook details
        $this->copyKpiTargetsToDetails();
        
        // 4. Populate daily_staff_summaries from existing data
        $this->populateDailyStaffSummaries();
        
        // 5. Populate daily_kpi_summaries from existing data
        $this->populateDailyKpiSummaries();
    }

    private function migrateAttachments(): void
    {
        // Get logbooks with gambar_bukti (before column dropped)
        // This would need to be run BEFORE migration 3 drops the column
        
        $logbooks = DB::table('logbooks')
            ->whereNotNull('gambar_bukti')
            ->where('gambar_bukti', '!=', '[]')
            ->get();

        foreach ($logbooks as $logbook) {
            $attachments = json_decode($logbook->gambar_bukti, true);
            
            if (!empty($attachments)) {
                // Distribute attachments to first KPI detail
                $firstDetail = DB::table('logbook_kpi_details')
                    ->where('logbook_id', $logbook->id)
                    ->first();
                
                if ($firstDetail) {
                    DB::table('logbook_kpi_details')
                        ->where('id', $firstDetail->id)
                        ->update(['lampiran_file' => json_encode($attachments)]);
                }
            }
        }
    }

    private function setDefaultKpiTargets(): void
    {
        DB::table('kpi_masters')
            ->whereNull('target_angka')
            ->orWhere('target_angka', 0)
            ->update([
                'target_angka' => 1,
                'satuan' => 'unit',
            ]);
    }

    private function copyKpiTargetsToDetails(): void
    {
        DB::statement("
            UPDATE logbook_kpi_details lkd
            SET target_angka = km.target_angka,
                satuan = km.satuan
            FROM kpi_masters km
            WHERE lkd.kpi_id = km.id
            AND (lkd.target_angka IS NULL OR lkd.target_angka = 0)
        ");
    }

    private function populateDailyStaffSummaries(): void
    {
        // Calculate and insert daily summaries from existing logbooks
        DB::statement("
            INSERT INTO daily_staff_summaries (
                id, user_id, date, total_work_minutes, logbook_count,
                accepted_count, rejected_count, pending_count,
                avg_rating, total_kpi_achieved, total_kpi_target,
                overall_kpi_percentage, created_at, updated_at
            )
            SELECT 
                gen_random_uuid(),
                l.user_id,
                l.tanggal,
                COALESCE(SUM(EXTRACT(EPOCH FROM (l.end_kerja - l.start_kerja)) / 60), 0)::integer,
                COUNT(l.id),
                COUNT(CASE WHEN l.status = 'ACCEPTED' THEN 1 END),
                COUNT(CASE WHEN l.status = 'REJECTED' THEN 1 END),
                COUNT(CASE WHEN l.status = 'SUBMITTED' THEN 1 END),
                AVG(l.rating),
                COALESCE(SUM(d.capaian_angka), 0),
                COALESCE(SUM(d.target_angka), 0),
                CASE 
                    WHEN SUM(d.target_angka) > 0 
                    THEN (SUM(d.capaian_angka) / SUM(d.target_angka)) * 100
                    ELSE 0
                END,
                NOW(),
                NOW()
            FROM logbooks l
            LEFT JOIN logbook_kpi_details d ON l.id = d.logbook_id
            WHERE l.deleted_at IS NULL
            GROUP BY l.user_id, l.tanggal
            ON CONFLICT (user_id, date) DO UPDATE SET
                total_work_minutes = EXCLUDED.total_work_minutes,
                logbook_count = EXCLUDED.logbook_count,
                accepted_count = EXCLUDED.accepted_count,
                rejected_count = EXCLUDED.rejected_count,
                pending_count = EXCLUDED.pending_count,
                avg_rating = EXCLUDED.avg_rating,
                total_kpi_achieved = EXCLUDED.total_kpi_achieved,
                total_kpi_target = EXCLUDED.total_kpi_target,
                overall_kpi_percentage = EXCLUDED.overall_kpi_percentage,
                updated_at = NOW()
        ");
    }

    private function populateDailyKpiSummaries(): void
    {
        // Calculate and insert daily KPI summaries from existing logbooks
        DB::statement("
            INSERT INTO daily_kpi_summaries (
                id, user_id, date, kpi_id, kpi_nama,
                total_achieved, total_target, percentage,
                created_at, updated_at
            )
            SELECT 
                gen_random_uuid(),
                l.user_id,
                l.tanggal,
                d.kpi_id,
                d.kpi_nama,
                SUM(d.capaian_angka),
                SUM(d.target_angka),
                CASE 
                    WHEN SUM(d.target_angka) > 0 
                    THEN (SUM(d.capaian_angka) / SUM(d.target_angka)) * 100
                    ELSE 0
                END,
                NOW(),
                NOW()
            FROM logbooks l
            JOIN logbook_kpi_details d ON l.id = d.logbook_id
            WHERE l.deleted_at IS NULL AND d.deleted_at IS NULL
            GROUP BY l.user_id, l.tanggal, d.kpi_id, d.kpi_nama
            ON CONFLICT (user_id, date, kpi_id) DO UPDATE SET
                kpi_nama = EXCLUDED.kpi_nama,
                total_achieved = EXCLUDED.total_achieved,
                total_target = EXCLUDED.total_target,
                percentage = EXCLUDED.percentage,
                updated_at = NOW()
        ");
    }
}
```

---

## 4. Migration Execution Order

```bash
# 1. Backup database
pg_dump -U username -d logbook_db > backup_before_migration.sql

# 2. Run migrations in order
php artisan migrate

# 3. Run data migration seeder (if needed)
php artisan db:seed --class=MigrateExistingDataSeeder

# 4. Verify data
php artisan tinker
>>> \App\Models\User::count()
>>> \App\Models\Logbook::where('status', 'ACCEPTED')->count()
>>> \App\Models\LogbookKpiDetail::whereNotNull('lampiran_file')->count()
>>> \App\Models\DailyStaffSummary::count()
>>> \App\Models\DailyKpiSummary::count()

# 5. Run tests
php artisan test
```

---

## 5. Rollback Plan

If migration fails:

```bash
# Rollback specific migration
php artisan migrate:rollback --step=1

# Rollback all new migrations (10 total)
php artisan migrate:rollback --step=10

# Restore from backup
psql -U username -d logbook_db < backup_before_migration.sql
```

---

## 6. Post-Migration Checklist

- [ ] All migrations executed successfully (10 migrations)
- [ ] Data migrated correctly (spot check records)
- [ ] No data loss (compare row counts)
- [ ] Indexes created
- [ ] View created and working
- [ ] Summary tables populated
- [ ] Application tests passing
- [ ] API endpoints working correctly
- [ ] LogbookObserver triggered on logbook changes

---

## 7. LogbookObserver Implementation

After migrations, implement the LogbookObserver to auto-update summaries:

```php
<?php
// app/Observers/LogbookObserver.php

namespace App\Observers;

use App\Models\Logbook;
use App\Services\DailySummaryService;

class LogbookObserver
{
    public function __construct(
        private DailySummaryService $summaryService
    ) {}

    public function created(Logbook $logbook): void
    {
        $this->recalculateSummaries($logbook);
    }

    public function updated(Logbook $logbook): void
    {
        $this->recalculateSummaries($logbook);
        
        // If date changed, recalculate old date too
        if ($logbook->isDirty('tanggal')) {
            $oldDate = $logbook->getOriginal('tanggal');
            $this->summaryService->recalculate($logbook->user_id, $oldDate);
        }
    }

    public function deleted(Logbook $logbook): void
    {
        $this->recalculateSummaries($logbook);
    }

    private function recalculateSummaries(Logbook $logbook): void
    {
        $this->summaryService->recalculate(
            $logbook->user_id,
            $logbook->tanggal
        );
    }
}
```

---

**Next Document**: `05-GAP-ANALYSIS.md` - Gap analysis current vs target implementation
