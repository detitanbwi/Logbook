<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_logbook_work_duration');
    }
};
