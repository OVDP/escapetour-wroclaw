<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_steps', function (Blueprint $table): void {
            if (! Schema::hasColumn('quest_steps', 'reward_suspect_name')) {
                $table->string('reward_suspect_name')->nullable()->after('reward_letter');
            }
        });

        Schema::table('route_progress', function (Blueprint $table): void {
            if (! Schema::hasColumn('route_progress', 'unlocked_suspects')) {
                $table->json('unlocked_suspects')->nullable()->after('unlocked_story_entries');
            }
        });
    }

    public function down(): void
    {
        Schema::table('route_progress', function (Blueprint $table): void {
            if (Schema::hasColumn('route_progress', 'unlocked_suspects')) {
                $table->dropColumn('unlocked_suspects');
            }
        });

        Schema::table('quest_steps', function (Blueprint $table): void {
            if (Schema::hasColumn('quest_steps', 'reward_suspect_name')) {
                $table->dropColumn('reward_suspect_name');
            }
        });
    }
};
