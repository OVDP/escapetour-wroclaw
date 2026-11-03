<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->json('possible_suspects')->nullable()->after('case_briefing');
            $table->json('possible_locations')->nullable()->after('possible_suspects');
            $table->text('story_intro')->nullable()->after('possible_locations');
            $table->text('story_mid')->nullable()->after('story_intro');
            $table->text('story_final')->nullable()->after('story_mid');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->dropColumn(['possible_suspects', 'possible_locations', 'story_intro', 'story_mid', 'story_final']);
        });
    }
};
