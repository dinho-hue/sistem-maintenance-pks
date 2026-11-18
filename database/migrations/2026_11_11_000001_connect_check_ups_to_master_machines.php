<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_ups', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->constrained('stations')->restrictOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->restrictOnDelete();
            $table->unsignedBigInteger('stasiun_id')->nullable()->change();
            $table->unsignedBigInteger('mesin_id')->nullable()->change();
        });

        $legacyCheckUps = DB::table('check_ups')
            ->join('stasiuns', 'stasiuns.id', '=', 'check_ups.stasiun_id')
            ->join('mesins', 'mesins.id', '=', 'check_ups.mesin_id')
            ->select(
                'check_ups.id',
                'stasiuns.nama_stasiun',
                'mesins.nama_mesin'
            )
            ->get();

        foreach ($legacyCheckUps as $checkUp) {
            $stationId = DB::table('stations')
                ->where('name', $checkUp->nama_stasiun)
                ->value('id');

            if ($stationId === null) {
                continue;
            }

            $machineId = DB::table('machines')
                ->where('station_id', $stationId)
                ->where('name', $checkUp->nama_mesin)
                ->value('id');

            if ($machineId !== null) {
                DB::table('check_ups')
                    ->where('id', $checkUp->id)
                    ->update([
                        'station_id' => $stationId,
                        'machine_id' => $machineId,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('check_ups', function (Blueprint $table) {
            $table->dropForeign(['station_id']);
            $table->dropForeign(['machine_id']);
            $table->dropColumn(['station_id', 'machine_id']);
        });
    }
};
