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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Razón Social o Nombre Comercial
            $table->string('tax_id')->unique()->nullable(); // NIT, RUT, Identificación Fiscal
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('legal_representative')->nullable();
            $table->string('legal_representative_phone')->nullable();
            $table->boolean('is_active')->default(true); // Para poder suspender clientes si es necesario
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
