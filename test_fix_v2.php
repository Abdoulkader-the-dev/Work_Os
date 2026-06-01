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
echo "Current Workspace ID: " . ($user->current_workspace_id ?? 'NULL') . "\n";

try {
    echo "Accessing currentWorkspace property...\n";
    $workspace = $user->currentWorkspace;
    echo "Current Workspace: " . ($workspace ? $workspace->name : "None") . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
