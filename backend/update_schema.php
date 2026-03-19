<?php

$migrationsDir = __DIR__.'/database/migrations/';
$modelsDir = __DIR__.'/app/Models/';

$usersMigration = glob($migrationsDir.'*_create_users_table.php')[0];
$usersMigrationContent = file_get_contents($usersMigration);
$usersMigrationContent = str_replace('$table->id();', '$table->uuid(\'id\')->primary();', $usersMigrationContent);
$usersMigrationContent = preg_replace(
    '/\$table->string\(\'email\'\)->unique\(\);/',
    "\$table->string('email')->unique();\n            \$table->string('nip')->unique()->nullable();\n            \$table->string('role')->default('Staff');\n            \$table->uuid('manager_id')->nullable();\n            \$table->timestamp('last_password_change')->nullable();\n            \$table->softDeletes();",
    $usersMigrationContent
);
file_put_contents($usersMigration, $usersMigrationContent);

$userModel = $modelsDir.'User.php';
$userModelContent = file_get_contents($userModel);
$userModelContent = str_replace('use HasFactory, Notifiable;', "use HasFactory, Notifiable, \Illuminate\Database\Eloquent\Concerns\HasUuids, \Illuminate\Database\Eloquent\SoftDeletes;\n", $userModelContent);
$userModelContent = str_replace('class User extends Authenticatable', "class User extends Authenticatable\n{\n    use HasUuids, SoftDeletes;", $userModelContent);
$userModelContent = preg_replace('/protected \$fillable = \[.*?\];/s', "protected \$fillable = [\n        'name', 'email', 'password', 'nip', 'role', 'manager_id', 'last_password_change',\n    ];", $userModelContent);
file_put_contents($userModel, $userModelContent);

// This script does the minimal string replacements. I'll run this to do the heavy lifting or simply tell the user the script is started.
echo "Users updated\n";
