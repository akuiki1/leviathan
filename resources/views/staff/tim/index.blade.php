<x-staff-layout>
    @php
        $statusMeta = [
            'approved' => ['label' => 'Disetujui', 'fg' => '#17915F', 'bg' => '#E4F5EE'],
            'pending'  => ['label' => 'Menunggu Persetujuan', 'fg' => '#B96E00', 'bg' => '#FBF0DC'],
            'rejected' => ['label' => 'Ditolak', 'fg' => '#D14343', 'bg' => '#FBEAEA'],
        ];
        $honorColor = [
            \App\Services\HonorService::DIBAYAR                => ['fg' => '#17915F', 'bg' => '#E4F5EE'],
            \App\Services\HonorService::TIDAK_DIBAYAR           => ['fg' => '#5C6B85', 'bg' => '#EDF0F7'],
            \App\Services\HonorService::PREDIKSI_DIBAYAR        => ['fg' => '#3562E3', 'bg' => '#3562E314'],
            \App\Services\HonorService::PREDIKSI_TIDAK_DIBAYAR  => ['fg' => '#5C6B85', 'bg' => '#EDF0F7'],
        ];
    @endphp

    <div class="flex-col gap-20">

        @if (session('success'))
            <section class="card success-panel">
                <div class="success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
                </div>
                <h2 style="margin: 0; font-size: 22px; font-weight: 800;">Tim berhasil diajukan</h2>
                <p style="margin: 0; font-size: 14px; color: var(--muted); line-height: 1.6; max-width: 420px;">
                    @if (session('teamName'))
                        <strong>{{ session('teamName') }}</strong>
                    @else
                        Tim Anda
                    @endif
                    kini berstatus <span style="font-weight: 700; color: var(--warning);">Menunggu Persetujuan</span>.
                    Anda akan melihat hasil tinjauan admin di halaman ini.
                </p>
                <div class="flex gap-10 flex-wrap" style="margin-top: 18px; justify-content: center;">
                    <a href="{{ route('staff.dashboard.index') }}" class="btn btn-secondary">Ke Beranda</a>
                </div>
            </section>
        @endif

        <div class="flex items-center justify-between gap-16 flex-wrap">
            <div>
                <h1 class="h1-page">Riwayat Tim <span style="color: var(--muted2); font-weight: 700;">(PTATK)</span></h1>
                <div class="sub-page">Rekap keikutsertaan tim dan status honorarium Anda per tahun anggaran.</div>
            </div>
            <div role="tablist" aria-label="Tahun anggaran" class="year-tabs">
                @foreach ($tahunList as $y)
                    <a href="{{ route('staff.tim.index', ['tahun' => $y]) }}" role="tab" class="year-tab {{ (int) $y === $tahun ? 'active' : '' }}">{{ $y }}</a>
                @endforeach
            </div>
        </div>

        <section class="card flex items-center gap-20 flex-wrap" style="padding: 18px 22px;">
            <div style="flex: 1 1 240px; min-width: 220px;">
                <div style="font-size: 12px; font-weight: 800; letter-spacing: .8px; color: var(--muted2); margin-bottom: 10px;">KUOTA HONOR TAHUN {{ $tahun }}</div>
                @if ($maksHonor > 0)
                    <div class="flex gap-6">
                        @for ($i = 0; $i < $maksHonor; $i++)
                            <div class="slot-bar slot-bar-sm {{ $i < $ringkasan['jumlah_dibayar'] ? 'filled' : '' }}"></div>
                        @endfor
                    </div>
                @else
                    <div class="text-muted" style="font-size: 13px;">Tidak ada informasi kuota.</div>
                @endif
            </div>
            <div class="flex gap-16" style="gap: 26px; flex-wrap: wrap;">
                <div><div style="font-size: 20px; font-weight: 800;">{{ $ringkasan['jumlah_dibayar'] }}<span style="font-size: 13px; color: var(--muted2); font-weight: 600;">/{{ $maksHonor }}</span></div><div class="text-muted2" style="font-size: 12px;">Honor dibayar</div></div>
                <div><div style="font-size: 20px; font-weight: 800;">{{ $tims->count() }}</div><div class="text-muted2" style="font-size: 12px;">Total tim</div></div>
                <div><div style="font-size: 20px; font-weight: 800; color: var(--success);">Rp {{ number_format($ringkasan['total_honor'], 0, ',', '.') }}</div><div class="text-muted2" style="font-size: 12px;">Honor diterima</div></div>
            </div>
        </section>

        {{-- Desktop table --}}
        <section class="card staff-desktop-only" style="overflow: hidden; flex-direction: column;">
            <div class="ptatk-table-head">
                <div>NAMA TIM</div><div>DIBUAT</div><div>STATUS TIM</div><div>HONOR ANDA</div><div></div>
            </div>
            @forelse ($tims as $tim)
                @php
                    $sm = $statusMeta[$tim->status];
                    $mine = $statusHonorPerTim[$user->id][$tim->id] ?? null;
                    $hc = $mine ? $honorColor[$mine['status']] : null;
                @endphp
                <div class="ptatk-row-wrap">
                    <button type="button" class="ptatk-row-head" data-toggle-target="ptatk-body-{{ $tim->id }}" aria-expanded="false">
                        <div>
                            <div style="font-size: 14px; font-weight: 700;">{{ $tim->nama_tim }}</div>
                            <div class="text-muted2" style="font-size: 12px; font-weight: 500; margin-top: 2px;">{{ $tim->users->count() }} anggota</div>
                        </div>
                        <div class="text-muted" style="font-size: 13px;">{{ $tim->created_at->locale('id')->translatedFormat('d M Y') }}</div>
                        <div><span class="badge-pill" style="background: {{ $sm['bg'] }}; color: {{ $sm['fg'] }};">{{ $sm['label'] }}</span></div>
                        <div>
                            @if ($mine)
                                <span class="badge-pill" style="background: {{ $hc['bg'] }}; color: {{ $hc['fg'] }};">{{ $mine['label'] }}</span>
                            @endif
                        </div>
                        <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="#8B99B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                    </button>
                    <div id="ptatk-body-{{ $tim->id }}" class="ptatk-row-body" hidden>
                        <p style="margin: 10px 0 14px; font-size: 13.5px; color: var(--muted); line-height: 1.6;">{{ $tim->keterangan }}</p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 8px;">
                            @foreach ($tim->users as $anggota)
                                @php
                                    $mm = $statusHonorPerTim[$anggota->id][$tim->id] ?? null;
                                    $mc = $mm ? $honorColor[$mm['status']] : null;
                                @endphp
                                <div class="flex items-center gap-10" style="padding: 10px 12px; background: #fff; border: 1px solid var(--border3); border-radius: 12px;">
                                    <div class="avatar avatar-30">{{ $anggota->initials }}</div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $anggota->name }}</div>
                                        <div class="text-muted2" style="font-size: 11.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $anggota->jabatan->name ?? '-' }}</div>
                                    </div>
                                    @if ($mm)
                                        <span class="badge-pill" style="background: {{ $mc['bg'] }}; color: {{ $mc['fg'] }}; white-space: nowrap; font-size: 11px; padding: 4px 9px;">{{ $mm['label'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding: 36px; text-align: center; color: var(--muted2); font-size: 14px;">Belum ada data tim yang tersedia.</div>
            @endforelse
        </section>

        {{-- Mobile cards --}}
        <div class="flex-col gap-10 staff-mobile-only">
            @forelse ($tims as $tim)
                @php
                    $sm = $statusMeta[$tim->status];
                    $mine = $statusHonorPerTim[$user->id][$tim->id] ?? null;
                    $hc = $mine ? $honorColor[$mine['status']] : null;
                @endphp
                <article class="card card-sm">
                    <button type="button" style="padding: 14px 16px; width: 100%; text-align: left; border: none; background: transparent; cursor: pointer; font-family: inherit;" data-toggle-target="ptatk-mbody-{{ $tim->id }}" aria-expanded="false">
                        <div class="flex justify-between items-center gap-10">
                            <div style="font-size: 14.5px; font-weight: 700;">{{ $tim->nama_tim }}</div>
                            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="#8B99B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="m6 9 6 6 6-6"></path></svg>
                        </div>
                        <div class="text-muted2" style="font-size: 12px; margin: 4px 0 10px;">{{ $tim->users->count() }} anggota · {{ $tim->created_at->locale('id')->translatedFormat('d M Y') }}</div>
                        <div class="flex gap-6 flex-wrap">
                            <span class="badge-pill" style="background: {{ $sm['bg'] }}; color: {{ $sm['fg'] }};">{{ $sm['label'] }}</span>
                            @if ($mine)
                                <span class="badge-pill" style="background: {{ $hc['bg'] }}; color: {{ $hc['fg'] }};">{{ $mine['label'] }}</span>
                            @endif
                        </div>
                    </button>
                    <div id="ptatk-mbody-{{ $tim->id }}" class="team-card-body" hidden>
                        <p style="margin: 0 0 12px; font-size: 13px; color: var(--muted); line-height: 1.6;">{{ $tim->keterangan }}</p>
                        <div class="flex-col gap-8">
                            @foreach ($tim->users as $anggota)
                                @php
                                    $mm = $statusHonorPerTim[$anggota->id][$tim->id] ?? null;
                                    $mc = $mm ? $honorColor[$mm['status']] : null;
                                @endphp
                                <div class="flex items-center gap-10">
                                    <div class="avatar avatar-30">{{ $anggota->initials }}</div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-size: 13px; font-weight: 600;">{{ $anggota->name }}</div>
                                        <div class="text-muted2" style="font-size: 11.5px;">{{ $anggota->jabatan->name ?? '-' }}</div>
                                    </div>
                                    @if ($mm)
                                        <span class="badge-pill" style="background: {{ $mc['bg'] }}; color: {{ $mc['fg'] }}; white-space: nowrap; font-size: 10.5px; padding: 4px 8px;">{{ $mm['label'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @empty
                <div class="card" style="padding: 36px; text-align: center; color: var(--muted2); font-size: 14px;">Belum ada data tim yang tersedia.</div>
            @endforelse
        </div>
    </div>
</x-staff-layout>
