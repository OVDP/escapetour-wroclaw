<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('game_routes', function (Blueprint $table) {
            $table->string('creator_device_hash', 64)->nullable()->unique();
            $table->boolean('start_assigned')->default(true);
            $table->foreignId('starting_quest_step_id')->nullable()->constrained('quest_steps')->nullOnDelete();
        });
        Schema::table('route_progress', fn (Blueprint $table) => $table->timestamp('location_received_at')->nullable());
    }
    public function down(): void {
        Schema::table('route_progress', fn (Blueprint $table) => $table->dropColumn('location_received_at'));
        Schema::table('game_routes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('starting_quest_step_id');
            $table->dropColumn(['creator_device_hash', 'start_assigned']);
        });
    }
};
