<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_id')->nullable()->constrained('games')->nullOnDelete();
            $table->foreignId('game_route_id')->nullable()->constrained('game_routes')->nullOnDelete();
            $table->enum('audience', ['all_active', 'game', 'group'])->default('all_active');
            $table->string('title')->nullable();
            $table->text('message');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['audience', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_messages');
    }
};
