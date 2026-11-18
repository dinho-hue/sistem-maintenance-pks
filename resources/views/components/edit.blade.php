<x-app-layout>
    <x-slot name="header">
        <h1>Edit Komponen</h1>
        <p>Perbarui nama komponen dan mesin terkait.</p>
    </x-slot>

    <div class="master-page">
        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="page-heading">
            <div>
                <h2>Edit {{ $part->name }}</h2>
                <p>Pilih stasiun dan mesin yang sesuai, lalu simpan perubahan.</p>
            </div>
            <a class="secondary-button" href="{{ route('components.index') }}">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('components.update', $part) }}" class="panel station-detail-form">
            @csrf
            @method('PUT')
            <div class="station-fields">
                <div>
                    <label for="name">Nama Komponen</label>
                    <input id="name" name="name" value="{{ old('name', $part->name) }}"
                           required maxlength="255" class="form-control">
                </div>
                <div>
                    <label for="station_id">Stasiun</label>
                    <select id="station_id" name="station_id" required class="form-control">
                        <option value="">Pilih stasiun</option>
                        @foreach ($stations as $station)
                            <option value="{{ $station->id }}"
                                @selected(old('station_id', $part->machine->station_id) == $station->id)>
                                {{ $station->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="machine_id">Mesin</label>
                    <select id="machine_id" name="machine_id" required class="form-control">
                        <option value="">Pilih mesin</option>
                        @foreach ($stations as $station)
                            @foreach ($station->machines as $machine)
                                <option value="{{ $machine->id }}" data-station-id="{{ $station->id }}"
                                    @selected(old('machine_id', $part->machine_id) == $machine->id)>
                                    {{ $machine->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="installed_at">Tanggal Pemasangan</label>
                    <input id="installed_at" name="installed_at" type="date"
                           value="{{ old('installed_at', $part->installed_at?->toDateString()) }}"
                           max="{{ now()->toDateString() }}" class="form-control">
                </div>
                <div>
                    <label for="hm_installed">HM Saat Pemasangan</label>
                    <input id="hm_installed" name="hm_installed" type="number" min="0" step="0.01"
                           value="{{ old('hm_installed', $part->hm_installed) }}" class="form-control">
                </div>
                <div>
                    <label for="target_lifetime_hm">Target Lifetime (HM)</label>
                    <input id="target_lifetime_hm" name="target_lifetime_hm" type="number" min="0.01" step="0.01"
                           value="{{ old('target_lifetime_hm', $part->target_lifetime_hm) }}" class="form-control">
                </div>
                <div>
                    <label for="target_lifetime_months">Target Lifetime (Bulan)</label>
                    <input id="target_lifetime_months" name="target_lifetime_months" type="number" min="1" step="1"
                           value="{{ old('target_lifetime_months', $part->target_lifetime_months) }}" class="form-control">
                </div>
            </div>
            <button type="submit" class="primary-button station-save-button">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
        </form>
    </div>

    <script>
        const stationSelect = document.getElementById('station_id');
        const machineSelect = document.getElementById('machine_id');

        function filterMachinesByStation() {
            const stationId = stationSelect.value;
            const selectedMachine = machineSelect.value;
            let selectedMachineIsVisible = false;

            Array.from(machineSelect.options).forEach((option) => {
                if (!option.value) return;

                const matchesStation = option.dataset.stationId === stationId;
                option.hidden = !matchesStation;
                option.disabled = !matchesStation;
                if (option.value === selectedMachine && matchesStation) selectedMachineIsVisible = true;
            });

            if (!selectedMachineIsVisible) machineSelect.value = '';
        }

        stationSelect.addEventListener('change', filterMachinesByStation);
        filterMachinesByStation();
    </script>
</x-app-layout>
