<x-app-layout>
    <x-slot name="header">
        <h1>Detail Stasiun</h1>
        <p>Kelola informasi stasiun dan daftar mesin.</p>
    </x-slot>

    <div class="master-page">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="page-heading">
            <div>
                <h2>{{ $station->name }}</h2>
                <p>Perbarui keterangan stasiun atau tambahkan mesin yang berada di area ini.</p>
            </div>
            <a class="secondary-button" href="{{ route('stations.index') }}">
                <i class="fa-solid fa-arrow-left"></i> Daftar Stasiun
            </a>
        </div>

        <form method="POST" action="{{ route('stations.update', $station) }}" class="panel station-detail-form">
            @csrf
            @method('PUT')
            <div class="panel-heading">
                <div class="panel-heading-icon"><i class="fa-solid fa-industry"></i></div>
                <div><h3>Informasi Stasiun</h3><p>Nama dan keterangan area pabrik.</p></div>
            </div>
            <div class="station-fields">
                <div>
                    <label for="name">Nama Stasiun</label>
                    <input id="name" name="name" value="{{ old('name', $station->name) }}" required maxlength="255"
                           class="form-control">
                </div>
                <div>
                    <label for="description">Keterangan</label>
                    <textarea id="description" name="description" rows="3" maxlength="2000"
                              placeholder="Tambahkan keterangan stasiun (opsional)"
                              class="form-control">{{ old('description', $station->description) }}</textarea>
                </div>
            </div>
            <button type="submit" class="primary-button station-save-button">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
        </form>

        <div class="page-heading machine-section-heading">
            <div>
                <h2>Data Mesin</h2>
                <p>Mesin yang terdaftar pada stasiun {{ $station->name }}.</p>
            </div>
            <span class="date-chip"><i class="fa-solid fa-gears"></i> {{ $station->machines->count() }} mesin</span>
        </div>

        <section class="panel">
            <div class="panel-heading">
                <div class="panel-heading-icon"><i class="fa-solid fa-plus"></i></div>
                <div><h3>Tambah Mesin ke Stasiun</h3><p>Masukkan identitas mesin, PIC, tanggal pemasangan, merk/tipe, dan HM mesin.</p></div>
            </div>
            <form method="POST" action="{{ route('stations.machines.store', $station) }}" class="station-fields machine-create-fields">
                @csrf
                <div class="machine-name-field">
                    <label for="machine_name">Nama Mesin</label>
                    <input id="machine_name" name="name" value="{{ old('name') }}" required maxlength="255"
                           placeholder="Contoh: FFB SCRAPPER CONVEYOR NO. 1" class="form-control">
                </div>
                <div>
                    <label for="pic">PIC</label>
                    <input id="pic" name="pic" value="{{ old('pic') }}" maxlength="255"
                           placeholder="Nama penanggung jawab" class="form-control">
                </div>
                <div>
                    <label for="installation_date">Tanggal Pemasangan</label>
                    <input id="installation_date" name="installation_date" type="date"
                           value="{{ old('installation_date') }}" max="{{ now()->toDateString() }}"
                           required class="form-control">
                </div>
                <div>
                    <label for="brand_type">Merk / Tipe Mesin</label>
                    <input id="brand_type" name="brand_type" value="{{ old('brand_type') }}"
                           maxlength="255" placeholder="Contoh: SKF / Conveyor X200"
                           required class="form-control">
                </div>
                <div>
                    <label for="hm_awal">HM Mesin</label>
                    <input id="hm_awal" name="hm_awal" type="number" min="0" step="0.01"
                           value="{{ old('hm_awal', '0.00') }}" required class="form-control">
                </div>
                <button type="submit" class="primary-button">
                    <i class="fa-solid fa-plus"></i> Tambah Mesin
                </button>
            </form>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div class="panel-heading-icon"><i class="fa-solid fa-gears"></i></div>
                <div><h3>Daftar Mesin {{ $station->name }}</h3><p>Identitas dan informasi pemasangan mesin.</p></div>
            </div>
            <div class="overflow-x-auto">
                <table class="hm-table">
                    <thead>
                        <tr><th>NO</th><th>NAMA MESIN</th><th>PIC</th><th>TANGGAL PEMASANGAN</th><th>MERK / TIPE</th><th>HM MESIN</th><th>AKSI</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($station->machines as $machine)
                            <tr>
                                <td class="row-number">{{ $loop->iteration }}</td>
                                <td class="machine-name">{{ $machine->name }}</td>
                                <td>{{ $machine->pic ?: '-' }}</td>
                                <td>{{ $machine->installation_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $machine->brand_type ?: '-' }}</td>
                                <td>{{ number_format((float) $machine->hm_awal, 2, ',', '.') }}</td>
                                <td>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a class="table-action-link"
                                           href="{{ route('machines.edit', ['machine' => $machine, 'return_to' => 'station']) }}">
                                            Edit <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('machines.destroy', ['machine' => $machine, 'return_to' => 'station']) }}"
                                              onsubmit="return confirm('Yakin ingin menghapus mesin {{ addslashes($machine->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    @disabled($machine->hm_logs_count > 0)
                                                    title="{{ $machine->hm_logs_count > 0 ? 'Mesin memiliki catatan HM dan tidak dapat dihapus' : 'Hapus mesin' }}"
                                                    class="inline-flex items-center gap-1 rounded px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="empty-state" colspan="7">Belum ada mesin pada stasiun ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
