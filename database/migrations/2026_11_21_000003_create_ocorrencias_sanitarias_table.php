<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocorrencias_sanitarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('tipo', ['doenca', 'mortalidade_anormal', 'outro']);
            $table->text('descricao');
            $table->enum('status', ['aberta', 'resolvida'])->default('aberta');
            $table->date('data_abertura');
            $table->date('data_resolucao')->nullable();
            $table->timestamps();

            $table->index(['lote_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocorrencias_sanitarias');
    }
};
