<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('mesa_conta_itens');
    }

    public function down(): void
    {
        // Sem rollback: a tabela foi descontinuada.
    }
};
