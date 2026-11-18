<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stasiun_id')->constrained('stasiuns')->restrictOnDelete();
            $table->string('nama_mesin');
            $table->text('spesifikasi')->nullable();
            $table->timestamps();

            $table->unique(['stasiun_id', 'nama_mesin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesins');
    }
};
