<?php

$userModel = __DIR__.'/app/Models/User.php';
$code = file_get_contents($userModel);
$code = str_replace(
    "use Illuminate\Notifications\Notifiable;",
    "use Illuminate\Notifications\Notifiable;\nuse Illuminate\Database\Eloquent\Concerns\HasUuids;\nuse Illuminate\Database\Eloquent\SoftDeletes;",
    $code
);
file_put_contents($userModel, $code);
echo "Fixed 2\n";
