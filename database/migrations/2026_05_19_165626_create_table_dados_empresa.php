<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('dados_empresa', function (Blueprint $table) {
            $table->id();
            $table->string('Informacao');
            $table->string("Valor")->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('dados_empresa');
    }
};

