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
        Schema::table('equipes', function (Blueprint $table) {
            $table->foreign(['departement_id'])->references(['id'])->on('departements')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['TL'])->references(['id'])->on('collaborateurs')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipes', function (Blueprint $table) {
            $table->dropForeign('equipes_departement_id_foreign');
            $table->dropForeign('equipes_tl_foreign');
        });
    }
};
