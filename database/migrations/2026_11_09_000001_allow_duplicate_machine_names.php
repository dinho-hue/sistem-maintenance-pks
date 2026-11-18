<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->index('station_id');
            $table->dropUnique(['station_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->unique(['station_id', 'name']);
            $table->dropIndex(['station_id']);
        });
    }
};
