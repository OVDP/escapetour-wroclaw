<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_route_id')->constrained('game_routes')->cascadeOnDelete();
            $table->unsignedInteger('current_sort_order')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique('game_route_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_progress');
    }
};
