<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aviarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('granja_id')->constrained('granjas')->cascadeOnDelete();
            $table->string('identificacao', 50);
            $table->decimal('area_m2', 10, 2);
            $table->unsignedInteger('capacidade_aves');
            $table->enum('tipo', ['convencional', 'dark_house'])->default('convencional');
            $table->timestamps();

            $table->unique(['granja_id', 'identificacao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aviarios');
    }
};
