<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tickets')
            ->where('status', 'resolved')
            ->update([
                'status' => 'closed',
                'closed_at' => DB::raw('COALESCE(closed_at, updated_at, created_at)'),
            ]);
    }

    public function down(): void
    {
        // Los tickets convertidos no son distinguibles de los ya cerrados.
    }
};
