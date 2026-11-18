<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hm_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->decimal('hm_reading', 12, 2);
            $table->decimal('hm_terpakai', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['machine_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hm_logs');
    }
};
