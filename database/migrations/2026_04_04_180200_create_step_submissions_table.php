<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('step_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_route_id')->constrained('game_routes')->cascadeOnDelete();
            $table->foreignId('route_player_id')->nullable()->constrained('route_players')->nullOnDelete();
            $table->foreignId('quest_step_id')->constrained('quest_steps')->cascadeOnDelete();
            $table->unsignedInteger('step_sort_order');
            $table->text('submitted_answer')->nullable();
            $table->string('submitted_choice')->nullable();
            $table->decimal('submitted_latitude', 10, 7)->nullable();
            $table->decimal('submitted_longitude', 10, 7)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'auto_passed', 'auto_failed'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['game_route_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('step_submissions');
    }
};
