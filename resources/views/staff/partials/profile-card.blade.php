@php use Illuminate\Support\Facades\Auth; @endphp

<div class="flex items-center gap-16" style="margin-bottom: 20px;">
    <div class="avatar avatar-62">{{ $user->initials }}</div>
    <div>
        <div style="font-size: 18px; font-weight: 800;">{{ $user->name }}</div>
        <div class="text-muted2" style="font-size: 13px;">NIP {{ $user->nip }}</div>
    </div>
</div>

<div class="flex-col" style="gap: 0;">
    <div class="flex justify-between gap-14" style="padding: 12px 0; border-top: 1px solid var(--border4); font-size: 13.5px;">
        <span class="text-muted2">Jabatan</span><strong style="text-align: right;">{{ $user->jabatan->name ?? '-' }}</strong>
    </div>
    <div class="flex justify-between gap-14" style="padding: 12px 0; border-top: 1px solid var(--border4); font-size: 13.5px;">
        <span class="text-muted2">Eselon</span><strong>{{ $user->jabatan->eselon->name ?? '-' }}</strong>
    </div>
    <div class="flex justify-between gap-14" style="padding: 12px 0; border-top: 1px solid var(--border4); font-size: 13.5px;">
        <span class="text-muted2">Email</span><strong>{{ $user->email }}</strong>
    </div>
    <div class="flex justify-between gap-14" style="padding: 12px 0; border-top: 1px solid var(--border4); font-size: 13.5px;">
        <span class="text-muted2">Kuota honor / tahun</span><strong>{{ $ringkasan['maks_honor'] }} tim</strong>
    </div>
</div>

<form action="{{ route('logout') }}" method="POST" style="margin-top: 18px;">
    @csrf
    <button type="submit" class="btn btn-ghost" style="width: 100%; height: 42px; font-size: 13.5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path></svg>
        Keluar dari Akun
    </button>
</form>
