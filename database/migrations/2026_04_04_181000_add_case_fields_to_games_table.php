<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->string('incident_location')->nullable()->after('end_at');
            $table->timestamp('incident_at')->nullable()->after('incident_location');
            $table->text('case_briefing')->nullable()->after('incident_at');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->dropColumn(['incident_location', 'incident_at', 'case_briefing']);
        });
    }
};
