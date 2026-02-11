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
        Schema::create('partes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')
                ->constrained('projetos')
                ->onDelete('cascade');
            $table->string('nome', 255);
            $table->foreignId('colaborador_id')
                ->nullable()
                ->constrained('colaboradores')
                ->onDelete('set null');
            $table->integer('extensao_desenho')->default(0);
            $table->integer('extensao_projeto')->default(0);
            $table->integer('postes_desenhados')->default(0);
            $table->integer('postes_projetados')->default(0);
            $table->timestamps();
            $table->index('projeto_id');
            $table->index('colaborador_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partes');
    }
};
