<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_routes', function (Blueprint $table): void {
            $table->string('join_token', 32)->nullable()->unique()->after('description');
        });

        $routeIds = DB::table('game_routes')->pluck('id');

        foreach ($routeIds as $routeId) {
            do {
                $token = strtoupper(Str::random(10));
                $exists = DB::table('game_routes')->where('join_token', $token)->exists();
            } while ($exists);

            DB::table('game_routes')
                ->where('id', $routeId)
                ->update(['join_token' => $token]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_routes', function (Blueprint $table): void {
            $table->dropUnique(['join_token']);
            $table->dropColumn('join_token');
        });
    }
};
