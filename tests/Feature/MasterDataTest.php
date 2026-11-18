<?php

namespace Tests\Feature;

use App\Models\HmLog;
use App\Models\Machine;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_master_starts_empty_and_uses_existing_station_machines(): void
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
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('components.index'))
            ->assertOk()
            ->assertSee('Data Komponen')
            ->assertSee('Belum ada data komponen.')
            ->assertSee('Opt. EBP')
            ->assertSee('Conveyor EBP');

        $this->assertDatabaseCount('components', 0);

        $this->actingAs($user)
            ->post(route('components.store'), [
                'name' => 'Bearing',
                'station_id' => $station->id,
                'machine_id' => $machine->id,
            ])
            ->assertRedirect(route('components.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('components', [
            'name' => 'Bearing',
            'machine_id' => $machine->id,
        ]);

        $this->actingAs($user)
            ->get(route('components.index'))
            ->assertSee('Bearing')
            ->assertSee('Opt. EBP')
            ->assertSee('Conveyor EBP');
    }

    public function test_component_must_be_assigned_to_a_machine_in_the_selected_station(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $otherStation = Station::create(['name' => 'Opt. Boiler']);
        $machine = Machine::create([
            'station_id' => $otherStation->id,
            'name' => 'Conveyor Boiler',
            'hm_awal' => 200,
        ]);

        $this->actingAs(User::factory()->create())
            ->from(route('components.index'))
            ->post(route('components.store'), [
                'name' => 'Bearing',
                'station_id' => $station->id,
                'machine_id' => $machine->id,
            ])
            ->assertRedirect(route('components.index'))
            ->assertSessionHasErrors('machine_id');

        $this->assertDatabaseCount('components', 0);
    }

    public function test_machine_with_components_cannot_be_deleted(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor EBP',
            'hm_awal' => 100,
        ]);
        $machine->components()->create(['name' => 'Bearing']);

        $this->actingAs(User::factory()->create())
            ->delete(route('machines.destroy', $machine))
            ->assertRedirect(route('machines.index'))
            ->assertSessionHas('error', 'Mesin tidak dapat dihapus karena masih memiliki data komponen.');

        $this->assertDatabaseHas('machines', ['id' => $machine->id]);
    }

    public function test_component_can_be_updated_and_deleted(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor EBP',
            'hm_awal' => 100,
        ]);
        $component = $machine->components()->create(['name' => 'Bearing lama']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('components.edit', $component))
            ->assertOk()
            ->assertSee('Edit Bearing lama')
            ->assertSee('Conveyor EBP');

        $this->actingAs($user)
            ->put(route('components.update', $component), [
                'name' => 'Bearing baru',
                'station_id' => $station->id,
                'machine_id' => $machine->id,
            ])
            ->assertRedirect(route('components.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('components', [
            'id' => $component->id,
            'name' => 'Bearing baru',
        ]);

        $this->actingAs($user)
            ->delete(route('components.destroy', $component))
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('status', 'Komponen berhasil dihapus.');

        $this->assertDatabaseMissing('components', ['id' => $component->id]);
    }

    public function test_component_replacement_updates_active_component_and_records_history(): void
    {
        $station = Station::create(['name' => 'PRESS']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'SCREW PRESS NO.1',
            'hm_awal' => 100,
        ]);
        $component = $machine->components()->create([
            'name' => 'Bearing 6312',
            'installed_at' => now()->subYear()->toDateString(),
            'hm_installed' => 100,
            'target_lifetime_hm' => 2000,
            'target_lifetime_months' => 12,
        ]);
        $user = User::factory()->create();
        $today = now()->toDateString();

        $this->actingAs($user)
            ->get(route('components.replacements.create', $component))
            ->assertOk()
            ->assertSee('Data Komponen Lama')
            ->assertSee('Data Komponen Baru');

        $this->actingAs($user)
            ->post(route('components.replacements.store', $component), [
                'removed_at' => $today,
                'hm_removed' => 250,
                'new_name' => 'Bearing 6312 Baru',
                'installed_at_new' => $today,
                'hm_installed_new' => 250,
                'target_lifetime_hm_new' => 2000,
                'target_lifetime_months_new' => 12,
                'reason' => 'Perawatan Berkala',
                'notes' => 'Penggantian sesuai jadwal.',
            ])
            ->assertRedirect(route('components.show', $component))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('components', [
            'id' => $component->id,
            'name' => 'Bearing 6312 Baru',
            'hm_installed' => 250,
        ]);
        $this->assertDatabaseHas('component_replacements', [
            'component_id' => $component->id,
            'old_name' => 'Bearing 6312',
            'new_name' => 'Bearing 6312 Baru',
            'hm_removed' => 250,
            'reason' => 'Perawatan Berkala',
        ]);

        $this->actingAs($user)
            ->get(route('component-replacements.index'))
            ->assertOk()
            ->assertSee('Bearing 6312')
            ->assertSee('Bearing 6312 Baru')
            ->assertSee('SCREW PRESS NO.1')
            ->assertSee('Penggantian sesuai jadwal.')
            ->assertDontSee('Unduh Excel')
            ->assertDontSee(route('component-replacements.export'), false);

        $exportResponse = $this->actingAs($user)
            ->get(route('component-replacements.export', ['search' => 'Bearing 6312']))
            ->assertOk();
        $exportResponse
            ->assertHeader('content-disposition', 'attachment; filename=riwayat-pergantian-komponen.xlsx')
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );

        $temporaryFile = $exportResponse->baseResponse->getFile()->getPathname();
        $spreadsheet = null;

        try {
            $spreadsheet = IOFactory::load($temporaryFile);
            $sheet = $spreadsheet->getActiveSheet();

            $this->assertSame('RIWAYAT PERGANTIAN KOMPONEN', $sheet->getCell('A1')->getValue());
            $this->assertSame('NO', $sheet->getCell('A4')->getValue());
            $this->assertSame('Bearing 6312', $sheet->getCell('B5')->getValue());
            $this->assertTrue($sheet->getStyle('A4')->getFont()->getBold());
            $this->assertSame('FF0F2A47', $sheet->getStyle('A1')->getFill()->getStartColor()->getARGB());
            $this->assertSame('FF1565C0', $sheet->getStyle('A4')->getFill()->getStartColor()->getARGB());
            $this->assertSame('dd/mm/yyyy', $sheet->getStyle('F5')->getNumberFormat()->getFormatCode());
            $this->assertSame('A1:L1', array_key_first($sheet->getMergeCells()));
        } finally {
            $spreadsheet?->disconnectWorksheets();
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }

        $this->actingAs($user)
            ->get(route('components.show', $component))
            ->assertOk()
            ->assertSee('Riwayat Pergantian Komponen')
            ->assertSee('Bearing 6312 Baru');
    }

    public function test_component_replacement_rejects_decreasing_hourmeter(): void
    {
        $station = Station::create(['name' => 'PRESS']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'SCREW PRESS NO.1',
            'hm_awal' => 100,
        ]);
        $component = $machine->components()->create([
            'name' => 'Bearing 6312',
            'installed_at' => now()->subMonth()->toDateString(),
            'hm_installed' => 200,
            'target_lifetime_hm' => 2000,
            'target_lifetime_months' => 12,
        ]);

        $this->actingAs(User::factory()->create())
            ->from(route('components.replacements.create', $component))
            ->post(route('components.replacements.store', $component), [
                'removed_at' => now()->toDateString(),
                'hm_removed' => 150,
                'new_name' => 'Bearing Baru',
                'installed_at_new' => now()->toDateString(),
                'hm_installed_new' => 150,
                'target_lifetime_hm_new' => 2000,
                'target_lifetime_months_new' => 12,
                'reason' => 'Kerusakan',
            ])
            ->assertRedirect(route('components.replacements.create', $component))
            ->assertSessionHasErrors('hm_removed');

        $this->assertDatabaseCount('component_replacements', 0);
        $this->assertDatabaseHas('components', [
            'id' => $component->id,
            'name' => 'Bearing 6312',
        ]);
    }

    public function test_authenticated_user_can_create_station_and_machine(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('stations.store'), [
                'name' => 'Opt. EBP',
                'description' => 'Area pemrosesan EBP',
            ])
            ->assertRedirect(route('stations.index'));

        $station = Station::where('name', 'Opt. EBP')->firstOrFail();
        $this->assertSame('Area pemrosesan EBP', $station->description);

        $this->actingAs($user)
            ->get(route('stations.show', $station))
            ->assertOk()
            ->assertSee('Informasi Stasiun')
            ->assertSee('Area pemrosesan EBP')
            ->assertSee('Tambah Mesin ke Stasiun');

        $this->actingAs($user)
            ->post(route('stations.machines.store', $station), [
                'name' => 'FFB SCRAPPER CONVEYOR NO. 1',
                'pic' => 'Operator',
                'installation_date' => '2025-02-20',
                'brand_type' => 'SKF / Conveyor X200',
                'hm_awal' => '125.50',
            ])
            ->assertRedirect(route('stations.show', $station));

        $this->assertDatabaseHas('machines', [
            'station_id' => $station->id,
            'name' => 'FFB SCRAPPER CONVEYOR NO. 1',
            'pic' => 'Operator',
            'brand_type' => 'SKF / Conveyor X200',
            'hm_awal' => 125.50,
        ]);
        $machine = Machine::where('name', 'FFB SCRAPPER CONVEYOR NO. 1')->firstOrFail();
        $this->assertSame('2025-02-20', $machine->installation_date->toDateString());

        $this->actingAs($user)
            ->get(route('stations.show', $station))
            ->assertOk()
            ->assertSee('Daftar Mesin Opt. EBP')
            ->assertSee('Edit')
            ->assertSee('Hapus');

        $this->actingAs($user)
            ->get(route('machines.index'))
            ->assertOk()
            ->assertSee('Tanggal Pemasangan')
            ->assertSee('Merk / Tipe Mesin')
            ->assertSee('20/02/2025')
            ->assertSee('SKF / Conveyor X200')
            ->assertSee('HM Mesin')
            ->assertSee('125,50')
            ->assertSee('Edit')
            ->assertSee('Hapus')
            ->assertDontSee('Input HM')
            ->assertDontSee('HM AWAL');
    }

    public function test_machine_can_be_updated_and_return_to_its_station(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor 1',
            'pic' => 'PIC lama',
            'installation_date' => '2025-02-20',
            'brand_type' => 'Tipe lama',
            'hm_awal' => 100,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('machines.edit', ['machine' => $machine, 'return_to' => 'station']))
            ->assertOk()
            ->assertSee('Edit Conveyor 1');

        $this->actingAs($user)
            ->put(route('machines.update', $machine), [
                'return_to' => 'station',
                'station_id' => $station->id,
                'name' => 'Conveyor Utama',
                'pic' => 'PIC baru',
                'installation_date' => '2025-03-01',
                'brand_type' => 'Tipe baru',
                'hm_awal' => 125.5,
            ])
            ->assertRedirect(route('stations.show', $station))
            ->assertSessionHas('status', 'Data mesin berhasil diperbarui.');

        $this->assertDatabaseHas('machines', [
            'id' => $machine->id,
            'name' => 'Conveyor Utama',
            'pic' => 'PIC baru',
            'brand_type' => 'Tipe baru',
            'hm_awal' => 125.5,
        ]);
    }

    public function test_machine_without_hm_logs_can_be_deleted(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor 1',
            'hm_awal' => 0,
        ]);

        $this->actingAs(User::factory()->create())
            ->delete(route('machines.destroy', ['machine' => $machine, 'return_to' => 'station']))
            ->assertRedirect(route('stations.show', $station))
            ->assertSessionHas('status', 'Mesin berhasil dihapus.');

        $this->assertDatabaseMissing('machines', ['id' => $machine->id]);
    }

    public function test_machine_with_hm_logs_cannot_be_deleted(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $machine = Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor 1',
            'hm_awal' => 0,
        ]);
        HmLog::create([
            'machine_id' => $machine->id,
            'date' => '2025-02-20',
            'hm_reading' => 100,
            'hm_terpakai' => 0,
        ]);

        $this->actingAs(User::factory()->create())
            ->delete(route('machines.destroy', $machine))
            ->assertRedirect(route('machines.index'))
            ->assertSessionHas('error', 'Mesin tidak dapat dihapus karena sudah memiliki catatan HM.');

        $this->assertDatabaseHas('machines', ['id' => $machine->id]);
    }

    public function test_station_description_can_be_updated(): void
    {
        $station = Station::create([
            'name' => 'Sterilizer',
            'description' => 'Keterangan awal',
        ]);

        $this->actingAs(User::factory()->create())
            ->put(route('stations.update', $station), [
                'name' => 'Sterilizer',
                'description' => 'Area rebusan buah',
            ])
            ->assertRedirect(route('stations.show', $station));

        $this->assertDatabaseHas('stations', [
            'id' => $station->id,
            'description' => 'Area rebusan buah',
        ]);
    }

    public function test_duplicate_machine_names_can_be_registered_in_the_same_station(): void
    {
        $station = Station::create(['name' => 'Opt. EBP']);
        $user = User::factory()->create();
        $attributes = [
            'station_id' => $station->id,
            'name' => 'Conveyor 1',
            'installation_date' => '2025-02-20',
            'brand_type' => 'SKF',
            'hm_awal' => 0,
        ];

        $this->actingAs($user)
            ->post(route('machines.store'), $attributes)
            ->assertRedirect(route('machines.index'))
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->post(route('machines.store'), $attributes)
            ->assertRedirect(route('machines.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('machines', 2);
        $this->assertDatabaseHas('machines', [
            'station_id' => $station->id,
            'name' => 'Conveyor 1',
        ]);
    }

    public function test_station_without_machines_can_be_deleted(): void
    {
        $station = Station::create(['name' => 'Station to delete']);

        $this->actingAs(User::factory()->create())
            ->delete(route('stations.destroy', $station))
            ->assertRedirect(route('stations.index'))
            ->assertSessionHas('status', 'Stasiun berhasil dihapus.');

        $this->assertDatabaseMissing('stations', ['id' => $station->id]);
    }

    public function test_station_with_machines_cannot_be_deleted(): void
    {
        $station = Station::create(['name' => 'Station with machine']);
        Machine::create([
            'station_id' => $station->id,
            'name' => 'Conveyor 1',
            'hm_awal' => 0,
        ]);

        $this->actingAs(User::factory()->create())
            ->delete(route('stations.destroy', $station))
            ->assertRedirect(route('stations.index'))
            ->assertSessionHas('error', 'Stasiun tidak dapat dihapus karena masih memiliki mesin.');

        $this->assertDatabaseHas('stations', ['id' => $station->id]);
    }
}
