<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'caleb@unipod.com')->first();
if (!$user) {
    echo "User not found\n";
    exit(1);
}

echo "User: " . $user->name . "\n";
echo "activeWorkspace: " . ($user->activeWorkspace ? $user->activeWorkspace->name : 'NULL') . "\n";
echo "active_workspace: " . ($user->active_workspace ? $user->active_workspace->name : 'NULL') . "\n";
