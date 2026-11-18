<x-app-layout>
    <x-slot name="header">
        <h1>Form Pergantian Komponen</h1>
        <p>Catat pelepasan komponen lama dan pemasangan komponen baru.</p>
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
                <h2>Pergantian {{ $part->name }}</h2>
                <p>{{ $part->machine->station->name }} · {{ $part->machine->name }}</p>
            </div>
            <a class="secondary-button" href="{{ route('components.show', $part) }}">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Detail
            </a>
        </div>

        <form method="POST" action="{{ route('components.replacements.store', $part) }}" class="panel replacement-form">
            @csrf
            <div class="replacement-columns">
                <section class="replacement-section">
                    <div class="replacement-section-heading">
                        <span class="replacement-section-icon"><i class="fa-solid fa-box-open"></i></span>
                        <div><h3>Data Komponen Lama</h3><p>Informasi komponen yang dilepas.</p></div>
                    </div>
                    <dl class="replacement-old-details">
                        <div><dt>Stasiun</dt><dd>{{ $part->machine->station->name }}</dd></div>
                        <div><dt>Mesin</dt><dd>{{ $part->machine->name }}</dd></div>
                        <div><dt>Nama Komponen</dt><dd>{{ $part->name }}</dd></div>
                        <div><dt>Tanggal Pemasangan</dt><dd>{{ $part->installed_at?->format('d/m/Y') ?? 'Belum diatur' }}</dd></div>
                        <div><dt>HM Saat Pemasangan</dt><dd>{{ $part->hm_installed === null ? 'Belum diatur' : number_format((float) $part->hm_installed, 2, ',', '.') }}</dd></div>
                    </dl>
                    <div class="replacement-field-grid">
                        <div>
                            <label for="removed_at">Tanggal Pelepasan</label>
                            <input id="removed_at" name="removed_at" type="date"
                                   value="{{ old('removed_at', now()->toDateString()) }}"
                                   max="{{ now()->toDateString() }}" required class="form-control">
                        </div>
                        <div>
                            <label for="hm_removed">HM Saat Pelepasan</label>
                            <input id="hm_removed" name="hm_removed" type="number" min="0" step="0.01"
                                   value="{{ old('hm_removed', number_format($part->current_hm, 2, '.', '')) }}"
                                   required class="form-control">
                        </div>
                    </div>
                </section>

                <section class="replacement-section">
                    <div class="replacement-section-heading">
                        <span class="replacement-section-icon replacement-section-icon-green"><i class="fa-solid fa-box"></i></span>
                        <div><h3>Data Komponen Baru</h3><p>Informasi komponen yang dipasang.</p></div>
                    </div>
                    <div class="replacement-field-grid">
                        <div class="replacement-field-wide">
                            <label for="new_name">Nama Komponen</label>
                            <input id="new_name" name="new_name" value="{{ old('new_name', $part->name) }}"
                                   required maxlength="255" class="form-control">
                        </div>
                        <div>
                            <label for="installed_at_new">Tanggal Pemasangan</label>
                            <input id="installed_at_new" name="installed_at_new" type="date"
                                   value="{{ old('installed_at_new', now()->toDateString()) }}"
                                   max="{{ now()->toDateString() }}" required class="form-control">
                        </div>
                        <div>
                            <label for="hm_installed_new">HM Saat Pemasangan</label>
                            <input id="hm_installed_new" name="hm_installed_new" type="number" min="0" step="0.01"
                                   value="{{ old('hm_installed_new', number_format($part->current_hm, 2, '.', '')) }}"
                                   required class="form-control">
                        </div>
                        <div>
                            <label for="target_lifetime_hm_new">Target Lifetime (HM)</label>
                            <input id="target_lifetime_hm_new" name="target_lifetime_hm_new" type="number"
                                   min="0.01" step="0.01" value="{{ old('target_lifetime_hm_new') }}"
                                   required class="form-control">
                        </div>
                        <div>
                            <label for="target_lifetime_months_new">Target Lifetime (Kalender)</label>
                            <div class="input-suffix">
                                <input id="target_lifetime_months_new" name="target_lifetime_months_new"
                                       type="number" min="1" step="1"
                                       value="{{ old('target_lifetime_months_new') }}" required class="form-control">
                                <span>Bulan</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="replacement-notes-grid">
                <div>
                    <label for="reason">Alasan Pergantian</label>
                    <select id="reason" name="reason" required class="form-control">
                        @foreach (['Perawatan Berkala', 'Kerusakan', 'Lainnya'] as $reason)
                            <option value="{{ $reason }}" @selected(old('reason', 'Perawatan Berkala') === $reason)>
                                {{ $reason }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="notes">Keterangan</label>
                    <textarea id="notes" name="notes" rows="3" maxlength="5000" class="form-control"
                              placeholder="Tambahkan keterangan pergantian (opsional)">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="replacement-form-actions">
                <a class="secondary-button" href="{{ route('components.show', $part) }}">Batal</a>
                <button type="submit" class="primary-button">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Pergantian
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
