<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->migrateUsersTable();
        $this->migrateKpiMastersTable();
        $this->migrateLogbooksTable();
        $this->migrateLogbookKpiDetailsTable();
        $this->migrateNotificationsTable();
        $this->createSummaryTables();
        $this->createWorkDurationView();
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_logbook_work_duration');

        Schema::dropIfExists('daily_kpi_summaries');
        Schema::dropIfExists('daily_staff_summaries');

        if (Schema::hasTable('notifications') && ! Schema::hasColumn('notifications', 'read_at')) {
            Schema::table('notifications', function (Blueprint $table): void {
                $table->timestamp('read_at')->nullable()->after('is_read');
            });
        }

        if (Schema::hasTable('logbook_kpi_details') && ! Schema::hasColumn('logbook_kpi_details', 'is_finished')) {
            Schema::table('logbook_kpi_details', function (Blueprint $table): void {
                $table->boolean('is_finished')->default(false)->after('kpi_nama');
            });

            DB::table('logbook_kpi_details')
                ->whereNull('deleted_at')
                ->update([
                    'is_finished' => DB::raw('COALESCE(capaian_angka, 0) > 0'),
                ]);
        }
    }

    private function migrateUsersTable(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'name') && ! Schema::hasColumn('users', 'nama')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->renameColumn('name', 'nama');
            });
        }

        if (Schema::hasColumn('users', 'nip') && ! Schema::hasColumn('users', 'npp')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->renameColumn('nip', 'npp');
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'foto')) {
                $table->string('foto')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('foto');
            }

            if (! Schema::hasColumn('users', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            }

            if (! Schema::hasColumn('users', 'nik')) {
                $table->string('nik')->nullable()->after('tanggal_lahir');
            }

            if (! Schema::hasColumn('users', 'npwp')) {
                $table->string('npwp')->nullable()->after('nik');
            }

            if (! Schema::hasColumn('users', 'alamat')) {
                $table->text('alamat')->nullable()->after('npwp');
            }

            if (! Schema::hasColumn('users', 'status_kawin')) {
                $table->string('status_kawin')->nullable()->after('alamat');
            }

            if (! Schema::hasColumn('users', 'riwayat_pendidikan')) {
                $table->json('riwayat_pendidikan')->nullable()->after('status_kawin');
            }

            if (! Schema::hasColumn('users', 'riwayat_karir')) {
                $table->json('riwayat_karir')->nullable()->after('riwayat_pendidikan');
            }
        });

        DB::statement("UPDATE users SET role = CASE UPPER(role) WHEN 'SUPERADMIN' THEN 'SuperAdmin' WHEN 'ADMIN' THEN 'Admin' WHEN 'MANAGER' THEN 'Manager' WHEN 'STAFF' THEN 'Staff' ELSE role END");

        Schema::table('users', function (Blueprint $table): void {
            if (! $this->hasIndex('users', 'users_role_manager_idx')) {
                $table->index(['role', 'manager_id'], 'users_role_manager_idx');
            }
        });
    }

    private function migrateKpiMastersTable(): void
    {
        if (! Schema::hasTable('kpi_masters')) {
            return;
        }

        Schema::table('kpi_masters', function (Blueprint $table): void {
            if (! Schema::hasColumn('kpi_masters', 'target_angka')) {
                $table->decimal('target_angka', 14, 2)->nullable()->after('nama');
            }

            if (! Schema::hasColumn('kpi_masters', 'satuan')) {
                $table->string('satuan', 100)->nullable()->after('target_angka');
            }

            if (! Schema::hasColumn('kpi_masters', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('satuan');
            }

            if (! $this->hasIndex('kpi_masters', 'kpi_masters_status_deleted_idx')) {
                $table->index(['status_aktif', 'deleted_at'], 'kpi_masters_status_deleted_idx');
            }
        });
    }

    private function migrateLogbooksTable(): void
    {
        if (! Schema::hasTable('logbooks')) {
            return;
        }

        $driver = DB::getDriverName();

        Schema::table('logbooks', function (Blueprint $table): void {
            if (! Schema::hasColumn('logbooks', 'tanggal')) {
                $table->date('tanggal')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('logbooks', 'reviewer_comment')) {
                $table->text('reviewer_comment')->nullable()->after('reviewed_at');
            }

            if (! Schema::hasColumn('logbooks', 'lokasi')) {
                $table->text('lokasi')->nullable()->after('end_kerja');
            }

            if (! Schema::hasColumn('logbooks', 'start_kerja_time')) {
                $table->time('start_kerja_time')->nullable()->after('start_kerja');
            }

            if (! Schema::hasColumn('logbooks', 'end_kerja_time')) {
                $table->time('end_kerja_time')->nullable()->after('end_kerja');
            }

            if (! Schema::hasColumn('logbooks', 'status_new')) {
                $table->enum('status_new', ['DRAFT', 'SUBMITTED', 'ACCEPTED', 'REJECTED'])->nullable()->after('status');
            }
        });

        if ($driver === 'pgsql') {
            DB::statement('UPDATE logbooks SET tanggal = COALESCE(tanggal, start_kerja::date) WHERE tanggal IS NULL AND start_kerja IS NOT NULL');
            DB::statement('UPDATE logbooks SET start_kerja_time = COALESCE(start_kerja_time, start_kerja::time) WHERE start_kerja IS NOT NULL');
            DB::statement('UPDATE logbooks SET end_kerja_time = COALESCE(end_kerja_time, end_kerja::time) WHERE end_kerja IS NOT NULL');
        } else {
            DB::statement('UPDATE logbooks SET tanggal = COALESCE(tanggal, DATE(start_kerja)) WHERE tanggal IS NULL AND start_kerja IS NOT NULL');
            DB::statement('UPDATE logbooks SET start_kerja_time = COALESCE(start_kerja_time, TIME(start_kerja)) WHERE start_kerja IS NOT NULL');
            DB::statement('UPDATE logbooks SET end_kerja_time = COALESCE(end_kerja_time, TIME(end_kerja)) WHERE end_kerja IS NOT NULL');
        }

        DB::statement("UPDATE logbooks SET status_new = CASE status WHEN 'REVIEWED' THEN 'ACCEPTED' WHEN 'DRAFT' THEN 'DRAFT' WHEN 'SUBMITTED' THEN 'SUBMITTED' WHEN 'REJECTED' THEN 'REJECTED' WHEN 'ACCEPTED' THEN 'ACCEPTED' ELSE 'DRAFT' END");
        DB::statement('UPDATE logbooks SET lokasi = COALESCE(lokasi, lokasi_end, lokasi_start)');

        Schema::table('logbooks', function (Blueprint $table): void {
            if (Schema::hasColumn('logbooks', 'start_kerja')) {
                $table->dropColumn('start_kerja');
            }

            if (Schema::hasColumn('logbooks', 'end_kerja')) {
                $table->dropColumn('end_kerja');
            }

            if (Schema::hasColumn('logbooks', 'lokasi_start')) {
                $table->dropColumn('lokasi_start');
            }

            if (Schema::hasColumn('logbooks', 'lokasi_end')) {
                $table->dropColumn('lokasi_end');
            }

            if (Schema::hasColumn('logbooks', 'gambar_bukti')) {
                $table->dropColumn('gambar_bukti');
            }

            if (Schema::hasColumn('logbooks', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('logbooks', function (Blueprint $table): void {
            if (Schema::hasColumn('logbooks', 'start_kerja_time')) {
                $table->renameColumn('start_kerja_time', 'start_kerja');
            }

            if (Schema::hasColumn('logbooks', 'end_kerja_time')) {
                $table->renameColumn('end_kerja_time', 'end_kerja');
            }

            if (Schema::hasColumn('logbooks', 'status_new')) {
                $table->renameColumn('status_new', 'status');
            }
        });

        Schema::table('logbooks', function (Blueprint $table): void {
            if (! $this->hasIndex('logbooks', 'logbooks_user_tanggal_idx')) {
                $table->index(['user_id', 'tanggal'], 'logbooks_user_tanggal_idx');
            }

            if (! $this->hasIndex('logbooks', 'logbooks_status_tanggal_idx')) {
                $table->index(['status', 'tanggal'], 'logbooks_status_tanggal_idx');
            }

            if (! $this->hasIndex('logbooks', 'logbooks_reviewer_status_tanggal_idx')) {
                $table->index(['reviewed_by', 'status', 'tanggal'], 'logbooks_reviewer_status_tanggal_idx');
            }
        });
    }

    private function migrateLogbookKpiDetailsTable(): void
    {
        if (! Schema::hasTable('logbook_kpi_details')) {
            return;
        }

        $driver = DB::getDriverName();

        Schema::table('logbook_kpi_details', function (Blueprint $table): void {
            if (! Schema::hasColumn('logbook_kpi_details', 'target_angka')) {
                $table->decimal('target_angka', 14, 2)->nullable()->after('kpi_nama');
            }

            if (! Schema::hasColumn('logbook_kpi_details', 'satuan')) {
                $table->string('satuan', 100)->nullable()->after('target_angka');
            }

            if (! Schema::hasColumn('logbook_kpi_details', 'capaian_angka')) {
                $table->decimal('capaian_angka', 14, 2)->nullable()->after('satuan');
            }

            if (! Schema::hasColumn('logbook_kpi_details', 'lampiran_file')) {
                $table->string('lampiran_file')->nullable()->after('capaian_angka');
            }
        });

        if ($driver === 'pgsql') {
            DB::statement("UPDATE logbook_kpi_details d SET target_angka = COALESCE(d.target_angka, k.target_angka, 1), satuan = COALESCE(d.satuan, k.satuan, 'unit') FROM kpi_masters k WHERE d.kpi_id = k.id");
        } elseif ($driver === 'sqlite') {
            DB::statement("UPDATE logbook_kpi_details SET target_angka = COALESCE(target_angka, (SELECT km.target_angka FROM kpi_masters km WHERE km.id = logbook_kpi_details.kpi_id), 1), satuan = COALESCE(satuan, (SELECT km.satuan FROM kpi_masters km WHERE km.id = logbook_kpi_details.kpi_id), 'unit')");
        } else {
            DB::statement("UPDATE logbook_kpi_details d JOIN kpi_masters k ON k.id = d.kpi_id SET d.target_angka = COALESCE(d.target_angka, k.target_angka, 1), d.satuan = COALESCE(d.satuan, k.satuan, 'unit')");
        }

        DB::statement('UPDATE logbook_kpi_details SET target_angka = COALESCE(target_angka, 1)');
        DB::statement('UPDATE logbook_kpi_details SET capaian_angka = CASE WHEN is_finished THEN COALESCE(target_angka, 1) ELSE 0 END WHERE capaian_angka IS NULL');

        Schema::table('logbook_kpi_details', function (Blueprint $table): void {
            if (Schema::hasColumn('logbook_kpi_details', 'is_finished')) {
                $table->dropColumn('is_finished');
            }

            if (! $this->hasIndex('logbook_kpi_details', 'logbook_kpi_details_logbook_kpi_idx')) {
                $table->index(['logbook_id', 'kpi_id'], 'logbook_kpi_details_logbook_kpi_idx');
            }

            if (! $this->hasIndex('logbook_kpi_details', 'logbook_kpi_details_kpi_finished_idx')) {
                $table->index(['kpi_id', 'finished_at'], 'logbook_kpi_details_kpi_finished_idx');
            }
        });
    }

    private function migrateNotificationsTable(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table): void {
            if (Schema::hasColumn('notifications', 'read_at')) {
                $table->dropColumn('read_at');
            }

            if (! $this->hasIndex('notifications', 'notifications_user_read_created_idx')) {
                $table->index(['user_id', 'is_read', 'created_at'], 'notifications_user_read_created_idx');
            }

            if (! $this->hasIndex('notifications', 'notifications_type_created_idx')) {
                $table->index(['type', 'created_at'], 'notifications_type_created_idx');
            }
        });
    }

    private function createSummaryTables(): void
    {
        if (! Schema::hasTable('daily_staff_summaries')) {
            Schema::create('daily_staff_summaries', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users');
                $table->date('tanggal');
                $table->unsignedInteger('total_logbooks')->default(0);
                $table->unsignedInteger('submitted_logbooks')->default(0);
                $table->unsignedInteger('accepted_logbooks')->default(0);
                $table->unsignedInteger('rejected_logbooks')->default(0);
                $table->unsignedInteger('total_work_minutes')->default(0);
                $table->unsignedInteger('total_kpi')->default(0);
                $table->decimal('target_angka_total', 14, 2)->default(0);
                $table->decimal('capaian_angka_total', 14, 2)->default(0);
                $table->decimal('progress_percent', 5, 2)->default(0);
                $table->timestamps();

                $table->unique(['user_id', 'tanggal'], 'daily_staff_summaries_user_tanggal_unique');
                $table->index(['tanggal', 'accepted_logbooks'], 'daily_staff_summaries_tanggal_accepted_idx');
            });
        }

        if (! Schema::hasTable('daily_kpi_summaries')) {
            Schema::create('daily_kpi_summaries', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users');
                $table->foreignUuid('kpi_id')->constrained('kpi_masters');
                $table->date('tanggal');
                $table->string('kpi_nama');
                $table->string('satuan', 100)->nullable();
                $table->decimal('target_angka_total', 14, 2)->default(0);
                $table->decimal('capaian_angka_total', 14, 2)->default(0);
                $table->decimal('progress_percent', 5, 2)->default(0);
                $table->unsignedInteger('total_lampiran')->default(0);
                $table->timestamps();

                $table->unique(['user_id', 'kpi_id', 'tanggal'], 'daily_kpi_summaries_user_kpi_tanggal_unique');
                $table->index(['kpi_id', 'tanggal'], 'daily_kpi_summaries_kpi_tanggal_idx');
                $table->index(['tanggal', 'progress_percent'], 'daily_kpi_summaries_tanggal_progress_idx');
            });
        }
    }

    private function createWorkDurationView(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_logbook_work_duration');

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement(<<<'SQL'
                CREATE VIEW v_logbook_work_duration AS
                SELECT
                    src.id AS logbook_id,
                    src.user_id,
                    src.tanggal,
                    src.start_kerja,
                    src.end_kerja,
                    src.status,
                    CASE
                        WHEN src.end_kerja IS NULL THEN 0
                        ELSE GREATEST(FLOOR(EXTRACT(EPOCH FROM (src.work_end - src.work_start)) / 60)::INT, 0)
                    END AS gross_work_minutes,
                    CASE
                        WHEN src.end_kerja IS NULL THEN 0
                        ELSE GREATEST(FLOOR(EXTRACT(EPOCH FROM (LEAST(src.work_end, src.break_end) - GREATEST(src.work_start, src.break_start))) / 60)::INT, 0)
                    END AS break_overlap_minutes,
                    CASE
                        WHEN src.end_kerja IS NULL THEN 0
                        ELSE GREATEST(
                            FLOOR(EXTRACT(EPOCH FROM (src.work_end - src.work_start)) / 60)::INT
                            - GREATEST(FLOOR(EXTRACT(EPOCH FROM (LEAST(src.work_end, src.break_end) - GREATEST(src.work_start, src.break_start))) / 60)::INT, 0),
                            0
                        )
                    END AS net_work_minutes
                FROM (
                    SELECT
                        l.*,
                        (l.tanggal::timestamp + l.start_kerja) AS work_start,
                        (
                            CASE
                                WHEN l.end_kerja >= l.start_kerja THEN (l.tanggal::timestamp + l.end_kerja)
                                ELSE (l.tanggal::timestamp + l.end_kerja + INTERVAL '1 day')
                            END
                        ) AS work_end,
                        (l.tanggal::timestamp + TIME '12:00:00') AS break_start,
                        (l.tanggal::timestamp + TIME '13:00:00') AS break_end
                    FROM logbooks l
                    WHERE l.deleted_at IS NULL
                ) AS src
            SQL);

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement(<<<'SQL'
                CREATE VIEW v_logbook_work_duration AS
                SELECT
                    src.logbook_id,
                    src.user_id,
                    src.tanggal,
                    src.start_kerja,
                    src.end_kerja,
                    src.status,
                    CASE
                        WHEN src.end_kerja IS NULL THEN 0
                        ELSE MAX(CAST((julianday(src.work_end) - julianday(src.work_start)) * 1440 AS INTEGER), 0)
                    END AS gross_work_minutes,
                    CASE
                        WHEN src.end_kerja IS NULL THEN 0
                        ELSE MAX(CAST((julianday(MIN(src.work_end, src.break_end)) - julianday(MAX(src.work_start, src.break_start))) * 1440 AS INTEGER), 0)
                    END AS break_overlap_minutes,
                    CASE
                        WHEN src.end_kerja IS NULL THEN 0
                        ELSE MAX(
                            MAX(CAST((julianday(src.work_end) - julianday(src.work_start)) * 1440 AS INTEGER), 0)
                            - MAX(CAST((julianday(MIN(src.work_end, src.break_end)) - julianday(MAX(src.work_start, src.break_start))) * 1440 AS INTEGER), 0),
                            0
                        )
                    END AS net_work_minutes
                FROM (
                    SELECT
                        l.id AS logbook_id,
                        l.user_id,
                        l.tanggal,
                        l.start_kerja,
                        l.end_kerja,
                        l.status,
                        datetime(l.tanggal || ' ' || l.start_kerja) AS work_start,
                        CASE
                            WHEN l.end_kerja >= l.start_kerja THEN datetime(l.tanggal || ' ' || l.end_kerja)
                            ELSE datetime(l.tanggal || ' ' || l.end_kerja, '+1 day')
                        END AS work_end,
                        datetime(l.tanggal || ' 12:00:00') AS break_start,
                        datetime(l.tanggal || ' 13:00:00') AS break_end
                    FROM logbooks l
                    WHERE l.deleted_at IS NULL
                ) AS src
            SQL);

            return;
        }

        DB::statement(<<<'SQL'
            CREATE VIEW v_logbook_work_duration AS
            SELECT
                l.id AS logbook_id,
                l.user_id,
                l.tanggal,
                l.start_kerja,
                l.end_kerja,
                l.status,
                CASE
                    WHEN l.end_kerja IS NULL THEN 0
                    ELSE GREATEST(
                        TIMESTAMPDIFF(
                            MINUTE,
                            TIMESTAMP(l.tanggal, l.start_kerja),
                            CASE
                                WHEN l.end_kerja >= l.start_kerja THEN TIMESTAMP(l.tanggal, l.end_kerja)
                                ELSE DATE_ADD(TIMESTAMP(l.tanggal, l.end_kerja), INTERVAL 1 DAY)
                            END
                        ),
                        0
                    )
                END AS gross_work_minutes,
                CASE
                    WHEN l.end_kerja IS NULL THEN 0
                    ELSE GREATEST(
                        TIMESTAMPDIFF(
                            MINUTE,
                            GREATEST(TIMESTAMP(l.tanggal, l.start_kerja), TIMESTAMP(l.tanggal, '12:00:00')),
                            LEAST(
                                CASE
                                    WHEN l.end_kerja >= l.start_kerja THEN TIMESTAMP(l.tanggal, l.end_kerja)
                                    ELSE DATE_ADD(TIMESTAMP(l.tanggal, l.end_kerja), INTERVAL 1 DAY)
                                END,
                                TIMESTAMP(l.tanggal, '13:00:00')
                            )
                        ),
                        0
                    )
                END AS break_overlap_minutes,
                CASE
                    WHEN l.end_kerja IS NULL THEN 0
                    ELSE GREATEST(
                        TIMESTAMPDIFF(
                            MINUTE,
                            TIMESTAMP(l.tanggal, l.start_kerja),
                            CASE
                                WHEN l.end_kerja >= l.start_kerja THEN TIMESTAMP(l.tanggal, l.end_kerja)
                                ELSE DATE_ADD(TIMESTAMP(l.tanggal, l.end_kerja), INTERVAL 1 DAY)
                            END
                        )
                        - GREATEST(
                            TIMESTAMPDIFF(
                                MINUTE,
                                GREATEST(TIMESTAMP(l.tanggal, l.start_kerja), TIMESTAMP(l.tanggal, '12:00:00')),
                                LEAST(
                                    CASE
                                        WHEN l.end_kerja >= l.start_kerja THEN TIMESTAMP(l.tanggal, l.end_kerja)
                                        ELSE DATE_ADD(TIMESTAMP(l.tanggal, l.end_kerja), INTERVAL 1 DAY)
                                    END,
                                    TIMESTAMP(l.tanggal, '13:00:00')
                                )
                            ),
                            0
                        ),
                        0
                    )
                END AS net_work_minutes
            FROM logbooks l
            WHERE l.deleted_at IS NULL
        SQL);
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $schema = DB::getConfig('schema') ?? 'public';
            $result = DB::selectOne('SELECT 1 FROM pg_indexes WHERE schemaname = ? AND tablename = ? AND indexname = ? LIMIT 1', [
                $schema,
                $table,
                $indexName,
            ]);

            return $result !== null;
        }

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('{$table}')");

            foreach ($rows as $row) {
                if (($row->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $rows = DB::select("SHOW INDEX FROM {$table}");
        foreach ($rows as $row) {
            if (($row->Key_name ?? null) === $indexName) {
                return true;
            }
        }

        return false;
    }
};
