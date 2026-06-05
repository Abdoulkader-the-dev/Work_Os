<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignId('workspace_id')
                ->nullable()
                ->after('date')
                ->constrained()
                ->nullOnDelete();
        });

        DB::table('meetings')
            ->orderBy('id')
            ->get()
            ->each(function ($meeting) {
                $workspaceId = DB::table('users')
                    ->where('id', $meeting->user_id)
                    ->value('current_workspace_id');

                if (!$workspaceId) {
                    $workspaceId = DB::table('workspace_user')
                        ->where('user_id', $meeting->user_id)
                        ->value('workspace_id');
                }

                if ($workspaceId) {
                    DB::table('meetings')
                        ->where('id', $meeting->id)
                        ->update(['workspace_id' => $workspaceId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workspace_id');
        });
    }
};
