<x-app-layout>
    <x-slot name="header">
        <h1>Data Stasiun</h1>
        <p>Kelola area operasional mesin pabrik.</p>
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
                <h2>Master Data Stasiun</h2>
                <p>Pilih stasiun untuk mengubah data dan mengelola mesin di dalamnya.</p>
            </div>
            <div class="heading-actions">
                <span class="date-chip"><i class="fa-solid fa-industry"></i> {{ $stations->count() }} stasiun</span>
                <button class="primary-button" type="button" id="toggleStationForm" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
                    <i class="fa-solid fa-plus"></i> Tambah Stasiun
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('stations.store') }}"
              class="filter-panel master-form {{ $errors->any() ? '' : 'is-hidden' }}" id="stationCreateForm">
            @csrf
            <div class="master-name-field">
                <label for="name">Nama Stasiun</label>
                <input id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                       placeholder="Contoh: Sterilizer" class="form-control">
            </div>
            <div class="master-description-field">
                <label for="description">Keterangan</label>
                <textarea id="description" name="description" rows="2" maxlength="2000"
                          placeholder="Keterangan stasiun (opsional)" class="form-control">{{ old('description') }}</textarea>
            </div>
            <button type="submit" class="primary-button"><i class="fa-solid fa-floppy-disk"></i> Simpan Stasiun</button>
        </form>

        <section class="panel">
            <div class="panel-heading">
                <div class="panel-heading-icon"><i class="fa-solid fa-list"></i></div>
                <div><h3>Daftar Stasiun</h3><p>Area yang tersedia untuk input HM.</p></div>
            </div>
            <div class="overflow-x-auto">
                <table class="hm-table">
                    <thead><tr><th>NO</th><th>NAMA STASIUN</th><th>KETERANGAN</th><th>JUMLAH MESIN</th><th>AKSI</th></tr></thead>
                    <tbody>
                        @forelse ($stations as $station)
                            <tr>
                                <td class="row-number">{{ $loop->iteration }}</td>
                                <td class="machine-name">
                                    <a class="station-row-link" href="{{ route('stations.show', $station) }}">
                                        {{ $station->name }}
                                    </a>
                                </td>
                                <td>{{ $station->description ?: '-' }}</td>
                                <td>{{ number_format($station->machines_count) }} unit</td>
                                <td>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a class="table-action-link" href="{{ route('stations.show', $station) }}">
                                            Kelola <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                        <form method="POST" action="{{ route('stations.destroy', $station) }}"
                                              onsubmit="return confirm('Yakin ingin menghapus stasiun {{ addslashes($station->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    @disabled($station->machines_count > 0)
                                                    title="{{ $station->machines_count > 0 ? 'Hapus semua mesin terlebih dahulu' : 'Hapus stasiun' }}"
                                                    class="inline-flex items-center gap-1 rounded px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="empty-state" colspan="5">Belum ada stasiun.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <script>
        const toggleStationForm = document.getElementById('toggleStationForm');
        const stationCreateForm = document.getElementById('stationCreateForm');

        toggleStationForm.addEventListener('click', () => {
            const isHidden = stationCreateForm.classList.toggle('is-hidden');
            toggleStationForm.setAttribute('aria-expanded', String(!isHidden));
            if (!isHidden) document.getElementById('name').focus();
        });
    </script>
</x-app-layout>
