<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_steps', function (Blueprint $table): void {
            $table->string('clue_category')->nullable()->after('story_text');
            $table->text('clue_reveal_text')->nullable()->after('clue_category');
        });
    }

    public function down(): void
    {
        Schema::table('quest_steps', function (Blueprint $table): void {
            $table->dropColumn(['clue_category', 'clue_reveal_text']);
        });
    }
};
