<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::table('quest_steps', fn (Blueprint $table) => $table->boolean('review_on_mismatch')->default(false));
        DB::table('quest_steps')->whereIn('game_id', DB::table('games')->where('slug','red-de-zabka-kikker')->select('id'))
            ->where(function ($q) { $q->where('title','like','04 ·%')->orWhere('title','like','09 ·%')->orWhere('title','like','12 ·%'); })
            ->update(['review_on_mismatch'=>true,'manual_approval'=>false,'manual_approval_required'=>false,'step_type'=>'text_answer','question_type'=>'direct']);
        DB::table('quest_steps')->update(['accepts_whatsapp_upload'=>false,'reward_letter'=>null,'reward_suspect_name'=>null]);
    }
    public function down(): void {
        Schema::table('quest_steps', fn (Blueprint $table) => $table->dropColumn('review_on_mismatch'));
    }
};
