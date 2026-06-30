@php
    $statuses = ['Tất cả', 'Đang hoạt động', 'Đã kết thúc'];
@endphp

<div class="mx-auto max-w-7xl space-y-6 p-4 pb-24 sm:p-6 lg:p-8">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-on-surface">Lớp tôi quản lý</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Danh sách các lớp bạn đang làm chủ lớp.</p>
        </div>
        <a href="{{ route('create-class') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-container">
            <x-user.icon name="plus" :size="18" />
            Tạo lớp mới
        </a>
    </header>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <label class="relative flex-1">
            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant">
                <x-user.icon name="search" :size="18" />
            </span>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
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
                        'bg-primary/10 text-primary' => $statusFilter === $status,
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
                $isEnded = $class->status === 'ended';

                $present  = $class->sum_present  ?? 0;
                $late     = $class->sum_late     ?? 0;
                $absent   = $class->sum_absent   ?? 0;
                $excused  = $class->sum_excused  ?? 0;

                $totalStudied    = $present + $late + $absent + $excused;
                $plannedSessions = max((int) ($class->total_sessions ?? 0), $totalStudied);

                $attendancePct = \App\Services\AttendanceCalculator::percentOfPlanned(
                    $plannedSessions,
                    ['late' => $late, 'absent' => $absent, 'excused' => $excused],
                );

                $allowedAbsent = \App\Services\AttendanceCalculator::allowedAbsentSessions($plannedSessions);

                $isBanned  = $plannedSessions > 0 && ($absent > $allowedAbsent || $attendancePct < \App\Services\AttendanceCalculator::MIN_ATTENDANCE_PERCENT);
                $isWarning = ! $isBanned && $attendancePct < 85;

                if ($isEnded) {
                    $barClass = 'bg-on-surface-variant'; $textClass = 'text-on-surface-variant';
                } elseif ($attendancePct < 70) {
                    $barClass = 'bg-error'; $textClass = 'text-error';
                } elseif ($attendancePct < 90) {
                    $barClass = 'bg-amber-500'; $textClass = 'text-amber-600';
                } else {
                    $barClass = 'bg-tertiary'; $textClass = 'text-tertiary';
                }

                $colorOptions = [
                    'bg-[#475569]', 'bg-[#1D4ED8]', 'bg-[#0F766E]', 'bg-[#4338CA]',
                    'bg-[#047857]', 'bg-[#0369A1]', 'bg-[#6D28D9]', 'bg-[#B45309]',
                ];
                $themeColor = $colorOptions[$class->id % count($colorOptions)];
            @endphp

            <article @class([
                'group relative flex flex-col rounded-xl border border-outline-variant bg-white transition-all duration-200',
                'hover:border-outline-variant hover:shadow-md' => ! $isEnded,
                'opacity-75' => $isEnded,
            ])>
                <a href="{{ route('lecturer.classes.show', $class->id) }}" class="absolute inset-0 z-10"><span class="sr-only">Xem chi tiết lớp</span></a>

                <div class="flex items-start gap-3 p-4">
                    <span class="{{ $isEnded ? 'bg-on-surface-variant' : $themeColor }} grid h-11 w-11 shrink-0 place-items-center rounded-lg text-lg font-bold text-white">
                        {{ mb_strtoupper(mb_substr($class->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate font-semibold text-on-surface" title="{{ $class->name }}">{{ $class->name }}</h3>
                        <p class="mt-0.5 truncate text-xs text-on-surface-variant">
                            {{ $class->subject_code ?? 'N/A' }} · Mã <span class="font-semibold text-on-surface">{{ $class->join_key }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 px-4">
                    <span @class([
                        'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                        'bg-tertiary/10 text-tertiary' => ! $isEnded,
                        'bg-surface-container text-on-surface-variant' => $isEnded,
                    ])>{{ $isEnded ? 'Đã kết thúc' : 'Đang hoạt động' }}</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-primary">
                        <x-user.icon name="shield" :size="10" /> Chủ lớp
                    </span>
                    @if ($isBanned)
                        <span class="inline-flex items-center rounded-full bg-error/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-error">Cấm thi</span>
                    @elseif ($isWarning)
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Cảnh báo</span>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 px-4">
                    <div class="rounded-lg border border-outline-variant bg-surface-container-low p-3">
                        <p class="flex items-center gap-1.5 text-[11px] font-medium text-on-surface-variant">
                            <x-user.icon name="users" :size="14" class="text-primary" /> Học viên
                        </p>
                        <p class="mt-1 text-xl font-bold leading-none text-on-surface">{{ $class->students_count }}</p>
                    </div>
                    <div class="rounded-lg border border-outline-variant bg-surface-container-low p-3">
                        <p class="flex items-center gap-1.5 text-[11px] font-medium text-on-surface-variant">
                            <x-user.icon name="check-square" :size="14" class="text-tertiary" /> Đã học
                        </p>
                        <p class="mt-1 text-xl font-bold leading-none text-on-surface">{{ $class->studied_sessions ?? 0 }}</p>
                    </div>
                </div>

                <div class="px-4 pt-4">
                    <div class="mb-1.5 flex items-center justify-between">
                        <span class="text-[11px] font-medium text-on-surface-variant">TB chuyên cần</span>
                        <span class="{{ $textClass }} text-sm font-bold">{{ $attendancePct }}%</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-container-high">
                        <div class="{{ $barClass }} h-full rounded-full" style="width: {{ $attendancePct }}%"></div>
                    </div>
                </div>

                <div class="relative z-20 mt-4 flex items-center justify-end gap-0.5 border-t border-outline-variant px-3 py-2">
                    <div x-data="{ copied: false }" class="relative z-20">
                        <button
                            type="button"
                            title="Sao chép mã lớp: {{ $class->join_key }}"
                            class="group/action rounded-lg p-2 transition-colors hover:bg-surface-container"
                            x-on:click="navigator.clipboard.writeText('{{ $class->join_key }}'); copied = true; setTimeout(() => copied = false, 2000); $event.stopPropagation()"
                        >
                            <template x-if="!copied"><x-user.icon name="copy" :size="18" class="text-on-surface-variant transition-colors group-hover/action:text-primary" /></template>
                            <template x-if="copied"><x-user.icon name="check-circle" :size="18" class="text-tertiary" /></template>
                        </button>
                    </div>
                    @foreach ([
                        ['label' => 'Điểm danh QR', 'icon' => 'qr-code'],
                        ['label' => 'Thủ công', 'icon' => 'check-square'],
                        ['label' => 'Quản lý SV', 'icon' => 'users'],
                        ['label' => 'Thống kê', 'icon' => 'bar-chart'],
                    ] as $action)
                        <a href="{{ match ($action['label']) { 'Điểm danh QR' => route('lecturer.attendance.create', ['class_id' => $class->id]), 'Thủ công' => route('lecturer.attendance.create', ['class_id' => $class->id]), 'Quản lý SV' => route('lecturer.students.index', ['class_id' => $class->id]), 'Thống kê' => route('lecturer.class.statistics', ['class_id' => $class->id]), default => '#' } }}" @class([
                            'group/action rounded-lg p-2 transition-colors hover:bg-surface-container',
                            'pointer-events-none opacity-40' => $isEnded && in_array($action['icon'], ['qr-code', 'check-square'], true),
                        ]) title="{{ $action['label'] }}">
                            <x-user.icon :name="$action['icon']" class="text-on-surface-variant transition-colors group-hover/action:text-primary" :size="18"/>
                        </a>
                    @endforeach

                    <div class="relative z-20" x-data="{ open: false }">
                        <button type="button" x-on:click.stop="open = ! open" class="rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container">
                            <x-user.icon name="more-vertical" :size="18" />
                        </button>
                        <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute right-0 bottom-full z-50 mb-1 w-44 overflow-hidden rounded-lg border border-outline-variant bg-white py-1 shadow-lg">
                            <a href="{{ route('lecturer.classes.show', $class->id) }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container">Xem lớp học</a>
                            @if (! $isEnded)
                                <a href="{{ route('lecturer.classes.settings', $class->id) }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container">Cài đặt lớp</a>
                                <button type="button" wire:click.stop.prevent="confirmEndClass({{ $class->id }})" class="block w-full px-4 py-2 text-left text-sm text-error hover:bg-error/10">Kết thúc lớp</button>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-outline-variant bg-white py-16 text-center">
                <x-user.icon name="inbox" :size="40" class="mx-auto mb-3 text-on-surface-variant/50" />
                <p class="font-semibold text-on-surface">Không tìm thấy lớp học nào</p>
                <p class="mt-1 text-sm text-on-surface-variant">Tạo lớp mới hoặc thử đổi bộ lọc tìm kiếm.</p>
            </div>
        @endforelse
    </section>

    @if ($confirmingEndClassId)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[110] flex items-center justify-center bg-on-background/40 p-4 backdrop-blur-sm"
                 x-data
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="w-full max-w-md rounded-xl border border-outline-variant bg-white p-6 shadow-2xl"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-amber-100">
                            <x-user.icon name="alert-triangle" :size="20" class="text-amber-600" />
                        </div>
                        <h3 class="text-lg font-bold text-on-surface">Xác nhận kết thúc lớp</h3>
                    </div>
                    <p class="text-sm text-on-surface-variant">Bạn có chắc muốn kết thúc lớp học này? Hành động này sẽ khóa toàn bộ hoạt động điểm danh của lớp.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="cancelEndClass" class="rounded-lg border border-outline-variant px-5 py-2.5 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container">Hủy bỏ</button>
                        <button type="button" wire:click="endClass({{ $confirmingEndClassId }})" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-amber-600">Kết thúc lớp</button>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
