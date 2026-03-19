<?php

$migrationsDir = __DIR__.'/database/migrations/';
$modelsDir = __DIR__.'/app/Models/';

function updateMigration($filePattern, $content)
{
    global $migrationsDir;
    $files = glob($migrationsDir.'*_'.$filePattern.'.php');
    if (! $files) {
        return;
    }
    $file = $files[0];
    $code = file_get_contents($file);
    $code = preg_replace('/\$table->id\(\);/', '$table->uuid(\'id\')->primary();'."\n            ".$content, $code);
    file_put_contents($file, $code);
}

function updateModel($modelName, $casts, $relations)
{
    global $modelsDir;
    $file = $modelsDir.$modelName.'.php';
    $code = file_get_contents($file);
    $use = "use Illuminate\Database\Eloquent\Concerns\HasUuids;\nuse Illuminate\Database\Eloquent\SoftDeletes;\nuse Illuminate\Database\Eloquent\Relations\HasMany;\nuse Illuminate\Database\Eloquent\Relations\BelongsTo;\n";
    $code = preg_replace('/use HasFactory;/', $use.'use HasFactory, HasUuids'.(strpos($code, 'SoftDeletes') === false ? ', SoftDeletes;' : ';'), $code);

    $body = "\n    protected \$guarded = [];\n\n    protected function casts(): array\n    {\n        return [\n$casts\n        ];\n    }\n\n$relations\n";
    $code = preg_replace('/\{/', "{\n".$body, $code, 1);
    file_put_contents($file, $code);
}

updateMigration('create_kpi_masters_table', "\$table->string('nama');\n            \$table->boolean('status_aktif')->default(true);\n            \$table->softDeletes();");
updateModel('KpiMaster', "            'status_aktif' => 'boolean',", "    public function assignments(): HasMany\n    {\n        return \$this->hasMany(UserKpiAssignment::class, 'kpi_id');\n    }");

updateMigration('create_user_kpi_assignments_table', "\$table->foreignUuid('user_id')->constrained('users');\n            \$table->foreignUuid('kpi_id')->constrained('kpi_masters');\n            \$table->foreignUuid('assigned_by')->nullable()->constrained('users');");
updateModel('UserKpiAssignment', '', "    public function user(): BelongsTo\n    {\n        return \$this->belongsTo(User::class);\n    }\n\n    public function kpi(): BelongsTo\n    {\n        return \$this->belongsTo(KpiMaster::class, 'kpi_id');\n    }\n\n    public function assigner(): BelongsTo\n    {\n        return \$this->belongsTo(User::class, 'assigned_by');\n    }");

updateMigration('create_logbooks_table', "\$table->foreignUuid('user_id')->constrained('users');\n            \$table->timestamp('start_kerja');\n            \$table->timestamp('end_kerja')->nullable();\n            \$table->string('lokasi_start');\n            \$table->string('lokasi_end')->nullable();\n            \$table->jsonb('gambar_bukti')->nullable();\n            \$table->enum('status', ['DRAFT', 'SUBMITTED', 'REVIEWED'])->default('DRAFT');\n            \$table->integer('rating')->nullable();\n            \$table->foreignUuid('reviewed_by')->nullable()->constrained('users');\n            \$table->timestamp('reviewed_at')->nullable();\n            \$table->softDeletes();");
updateModel('Logbook', "            'start_kerja' => 'datetime',\n            'end_kerja' => 'datetime',\n            'reviewed_at' => 'datetime',\n            'gambar_bukti' => 'array',\n            'rating' => 'integer',", "    public function user(): BelongsTo\n    {\n        return \$this->belongsTo(User::class);\n    }\n\n    public function reviewer(): BelongsTo\n    {\n        return \$this->belongsTo(User::class, 'reviewed_by');\n    }\n\n    public function kpiDetails(): HasMany\n    {\n        return \$this->hasMany(LogbookKpiDetail::class);\n    }");

updateMigration('create_logbook_kpi_details_table', "\$table->foreignUuid('logbook_id')->constrained('logbooks');\n            \$table->foreignUuid('kpi_id')->constrained('kpi_masters');\n            \$table->string('kpi_nama');\n            \$table->boolean('is_finished')->default(false);\n            \$table->timestamp('finished_at')->nullable();");
updateModel('LogbookKpiDetail', "            'is_finished' => 'boolean',\n            'finished_at' => 'datetime',", "    public function logbook(): BelongsTo\n    {\n        return \$this->belongsTo(Logbook::class);\n    }\n\n    public function kpi(): BelongsTo\n    {\n        return \$this->belongsTo(KpiMaster::class, 'kpi_id');\n    }");

updateMigration('create_audit_logs_table', "\$table->string('table_name');\n            \$table->uuid('record_id');\n            \$table->string('action');\n            \$table->jsonb('old_data')->nullable();\n            \$table->jsonb('new_data')->nullable();\n            \$table->foreignUuid('performed_by')->nullable()->constrained('users');\n            \$table->timestamp('performed_at');\n            \$table->string('ip_address')->nullable();\n            \$table->string('user_agent')->nullable();");
updateModel('AuditLog', "            'old_data' => 'array',\n            'new_data' => 'array',\n            'performed_at' => 'datetime',", "    public function user(): BelongsTo\n    {\n        return \$this->belongsTo(User::class, 'performed_by');\n    }");

updateMigration('create_notifications_table', "\$table->foreignUuid('user_id')->constrained('users');\n            \$table->string('title');\n            \$table->text('message');\n            \$table->string('type');\n            \$table->uuid('reference_id')->nullable();\n            \$table->boolean('is_read')->default(false);\n            \$table->timestamp('read_at')->nullable();");
updateModel('Notification', "            'is_read' => 'boolean',\n            'read_at' => 'datetime',", "    public function user(): BelongsTo\n    {\n        return \$this->belongsTo(User::class);\n    }");

echo "Schema updated.\n";
