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
        Schema::create('override_reservations', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('reservation_id');
            $table->integer('motif');
            $table->text('justification');
            $table->uuid('requested_by'); // STL/ TL
            $table->timestamps();

            $table->foreign('reservation_id')->references('id')->on('reservations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('override_reservations');
    }
};
