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
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->enum('lieu_depart', ['Casa', 'OUZ', 'Kenitra']);
            $table->enum('lieu_arrivee', ['Casa', 'OUZ', 'Kenitra']);
            $table->date('mission_date_debut');
            $table->date('mission_date_fin');
            $table->integer('nbr_jours');
            $table->enum('transport', ['Avion', 'Bus-Rer-Transilien', 'Taxi', 'Train', 'Voiture de location','Voiture personnelle','Bateau','Péage','Parking' ]); 
            $table->foreignId('prestataire')->constrained('collaborateurs')->onDelete('cascade');
            $table->foreignId('accompagnant')->constrained('collaborateurs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
