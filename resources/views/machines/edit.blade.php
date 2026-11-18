<x-app-layout>
    <x-slot name="header">
        <h1>Edit Mesin</h1>
        <p>Perbarui identitas dan informasi pemasangan mesin.</p>
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
                <h2>Edit {{ $machine->name }}</h2>
                <p>Ubah data mesin di bawah ini, lalu simpan perubahan.</p>
            </div>
            <a class="secondary-button"
               href="{{ $returnToStation ? route('stations.show', $machine->station) : route('machines.index') }}">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('machines.update', $machine) }}" class="panel station-detail-form">
            @csrf
            @method('PUT')
            @if ($returnToStation)
                <input type="hidden" name="return_to" value="station">
            @endif
            <div class="station-fields">
                <div>
                    <label for="station_id">Stasiun</label>
                    <select id="station_id" name="station_id" required class="form-control">
                        @foreach ($stations as $station)
                            <option value="{{ $station->id }}"
                                @selected(old('station_id', $machine->station_id) == $station->id)>
                                {{ $station->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="name">Nama Mesin</label>
                    <input id="name" name="name" value="{{ old('name', $machine->name) }}"
                           required maxlength="255" class="form-control">
                </div>
                <div>
                    <label for="pic">PIC</label>
                    <input id="pic" name="pic" value="{{ old('pic', $machine->pic) }}"
                           maxlength="255" class="form-control">
                </div>
                <div>
                    <label for="installation_date">Tanggal Pemasangan</label>
                    <input id="installation_date" name="installation_date" type="date"
                           value="{{ old('installation_date', $machine->installation_date?->toDateString()) }}"
                           max="{{ now()->toDateString() }}" class="form-control">
                </div>
                <div>
                    <label for="brand_type">Merk / Tipe Mesin</label>
                    <input id="brand_type" name="brand_type" value="{{ old('brand_type', $machine->brand_type) }}"
                           maxlength="255" class="form-control">
                </div>
                <div>
                    <label for="hm_awal">HM Mesin</label>
                    <input id="hm_awal" name="hm_awal" type="number" min="0" step="0.01"
                           value="{{ old('hm_awal', $machine->hm_awal) }}" required class="form-control">
                </div>
            </div>
            <button type="submit" class="primary-button">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
        </form>
    </div>
</x-app-layout>
