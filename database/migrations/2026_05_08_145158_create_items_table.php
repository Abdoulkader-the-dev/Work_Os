<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('todo');
            $table->string('priority')->default('moyenne');
            $table->date('deadline')->nullable();
            $table->text('deliverable')->nullable();
            $table->text('obstacles')->nullable();
            $table->integer('order')->default(0);
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Table pivot item ↔ user (assignés)
        Schema::create('item_user', function (Blueprint $table) {
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['item_id', 'user_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('item_user');
        Schema::dropIfExists('items');
    }
};