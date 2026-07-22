<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $types = [
            ['name' => 'General', 'description' => 'Tarea general', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Visita', 'description' => 'Visita en sitio', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mantenimiento', 'description' => 'Tarea de mantenimiento', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Desarrollo', 'description' => 'Tarea de desarrollo', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('task_types')->insert($types);

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('task_type_id')->nullable()->after('status')->constrained('task_types');
        });

        $typeMap = [
            'general' => 'General',
            'visit' => 'Visita',
            'maintenance' => 'Mantenimiento',
            'development' => 'Desarrollo',
        ];

        foreach ($typeMap as $oldValue => $name) {
            $record = DB::table('task_types')->where('name', $name)->first();
            if ($record) {
                DB::table('tasks')->where('type', $oldValue)->update(['task_type_id' => $record->id]);
            }
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('type', 20)->default('general')->after('status');
        });

        DB::table('task_types')->truncate();
        Schema::dropIfExists('task_types');
    }
};
