<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('closed_at')->nullable()->after('status');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->integer('time_spent_minutes')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('closed_at');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('time_spent_minutes');
        });
    }
};
