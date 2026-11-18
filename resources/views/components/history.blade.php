<x-app-layout>
    <x-slot name="header">
        <h1>Riwayat Pergantian Komponen</h1>
        <p>Riwayat lengkap pemasangan dan pelepasan komponen mesin.</p>
    </x-slot>

    <div class="master-page">
        <div class="page-heading">
            <div>
                <h2>Riwayat Pergantian Komponen</h2>
                <p>Gunakan filter untuk menemukan riwayat pada stasiun, mesin, atau periode tertentu.</p>
            </div>
            <a class="secondary-button" href="{{ route('components.index') }}">
                <i class="fa-solid fa-arrow-left"></i> Data Komponen
            </a>
        </div>

        <form method="GET" action="{{ route('component-replacements.index') }}" class="filter-panel">
            <div>
                <label for="history_station_id">Stasiun</label>
                <select id="history_station_id" name="station_id" class="form-control">
                    <option value="">Semua stasiun</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected(request('station_id') == $station->id)>
                            {{ $station->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="history_machine_id">Mesin</label>
                <select id="history_machine_id" name="machine_id" class="form-control">
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
                <label for="history_component_id">Komponen</label>
                <select id="history_component_id" name="component_id" class="form-control">
                    <option value="">Semua komponen</option>
                    @foreach ($components as $part)
                        <option value="{{ $part->id }}" @selected(request('component_id') == $part->id)>
                            {{ $part->name }} · {{ $part->machine->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from">Tanggal Mulai</label>
                <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}"
                       class="form-control">
            </div>
            <div>
                <label for="date_to">Tanggal Akhir</label>
                <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}"
                       class="form-control">
            </div>
            <div>
                <label for="history_search">Cari nama komponen</label>
                <input id="history_search" name="search" value="{{ request('search') }}"
                       maxlength="255" placeholder="Cari komponen" class="form-control">
            </div>
            <button type="submit" class="primary-button"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
            <a class="secondary-button" href="{{ route('component-replacements.index') }}">Reset</a>
        </form>

        <section class="panel">
            <div class="panel-heading">
                <div class="panel-heading-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div><h3>Daftar Riwayat</h3><p>{{ number_format($replacements->total()) }} catatan pergantian.</p></div>
            </div>
            <div class="overflow-x-auto">
                <table class="hm-table">
                    <thead>
                        <tr>
                            <th>NO</th><th>NAMA KOMPONEN</th><th>STASIUN</th><th>MESIN</th>
                            <th>TANGGAL PASANG</th><th>TANGGAL LEPAS</th><th>HM PASANG</th>
                            <th>HM LEPAS</th><th>PEMAKAIAN</th><th>KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($replacements as $replacement)
                            <tr>
                                <td class="row-number">{{ $replacements->firstItem() + $loop->index }}</td>
                                <td class="machine-name">
                                    <a class="station-row-link" href="{{ route('components.show', $replacement->component) }}">
                                        {{ $replacement->old_name }}
                                    </a>
                                    <small class="replacement-new-name">Diganti menjadi {{ $replacement->new_name }}</small>
                                </td>
                                <td>{{ $replacement->component->machine->station->name }}</td>
                                <td>{{ $replacement->component->machine->name }}</td>
                                <td>{{ $replacement->installed_at?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $replacement->removed_at->format('d/m/Y') }}</td>
                                <td>{{ $replacement->hm_installed === null ? '-' : number_format((float) $replacement->hm_installed, 2, ',', '.') }}</td>
                                <td>{{ number_format((float) $replacement->hm_removed, 2, ',', '.') }}</td>
                                <td>{{ $replacement->hm_installed === null ? '-' : number_format(max(0, (float) $replacement->hm_removed - (float) $replacement->hm_installed), 2, ',', '.').' HM' }}</td>
                                <td>{{ $replacement->reason }}{{ $replacement->notes ? ': '.$replacement->notes : '' }}</td>
                            </tr>
                        @empty
                            <tr><td class="empty-state" colspan="10">Belum ada riwayat pergantian komponen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $replacements->links() }}</div>
        </section>
    </div>

    <script>
        const historyStation = document.getElementById('history_station_id');
        const historyMachine = document.getElementById('history_machine_id');

        function filterHistoryMachines() {
            const stationId = historyStation.value;
            const selectedMachine = historyMachine.value;
            let selectedMachineIsVisible = false;

            Array.from(historyMachine.options).forEach((option) => {
                if (!option.value) return;

                const matchesStation = !stationId || option.dataset.stationId === stationId;
                option.hidden = !matchesStation;
                option.disabled = !matchesStation;
                if (option.value === selectedMachine && matchesStation) selectedMachineIsVisible = true;
            });

            if (!selectedMachineIsVisible) historyMachine.value = '';
        }

        historyStation.addEventListener('change', filterHistoryMachines);
        filterHistoryMachines();
    </script>
</x-app-layout>
