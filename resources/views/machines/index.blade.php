<x-app-layout>
    <x-slot name="header">
        <h1>Data Mesin</h1>
        <p>Kelola identitas, tanggal pemasangan, dan merk/tipe mesin.</p>
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
        @if ($stations->isEmpty())
            <div class="alert alert-info">
                Tambahkan stasiun terlebih dahulu sebelum mendaftarkan mesin.
                <a href="{{ route('stations.index') }}">Buka Data Stasiun</a>
            </div>
        @endif

        <div class="page-heading">
            <div>
                <h2>Master Data Mesin</h2>
                <p>Daftarkan mesin sebelum melakukan pencatatan HM harian.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('machines.store') }}" class="filter-panel master-form">
            @csrf
            <div>
                <label for="station_id">Stasiun</label>
                <select id="station_id" name="station_id" required class="form-control">
                    <option value="">Pilih stasiun</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected(old('station_id') == $station->id)>
                            {{ $station->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="master-name-field">
                <label for="name">Nama Mesin</label>
                <input id="name" name="name" value="{{ old('name') }}" required maxlength="255"
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
            <button type="submit" class="primary-button" @disabled($stations->isEmpty())>
                <i class="fa-solid fa-plus"></i> Tambah Mesin
            </button>
        </form>

        <section class="panel">
            <div class="panel-heading">
                <div class="panel-heading-icon"><i class="fa-solid fa-gears"></i></div>
                <div><h3>Daftar Mesin</h3><p>Mesin terdaftar pada setiap stasiun.</p></div>
            </div>
            <div class="overflow-x-auto">
                <table class="hm-table">
                    <thead><tr><th>NO</th><th>UNIT MESIN</th><th>STASIUN</th><th>PIC</th><th>TANGGAL PEMASANGAN</th><th>MERK / TIPE</th><th>HM MESIN</th><th>AKSI</th></tr></thead>
                    <tbody>
                        @forelse ($machines as $machine)
                            <tr>
                                <td class="row-number">{{ $machines->firstItem() + $loop->index }}</td>
                                <td class="machine-name">{{ $machine->name }}</td>
                                <td>{{ $machine->station->name }}</td>
                                <td>{{ $machine->pic ?: '-' }}</td>
                                <td>{{ $machine->installation_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $machine->brand_type ?: '-' }}</td>
                                <td>{{ number_format((float) $machine->hm_awal, 2, ',', '.') }}</td>
                                <td>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a class="table-action-link" href="{{ route('machines.edit', $machine) }}">
                                            Edit <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="POST" action="{{ route('machines.destroy', $machine) }}"
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
                            <tr><td class="empty-state" colspan="8">Belum ada mesin terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $machines->links() }}</div>
        </section>
    </div>
</x-app-layout>
