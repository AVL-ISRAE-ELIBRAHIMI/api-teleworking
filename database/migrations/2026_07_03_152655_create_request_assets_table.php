<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_assets', function (Blueprint $table) {
            $table->id();
            $table->string('new_location');
            $table->foreignId('materiel_id')->constrained('materiels')->onDelete('cascade');
            $table->foreignId('requestor')->constrained('collaborateurs')->onDelete('cascade');
            $table->foreignId('validator')->constrained('collaborateurs')->onDelete('cascade');
            $table->date('borrow_date');
            $table->date('return_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_assets');
    }
};
