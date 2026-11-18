<?php

namespace Tests\Feature;

use App\Exports\HmReportExport;
use App\Models\HmLog;
use App\Models\Machine;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HmLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_daily_input(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor EBP',
            'hm_awal' => 100,
        ]);
        $otherStation = Station::create(['name' => 'Opt. Boiler']);
        Machine::create([
            'station_id' => $otherStation->id,
            'name' => 'Conveyor Boiler',
            'hm_awal' => 200,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('hm.input', ['station_id' => $station->id]))
            ->assertOk()
            ->assertDontSee('Dashboard Monitoring HM')
            ->assertSee('Sistem Informasi Manajemen Perawatan Mesin PKS')
            ->assertSee('Opt. EBP')
            ->assertSee('Conveyor EBP')
            ->assertSee('name="readings['.$machine->id.']"', false)
            ->assertSee('station.form.requestSubmit()', false)
            ->assertDontSee('Conveyor Boiler');
    }

    public function test_check_hm_page_shows_only_hm_export_and_input_sections(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('hm.input'))
            ->assertOk()
            ->assertSeeInOrder([
                'Export Laporan Bulanan',
                'Download Excel',
                'Input HM Harian',
            ])
            ->assertDontSee('Export Laporan Check Up Mesin')
            ->assertSee(route('hm.export'));
    }

    public function test_dashboard_shows_summary_without_daily_hm_input(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder([
                'Total Mesin',
                'Total Stasiun',
                'Input Hari Ini',
                'HM Bulan Ini',
            ])
            ->assertDontSee('Input HM Harian')
            ->assertDontSee('Tanggal Input')
            ->assertDontSee('Tampilkan Mesin')
            ->assertDontSee('Export Laporan Bulanan')
            ->assertDontSee('Export Laporan Check Up Mesin')
            ->assertDontSee('Download Excel Check Up Mesin');
    }

    public function test_reports_page_contains_all_excel_exports(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('hm.reports'))
            ->assertOk()
            ->assertSee('Laporan')
            ->assertSee('Laporan Check Up Mesin')
            ->assertSee('Export Laporan Bulanan')
            ->assertSee('Export Riwayat Pergantian Komponen')
            ->assertSee(route('check-ups.export'))
            ->assertSee(route('component-replacements.export'))
            ->assertSee(route('hm.export'))
            ->assertSee('name="month"', false)
            ->assertSee('name="year"', false);
    }

    public function test_daily_input_can_filter_machines_by_brand_or_type(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor SKF',
            'brand_type' => 'SKF X200',
            'hm_awal' => 100,
        ]);
        Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor FAG',
            'brand_type' => 'FAG X100',
            'hm_awal' => 100,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('hm.input', [
                'station_id' => $station->id,
                'brand_type' => 'SKF',
            ]))
            ->assertOk()
            ->assertSee('name="brand_type"', false)
            ->assertSee('SKF X200')
            ->assertSee('Conveyor SKF')
            ->assertDontSee('FAG X100')
            ->assertDontSee('Conveyor FAG');
    }

    public function test_daily_reading_input_is_next_to_machine_name(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor 1',
            'hm_awal' => 100,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('hm.input', ['station_id' => $station->id]))
            ->assertOk()
            ->assertSeeInOrder([
                '<th>UNIT MESIN</th>',
                '<th>HM READING HARI INI</th>',
                '<th>MERK / TIPE</th>',
            ], false)
            ->assertSee('name="readings['.$machine->id.']"', false)
            ->assertSee('aria-label="HM harian Conveyor 1"', false);
    }

    public function test_daily_reading_saves_usage_from_previous_reading(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor 1',
            'hm_awal' => 100,
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('hm.store'), [
                'date' => '2026-10-02',
                'station_id' => $station->id,
                'readings' => [$machine->id => '103.50'],
            ])
            ->assertRedirect(route('hm.input', [
                'station_id' => $station->id,
                'date' => '2026-10-02',
            ]));

        $log = HmLog::where('machine_id', $machine->id)
            ->whereDate('date', '2026-10-02')
            ->firstOrFail();

        $this->assertEquals(103.5, (float) $log->hm_reading);
        $this->assertEquals(3.5, (float) $log->hm_terpakai);
    }

    public function test_backdated_reading_recalculates_next_log_usage(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor 1',
            'hm_awal' => 100,
        ]);

        HmLog::create([
            'machine_id' => $machine->id,
            'date' => '2026-10-03',
            'hm_reading' => 120,
            'hm_terpakai' => 20,
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('hm.store'), [
                'date' => '2026-10-02',
                'station_id' => $station->id,
                'readings' => [$machine->id => '115'],
            ])
            ->assertSessionHasNoErrors();

        $nextLog = HmLog::where('machine_id', $machine->id)
            ->whereDate('date', '2026-10-03')
            ->firstOrFail();

        $this->assertEquals(5, (float) $nextLog->hm_terpakai);
    }

    public function test_monthly_export_has_pivot_columns_and_station_sheets(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor 1',
            'pic' => 'Operator',
            'hm_awal' => 100,
        ]);
        HmLog::create([
            'machine_id' => $machine->id,
            'date' => '2026-10-01',
            'hm_reading' => 101.5,
            'hm_terpakai' => 1.5,
        ]);

        $sheets = (new HmReportExport(2026, 10))->sheets();

        $this->assertCount(2, $sheets);
        $headings = $sheets[0]->headings();
        $this->assertSame('HM Mesin', $headings[0][2]);
        $this->assertSame('HM Reading', $headings[0][3]);
        $this->assertSame(31, $headings[1][33]);
        $this->assertSame('HM Terpakai', $headings[0][34]);
        $this->assertSame('Total HM Terpakai', $headings[0][65]);

        $row = $sheets[0]->map($machine->load('hmLogs'));
        $this->assertSame('Conveyor 1', $row[0]);
        $this->assertEquals(101.5, $row[3]);
        $this->assertEquals(1.5, $row[34]);
        $this->assertEquals(1.5, $row[65]);
        $this->assertSame('Opt. EBP', $sheets[1]->title());

        $this->actingAs(User::factory()->create())
            ->get(route('hm.export', ['month' => 10, 'year' => 2026]))
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
    }
}
