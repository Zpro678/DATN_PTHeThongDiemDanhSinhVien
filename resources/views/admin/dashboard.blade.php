<x-admin-layout title="Admin Center">
    <div class="mx-auto flex max-w-[1500px] flex-col gap-6">
        <section id="overview" class="scroll-mt-24">
            @include('admin.dashboard.dashboard-header')
        </section>

        <section id="stats" class="scroll-mt-24">
            @include('admin.dashboard.stats-overview')
        </section>

        <section id="attendance" class="scroll-mt-24">
            @include('admin.dashboard.attendance-chart')
        </section>

        <section id="warnings" class="scroll-mt-24">
            @include('admin.dashboard.warning-students')
        </section>

        <section id="faculty" class="scroll-mt-24">
            @include('admin.dashboard.faculty-chart')
        </section>
    </div>
</x-admin-layout>
