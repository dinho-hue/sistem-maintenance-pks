<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->date('installed_at')->nullable();
            $table->decimal('hm_installed', 12, 2)->nullable();
            $table->decimal('target_lifetime_hm', 12, 2)->nullable();
            $table->unsignedInteger('target_lifetime_months')->nullable();
        });

        Schema::create('component_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained()->restrictOnDelete();
            $table->string('old_name');
            $table->string('new_name');
            $table->date('installed_at')->nullable();
            $table->date('removed_at');
            $table->decimal('hm_installed', 12, 2)->nullable();
            $table->decimal('hm_removed', 12, 2);
            $table->decimal('target_lifetime_hm', 12, 2)->nullable();
            $table->unsignedInteger('target_lifetime_months')->nullable();
            $table->date('installed_at_new');
            $table->decimal('hm_installed_new', 12, 2);
            $table->decimal('target_lifetime_hm_new', 12, 2);
            $table->unsignedInteger('target_lifetime_months_new');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['component_id', 'removed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_replacements');

        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn([
                'installed_at',
                'hm_installed',
                'target_lifetime_hm',
                'target_lifetime_months',
            ]);
        });
    }
};
