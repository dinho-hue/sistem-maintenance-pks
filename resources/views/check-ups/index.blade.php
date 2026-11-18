<x-app-layout>
    <x-slot name="header">
        <h1>Check Up Mesin</h1>
        <p>Catat pemeriksaan dan perbaikan mesin beserta hour meter.</p>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h2 class="mb-6 text-lg font-semibold text-gray-900">Input Check Up</h2>

            <form method="POST" action="{{ route('check-ups.store') }}" class="grid gap-5 md:grid-cols-2">
                @csrf

                <div>
                    <label for="station_id" class="mb-1 block text-sm font-medium text-gray-700">Nama Stasiun</label>
                    <select id="station_id" name="station_id" required
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Pilih stasiun</option>
                        @foreach ($stations as $station)
                            <option value="{{ $station->id }}" @selected(old('station_id') == $station->id)>
                                {{ $station->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="nama_teknisi" class="mb-1 block text-sm font-medium text-gray-700">Nama Teknisi</label>
                    <input id="nama_teknisi" name="nama_teknisi" type="text" value="{{ old('nama_teknisi') }}"
                           maxlength="255" required
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="machine_id" class="mb-1 block text-sm font-medium text-gray-700">Mesin yang Diperbaiki</label>
                    <select id="machine_id" name="machine_id" required disabled
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100">
                        <option value="">Pilih stasiun terlebih dahulu</option>
                        @foreach ($stations as $station)
                            @foreach ($station->machines as $machine)
                                <option value="{{ $machine->id }}" data-station="{{ $station->id }}"
                                        @selected(old('machine_id') == $machine->id)>
                                    {{ $machine->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tanggal" class="mb-1 block text-sm font-medium text-gray-700">Hari / Tanggal</label>
                    <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', now()->toDateString()) }}" required
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="hm_perbaikan" class="mb-1 block text-sm font-medium text-gray-700">HM Perbaikan</label>
                    <input id="hm_perbaikan" name="hm_perbaikan" type="number" min="0" step="0.01"
                           value="{{ old('hm_perbaikan') }}" required
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label for="keterangan" class="mb-1 block text-sm font-medium text-gray-700">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="4" maxlength="5000"
                              class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('keterangan') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Simpan Check Up
                    </button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Check Up</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left font-medium text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3">Stasiun</th>
                            <th class="px-6 py-3">Mesin</th>
                            <th class="px-6 py-3">Teknisi</th>
                            <th class="px-6 py-3">HM</th>
                            <th class="px-6 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($checkUps as $checkUp)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4">{{ $checkUp->tanggal->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">{{ $checkUp->station?->name ?? $checkUp->legacyStation?->nama_stasiun ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $checkUp->machine?->name ?? $checkUp->legacyMachine?->nama_mesin ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $checkUp->nama_teknisi }}</td>
                                <td class="whitespace-nowrap px-6 py-4">{{ number_format((float) $checkUp->hm_perbaikan, 2, ',', '.') }}</td>
                                <td class="px-6 py-4">{{ $checkUp->keterangan ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat check up.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($checkUps->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">{{ $checkUps->links() }}</div>
            @endif
        </section>
        <div class="check-up-export">
            <a href="{{ route('check-ups.export') }}" class="primary-button export-button">
                <i class="fa-solid fa-file-excel" aria-hidden="true"></i>
                Download Excel Check Up
            </a>
        </div>
    </div>

    <script>
        (() => {
            const station = document.getElementById('station_id');
            const machine = document.getElementById('machine_id');
            const initialMachine = @json(old('machine_id'));

            const filterMachines = () => {
                const stationId = station.value;
                let selectedAvailable = false;

                Array.from(machine.options).forEach((option, index) => {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }

                    const available = option.dataset.station === stationId;
                    option.hidden = !available;
                    if (available && option.value === machine.value) {
                        selectedAvailable = true;
                    }
                });

                machine.disabled = !stationId;
                if (!selectedAvailable) {
                    machine.value = '';
                }
                machine.options[0].textContent = stationId ? 'Pilih mesin' : 'Pilih stasiun terlebih dahulu';
            };

            if (initialMachine) {
                machine.value = initialMachine;
            }
            station.addEventListener('change', filterMachines);
            filterMachines();
        })();
    </script>
</x-app-layout>
