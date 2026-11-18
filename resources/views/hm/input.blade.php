<x-app-layout>
    <x-slot name="header">
        <h1>Sistem Informasi Manajemen Perawatan Mesin PKS</h1>
    </x-slot>

    <div class="hm-page">
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($isDashboard)
            <div class="stat-cards-grid">
                <article class="ref-stat-card">
                    <div class="stat-icon-box box-blue"><i class="fa-solid fa-gears"></i></div>
                    <div class="stat-meta"><span class="stat-label">Total Mesin</span><strong class="stat-number">{{ number_format($machineCount) }}</strong><span class="stat-caption">Unit terdaftar</span></div>
                </article>
                <article class="ref-stat-card">
                    <div class="stat-icon-box box-green"><i class="fa-solid fa-industry"></i></div>
                    <div class="stat-meta"><span class="stat-label">Total Stasiun</span><strong class="stat-number">{{ number_format($stationCount) }}</strong><span class="stat-caption">Area operasional</span></div>
                </article>
                <article class="ref-stat-card">
                    <div class="stat-icon-box box-orange"><i class="fa-regular fa-calendar-check"></i></div>
                    <div class="stat-meta"><span class="stat-label">Input Hari Ini</span><strong class="stat-number">{{ number_format($todayLogCount) }}</strong><span class="stat-caption">Pembacaan mesin</span></div>
                </article>
                <article class="ref-stat-card">
                    <div class="stat-icon-box box-purple"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="stat-meta"><span class="stat-label">HM Bulan Ini</span><strong class="stat-number">{{ number_format($monthHmTotal, 2, ',', '.') }}</strong><span class="stat-caption">Jam terpakai</span></div>
                </article>
            </div>
        @else
            <form method="GET" action="{{ route('hm.export') }}"
                  class="export-panel" id="export-card">
                <div class="export-intro">
                    <div class="export-icon"><i class="fa-solid fa-file-excel"></i></div>
                    <div><h3>Export Laporan Bulanan</h3><p>Unduh rekap HM dalam format Excel per bulan dan stasiun.</p></div>
                </div>
                <div class="export-controls">
                    <div>
                        <label for="month">Bulan</label>
                        <select id="month" name="month" class="form-control">
                            @for ($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}" @selected($month === (int) now()->month)>
                                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="year">Tahun</label>
                        <input id="year" name="year" type="number" min="2000" max="2100"
                               value="{{ now()->year }}"
                               class="form-control year-input">
                    </div>
                    <button type="submit" class="primary-button export-button">
                        <i class="fa-solid fa-download"></i>
                        Download Excel
                    </button>
                </div>
            </form>
        @endif

        @if (! $isDashboard)
        <div class="page-heading" id="station-filter">
            <div>
                <h2>Input HM Harian</h2>
                <p>Pilih tanggal dan stasiun, lalu masukkan pembacaan hourmeter setiap mesin.</p>
            </div>
            <span class="date-chip"><i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span>
        </div>

        <form method="GET" action="{{ route('hm.input') }}"
              class="filter-panel">
            <div>
                <label for="date">Tanggal Input</label>
                <input id="date" name="date" type="date" value="{{ $date }}" required
                       class="form-control">
            </div>
            <div>
                <label for="station_id">Stasiun</label>
                <select id="station_id" name="station_id" required
                        class="form-control">
                    <option value="">Pilih station</option>
                    @foreach ($stations as $item)
                        <option value="{{ $item->id }}"
                            @selected((string) request('station_id') === (string) $item->id)>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="brand_type">Merk / Tipe Mesin</label>
                <input id="brand_type" name="brand_type" type="search"
                       value="{{ $brandType }}" maxlength="255"
                       placeholder="Cari merk / tipe mesin"
                       class="form-control">
            </div>
            <button type="submit" class="primary-button">
                <i class="fa-solid fa-filter"></i>
                Tampilkan Mesin
            </button>
        </form>

        <script>
            document.getElementById('station_id').addEventListener('change', (event) => {
                const station = event.currentTarget;

                if (station.value) {
                    station.form.requestSubmit();
                }
            });
        </script>

        @if ($station)
            <form method="POST" action="{{ route('hm.store') }}"
                  class="panel" id="machine-list">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <input type="hidden" name="station_id" value="{{ $station->id }}">

                <div class="panel-heading">
                    <div class="panel-heading-icon"><i class="fa-solid fa-industry"></i></div>
                    <div><h3>{{ $station->name }}</h3><p>{{ $station->machines->count() }} unit mesin · {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</p></div>
                    <span class="panel-badge">HM READING</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="hm-table">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>UNIT MESIN</th>
                                <th>HM READING HARI INI</th>
                                <th>MERK / TIPE</th>
                                <th>PIC</th>
                                <th>HM MESIN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($station->machines as $machine)
                                @php($todayLog = $machine->hmLogs->first())
                                <tr>
                                    <td class="row-number">{{ $loop->iteration }}</td>
                                    <td class="machine-name">{{ $machine->name }}</td>
                                    <td>
                                        <input type="number" min="0" step="0.01"
                                               name="readings[{{ $machine->id }}]"
                                               value="{{ old('readings.'.$machine->id, $todayLog?->hm_reading) }}"
                                               placeholder="Masukkan HM"
                                               aria-label="HM harian {{ $machine->name }}"
                                               class="form-control reading-input">
                                    </td>
                                    <td>{{ $machine->brand_type ?: '-' }}</td>
                                    <td>{{ $machine->pic ?: '-' }}</td>
                                    <td>{{ number_format((float) $machine->hm_awal, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        {{ $brandType !== '' ? 'Tidak ada mesin dengan merk/tipe tersebut pada stasiun ini.' : 'Belum ada mesin terdaftar pada station ini.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($station->machines->isNotEmpty())
                    <button type="submit"
                            class="primary-button save-button">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan HM
                    </button>
                @endif
            </form>

        @endif

        @endif

    </div>
</x-app-layout>
