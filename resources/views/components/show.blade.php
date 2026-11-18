<x-app-layout>
    <x-slot name="header">
        <h1>Detail Komponen</h1>
        <p>Informasi pemakaian dan riwayat pergantian komponen.</p>
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

        <div class="page-heading">
            <div>
                <h2>{{ $part->name }}</h2>
                <p>{{ $part->machine->station->name }} · {{ $part->machine->name }}</p>
            </div>
            <div class="heading-actions">
                <a class="secondary-button" href="{{ route('components.index') }}">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
                <a class="primary-button" href="{{ route('components.replacements.create', $part) }}">
                    <i class="fa-solid fa-rotate"></i> Ganti Komponen
                </a>
            </div>
        </div>

        <div class="component-detail-layout">
            <section class="panel component-overview">
                <div class="component-overview-icon"><i class="fa-solid fa-gears"></i></div>
                <div class="component-overview-content">
                    <div class="component-overview-heading">
                        <div>
                            <span class="component-eyebrow">KOMPONEN AKTIF</span>
                            <h3>{{ $part->name }}</h3>
                        </div>
                        <span class="component-status component-status-{{ \Illuminate\Support\Str::slug($part->status) }}">
                            {{ $part->status }}
                        </span>
                    </div>
                    <dl class="component-details-grid">
                        <div><dt>Stasiun</dt><dd>{{ $part->machine->station->name }}</dd></div>
                        <div><dt>Mesin</dt><dd>{{ $part->machine->name }}</dd></div>
                        <div><dt>Tanggal Pemasangan</dt><dd>{{ $part->installed_at?->translatedFormat('d F Y') ?? 'Belum diatur' }}</dd></div>
                        <div><dt>HM Saat Pemasangan</dt><dd>{{ $part->hm_installed === null ? 'Belum diatur' : number_format((float) $part->hm_installed, 2, ',', '.') }}</dd></div>
                        <div><dt>Target Lifetime HM</dt><dd>{{ $part->target_lifetime_hm === null ? 'Belum diatur' : number_format((float) $part->target_lifetime_hm, 2, ',', '.') }}</dd></div>
                        <div><dt>Target Lifetime Kalender</dt><dd>{{ $part->target_lifetime_months ? $part->target_lifetime_months.' Bulan' : 'Belum diatur' }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="component-life-card">
                <div class="component-life-heading">
                    <span>Pemakaian HM</span>
                    <i class="fa-solid fa-gauge-high"></i>
                </div>
                @if ($part->hm_usage_percent !== null)
                    <strong>{{ number_format($part->hm_usage, 2, ',', '.') }} HM <small>({{ $part->hm_usage_percent }}%)</small></strong>
                    <div class="component-progress"><span style="width: {{ $part->hm_usage_percent }}%"></span></div>
                    <p>Sisa {{ number_format($part->hm_remaining, 2, ',', '.') }} HM dari target {{ number_format((float) $part->target_lifetime_hm, 2, ',', '.') }} HM</p>
                @else
                    <strong>Belum diatur</strong>
                    <p>Isi HM pemasangan dan target lifetime pada data komponen.</p>
                @endif
            </section>

            <section class="component-life-card">
                <div class="component-life-heading">
                    <span>Pemakaian Kalender</span>
                    <i class="fa-regular fa-calendar"></i>
                </div>
                @if ($part->calendar_usage_percent !== null)
                    <strong>{{ $part->target_lifetime_months - $part->calendar_months_remaining }} Bulan <small>({{ $part->calendar_usage_percent }}%)</small></strong>
                    <div class="component-progress component-progress-blue"><span style="width: {{ $part->calendar_usage_percent }}%"></span></div>
                    <p>Sisa {{ $part->calendar_months_remaining }} Bulan dari target {{ $part->target_lifetime_months }} Bulan</p>
                @else
                    <strong>Belum diatur</strong>
                    <p>Isi tanggal pemasangan dan target lifetime kalender.</p>
                @endif
            </section>
        </div>

        <section class="panel">
            <div class="panel-heading">
                <div class="panel-heading-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div><h3>Riwayat Pergantian Komponen</h3><p>Riwayat untuk {{ $part->name }} pada mesin ini.</p></div>
                <a class="table-action-link panel-badge" href="{{ route('component-replacements.index', ['component_id' => $part->id]) }}">
                    Lihat Semua Riwayat <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="hm-table">
                    <thead>
                        <tr><th>NO</th><th>TANGGAL PASANG</th><th>TANGGAL LEPAS</th><th>HM PASANG</th><th>HM LEPAS</th><th>PEMAKAIAN</th><th>KETERANGAN</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($part->replacements as $replacement)
                            <tr>
                                <td class="row-number">{{ $loop->iteration }}</td>
                                <td>{{ $replacement->installed_at?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $replacement->removed_at->format('d/m/Y') }}</td>
                                <td>{{ $replacement->hm_installed === null ? '-' : number_format((float) $replacement->hm_installed, 2, ',', '.') }}</td>
                                <td>{{ number_format((float) $replacement->hm_removed, 2, ',', '.') }}</td>
                                <td>{{ $replacement->hm_installed === null ? '-' : number_format(max(0, (float) $replacement->hm_removed - (float) $replacement->hm_installed), 2, ',', '.').' HM' }}</td>
                                <td>{{ $replacement->reason }}{{ $replacement->notes ? ': '.$replacement->notes : '' }}</td>
                            </tr>
                        @empty
                            <tr><td class="empty-state" colspan="7">Belum ada riwayat pergantian komponen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
