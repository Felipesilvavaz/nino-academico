<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            // RF22: destino do lote na programação de abate.
            $table->string('frigorifico_destino', 100)->nullable()->after('data_abate_prevista');

            // RF24: informe de rendimento e de monitoramento de Salmonella, preenchidos no encerramento.
            $table->decimal('rendimento_pct', 5, 2)->nullable()->after('data_encerramento');
            $table->enum('resultado_salmonella', ['nao_testado', 'negativo', 'positivo'])->default('nao_testado')->after('rendimento_pct');
        });
    }

    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropColumn(['frigorifico_destino', 'rendimento_pct', 'resultado_salmonella']);
        });
    }
};
