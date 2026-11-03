<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_steps', function (Blueprint $table): void {
            if (! Schema::hasColumn('quest_steps', 'start_location_lat')) {
                $table->decimal('start_location_lat', 10, 7)->nullable()->after('show_question_only_when_in_radius');
            }
            if (! Schema::hasColumn('quest_steps', 'start_location_lng')) {
                $table->decimal('start_location_lng', 10, 7)->nullable()->after('start_location_lat');
            }
            if (! Schema::hasColumn('quest_steps', 'start_location_address')) {
                $table->string('start_location_address')->nullable()->after('start_location_lng');
            }
            if (! Schema::hasColumn('quest_steps', 'start_location_radius_meters')) {
                $table->unsignedInteger('start_location_radius_meters')->nullable()->after('start_location_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quest_steps', function (Blueprint $table): void {
            $drop = [];
            foreach (['start_location_lat', 'start_location_lng', 'start_location_address', 'start_location_radius_meters'] as $column) {
                if (Schema::hasColumn('quest_steps', $column)) {
                    $drop[] = $column;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
