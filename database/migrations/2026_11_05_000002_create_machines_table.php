<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('pic')->nullable();
            $table->decimal('hm_awal', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['station_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
