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
        Schema::create('SecureKey_tipo_usuario', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('tipo_usuario_id')->constrained('tipo_usuarios');
            $table->foreignId('role_id')->constrained('roles');

            $table->unique(['tipo_usuario_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('SecureKey_tipo_usuario');
    }
};
