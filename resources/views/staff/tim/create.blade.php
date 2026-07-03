<x-staff-layout>
    @php
        $me = $availableUsers->firstWhere('id', auth()->id());
        $candidates = $availableUsers->reject(fn ($u) => $u->id === auth()->id())->values();

        $selfPakai = $timCounts[$me->id] ?? 0;
        $selfMaks = $me->jabatan->eselon->maks_honor ?? 0;
        $selfFull = $selfPakai >= $selfMaks;
        $selfLabel = $selfFull ? 'Tidak akan menerima honor' : 'Akan menerima honor jika disetujui';
        $selfHc = $selfFull ? ['fg' => '#5C6B85', 'bg' => '#EDF0F7'] : ['fg' => '#3562E3', 'bg' => '#3562E314'];

        $initialStep = 1;
        if ($errors->has('nama_tim') || $errors->has('keterangan')) {
            $initialStep = 1;
        } elseif ($errors->has('sk_file')) {
            $initialStep = 2;
        } elseif ($errors->has('anggota') || $errors->has('anggota.*')) {
            $initialStep = 3;
        }

        $oldAnggota = old('anggota', []);
        $stepLabels = ['Informasi Tim', 'Dokumen SK', 'Anggota', 'Tinjau & Ajukan'];
    @endphp

    <div style="max-width: 760px; margin: 0 auto;">
        <form id="wizardForm" action="{{ route('staff.tim.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="anggota[]" value="{{ auth()->id() }}">

            <div class="flex items-center justify-between gap-12" style="margin-bottom: 18px;">
                <div>
                    <h1 class="h1-page">Buat Tim Baru</h1>
                    <div class="sub-page" id="stepMeta">Langkah {{ $initialStep }} dari 4 — {{ $stepLabels[$initialStep - 1] }}</div>
                </div>
                <a href="{{ route('staff.dashboard.index') }}" class="btn btn-secondary" style="height: 36px; padding: 0 14px; font-size: 13px;">Batal</a>
            </div>

            {{-- Desktop stepper --}}
            <div class="stepper staff-desktop-only" id="stepper">
                @foreach ($stepLabels as $i => $label)
                    <div class="stepper-item" style="flex: {{ $i < 3 ? '1 1 0' : '0 0 auto' }};">
                        <div class="flex items-center gap-9">
                            <div class="stepper-circle" data-step-circle="{{ $i + 1 }}">
                                <span class="stepper-num">{{ $i + 1 }}</span>
                                <span class="stepper-check">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
                                </span>
                            </div>
                            <div class="stepper-label" data-step-label="{{ $i + 1 }}">{{ $label }}</div>
                        </div>
                        @if ($i < 3)
                            <div class="stepper-line" data-step-line="{{ $i + 1 }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Mobile progress bar --}}
            <div class="wizard-progress staff-mobile-only">
                <div class="wizard-progress-fill" id="wizardProgressFill"></div>
            </div>

            <div class="card" style="padding: 26px;">

                {{-- STEP 1: Informasi Tim --}}
                <div class="flex-col gap-18" data-step-panel="1" @if ($initialStep !== 1) hidden @endif>
                    <div class="flex-col gap-6">
                        <label for="w_nama" class="field-label">Nama Tim <span class="field-required">*</span></label>
                        <input id="w_nama" name="nama_tim" type="text" class="input" value="{{ old('nama_tim') }}" placeholder="cth. Tim Penyusunan LAKIP 2026">
                        <div class="field-hint">Gunakan nama persis seperti yang tertulis pada dokumen SK.</div>
                        @error('nama_tim') <div class="err-box">{{ $message }}</div> @enderror
                    </div>
                    <div class="flex-col gap-6">
                        <label for="w_ket" class="field-label">Keterangan <span class="field-required">*</span></label>
                        <textarea id="w_ket" name="keterangan" rows="4" class="textarea" placeholder="Jelaskan singkat tugas dan tujuan tim ini…">{{ old('keterangan') }}</textarea>
                        @error('keterangan') <div class="err-box">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- STEP 2: Dokumen SK --}}
                <div class="flex-col gap-16" data-step-panel="2" @if ($initialStep !== 2) hidden @endif>
                    <div>
                        <div class="field-label" style="margin-bottom: 4px;">Dokumen SK Tim <span class="field-required">*</span></div>
                        <div class="field-hint">Unggah Surat Keputusan pembentukan tim sebagai dasar persetujuan admin.</div>
                    </div>

                    <label for="w_file" class="dropzone" id="dropzone">
                        <div class="dropzone-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="M17 8l-5-5-5 5"></path><path d="M12 3v12"></path></svg>
                        </div>
                        <div style="font-size: 14.5px; font-weight: 700; color: var(--ink);">Klik untuk memilih file</div>
                        <div class="field-hint">Format PDF · maksimal 2 MB</div>
                    </label>

                    <div class="file-card" id="filePreview" hidden>
                        <div class="file-card-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 14px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="fileName"></div>
                            <div class="text-success" style="font-size: 12.5px; display: flex; align-items: center; gap: 5px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
                                <span id="fileSize"></span> · siap diunggah
                            </div>
                        </div>
                        <label for="w_file" class="btn-secondary" style="height: 34px; padding: 0 13px; font-size: 12.5px; display: flex; align-items: center; border-radius: 10px; cursor: pointer;">Ganti</label>
                        <button type="button" class="btn-icon" id="fileRemove" aria-label="Hapus file" style="width: 34px; height: 34px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>

                    <input id="w_file" name="sk_file" type="file" accept="application/pdf" style="display: none;">

                    <div class="err-box" id="fileError" hidden></div>
                    @error('sk_file') <div class="err-box">{{ $message }}</div> @enderror
                </div>

                {{-- STEP 3: Anggota --}}
                <div class="flex-col gap-14" data-step-panel="3" @if ($initialStep !== 3) hidden @endif>
                    <div class="flex items-center justify-between gap-10 flex-wrap">
                        <div>
                            <div class="field-label" style="margin-bottom: 4px;">Pilih Anggota Tim <span class="field-required">*</span></div>
                            <div class="field-hint">Sebagai pembuat tim, Anda otomatis menjadi anggota.</div>
                        </div>
                        <span class="badge-pill badge-accent" id="selectedCount">{{ count($oldAnggota ?: [auth()->id()]) }} anggota dipilih</span>
                    </div>

                    <div class="flex items-center gap-12" style="padding: 12px 14px; border: 1.5px solid var(--aksen-line); background: var(--aksen-tint); border-radius: 14px;">
                        <div class="avatar avatar-34 avatar-solid">{{ $me->initials }}</div>
                        <div style="flex: 1;">
                            <div style="font-size: 13.5px; font-weight: 700;">{{ $me->name }} <span style="font-weight: 600; color: var(--muted);">(Anda)</span></div>
                            <div class="text-muted" style="font-size: 12px;">{{ $me->jabatan->name ?? '-' }}</div>
                        </div>
                        <span class="badge-pill" style="background: {{ $selfFull ? '#FBF0DC' : '#E4F5EE' }}; color: {{ $selfFull ? '#B96E00' : '#17915F' }};">Kuota {{ $selfPakai }}/{{ $selfMaks }}</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#8B99B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>

                    <div style="position: relative;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8B99B3" stroke-width="2" stroke-linecap="round" style="position: absolute; left: 12px; top: 12px;"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        <input type="text" id="w_member_search" class="input" style="height: 40px; padding-left: 36px;" placeholder="Cari nama atau jabatan…" aria-label="Cari anggota">
                    </div>

                    <div class="flex-col gap-6" style="max-height: 320px; overflow-y: auto; padding-right: 2px;" id="candidateList">
                        @forelse ($candidates as $u)
                            @php
                                $pakai = $timCounts[$u->id] ?? 0;
                                $maks = $u->jabatan->eselon->maks_honor ?? 0;
                                $full = $pakai >= $maks;
                            @endphp
                            <label class="check-row" data-name="{{ mb_strtolower($u->name . ' ' . ($u->jabatan->name ?? '')) }}">
                                <input type="checkbox" name="anggota[]" value="{{ $u->id }}" class="sr-checkbox" data-user-id="{{ $u->id }}" @checked(in_array($u->id, $oldAnggota))>
                                <span class="check-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg></span>
                                <div class="avatar avatar-34">{{ $u->initials }}</div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 13.5px; font-weight: 700;">{{ $u->name }}</div>
                                    <div class="text-muted2" style="font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $u->jabatan->name ?? '-' }} · NIP {{ $u->nip }}</div>
                                </div>
                                <span class="badge-pill" style="background: {{ $full ? '#FBF0DC' : '#E4F5EE' }}; color: {{ $full ? '#B96E00' : '#17915F' }}; white-space: nowrap;">{{ $full ? 'Kuota penuh ' : 'Kuota ' }}{{ $pakai }}/{{ $maks }}</span>
                            </label>
                        @empty
                            <div style="padding: 24px; text-align: center; color: var(--muted2); font-size: 13.5px;">Tidak ada ASN lain yang terdaftar.</div>
                        @endforelse
                        <div style="padding: 24px; text-align: center; color: var(--muted2); font-size: 13.5px;" id="candidatesEmpty" hidden>Tidak ada ASN yang cocok dengan pencarian.</div>
                    </div>

                    <div class="info-box">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#8B99B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;"><circle cx="12" cy="12" r="9"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                        Anggota berlabel &quot;Kuota penuh&quot; tetap dapat bergabung, namun tidak akan menerima honor dari tim ini.
                    </div>
                    @error('anggota') <div class="err-box">{{ $message }}</div> @enderror
                </div>

                {{-- STEP 4: Tinjau & Ajukan --}}
                <div class="flex-col gap-18" data-step-panel="4" @if ($initialStep !== 4) hidden @endif>
                    <div class="review-block">
                        <div class="review-block-head">
                            <div style="font-size: 12px; font-weight: 800; letter-spacing: .6px; color: var(--muted2);">INFORMASI TIM</div>
                            <button type="button" class="btn-link" data-goto="1">Ubah</button>
                        </div>
                        <div style="font-size: 15px; font-weight: 700; margin-bottom: 6px;" id="reviewNama"></div>
                        <div class="text-muted" style="font-size: 13.5px; line-height: 1.6;" id="reviewKet"></div>
                    </div>

                    <div class="review-block">
                        <div class="review-block-head">
                            <div style="font-size: 12px; font-weight: 800; letter-spacing: .6px; color: var(--muted2);">DOKUMEN SK</div>
                            <button type="button" class="btn-link" data-goto="2">Ubah</button>
                        </div>
                        <div class="flex items-center gap-10">
                            <div class="file-card-icon" style="width: 34px; height: 34px;">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 700;" id="reviewFileName"></div>
                                <div class="text-muted2" style="font-size: 12px;" id="reviewFileSize"></div>
                            </div>
                        </div>
                    </div>

                    <div class="review-block">
                        <div class="review-block-head">
                            <div style="font-size: 12px; font-weight: 800; letter-spacing: .6px; color: var(--muted2);" id="reviewMemberCount">ANGGOTA</div>
                            <button type="button" class="btn-link" data-goto="3">Ubah</button>
                        </div>
                        <div class="flex-col gap-8">
                            <div class="flex items-center gap-10">
                                <div class="avatar avatar-30 avatar-solid">{{ $me->initials }}</div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 13.5px; font-weight: 600;">{{ $me->name }} (Anda)</div>
                                    <div class="text-muted2" style="font-size: 11.5px;">{{ $me->jabatan->name ?? '-' }}</div>
                                </div>
                                <span class="badge-pill" style="background: {{ $selfHc['bg'] }}; color: {{ $selfHc['fg'] }}; white-space: nowrap;">{{ $selfLabel }}</span>
                            </div>
                            @foreach ($candidates as $u)
                                @php
                                    $pakai = $timCounts[$u->id] ?? 0;
                                    $maks = $u->jabatan->eselon->maks_honor ?? 0;
                                    $full = $pakai >= $maks;
                                    $rLabel = $full ? 'Tidak akan menerima honor' : 'Akan menerima honor jika disetujui';
                                    $rFg = $full ? '#5C6B85' : '#3562E3';
                                    $rBg = $full ? '#EDF0F7' : '#3562E314';
                                @endphp
                                <div class="flex items-center gap-10" data-review-member="{{ $u->id }}" hidden>
                                    <div class="avatar avatar-30">{{ $u->initials }}</div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-size: 13.5px; font-weight: 600;">{{ $u->name }}</div>
                                        <div class="text-muted2" style="font-size: 11.5px;">{{ $u->jabatan->name ?? '-' }}</div>
                                    </div>
                                    <span class="badge-pill" style="background: {{ $rBg }}; color: {{ $rFg }}; white-space: nowrap;">{{ $rLabel }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="warn-box">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#B96E00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 3"></path></svg>
                        Setelah diajukan, tim berstatus <strong>Menunggu Persetujuan</strong> dan akan ditinjau oleh admin. Status honor tiap anggota mengikuti sisa kuota masing-masing.
                    </div>
                </div>

                {{-- Footer nav --}}
                <div class="flex items-center justify-between gap-12" style="margin-top: 26px; padding-top: 20px; border-top: 1px solid var(--border3);">
                    <button type="button" class="btn btn-secondary" id="btnBack">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
                        Kembali
                    </button>
                    <div class="flex items-center gap-14">
                        <span class="text-muted2" style="font-size: 12.5px;" id="stepHint"></span>
                        <button type="button" class="btn btn-primary" id="btnNext">
                            Lanjut
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit" hidden>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path></svg>
                            Ajukan Tim
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            (function () {
                const form = document.getElementById('wizardForm');
                const panels = form.querySelectorAll('[data-step-panel]');
                const stepperCircles = form.querySelectorAll('[data-step-circle]');
                const stepperLabels = form.querySelectorAll('[data-step-label]');
                const stepperLines = form.querySelectorAll('[data-step-line]');
                const progressFill = document.getElementById('wizardProgressFill');
                const stepMeta = document.getElementById('stepMeta');
                const stepLabels = @json($stepLabels);
                const btnBack = document.getElementById('btnBack');
                const btnNext = document.getElementById('btnNext');
                const btnSubmit = document.getElementById('btnSubmit');
                const stepHint = document.getElementById('stepHint');

                const namaInput = document.getElementById('w_nama');
                const ketInput = document.getElementById('w_ket');
                const fileInput = document.getElementById('w_file');
                const dropzone = document.getElementById('dropzone');
                const filePreview = document.getElementById('filePreview');
                const fileNameEl = document.getElementById('fileName');
                const fileSizeEl = document.getElementById('fileSize');
                const fileError = document.getElementById('fileError');
                const fileRemove = document.getElementById('fileRemove');

                const memberSearch = document.getElementById('w_member_search');
                const candidateList = document.getElementById('candidateList');
                const candidatesEmpty = document.getElementById('candidatesEmpty');
                const selectedCount = document.getElementById('selectedCount');

                let currentStep = {{ $initialStep }};

                function formatSize(bytes) {
                    return bytes > 1024 * 1024 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
                }

                function validateStep1() {
                    return namaInput.value.trim().length > 0 && ketInput.value.trim().length > 0;
                }

                function validateStep2() {
                    return fileInput.files.length > 0;
                }

                function isValid(step) {
                    if (step === 1) return validateStep1();
                    if (step === 2) return validateStep2();
                    return true;
                }

                function updateStepper() {
                    stepperCircles.forEach(c => {
                        const n = Number(c.dataset.stepCircle);
                        c.classList.toggle('done', n < currentStep);
                        c.classList.toggle('current', n === currentStep);
                    });
                    stepperLabels.forEach(l => {
                        const n = Number(l.dataset.stepLabel);
                        l.classList.toggle('done', n < currentStep);
                        l.classList.toggle('current', n === currentStep);
                    });
                    stepperLines.forEach(line => {
                        const n = Number(line.dataset.stepLine);
                        line.classList.toggle('done', n < currentStep);
                    });
                    if (progressFill) progressFill.style.width = (currentStep / 4 * 100) + '%';
                }

                function syncReview() {
                    document.getElementById('reviewNama').textContent = namaInput.value.trim();
                    document.getElementById('reviewKet').textContent = ketInput.value.trim();
                    const f = fileInput.files[0];
                    document.getElementById('reviewFileName').textContent = f ? f.name : '';
                    document.getElementById('reviewFileSize').textContent = f ? formatSize(f.size) : '';

                    const checked = candidateList.querySelectorAll('input[type=checkbox]:checked');
                    const checkedIds = new Set(Array.from(checked).map(c => c.dataset.userId));
                    form.querySelectorAll('[data-review-member]').forEach(row => {
                        row.hidden = !checkedIds.has(row.dataset.reviewMember);
                    });
                    document.getElementById('reviewMemberCount').textContent = 'ANGGOTA (' + (checked.length + 1) + ')';
                }

                function updateHint(step) {
                    const valid = isValid(step);
                    stepHint.textContent = valid ? '' : (
                        step === 1 ? 'Isi nama tim dan keterangan untuk melanjutkan' :
                        step === 2 ? 'Unggah dokumen SK untuk melanjutkan' : ''
                    );
                    btnNext.disabled = !valid;
                }

                function goToStep(step) {
                    currentStep = step;
                    panels.forEach(p => { p.hidden = Number(p.dataset.stepPanel) !== step; });
                    stepMeta.textContent = 'Langkah ' + step + ' dari 4 — ' + stepLabels[step - 1];
                    updateStepper();
                    btnBack.style.visibility = step > 1 ? '' : 'hidden';
                    btnNext.hidden = step === 4;
                    btnSubmit.hidden = step !== 4;
                    if (step === 4) syncReview();
                    updateHint(step);
                    window.scrollTo(0, 0);
                }

                namaInput.addEventListener('input', () => updateHint(1));
                ketInput.addEventListener('input', () => updateHint(1));

                function resetFileUi() {
                    dropzone.hidden = false;
                    filePreview.hidden = true;
                }

                fileInput.addEventListener('change', () => {
                    const f = fileInput.files[0];
                    fileError.hidden = true;
                    fileError.textContent = '';

                    if (!f) { resetFileUi(); updateHint(2); return; }

                    if (f.type !== 'application/pdf') {
                        fileError.textContent = 'Format file harus PDF.';
                        fileError.hidden = false;
                        fileInput.value = '';
                        resetFileUi();
                        updateHint(2);
                        return;
                    }
                    if (f.size > 2 * 1024 * 1024) {
                        fileError.textContent = 'Ukuran file melebihi 2 MB.';
                        fileError.hidden = false;
                        fileInput.value = '';
                        resetFileUi();
                        updateHint(2);
                        return;
                    }

                    fileNameEl.textContent = f.name;
                    fileSizeEl.textContent = formatSize(f.size);
                    dropzone.hidden = true;
                    filePreview.hidden = false;
                    updateHint(2);
                });

                fileRemove.addEventListener('click', () => {
                    fileInput.value = '';
                    resetFileUi();
                    updateHint(2);
                });

                memberSearch.addEventListener('input', () => {
                    const q = memberSearch.value.trim().toLowerCase();
                    let visible = 0;
                    candidateList.querySelectorAll('.check-row').forEach(row => {
                        const show = !q || row.dataset.name.includes(q);
                        row.hidden = !show;
                        if (show) visible++;
                    });
                    candidatesEmpty.hidden = visible > 0;
                });

                candidateList.addEventListener('change', (e) => {
                    const cb = e.target.closest('input[type=checkbox]');
                    if (!cb) return;
                    cb.closest('.check-row').classList.toggle('checked', cb.checked);
                    const count = candidateList.querySelectorAll('input[type=checkbox]:checked').length + 1;
                    selectedCount.textContent = count + ' anggota dipilih';
                });

                btnBack.addEventListener('click', () => { if (currentStep > 1) goToStep(currentStep - 1); });
                btnNext.addEventListener('click', () => { if (isValid(currentStep) && currentStep < 4) goToStep(currentStep + 1); });

                form.querySelectorAll('[data-goto]').forEach(btn => {
                    btn.addEventListener('click', () => goToStep(Number(btn.dataset.goto)));
                });

                form.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && currentStep < 4 && e.target.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                    }
                });

                // Reflect any pre-checked members (validation round-trip) in the UI.
                candidateList.querySelectorAll('input[type=checkbox]:checked').forEach(cb => cb.closest('.check-row').classList.add('checked'));

                goToStep(currentStep);
            })();
        </script>
    @endpush
</x-staff-layout>
