<x-staff-layout>
    <div class="flex-col gap-20">
        <h1 class="h1-page">Profil Saya</h1>

        <div class="profile-grid">
            <section class="card" style="padding: 26px;">
                @include('staff.partials.profile-card')
            </section>
            <section class="card" style="padding: 26px;">
                @include('staff.partials.profile-honor')
            </section>
        </div>

        <div class="staff-mobile-only flex-col gap-16">
            <section class="card" style="padding: 26px;">
                @include('staff.partials.profile-card')
            </section>
            <section class="card" style="padding: 26px;">
                @include('staff.partials.profile-honor')
            </section>
        </div>
    </div>
</x-staff-layout>
