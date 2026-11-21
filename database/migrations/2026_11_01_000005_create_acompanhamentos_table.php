<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acompanhamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('data');
            $table->unsignedSmallInteger('idade_dias');
            $table->unsignedInteger('mortalidade')->default(0);
            $table->unsignedInteger('eliminados')->default(0);
            $table->decimal('consumo_racao_kg', 10, 2)->default(0);
            $table->decimal('peso_medio_g', 8, 2)->nullable();
            $table->decimal('temperatura_c', 4, 1)->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();

            // RN03: um único acompanhamento por lote em cada data.
            $table->unique(['lote_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acompanhamentos');
    }
};
