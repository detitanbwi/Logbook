<?php

$userModel = __DIR__.'/app/Models/User.php';
$code = file_get_contents($userModel);
// Replace the erroneous double `{` block
$code = str_replace("class User extends Authenticatable\n{\n    use HasUuids, SoftDeletes;\n{", "class User extends Authenticatable\n{\n    use HasUuids, SoftDeletes;\n", $code);
// Fix the doubled imports
$code = str_replace("use HasFactory, Notifiable, \Illuminate\Database\Eloquent\Concerns\HasUuids, \Illuminate\Database\Eloquent\SoftDeletes;\n", "use HasFactory, Notifiable;\n", $code);

file_put_contents($userModel, $code);
echo "Fixed\n";
