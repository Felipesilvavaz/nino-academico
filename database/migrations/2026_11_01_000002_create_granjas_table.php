<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('granjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produtor_id')->constrained('produtores')->restrictOnDelete();
            $table->string('nome');
            $table->string('endereco')->nullable();
            $table->string('cidade', 100);
            $table->char('uf', 2);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('granjas');
    }
};
