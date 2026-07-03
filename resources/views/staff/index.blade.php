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
        $today = \Illuminate\Support\Carbon::now()->locale('id')->translatedFormat('l, d F Y');
        $firstName = explode(' ', $user->name)[0];

        $countHonor = $tims->filter(fn ($t) => ($statusHonorPerTim[$user->id][$t->id]['status'] ?? null) === \App\Services\HonorService::DIBAYAR)->count();
        $countMenunggu = $tims->where('status', 'pending')->count();
        $countDitolak = $tims->where('status', 'rejected')->count();
    @endphp

    <div class="flex-col gap-20">
        <div class="flex items-center justify-between gap-16 flex-wrap">
            <div>
                <h1 class="h1-page">Selamat datang, {{ $firstName }}</h1>
                <div class="sub-page">{{ $today }}</div>
            </div>
        </div>

        {{-- Kuota hero --}}
        <section aria-label="Kuota honorarium" class="card" style="padding: 22px 24px; display: flex; gap: 28px; align-items: stretch; flex-wrap: wrap;">
            <div style="flex: 1 1 320px; min-width: 260px;">
                <div style="font-size: 12px; font-weight: 800; letter-spacing: .8px; color: var(--muted2); margin-bottom: 14px;">
                    KUOTA HONORARIUM {{ $tahunBerjalan }}
                </div>
                @if ($maksHonor > 0)
                    <div class="flex gap-8" style="margin-bottom: 12px;">
                        @for ($i = 0; $i < $maksHonor; $i++)
                            <div class="slot-bar {{ $i < $ringkasanDiri['jumlah_dibayar'] ? 'filled' : '' }}"></div>
                        @endfor
                    </div>
                    <div style="font-size: 15px; font-weight: 700;">
                        {{ $ringkasanDiri['jumlah_dibayar'] }} dari {{ $maksHonor }} kuota terpakai
                        <span style="font-weight: 500; color: var(--muted);">· sisa {{ $ringkasanDiri['sisa_slot'] }} slot</span>
                    </div>
                    <div class="field-hint" style="margin-top: 6px; line-height: 1.5;">
                        Honor dibayarkan maksimal {{ $maksHonor }} tim per tahun sesuai ketentuan jabatan Anda.
                    </div>
                @else
                    <div class="text-muted" style="font-size: 13.5px;">Belum ada informasi kuota honor untuk jabatan Anda.</div>
                @endif
            </div>
            <div style="width: 1px; background: var(--border3);"></div>
            <div style="flex: 0 1 260px; min-width: 220px; display: flex; flex-direction: column; justify-content: center; gap: 10px;">
                <div class="flex justify-between" style="font-size: 13.5px;"><span class="text-muted">Tim disetujui</span><strong>{{ $ringkasanDiri['jumlah_tim_approved'] }}</strong></div>
                <div class="flex justify-between" style="font-size: 13.5px;"><span class="text-muted">Menunggu persetujuan</span><strong>{{ $countMenunggu }}</strong></div>
                <a href="{{ route('staff.tim.index') }}" class="btn-link flex items-center gap-6" style="margin-top: 4px;">
                    Lihat riwayat lengkap
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </section>

        {{-- Tim Anda --}}
        <section aria-label="Tim Anda" class="flex-col gap-14">
            <div class="flex items-center justify-between gap-12 flex-wrap">
                <h2 style="margin: 0; font-size: 17px; font-weight: 800;">Tim Anda — {{ $tahunBerjalan }}</h2>
                <div style="position: relative; flex: 0 1 280px; min-width: 200px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8B99B3" stroke-width="2" stroke-linecap="round" style="position: absolute; left: 12px; top: 11px;"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.3-4.3"></path></svg>
                    <input type="text" id="berandaSearch" class="input input-pill" placeholder="Cari nama tim…" aria-label="Cari tim">
                </div>
            </div>

            <div class="flex gap-8 flex-wrap" id="berandaChips">
                <button type="button" class="chip active" data-chip="semua">Semua ({{ $tims->count() }})</button>
                <button type="button" class="chip" data-chip="honor">Dapat Honor ({{ $countHonor }})</button>
                <button type="button" class="chip" data-chip="menunggu">Menunggu ({{ $countMenunggu }})</button>
                <button type="button" class="chip" data-chip="ditolak">Ditolak ({{ $countDitolak }})</button>
            </div>

            <div class="flex-col gap-10" id="berandaList">
                @forelse ($tims as $tim)
                    @php
                        $sm = $statusMeta[$tim->status];
                        $mine = $statusHonorPerTim[$user->id][$tim->id] ?? null;
                        $hc = $mine ? $honorColor[$mine['status']] : null;
                        $filters = ['semua'];
                        if ($mine && $mine['status'] === \App\Services\HonorService::DIBAYAR) $filters[] = 'honor';
                        if ($tim->status === 'pending') $filters[] = 'menunggu';
                        if ($tim->status === 'rejected') $filters[] = 'ditolak';
                    @endphp
                    <article class="card card-sm team-card" data-name="{{ mb_strtolower($tim->nama_tim) }}" data-filters="{{ implode(' ', $filters) }}">
                        <button type="button" class="team-card-head" data-toggle-target="team-body-{{ $tim->id }}" aria-expanded="false">
                            <div class="team-card-icon" style="background: {{ $sm['bg'] }}; color: {{ $sm['fg'] }};">
                                @if ($tim->status === 'approved')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                @elseif ($tim->status === 'pending')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 3"></path></svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="m15 9-6 6M9 9l6 6"></path></svg>
                                @endif
                            </div>
                            <div style="flex: 1; min-width: 180px;">
                                <div style="font-size: 15px; font-weight: 700; margin-bottom: 3px;">{{ $tim->nama_tim }}</div>
                                <div class="text-muted2" style="font-size: 12.5px;">{{ $tim->users->count() }} anggota · dibuat {{ $tim->created_at->locale('id')->translatedFormat('d M Y') }}</div>
                            </div>
                            <div class="flex gap-8 items-center flex-wrap">
                                <span class="badge-pill" style="background: {{ $sm['bg'] }}; color: {{ $sm['fg'] }};">{{ $sm['label'] }}</span>
                                @if ($mine)
                                    <span class="badge-pill" style="background: {{ $hc['bg'] }}; color: {{ $hc['fg'] }};">{{ $mine['label'] }}</span>
                                @endif
                                <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="#8B99B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                            </div>
                        </button>
                        <div id="team-body-{{ $tim->id }}" class="team-card-body" hidden>
                            <p style="margin: 0 0 14px; font-size: 13.5px; color: var(--muted); line-height: 1.6;">{{ $tim->keterangan }}</p>
                            <div style="font-size: 12px; font-weight: 800; letter-spacing: .6px; color: var(--muted2); margin-bottom: 10px;">ANGGOTA ({{ $tim->users->count() }})</div>
                            <div class="flex-col" style="gap: 2px;">
                                @foreach ($tim->users as $anggota)
                                    @php
                                        $mm = $statusHonorPerTim[$anggota->id][$tim->id] ?? null;
                                        $mc = $mm ? $honorColor[$mm['status']] : null;
                                    @endphp
                                    <div class="member-row">
                                        <div class="avatar avatar-32">{{ $anggota->initials }}</div>
                                        <div style="flex: 1; min-width: 120px;">
                                            <div style="font-size: 13.5px; font-weight: 600;">{{ $anggota->name }}{{ $anggota->id === $user->id ? ' (Anda)' : '' }}</div>
                                            <div class="text-muted2" style="font-size: 12px;">{{ $anggota->jabatan->name ?? '-' }}</div>
                                        </div>
                                        @if ($mm)
                                            <span class="badge-pill" style="background: {{ $mc['bg'] }}; color: {{ $mc['fg'] }};">{{ $mm['label'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="card" style="padding: 36px; text-align: center; color: var(--muted2); font-size: 14px;">
                        Belum ada tim yang terdaftar tahun ini.
                    </div>
                @endforelse
                <div class="card" id="berandaEmpty" style="padding: 36px; text-align: center; color: var(--muted2); font-size: 14px;" hidden>
                    Tidak ada tim yang cocok dengan pencarian atau filter.
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            (function () {
                const search = document.getElementById('berandaSearch');
                const chipsWrap = document.getElementById('berandaChips');
                const list = document.getElementById('berandaList');
                const empty = document.getElementById('berandaEmpty');
                const cards = Array.from(list.querySelectorAll('.team-card'));
                let activeChip = 'semua';

                function applyFilters() {
                    const q = search.value.trim().toLowerCase();
                    let visibleCount = 0;
                    cards.forEach(card => {
                        const matchesChip = activeChip === 'semua' || card.dataset.filters.split(' ').includes(activeChip);
                        const matchesSearch = !q || card.dataset.name.includes(q);
                        const show = matchesChip && matchesSearch;
                        card.hidden = !show;
                        if (show) visibleCount++;
                    });
                    empty.hidden = visibleCount > 0 || cards.length === 0;
                }

                if (search) search.addEventListener('input', applyFilters);

                if (chipsWrap) {
                    chipsWrap.addEventListener('click', function (e) {
                        const btn = e.target.closest('.chip');
                        if (!btn) return;
                        chipsWrap.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
                        btn.classList.add('active');
                        activeChip = btn.dataset.chip;
                        applyFilters();
                    });
                }
            })();
        </script>
    @endpush
</x-staff-layout>
