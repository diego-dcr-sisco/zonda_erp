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
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'category')) {
                // Agregar columna 'category' como string (CHAR sería mejor para valores cortos como 't', 'w', etc.)
                // Si sabes el tamaño máximo de la categoría, usa char(length) o varchar(length)
                $table->string('category', 10)->nullable()->after('name');
                // O si es un solo carácter como en tu ejemplo:
                // $table->char('category', 1)->nullable()->after('name');
            }
            
            // También podrías necesitar agregar 'type' si no existe
            if (!Schema::hasColumn('permissions', 'type')) {
                $table->string('type', 10)->nullable()->after('category');
                // O si es un solo carácter:
                // $table->char('type', 1)->nullable()->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['category', 'type']);
        });
    }
};