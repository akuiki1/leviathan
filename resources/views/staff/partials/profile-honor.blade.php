<div style="font-size: 12px; font-weight: 800; letter-spacing: .8px; color: var(--muted2); margin-bottom: 16px;">
    HONORARIUM TAHUN {{ $ringkasan['tahun'] }}
</div>

@if ($ringkasan['maks_honor'] > 0)
    <div class="flex gap-8" style="margin-bottom: 18px;">
        @for ($i = 0; $i < $ringkasan['maks_honor']; $i++)
            <div class="slot-bar {{ $i < $ringkasan['jumlah_dibayar'] ? 'filled' : '' }}" style="height: 12px; border-radius: 6px;"></div>
        @endfor
    </div>
@else
    <div class="text-muted" style="font-size: 13.5px; margin-bottom: 18px;">Belum ada informasi kuota honor untuk jabatan Anda.</div>
@endif

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px;">
    <div style="background: #F7F9FD; border-radius: 14px; padding: 14px 16px;">
        <div style="font-size: 20px; font-weight: 800;">{{ $ringkasan['jumlah_dibayar'] }}/{{ $ringkasan['maks_honor'] }}</div>
        <div class="text-muted2" style="font-size: 12px; margin-top: 2px;">Tim dibayar</div>
    </div>
    <div style="background: #F7F9FD; border-radius: 14px; padding: 14px 16px;">
        <div style="font-size: 20px; font-weight: 800;">{{ $ringkasan['jumlah_tim_approved'] }}</div>
        <div class="text-muted2" style="font-size: 12px; margin-top: 2px;">Tim disetujui</div>
    </div>
    <div style="background: #F7F9FD; border-radius: 14px; padding: 14px 16px;">
        <div style="font-size: 20px; font-weight: 800;">{{ $ringkasan['sisa_slot'] }}</div>
        <div class="text-muted2" style="font-size: 12px; margin-top: 2px;">Sisa slot</div>
    </div>
    <div style="background: var(--success-bg); border-radius: 14px; padding: 14px 16px;">
        <div style="font-size: 17px; font-weight: 800; color: var(--success);">Rp {{ number_format($ringkasan['total_honor'], 0, ',', '.') }}</div>
        <div style="font-size: 12px; margin-top: 2px; color: #4B8A6E;">Total honor diterima</div>
    </div>
</div>

@if ($ringkasan['is_over_limit'])
    <div class="warn-box" style="margin-top: 18px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#B96E00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;"><path d="M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
        Anda mengikuti {{ $ringkasan['jumlah_tim_approved'] }} tim approved, melebihi kuota {{ $ringkasan['maks_honor'] }}. Tim di luar kuota tidak menerima honor.
    </div>
@endif
