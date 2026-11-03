<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('quest_steps', function (Blueprint $table) {
            $table->string('question_image')->nullable();
            $table->json('acceptable_answers')->nullable();
            $table->text('review_notes')->nullable();
        });
        Schema::table('step_submissions', fn (Blueprint $table) => $table->string('photo_path')->nullable());
    }
    public function down(): void {
        Schema::table('quest_steps', fn (Blueprint $table) => $table->dropColumn(['question_image', 'acceptable_answers', 'review_notes']));
        Schema::table('step_submissions', fn (Blueprint $table) => $table->dropColumn('photo_path'));
    }
};
