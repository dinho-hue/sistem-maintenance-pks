<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stasiun_id')->constrained('stasiuns')->restrictOnDelete();
            $table->foreignId('mesin_id')->constrained('mesins')->restrictOnDelete();
            $table->string('nama_teknisi');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->decimal('hm_perbaikan', 12, 2);
            $table->timestamps();

            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_ups');
    }
};
