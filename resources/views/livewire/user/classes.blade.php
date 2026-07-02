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

<div class="relative mx-auto max-w-7xl space-y-8 p-4 pb-24 sm:p-6 lg:p-8">
    <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="rounded-xl border border-outline-variant bg-white p-5">
                <div class="{{ $stat['bg'] }} {{ $stat['color'] }} mb-3 grid h-10 w-10 place-items-center rounded-lg">
                    <x-user.icon :name="$stat['icon']" :size="20" />
                </div>
                <p class="text-2xl font-bold leading-none text-on-surface">{{ $stat['value'] }}</p>
                <p class="mt-1.5 text-xs font-medium text-on-surface-variant">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </section>

    <section>
        <h3 class="mb-4 text-base font-semibold text-on-surface">Thao tác nhanh</h3>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($actions as $action)
                <a href="{{ $action['href'] }}" class="group flex flex-col items-center justify-center gap-3 rounded-xl border border-outline-variant bg-white p-5 text-center transition-colors hover:border-primary/40 hover:bg-primary/5">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-surface-container text-on-surface-variant transition-colors group-hover:bg-primary group-hover:text-white">
                        <x-user.icon :name="$action['icon']" :size="20" />
                    </span>
                    <span class="text-xs font-medium text-on-surface">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-base font-semibold text-on-surface">Lớp tôi quản lý</h3>
            <div class="flex items-center gap-2">
                <div class="w-44">
                    <x-custom-select placeholder="" value="hk1-2024" :options="[
                        ['value' => 'hk1-2024', 'label' => 'Học kỳ I - 2024'],
                        ['value' => 'hk2-2023', 'label' => 'Học kỳ II - 2023'],
                    ]" />
                </div>
                <button type="button" class="grid h-9 w-9 place-items-center rounded-lg border border-outline-variant bg-white text-on-surface-variant transition-colors hover:bg-surface-container">
                    <x-user.icon name="filter" :size="18" />
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($classes as $class)
                <article class="group flex flex-col rounded-xl border border-outline-variant bg-white transition-shadow hover:shadow-md">
                    <div class="flex items-start gap-3 p-4">
                        <span class="{{ $class['bg'] }} grid h-11 w-11 shrink-0 place-items-center rounded-lg text-white">
                            <x-user.icon :name="$class['icon']" :size="22" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <h4 class="truncate font-semibold text-on-surface">{{ $class['title'] }}</h4>
                            <p class="mt-0.5 truncate text-xs text-on-surface-variant">{{ $class['code'] }} · {{ $class['status'] }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-tertiary/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-tertiary">Đang học</span>
                    </div>

                    <div class="px-4">
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <span class="flex items-center gap-1.5 text-on-surface-variant">
                                <x-user.icon name="users" :size="15" /> {{ $class['students'] }} học viên
                            </span>
                            <span class="font-semibold text-primary">{{ $class['attendance'] }}%</span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-container-high">
                            <div class="{{ $class['bar'] }} h-full rounded-full" style="width: {{ $class['attendance'] }}%"></div>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-2 p-4 pt-0">
                        <a href="{{ route('lecturer.attendance.create', ['class_id' => $class['id']]) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary py-2.5 text-sm font-semibold text-white transition-colors hover:bg-primary-container">
                            <x-user.icon name="check-square" :size="16" /> Điểm danh
                        </a>
                        <a href="{{ route('lecturer.classes.show', $class['id']) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant py-2.5 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container">
                            <x-user.icon name="eye" :size="16" /> Chi tiết
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <a href="{{ route('create-class') }}" class="fixed bottom-8 right-8 z-50 grid h-14 w-14 place-items-center rounded-xl bg-primary text-white shadow-lg shadow-primary/30 transition-transform hover:scale-105 active:scale-95">
        <x-user.icon name="plus" :size="24" />
    </a>
</div>
