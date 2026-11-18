<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kompons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesin_id')->constrained('mesins')->restrictOnDelete();
            $table->string('nama_komponen');
            $table->unsignedInteger('stok')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kompons');
    }
};
