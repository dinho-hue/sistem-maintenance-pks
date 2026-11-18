<x-app-layout>
    <x-slot name="header">
        <h1>Laporan</h1>
        <p>Unduh laporan Check Up Mesin, hourmeter, dan riwayat komponen dalam format Excel.</p>
    </x-slot>

    <div class="hm-page">
        <div class="export-panel">
            <div class="export-intro">
                <div class="export-icon"><i class="fa-solid fa-file-excel"></i></div>
                <div><h3>Export Laporan Check Up Mesin</h3><p>Unduh riwayat pemeriksaan dan perbaikan mesin dalam format Excel.</p></div>
            </div>
            <a href="{{ route('check-ups.export') }}" class="primary-button export-button">
                <i class="fa-solid fa-download"></i>
                Download Excel Check Up Mesin
            </a>
        </div>

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

        <div class="export-panel">
            <div class="export-intro">
                <div class="export-icon"><i class="fa-solid fa-file-excel"></i></div>
                <div>
                    <h3>Export Riwayat Pergantian Komponen</h3>
                    <p>Unduh seluruh riwayat pemasangan dan pelepasan komponen dalam format Excel.</p>
                </div>
            </div>
            <a href="{{ route('component-replacements.export') }}" class="primary-button export-button">
                <i class="fa-solid fa-download"></i>
                Download Excel Riwayat Komponen
            </a>
        </div>
    </div>
</x-app-layout>
