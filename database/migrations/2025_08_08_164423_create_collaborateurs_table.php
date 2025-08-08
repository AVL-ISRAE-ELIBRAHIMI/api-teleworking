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
        Schema::create('collaborateurs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->nullable()->unique();
            $table->string('account_name')->nullable()->unique();
            $table->string('manager')->nullable();
            $table->string('departement_id')->nullable();
            $table->string('equipe_id')->nullable();
            $table->string('activity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collaborateurs');
    }
};
