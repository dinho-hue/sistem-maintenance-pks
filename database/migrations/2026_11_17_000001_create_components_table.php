<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index(['machine_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
