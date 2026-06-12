<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Creamos la llave foránea opcional. 
            // Si una compañía se elimina, ponemos este campo en NULL para no borrar al usuario.
            $table->foreignId('company_id')
                  ->nullable()
                  ->after('id') // Lo posiciona visualmente después del ID en DBeaver
                  ->constrained('companies')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
