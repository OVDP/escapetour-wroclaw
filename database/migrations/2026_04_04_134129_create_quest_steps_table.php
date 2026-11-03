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
Schema::create('quest_steps', function (Blueprint $table) {
    $table->id();
    $table->foreignId('game_id')->constrained()->cascadeOnDelete();
    $table->foreignId('game_route_id')->constrained('game_routes')->cascadeOnDelete();

    $table->string('title');
    $table->text('story_text')->nullable();

    $table->enum('step_type', [
        'text_answer',
        'multiple_choice',
        'location_check',
        'manual_approval',
        'photo_upload',
        'video_upload',
    ])->default('text_answer');

    $table->integer('sort_order')->default(1);

    $table->boolean('requires_location')->default(false);
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->integer('radius_meters')->nullable();

    $table->text('answer_text')->nullable();
    $table->json('multiple_choice_json')->nullable();
    $table->string('correct_choice')->nullable();

    $table->integer('cooldown_seconds')->default(0);
    $table->boolean('manual_approval_required')->default(false);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quest_steps');
    }
};
