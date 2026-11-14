<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $steps = DB::table('quest_steps')
            ->whereIn('game_id', DB::table('games')->where('slug', 'red-de-zabka-kikker')->select('id'))
            ->where('title', '09 · Nicolaus Copernicus Park')->get();
        foreach ($steps as $step) {
            DB::table('quest_steps')->where('id', $step->id)->update([
                'step_type' => 'text_answer',
                'question_type' => 'direct',
                'manual_approval' => false,
                'manual_approval_required' => false,
                'accepts_whatsapp_upload' => false,
                'correct_answer' => 'Het Zwanenmeer',
                'answer_text' => 'Het Zwanenmeer',
                'question_text' => str_replace('Maak een selfie met dit beeld en geef de naam van het balletstuk.', 'Geef de naam van het balletstuk.', $step->question_text ?? ''),
                'review_notes' => 'Tekstinvoer met automatische antwoordcontrole: Het Zwanenmeer. Geen foto nodig.',
            ]);
        }
    }

    public function down(): void
    {
        // Preserve the corrected question and existing player progress.
    }
};
