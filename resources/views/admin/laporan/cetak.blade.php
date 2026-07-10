{{--
    Dokumen cetak resmi laporan rekap honor per eselon.
    Standalone (tanpa sidebar admin) supaya bersih di kertas A4 dan bisa diarsip/ditandatangani.

    CATATAN: Baris kop instansi, kota tanda tangan, serta nama & NIP pejabat pada
    blok "Mengetahui" adalah PLACEHOLDER — sesuaikan dengan perangkat daerah yang memakai
    aplikasi ini. Cari komentar "SESUAIKAN" di bawah.
--}}
@php
    $bulanId = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $tglCetak = $dicetakPada->day . ' ' . $bulanId[(int) $dicetakPada->month] . ' ' . $dicetakPada->year;
    $jamCetak = $dicetakPada->format('H:i') . ' WITA';

    // SESUAIKAN: kota tempat penandatanganan.
    $kotaTtd = 'Banjarbaru';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Rekap Honor {{ $tahun }}</title>
    <style>
        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: #e5e7eb;
            font-family: "Times New Roman", Times, serif;
            color: #000;
            font-size: 12pt;
            line-height: 1.4;
        }

        /* Lembar F4 / Folio (215 x 330 mm) di layar — standar naskah dinas */
        .sheet {
            width: 215mm;
            min-height: 330mm;
            margin: 16px auto;
            padding: 18mm 20mm;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .18);
        }

        /* ---------- Kop surat ---------- */
        .kop {
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
        }
        .kop img { width: 80px; height: 80px; object-fit: contain; }
        .kop-text { flex: 1; text-align: center; }
        .kop-text .l1 { font-size: 13pt; font-weight: 700; letter-spacing: .3px; }
        /* Nama instansi harus muat satu baris — jangan sampai kata "DAERAH" turun. */
        .kop-text .l2 { font-size: 14pt; font-weight: 700; letter-spacing: .3px; white-space: nowrap; }
        .kop-text .l3 { font-size: 9pt; margin-top: 3px; line-height: 1.35; }
        .kop-thin { border-bottom: 1px solid #000; margin-top: 2px; }

        /* ---------- Judul ---------- */
        .judul {
            text-align: center;
            margin: 22px 0 6px;
        }
        .judul .t1 { font-size: 13.5pt; font-weight: 700; text-decoration: underline; text-transform: uppercase; }
        .judul .t2 { font-size: 12pt; font-weight: 700; margin-top: 2px; }

        /* ---------- Metadata ---------- */
        .meta {
            margin: 16px 0 14px;
            font-size: 11pt;
        }
        .meta table { border-collapse: collapse; }
        .meta td { padding: 1px 0; vertical-align: top; }
        .meta td.k { width: 130px; }
        .meta td.s { width: 14px; text-align: center; }

        /* ---------- Tabel data ---------- */
        h3.sub {
            font-size: 12pt;
            margin: 20px 0 8px;
            font-weight: 700;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        table.data th, table.data td {
            border: 1px solid #000;
            padding: 5px 7px;
        }
        table.data thead th {
            background: #e9edf3;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            text-align: center;
            font-weight: 700;
        }
        table.data tfoot td {
            font-weight: 700;
            background: #f4f6f9;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .c { text-align: center; }
        .r { text-align: right; }
        .mono { font-family: "Courier New", monospace; font-size: 10pt; }
        .nihil { padding: 14px; text-align: center; font-style: italic; }

        /* ---------- Catatan ---------- */
        .catatan {
            margin-top: 14px;
            font-size: 9.5pt;
            text-align: justify;
            line-height: 1.35;
        }
        .catatan .lbl { font-weight: 700; }

        /* ---------- Tanda tangan (dua kolom seimbang, rata tengah) ---------- */
        .ttd {
            margin-top: 36px;
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        .ttd td { vertical-align: top; width: 50%; text-align: center; padding: 0 10px; }
        .ttd .tgl { display: inline-block; min-height: 1.4em; }
        .ttd .space { height: 66px; }
        .ttd .nama { font-weight: 700; text-decoration: underline; }

        /* ---------- Toolbar (hanya layar) ---------- */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            gap: 10px;
            justify-content: center;
            padding: 10px;
            background: #1f2937;
        }
        .toolbar button, .toolbar a {
            font-family: system-ui, sans-serif;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 6px;
            border: 0;
            cursor: pointer;
            text-decoration: none;
        }
        .toolbar .btn-print { background: #3b82f6; color: #fff; }
        .toolbar .btn-back { background: #e5e7eb; color: #1f2937; }

        @media print {
            html, body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
            table.data { page-break-inside: auto; }
            table.data tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
        }

        @page { size: 215mm 330mm; margin: 18mm 20mm; }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="btn-print" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
        <a class="btn-back" href="{{ route('admin.laporan-honor.index', ['tahun' => $tahun]) }}">← Kembali</a>
    </div>

    <div class="sheet">
        {{-- ================= KOP SURAT ================= --}}
        <div class="kop">
            <img src="{{ asset('images/logo-kalsel.svg') }}" alt="Lambang Kalsel">
            <div class="kop-text">
                <div class="l1">PEMERINTAH PROVINSI KALIMANTAN SELATAN</div>
                <div class="l2">BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH</div>
                <div class="l3">Jalan Raya Dharma Praja, Kawasan Perkantoran Pemerintah Provinsi Kalimantan Selatan, Banjarbaru 70700<br>Telepon (0511) 5910591 &middot; Laman bpkad.kalselprov.go.id</div>
            </div>
        </div>
        <div class="kop-thin"></div>

        {{-- ================= JUDUL ================= --}}
        <div class="judul">
            <div class="t1">Laporan Rekap Honor per Eselon</div>
            <div class="t2">Tahun Anggaran {{ $tahun }}</div>
        </div>

        {{-- ================= METADATA ================= --}}
        <div class="meta">
            <table>
                <tr>
                    <td class="k">Cakupan data</td>
                    <td class="s">:</td>
                    <td>
                        {{ $timCounts['approved'] }} tim disetujui (dihitung),
                        {{ $timCounts['pending'] }} tim menunggu (belum diproses, tidak dihitung),
                        {{ $timCounts['rejected'] }} tim ditolak (diabaikan)
                    </td>
                </tr>
                <tr>
                    <td class="k">Dicetak oleh</td>
                    <td class="s">:</td>
                    <td>{{ $dibuatOleh->name }}</td>
                </tr>
                <tr>
                    <td class="k">Waktu cetak</td>
                    <td class="s">:</td>
                    <td>{{ $tglCetak }}, pukul {{ $jamCetak }}</td>
                </tr>
            </table>
        </div>

        {{-- ================= TABEL 1: REKAP PER ESELON ================= --}}
        <h3 class="sub">A. Rekapitulasi per Eselon</h3>
        <table class="data">
            <thead>
                <tr>
                    <th style="width:34px">No</th>
                    <th>Eselon</th>
                    <th style="width:70px">Kuota/<br>Tahun</th>
                    <th style="width:64px">Jumlah<br>ASN</th>
                    <th style="width:70px">ASN Over<br>Limit</th>
                    <th style="width:80px">Tim<br>Dibayar</th>
                    <th style="width:88px">Tim Tidak<br>Dibayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekap as $i => $baris)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $baris['eselon']->name ?? 'Tanpa Eselon' }}</td>
                        <td class="c">{{ $baris['eselon'] ? $baris['eselon']->maks_honor . ' tim' : '—' }}</td>
                        <td class="c">{{ $baris['jumlah_asn'] }}</td>
                        <td class="c">{{ $baris['jumlah_over_limit'] }}</td>
                        <td class="c">{{ $baris['jumlah_tim_dibayar'] }}</td>
                        <td class="c">{{ $baris['jumlah_tim_tidak_dibayar'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="nihil">Belum ada data eselon.</td></tr>
                @endforelse
            </tbody>
            @if ($rekap->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="5" class="r">Total Tahun {{ $tahun }}</td>
                        <td class="c">{{ $rekap->sum('jumlah_tim_dibayar') }}</td>
                        <td class="c">{{ $rekap->sum('jumlah_tim_tidak_dibayar') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        {{-- ================= TABEL 2: LAMPIRAN OVER LIMIT ================= --}}
        <h3 class="sub">B. Lampiran &mdash; ASN Melebihi Kuota</h3>
        @if ($overLimit->isEmpty())
            <table class="data">
                <tbody>
                    <tr><td class="nihil">Tidak ada ASN yang melebihi kuota pada tahun anggaran ini.</td></tr>
                </tbody>
            </table>
        @else
            <table class="data">
                <thead>
                    <tr>
                        <th style="width:34px">No</th>
                        <th style="width:150px">NIP</th>
                        <th>Nama</th>
                        <th>Eselon Saat Ini</th>
                        <th style="width:54px">Kuota</th>
                        <th style="width:70px">Tim<br>Disetujui</th>
                        <th style="width:70px">Tidak<br>Dibayar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($overLimit as $i => $baris)
                        <tr>
                            <td class="c">{{ $i + 1 }}</td>
                            <td class="mono">{{ $baris['asn']->nip }}</td>
                            <td>{{ $baris['asn']->name }}</td>
                            <td>{{ $baris['asn']->jabatan->eselon->name ?? '-' }}</td>
                            <td class="c">{{ $baris['maks_honor'] }}</td>
                            <td class="c">{{ $baris['jumlah_tim_approved'] }}</td>
                            <td class="c">{{ $baris['jumlah_tidak_dibayar'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- ================= CATATAN ================= --}}
        <div class="catatan">
            <span class="lbl">Catatan:</span>
            Keanggotaan dihitung pada eselon yang berlaku <em>saat ASN bergabung ke tim</em> (snapshot),
            sehingga ASN yang mutasi eselon di tengah tahun dapat terhitung pada dua baris eselon.
            "Tim Tidak Dibayar" adalah keanggotaan yang melebihi kuota &mdash; potensi kelebihan bayar
            yang perlu ditinjau sebelum audit akhir tahun. Angka pada laporan ini merupakan kondisi
            data pada waktu cetak tersebut di atas.
        </div>

        {{-- ================= TANDA TANGAN ================= --}}
        <table class="ttd">
            <tr>
                {{-- Kolom kiri: pejabat "Mengetahui" (pilihan admin). Bila tak dipilih,
                     tampil default Kepala BPKAD dengan nama/NIP untuk diisi manual. --}}
                <td>
                    <span class="tgl">&nbsp;</span><br>
                    Mengetahui,<br>
                    @if ($mengetahui)
                        {{ $mengetahui->jabatan->name ?? 'Pejabat' }},
                    @else
                        Kepala BPKAD Provinsi Kalimantan Selatan,
                    @endif
                    <div class="space"></div>
                    <span class="nama">{{ $mengetahui->name ?? '..............................' }}</span><br>
                    NIP. {{ $mengetahui->nip ?? '..............................' }}
                </td>
                {{-- Kolom kanan: "Dibuat oleh" = pejabat yang login, nama + NIP otomatis. --}}
                <td>
                    <span class="tgl">{{ $kotaTtd }}, {{ $tglCetak }}</span><br>
                    Dibuat oleh,<br>
                    &nbsp;
                    <div class="space"></div>
                    <span class="nama">{{ $dibuatOleh->name }}</span><br>
                    NIP. {{ $dibuatOleh->nip }}
                </td>
            </tr>
        </table>
    </div>

    <script>
        // Buka dialog cetak otomatis saat halaman dibuka dari tombol "Cetak".
        window.addEventListener('load', function () {
            if (new URLSearchParams(window.location.search).get('autoprint') !== '0') {
                window.print();
            }
        });
    </script>
</body>
</html>
