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
        Schema::create('odm', function (Blueprint $table) {
            $table->id();
            $table->string('reference_cdc');
            $table->integer('devis');
            $table->string('chage_affaire_psa');
            $table->foreignId('consultant')->constrained('collaborateurs')->onDelete('cascade');
            $table->foreignId('missions_id')->constrained('missions')->onDelete('cascade');
            $table->foreignId('deplacement_id')->constrained('deplacements')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odm');
    }
};
