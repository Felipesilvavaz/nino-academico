<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aviario_id')->constrained('aviarios')->restrictOnDelete();
            $table->foreignId('tecnico_id')->constrained('users')->restrictOnDelete();
            $table->string('codigo', 30)->unique();
            $table->string('linhagem', 50);
            $table->date('data_alojamento');
            $table->unsignedInteger('qtd_alojada');
            $table->decimal('peso_inicial_g', 8, 2)->default(42);
            $table->date('data_abate_prevista')->nullable();
            $table->date('data_encerramento')->nullable();
            $table->enum('status', ['aberto', 'encerrado', 'cancelado'])->default('aberto');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['aviario_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
