<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('executed_at')->nullable()->after('closed_at');
        });

        DB::table('tickets')
            ->whereNotNull('closed_at')
            ->update(['executed_at' => DB::raw('closed_at')]);

        DB::table('tickets')
            ->whereNull('executed_at')
            ->where('status', 'in_progress')
            ->update(['executed_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('executed_at');
        });
    }
};