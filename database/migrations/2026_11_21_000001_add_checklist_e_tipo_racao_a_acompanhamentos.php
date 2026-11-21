<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acompanhamentos', function (Blueprint $table) {
            // Checklist de visita (RF20): itens conferidos pelo técnico no dia.
            $table->boolean('checklist_agua')->default(false)->after('observacao');
            $table->boolean('checklist_racao')->default(false)->after('checklist_agua');
            $table->boolean('checklist_ventilacao')->default(false)->after('checklist_racao');
            $table->boolean('checklist_cama')->default(false)->after('checklist_ventilacao');

            // RF23: tipo de ração usada no dia, para comparar o ganho de peso entre pelet e farelo.
            $table->enum('tipo_racao', ['pelet', 'farelo'])->nullable()->after('checklist_cama');
        });
    }

    public function down(): void
    {
        Schema::table('acompanhamentos', function (Blueprint $table) {
            $table->dropColumn(['checklist_agua', 'checklist_racao', 'checklist_ventilacao', 'checklist_cama', 'tipo_racao']);
        });
    }
};
