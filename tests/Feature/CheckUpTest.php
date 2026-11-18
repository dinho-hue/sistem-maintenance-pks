<?php

namespace Tests\Feature;

use App\Exports\CheckUpExport;
use App\Models\CheckUp;
use App\Models\Machine;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CheckUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_check_up_form(): void
    {
        $station = Station::create(['name' => 'Stasiun Press']);
        Machine::create([
            'station_id' => $station->id,
            'name' => 'Press 1',
            'hm_awal' => 100,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('check-ups.index'))
            ->assertOk()
            ->assertSee('Input Check Up')
            ->assertSee('Stasiun Press')
            ->assertSee('Press 1')
            ->assertSeeInOrder([
                'Riwayat Check Up',
                'Belum ada riwayat check up.',
                'Download Excel Check Up',
            ])
            ->assertSee('class="primary-button export-button"', false)
            ->assertSee(route('check-ups.export'));
    }

    public function test_check_up_is_saved_with_related_station_and_machine(): void
    {
        $station = Station::create(['name' => 'Stasiun Press']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Press 1',
            'hm_awal' => 100,
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('check-ups.store'), [
                'station_id' => $station->id,
                'nama_teknisi' => 'Budi',
                'machine_id' => $machine->id,
                'tanggal' => '2026-11-06',
                'keterangan' => 'Penggantian bearing',
                'hm_perbaikan' => '1234.50',
            ])
            ->assertRedirect(route('check-ups.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('check_ups', [
            'station_id' => $station->id,
            'machine_id' => $machine->id,
            'nama_teknisi' => 'Budi',
            'hm_perbaikan' => 1234.50,
        ]);
        $checkUp = CheckUp::firstOrFail();
        $this->assertSame('2026-11-06', $checkUp->tanggal->toDateString());
    }

    public function test_machine_must_belong_to_selected_station(): void
    {
        $stationOne = Station::create(['name' => 'Stasiun Press']);
        $stationTwo = Station::create(['name' => 'Stasiun Boiler']);
        $machine = Machine::create([
            'station_id' => $stationTwo->id,
            'name' => 'Boiler 1',
            'hm_awal' => 200,
        ]);

        $this->actingAs(User::factory()->create())
            ->from(route('check-ups.index'))
            ->post(route('check-ups.store'), [
                'station_id' => $stationOne->id,
                'nama_teknisi' => 'Budi',
                'machine_id' => $machine->id,
                'tanggal' => '2026-11-06',
                'hm_perbaikan' => '1234.50',
            ])
            ->assertSessionHasErrors('machine_id');

        $this->assertDatabaseCount('check_ups', 0);
    }

    public function test_machine_with_check_up_history_cannot_be_deleted(): void
    {
        $station = Station::create(['name' => 'Stasiun Press']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Press 1',
            'hm_awal' => 100,
        ]);
        CheckUp::create([
            'station_id' => $station->id,
            'machine_id' => $machine->id,
            'nama_teknisi' => 'Budi',
            'tanggal' => '2026-11-06',
            'hm_perbaikan' => 1234.50,
        ]);

        $this->actingAs(User::factory()->create())
            ->delete(route('machines.destroy', $machine))
            ->assertRedirect(route('machines.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('machines', ['id' => $machine->id]);
    }

    public function test_user_can_download_all_check_up_history_as_excel(): void
    {
        $station = Station::create(['name' => 'Stasiun Press']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Press 1',
            'hm_awal' => 100,
        ]);
        Machine::create([
            'station_id' => $station->id,
            'name' => 'Press 2',
            'hm_awal' => 200,
        ]);
        CheckUp::create([
            'station_id' => $station->id,
            'machine_id' => $machine->id,
            'nama_teknisi' => 'Budi',
            'tanggal' => '2026-11-06',
            'keterangan' => 'Penggantian bearing',
            'hm_perbaikan' => 1234.50,
        ]);

        Excel::fake();

        $this->actingAs(User::factory()->create())
            ->get(route('check-ups.export'))
            ->assertOk();

        Excel::assertDownloaded('riwayat-check-up.xlsx', function ($export) {
            $sheets = $export->sheets();

            if (count($sheets) !== 2
                || $sheets[0]->title() !== 'Press 1'
                || $sheets[1]->title() !== 'Press 2'
                || $sheets[0]->headings() !== ['NO', 'TANGGAL', 'DESCRIPTION', 'HM', 'PIC']
                || $sheets[0]->collection()->count() !== 1
                || $sheets[1]->collection()->isNotEmpty()) {
                return false;
            }

            $checkUp = $sheets[0]->collection()->first();

            return $sheets[0]->map($checkUp) === [
                1,
                '06-Nov-26',
                'Penggantian bearing',
                1234.5,
                'Budi',
            ];
        });
    }

    public function test_check_up_workbook_has_a_sheet_for_each_master_machine(): void
    {
        $station = Station::create(['name' => 'Stasiun Press']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Press 1',
            'hm_awal' => 100,
        ]);
        Machine::create([
            'station_id' => $station->id,
            'name' => 'Press 2',
            'hm_awal' => 200,
        ]);
        CheckUp::create([
            'station_id' => $station->id,
            'machine_id' => $machine->id,
            'nama_teknisi' => 'Budi',
            'tanggal' => '2026-11-06',
            'keterangan' => 'Penggantian bearing',
            'hm_perbaikan' => 1234.50,
        ]);

        $workbook = Excel::raw(new CheckUpExport(), ExcelWriter::XLSX);

        $this->assertStringStartsWith('PK', $workbook);
        $this->assertGreaterThan(1000, strlen($workbook));
    }
}
