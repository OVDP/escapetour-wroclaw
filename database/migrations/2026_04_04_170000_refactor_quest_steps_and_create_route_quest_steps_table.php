<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quest_steps', function (Blueprint $table): void {
            if (Schema::hasColumn('quest_steps', 'game_route_id')) {
                $table->dropConstrainedForeignId('game_route_id');
            }

            if (Schema::hasColumn('quest_steps', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });

        Schema::create('route_quest_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_route_id')->constrained('game_routes')->cascadeOnDelete();
            $table->foreignId('quest_step_id')->constrained('quest_steps')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['game_route_id', 'quest_step_id']);
            $table->unique(['game_route_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_quest_steps');

        Schema::table('quest_steps', function (Blueprint $table): void {
            if (! Schema::hasColumn('quest_steps', 'game_route_id')) {
                $table->foreignId('game_route_id')
                    ->nullable()
                    ->constrained('game_routes')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('quest_steps', 'sort_order')) {
                $table->integer('sort_order')->default(1);
            }
        });
    }
};
