@php
    $actions = [
        ['label' => 'Tạo lớp học mới', 'icon' => 'plus-circle', 'href' => route('create-class')],
        ['label' => 'Import học viên', 'icon' => 'upload', 'href' => route('lecturer.students.index')],
        ['label' => 'Tạo lớp học', 'icon' => 'plus-circle', 'href' => route('create-class')],
        ['label' => 'Điểm danh QR', 'icon' => 'qr-code', 'href' => route('lecturer.attendance.create')],
        ['label' => 'Điểm danh thủ công', 'icon' => 'edit', 'href' => route('lecturer.attendance.create')],
         ['label' => 'Quản lý học viên', 'icon' => 'users', 'href' => route('lecturer.students.index')], 
    ];
@endphp

<div class="relative mx-auto max-w-[1400px] space-y-8 p-4 pb-24 sm:p-8">
    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="glass-card group relative flex items-center gap-4 overflow-hidden rounded-2xl p-6 transition-transform duration-300 hover:scale-[1.02]">
                <div class="{{ $stat['bg'] }} {{ $stat['color'] }} flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                    <x-user.icon :name="$stat['icon']" :size="24" />
                </div>
                <div>
                    <p class="font-label-md font-bold uppercase text-on-surface-variant">{{ $stat['label'] }}</p>
                    <h3 class="font-stat-lg text-stat-lg text-on-surface">{{ $stat['value'] }}</h3>
                </div>
                <div class="absolute -bottom-4 -right-4 opacity-5 transition-opacity group-hover:opacity-10">
                    <x-user.icon :name="$stat['icon']" :size="96" />
                </div>
            </div>
        @endforeach
    </section>

    <section>
        <h3 class="mb-6 flex items-center gap-2 font-headline-sm text-headline-sm text-on-surface">
            <x-user.icon name="zap" class="text-primary" />
            Thao tác nhanh
        </h3>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            @foreach ($actions as $action)
                <a href="{{ $action['href'] }}" class="group flex flex-col items-center justify-center rounded-2xl border border-outline-variant/10 bg-surface-container-lowest p-6 transition-all hover:border-primary/40 hover:bg-primary-fixed-dim/10">
                    <span class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary/5 transition-all group-hover:bg-primary group-hover:text-white">
                        <x-user.icon :name="$action['icon']" :size="24" />
                    </span>
                    <span class="text-center font-label-md text-on-surface">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <h3 class="flex items-center gap-2 font-headline-sm text-headline-sm text-on-surface">
                <x-user.icon name="book" class="text-primary" />
                Lớp tôi quản lý
            </h3>
            <div class="flex items-center gap-2">
                <select class="rounded-lg border border-outline-variant/30 bg-white py-1.5 pl-3 pr-8 text-on-surface-variant focus:outline-none focus:ring-primary">
                    <option>Học kỳ I - 2024</option>
                    <option>Học kỳ II - 2023</option>
                </select>
                <button type="button" class="rounded-lg bg-surface-container-low p-2 text-on-surface-variant">
                    <x-user.icon name="filter" />
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($classes as $class)
                <article class="group flex flex-col overflow-hidden rounded-3xl border border-outline-variant/10 bg-white shadow-sm transition-all duration-300 hover:shadow-xl">
                    <div class="{{ $class['bg'] }} relative h-32 p-6 transition-all group-hover:brightness-105">
                        <div class="pointer-events-none absolute inset-0 opacity-20 [background-image:radial-gradient(circle_at_2px_2px,white_1px,transparent_0)] [background-size:24px_24px]"></div>
                        <div class="relative z-10 flex items-start justify-between">
                            <div class="rounded-2xl border border-white/30 bg-white/20 p-2.5 text-white backdrop-blur-md">
                                <x-user.icon :name="$class['icon']" :size="24" />
                            </div>
                            <span class="rounded-full bg-white/20 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md">Đang học</span>
                        </div>
                    </div>
                    <div class="relative z-20 -mt-6 flex flex-1 flex-col rounded-t-3xl bg-white p-6 shadow-[0_-8px_20px_rgba(0,0,0,0.02)]">
                        <h4 class="{{ $class['hover'] }} mb-1 font-headline-sm text-headline-sm text-on-surface transition-colors">{{ $class['title'] }}</h4>
                        <p class="mb-4 flex items-center gap-2 text-on-surface-variant">
                            <span class="font-bold">{{ $class['code'] }}</span>
                            <span class="h-1 w-1 rounded-full bg-outline-variant"></span>
                            <span>{{ $class['semester'] }}</span>
                        </p>
                        <div class="mb-6 space-y-4">
                            <div class="flex justify-between text-on-surface-variant">
                                <span class="flex items-center gap-2">
                                    <x-user.icon name="users" :size="16" />
                                    <span class="font-label-md">{{ $class['students'] }} Học viên</span>
                                </span>
                                <span class="font-label-md font-bold text-primary">{{ $class['attendance'] }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-surface-container-low">
                                <div class="{{ $class['bar'] }} h-full rounded-full" style="width: {{ $class['attendance'] }}%"></div>
                            </div>
                            <p class="text-[11px] italic text-on-surface-variant/70">Tỷ lệ chuyên cần trung bình</p>
                        </div>
                        <div class="mt-auto grid grid-cols-2 gap-3">
                            <a href="{{ route('lecturer.attendance.create', ['class_id' => $class['id']]) }}" class="flex items-center justify-center gap-2 rounded-xl bg-primary py-2.5 font-body-md font-bold text-white transition-colors hover:bg-on-primary-fixed-variant">
                                <x-user.icon name="check-square" :size="16" />
                                Điểm danh
                            </a>
                            <a href="{{ route('lecturer.classes.show', $class['id']) }}" class="flex items-center justify-center gap-2 rounded-xl border border-outline-variant/30 py-2.5 font-body-md font-bold text-on-surface-variant transition-colors hover:bg-surface-container">
                                <x-user.icon name="eye" :size="16" />
                                Chi tiết
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <a href="{{ route('create-class') }}" class="fixed bottom-8 right-8 z-50 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary text-white shadow-xl shadow-primary/30 transition-all hover:scale-110 active:scale-95">
        <x-user.icon name="plus" :size="24" />
    </a>
</div>
