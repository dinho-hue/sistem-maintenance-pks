<x-app-layout>
    <x-slot name="header">
        <h1>Data Komponen</h1>
        <p>Kelola komponen pada mesin yang terdaftar.</p>
    </x-slot>

    <div class="master-page">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-error" role="alert">{{ session('error') }}</div>
        @endif
        @if ($stations->isEmpty())
            <div class="alert alert-info">
                Tambahkan stasiun dan mesin terlebih dahulu sebelum mendaftarkan komponen.
                <a href="{{ route('stations.index') }}">Buka Data Stasiun</a>
            </div>
        @elseif ($stations->every(fn ($station) => $station->machines->isEmpty()))
            <div class="alert alert-info">
                Belum ada mesin terdaftar. Tambahkan mesin terlebih dahulu melalui
                <a href="{{ route('machines.index') }}">Data Mesin</a>.
            </div>
        @endif

        <div class="page-heading">
            <div>
                <h2>Master Data Komponen</h2>
                <p>Pantau masa pakai dan catat pergantian komponen pada setiap mesin.</p>
            </div>
            <div class="heading-actions">
                <a class="secondary-button" href="{{ route('component-replacements.index') }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Pergantian
                </a>
                <button class="primary-button" type="button" id="toggleComponentForm"
                        aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
                    <i class="fa-solid fa-plus"></i> Tambah Komponen
                </button>
            </div>
        </div>

        <div class="stat-cards-grid">
            <article class="ref-stat-card">
                <div class="stat-icon-box box-blue"><i class="fa-solid fa-gears"></i></div>
                <div class="stat-meta"><span class="stat-label">Total Komponen</span><strong class="stat-number">{{ number_format($componentCount) }}</strong><span class="stat-caption">Komponen aktif</span></div>
            </article>
            <article class="ref-stat-card">
                <div class="stat-icon-box box-green"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-meta"><span class="stat-label">Normal</span><strong class="stat-number">{{ number_format($normalCount) }}</strong><span class="stat-caption">Masa pakai aman</span></div>
            </article>
            <article class="ref-stat-card">
                <div class="stat-icon-box box-orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="stat-meta"><span class="stat-label">Mendekati Batas</span><strong class="stat-number">{{ number_format($nearLimitCount) }}</strong><span class="stat-caption">Perlu dijadwalkan</span></div>
            </article>
            <article class="ref-stat-card">
                <div class="stat-icon-box box-purple"><i class="fa-solid fa-circle-exclamation"></i></div>
                <div class="stat-meta"><span class="stat-label">Lewat Batas</span><strong class="stat-number">{{ number_format($overLimitCount) }}</strong><span class="stat-caption">Perlu pemeriksaan</span></div>
            </article>
        </div>

        <form method="POST" action="{{ route('components.store') }}"
              class="filter-panel master-form {{ $errors->any() ? '' : 'is-hidden' }}" id="componentCreateForm">
            @csrf
            <div class="master-name-field">
                <label for="component_name">Nama Komponen</label>
                <input id="component_name" name="name" value="{{ old('name') }}" required maxlength="255"
                       placeholder="Masukkan nama komponen" class="form-control">
            </div>
            <div>
                <label for="component_station_id">Stasiun</label>
                <select id="component_station_id" name="station_id" required class="form-control">
                    <option value="">Pilih stasiun</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected(old('station_id') == $station->id)>
                            {{ $station->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="component_machine_id">Mesin</label>
                <select id="component_machine_id" name="machine_id" required class="form-control">
                    <option value="">Pilih mesin</option>
                    @foreach ($stations as $station)
                        @foreach ($station->machines as $machine)
                            <option value="{{ $machine->id }}" data-station-id="{{ $station->id }}"
                                @selected(old('machine_id') == $machine->id)>
                                {{ $machine->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div>
                <label for="installed_at">Tanggal Pemasangan</label>
                <input id="installed_at" name="installed_at" type="date" value="{{ old('installed_at') }}"
                       max="{{ now()->toDateString() }}" class="form-control">
            </div>
            <div>
                <label for="hm_installed">HM Saat Pemasangan</label>
                <input id="hm_installed" name="hm_installed" type="number" min="0" step="0.01"
                       value="{{ old('hm_installed') }}" class="form-control">
            </div>
            <div>
                <label for="target_lifetime_hm">Target Lifetime (HM)</label>
                <input id="target_lifetime_hm" name="target_lifetime_hm" type="number" min="0.01" step="0.01"
                       value="{{ old('target_lifetime_hm') }}" class="form-control">
            </div>
            <div>
                <label for="target_lifetime_months">Target Lifetime (Bulan)</label>
                <input id="target_lifetime_months" name="target_lifetime_months" type="number" min="1" step="1"
                       value="{{ old('target_lifetime_months') }}" class="form-control">
            </div>
            <button type="submit" class="primary-button" @disabled($stations->every(fn ($station) => $station->machines->isEmpty()))>
                <i class="fa-solid fa-floppy-disk"></i> Simpan Komponen
            </button>
        </form>

        <form method="GET" action="{{ route('components.index') }}" class="filter-panel">
            <div>
                <label for="filter_station_id">Stasiun</label>
                <select id="filter_station_id" name="station_id" class="form-control">
                    <option value="">Semua stasiun</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected(request('station_id') == $station->id)>
                            {{ $station->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_machine_id">Mesin</label>
                <select id="filter_machine_id" name="machine_id" class="form-control">
                    <option value="">Semua mesin</option>
                    @foreach ($stations as $station)
                        @foreach ($station->machines as $machine)
                            <option value="{{ $machine->id }}" data-station-id="{{ $station->id }}"
                                @selected(request('machine_id') == $machine->id)>
                                {{ $machine->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div>
                <label for="component_search">Cari komponen</label>
                <input id="component_search" name="search" value="{{ request('search') }}"
                       maxlength="255" placeholder="Cari nama komponen" class="form-control">
            </div>
            <button type="submit" class="primary-button"><i class="fa-solid fa-filter"></i> Filter</button>
            <a href="{{ route('components.index') }}" class="secondary-button">Reset</a>
        </form>

        <section class="panel">
            <div class="panel-heading">
                <div class="panel-heading-icon"><i class="fa-solid fa-gears"></i></div>
                <div><h3>Daftar Komponen</h3><p>Komponen terdaftar pada setiap mesin.</p></div>
            </div>
            <div class="overflow-x-auto">
                <table class="hm-table">
                    <thead>
                        <tr>
                            <th>NO</th><th>NAMA KOMPONEN</th><th>STASIUN</th><th>MESIN</th>
                            <th>TANGGAL PASANG</th><th>PEMAKAIAN</th><th>SISA HM</th><th>STATUS</th><th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($components as $part)
                            <tr>
                                <td class="row-number">{{ $components->firstItem() + $loop->index }}</td>
                                <td class="machine-name">{{ $part->name }}</td>
                                <td>{{ $part->machine->station->name }}</td>
                                <td>{{ $part->machine->name }}</td>
                                <td>{{ $part->installed_at?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    @if ($part->hm_usage !== null)
                                        <span>{{ number_format($part->hm_usage, 2, ',', '.') }} HM</span>
                                        @if ($part->hm_usage_percent !== null)
                                            <div class="component-lifetime-bar">
                                                <span style="width: {{ $part->hm_usage_percent }}%"></span>
                                            </div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $part->hm_remaining === null ? '-' : number_format($part->hm_remaining, 2, ',', '.').' HM' }}</td>
                                <td>
                                    <span class="component-status component-status-{{ \Illuminate\Support\Str::slug($part->status) }}">
                                        {{ $part->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="component-actions">
                                        <a class="table-action-link" href="{{ route('components.show', $part) }}" title="Detail komponen">
                                            Detail <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                        <a class="table-action-link" href="{{ route('components.replacements.create', $part) }}" title="Ganti komponen">
                                            Ganti <i class="fa-solid fa-rotate"></i>
                                        </a>
                                        <a class="table-action-link" href="{{ route('components.edit', $part) }}">
                                            Edit <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="POST" action="{{ route('components.destroy', $part) }}"
                                              onsubmit="return confirm('Yakin ingin menghapus komponen {{ addslashes($part->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    @disabled($part->replacements_count > 0)
                                                    title="{{ $part->replacements_count > 0 ? 'Komponen memiliki riwayat pergantian' : 'Hapus komponen' }}"
                                                    class="inline-flex items-center gap-1 rounded px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="empty-state" colspan="9">Belum ada data komponen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $components->links() }}</div>
        </section>
    </div>

    <script>
        const toggleComponentForm = document.getElementById('toggleComponentForm');
        const componentCreateForm = document.getElementById('componentCreateForm');

        toggleComponentForm.addEventListener('click', () => {
            const isHidden = componentCreateForm.classList.toggle('is-hidden');
            toggleComponentForm.setAttribute('aria-expanded', String(!isHidden));
            if (!isHidden) document.getElementById('component_name').focus();
        });

        function filterMachinesByStation(stationSelect, machineSelect) {
            const stationId = stationSelect.value;
            const selectedMachine = machineSelect.value;
            let selectedMachineIsVisible = false;

            Array.from(machineSelect.options).forEach((option) => {
                if (!option.value) return;

                const matchesStation = !stationId || option.dataset.stationId === stationId;
                option.hidden = !matchesStation;
                option.disabled = !matchesStation;
                if (option.value === selectedMachine && matchesStation) selectedMachineIsVisible = true;
            });

            if (!selectedMachineIsVisible) machineSelect.value = '';
        }

        const componentStation = document.getElementById('component_station_id');
        const componentMachine = document.getElementById('component_machine_id');
        componentStation.addEventListener('change', () => filterMachinesByStation(componentStation, componentMachine));
        filterMachinesByStation(componentStation, componentMachine);

        const filterStation = document.getElementById('filter_station_id');
        const filterMachine = document.getElementById('filter_machine_id');
        filterStation.addEventListener('change', () => filterMachinesByStation(filterStation, filterMachine));
        filterMachinesByStation(filterStation, filterMachine);
    </script>
</x-app-layout>
