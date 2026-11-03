<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_steps', function (Blueprint $table): void {
            if (! Schema::hasColumn('quest_steps', 'accepts_whatsapp_upload')) {
                $table->boolean('accepts_whatsapp_upload')
                    ->default(false)
                    ->after('show_question_only_when_in_radius');
            }

            if (! Schema::hasColumn('quest_steps', 'location_submission_required')) {
                $table->boolean('location_submission_required')
                    ->default(false)
                    ->after('accepts_whatsapp_upload');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quest_steps', function (Blueprint $table): void {
            $drop = [];

            if (Schema::hasColumn('quest_steps', 'accepts_whatsapp_upload')) {
                $drop[] = 'accepts_whatsapp_upload';
            }

            if (Schema::hasColumn('quest_steps', 'location_submission_required')) {
                $drop[] = 'location_submission_required';
            }

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
