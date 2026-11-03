<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_steps', function (Blueprint $table): void {
            $table->text('story_intro')->nullable()->after('title');
            $table->text('question_text')->nullable()->after('story_intro');
            $table->text('extra_explanation')->nullable()->after('question_text');
            $table->string('question_type', 50)->nullable()->after('step_type');
            $table->text('correct_answer')->nullable()->after('correct_choice');
            $table->boolean('manual_approval')->default(false)->after('manual_approval_required');
            $table->unsignedInteger('timeout_seconds')->default(30)->after('cooldown_seconds');
            $table->text('reward_story_text')->nullable()->after('timeout_seconds');
            $table->string('reward_letter', 10)->nullable()->after('reward_story_text');
            $table->decimal('location_lat', 10, 7)->nullable()->after('requires_location');
            $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            $table->string('location_address')->nullable()->after('location_lng');
            $table->unsignedInteger('location_radius_meters')->nullable()->after('location_address');
            $table->boolean('show_question_only_when_in_radius')->default(false)->after('location_radius_meters');
        });

        Schema::table('route_progress', function (Blueprint $table): void {
            $table->json('unlocked_story_entries')->nullable()->after('last_seen_at');
            $table->json('hidden_collected_letters')->nullable()->after('unlocked_story_entries');
            $table->boolean('show_letters')->default(false)->after('hidden_collected_letters');
            $table->unsignedInteger('timeout_step_sort_order')->nullable()->after('show_letters');
            $table->unsignedInteger('timeout_remaining_seconds')->default(0)->after('timeout_step_sort_order');
            $table->timestamp('timeout_last_tick_at')->nullable()->after('timeout_remaining_seconds');
            $table->boolean('timeout_paused_state')->default(true)->after('timeout_last_tick_at');
            $table->unsignedInteger('location_locked_step_sort_order')->nullable()->after('timeout_paused_state');
            $table->decimal('location_locked_latitude', 10, 7)->nullable()->after('location_locked_step_sort_order');
            $table->decimal('location_locked_longitude', 10, 7)->nullable()->after('location_locked_latitude');
            $table->timestamp('location_locked_at')->nullable()->after('location_locked_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('route_progress', function (Blueprint $table): void {
            $table->dropColumn([
                'unlocked_story_entries',
                'hidden_collected_letters',
                'show_letters',
                'timeout_step_sort_order',
                'timeout_remaining_seconds',
                'timeout_last_tick_at',
                'timeout_paused_state',
                'location_locked_step_sort_order',
                'location_locked_latitude',
                'location_locked_longitude',
                'location_locked_at',
            ]);
        });

        Schema::table('quest_steps', function (Blueprint $table): void {
            $table->dropColumn([
                'story_intro',
                'question_text',
                'extra_explanation',
                'question_type',
                'correct_answer',
                'manual_approval',
                'timeout_seconds',
                'reward_story_text',
                'reward_letter',
                'location_lat',
                'location_lng',
                'location_address',
                'location_radius_meters',
                'show_question_only_when_in_radius',
            ]);
        });
    }
};
