@php
    $statuses = ['Tất cả', 'Đang học', 'Đã kết thúc', 'Cảnh báo chuyên cần'];
@endphp

<div class="mx-auto max-w-7xl space-y-6 p-4 pb-24 sm:p-6 lg:p-8">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-tertiary/10 px-3 py-1 text-xs font-semibold text-tertiary">
                <x-user.icon name="user" :size="14" /> Không gian Học viên
            </span>
            <h1 class="text-2xl font-bold tracking-tight text-on-surface">Lớp tôi tham gia</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Các lớp bạn đang học và theo dõi điểm danh.</p>
        </div>
        <button type="button" x-on:click="$dispatch('open-join-class-modal')" class="inline-flex items-center justify-center gap-2 rounded-lg bg-tertiary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-tertiary/90">
            <x-user.icon name="plus" :size="18" />
            Tham gia bằng mã
        </button>
    </header>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <label class="relative flex-1">
            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant">
                <x-user.icon name="search" :size="18" />
            </span>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Tìm theo mã lớp, tên lớp, môn học..."
                class="w-full rounded-lg border border-outline-variant bg-white py-2.5 pl-11 pr-4 text-sm text-on-surface transition-colors placeholder:text-on-surface-variant/70 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
            >
        </label>

        <div class="flex shrink-0 items-center gap-1 overflow-x-auto rounded-lg border border-outline-variant bg-white p-1 hide-scrollbar">
            @foreach ($statuses as $status)
                <button
                    type="button"
                    wire:click="setStatusFilter('{{ $status }}')"
                    @class([
                        'whitespace-nowrap rounded-md px-3.5 py-1.5 text-sm font-medium transition-colors',
                        'bg-error/10 text-error' => $statusFilter === $status && $status === 'Cảnh báo chuyên cần',
                        'bg-primary/10 text-primary' => $statusFilter === $status && $status !== 'Cảnh báo chuyên cần',
                        'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' => $statusFilter !== $status,
                    ])
                >
                    {{ $status }}
                </button>
            @endforeach
        </div>
    </div>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($classes as $class)
            @php
                $isEnded   = $class['ended'];
                $isWarning = $class['warning'];
                $isBanned  = $class['banned'];

                $colorOptions = [
                    'bg-[#475569]', 'bg-[#1D4ED8]', 'bg-[#0F766E]', 'bg-[#4338CA]',
                    'bg-[#047857]', 'bg-[#0369A1]', 'bg-[#6D28D9]', 'bg-[#B45309]',
                ];
                $themeColor = $colorOptions[hexdec(substr(md5((string) $class['id']), 0, 8)) % count($colorOptions)];

                if ($isEnded) {
                    $attendanceColor = 'text-on-surface-variant'; $barColor = 'bg-on-surface-variant';
                } elseif ($class['attendance'] < 70) {
                    $attendanceColor = 'text-error'; $barColor = 'bg-error';
                } elseif ($class['attendance'] < 90) {
                    $attendanceColor = 'text-amber-600'; $barColor = 'bg-amber-500';
                } else {
                    $attendanceColor = 'text-tertiary'; $barColor = 'bg-tertiary';
                }
            @endphp

            <article @class([
                'group relative flex flex-col rounded-xl border bg-white transition-all duration-200',
                'border-outline-variant hover:shadow-md' => ! $isEnded && ! $isWarning,
                'border-error/40 ring-1 ring-error/20' => $isWarning,
                'border-outline-variant opacity-75' => $isEnded,
            ])>
                <a href="{{ route('student.classes.show', $class['id']) }}" class="absolute inset-0 z-10"><span class="sr-only">Vào thông tin lớp</span></a>

                <div class="flex items-start gap-3 p-4">
                    <span class="{{ $isEnded ? 'bg-on-surface-variant' : ($isWarning ? 'bg-error' : $themeColor) }} grid h-11 w-11 shrink-0 place-items-center rounded-lg text-lg font-bold text-white">
                        {{ mb_strtoupper(mb_substr($class['title'], 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate font-semibold text-on-surface" title="{{ $class['title'] }}">{{ $class['title'] }}</h3>
                        <p class="mt-0.5 truncate text-xs text-on-surface-variant">{{ $class['schedule'] }} · {{ $class['teacher'] }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 px-4">
                    <span @class([
                        'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                        'bg-tertiary/10 text-tertiary' => ! $isEnded && ! $isWarning,
                        'bg-error/10 text-error' => ! $isEnded && $isWarning,
                        'bg-surface-container text-on-surface-variant' => $isEnded,
                    ])>{{ $isEnded ? 'Đã kết thúc' : 'Đang học' }}</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-secondary/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-secondary">
                        <x-user.icon name="graduation-cap" :size="10" /> Học viên
                    </span>
                    <span class="ml-auto text-[11px] text-on-surface-variant">Mã <span class="font-semibold text-on-surface">{{ $class['join_code'] }}</span></span>
                </div>

                @if ($isWarning)
                    <div class="mx-4 mt-3 flex items-start gap-2 rounded-lg border border-error/20 bg-error/5 p-2.5">
                        <x-user.icon name="alert-triangle" :size="16" class="mt-0.5 shrink-0 text-error" />
                        <p class="text-xs leading-snug text-error">Chuyên cần dưới ngưỡng. Vắng thêm có thể bị cấm thi.</p>
                    </div>
                @endif

                <div class="mt-4 grid grid-cols-3 gap-2 px-4">
                    <div class="rounded-lg border border-outline-variant bg-surface-container-low p-2.5 text-center">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-on-surface-variant">Có mặt</p>
                        <p class="mt-1 text-lg font-bold leading-none text-tertiary">{{ $class['present'] }}</p>
                    </div>
                    <div class="rounded-lg border border-outline-variant bg-surface-container-low p-2.5 text-center">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-on-surface-variant">Vắng</p>
                        <p class="mt-1 text-lg font-bold leading-none text-error">{{ $class['absent'] }}</p>
                    </div>
                    <div class="rounded-lg border border-outline-variant bg-surface-container-low p-2.5 text-center">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-on-surface-variant">Đi trễ</p>
                        <p class="mt-1 text-lg font-bold leading-none text-amber-600">{{ $class['late'] }}</p>
                    </div>
                </div>

                <div class="px-4 pt-4">
                    <div class="mb-1.5 flex items-center justify-between">
                        <span class="text-[11px] font-medium text-on-surface-variant">Chuyên cần</span>
                        <span class="{{ $attendanceColor }} text-sm font-bold">{{ $class['attendance'] }}%</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-container-high">
                        <div class="{{ $barColor }} h-full rounded-full transition-all duration-700" style="width: {{ $class['attendance'] }}%"></div>
                    </div>
                </div>

                <div class="relative z-20 mt-4 flex items-center justify-end gap-0.5 border-t border-outline-variant px-3 py-2">
                    <a href="{{ route('student.classes.show', $class['id']) }}" class="group/btn rounded-lg p-2 transition-colors hover:bg-surface-container" title="Hồ sơ môn học">
                        <x-user.icon name="folder" class="text-on-surface-variant transition-colors group-hover/btn:text-primary" :size="18"/>
                    </a>
                    <a href="{{ route('student.attendance.history', ['classFilter' => $class['id']]) }}" class="group/btn rounded-lg p-2 transition-colors hover:bg-surface-container" title="Lịch sử điểm danh">
                        <x-user.icon name="list" class="text-on-surface-variant transition-colors group-hover/btn:text-primary" :size="18"/>
                    </a>
                    <div class="relative z-20" x-data="{ open: false }">
                        <button type="button" x-on:click.stop="open = ! open" class="rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container">
                            <x-user.icon name="more-vertical" :size="18" />
                        </button>
                        <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute right-0 bottom-full z-50 mb-1 w-44 overflow-hidden rounded-lg border border-outline-variant bg-white py-1 shadow-lg">
                            <a href="{{ route('student.classes.show', $class['id']) }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container">Vào thông tin</a>
                            <a href="{{ route('student.attendance.history', ['classFilter' => $class['id']]) }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container">Lịch sử</a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-outline-variant bg-white py-16 text-center">
                <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-surface-container text-on-surface-variant">
                    <x-user.icon name="search" :size="28" />
                </div>
                <h3 class="font-semibold text-on-surface">Không tìm thấy lớp học nào</h3>
                <p class="mx-auto mt-1 max-w-sm text-sm text-on-surface-variant">Không có lớp khớp bộ lọc hoặc từ khóa. Hãy thử đổi bộ lọc.</p>
                <button type="button" wire:click="$set('search', ''); $set('statusFilter', 'Tất cả')" class="mt-5 text-sm font-semibold text-primary hover:underline">Xóa bộ lọc</button>
            </div>
        @endforelse
    </section>
</div>
