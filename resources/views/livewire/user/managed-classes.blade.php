@php
    $statuses = ['Tất cả', 'Đang hoạt động', 'Đã kết thúc'];

    // Thông tin gói cước để nhắc giới hạn lớp ngay tại nơi tạo lớp (Concept 1).
    $mcPlan = Auth::user()?->currentPlan();
    $mcPlanName = $mcPlan->name ?? 'Miễn phí';
    $mcPlanMax = (int) ($mcPlan->max_classes ?? 2);
    // Giới hạn số học viên/lớp do admin cấu hình (null = không giới hạn).
    $mcMaxStudents = $mcPlan?->max_students_per_class;
    $mcOwnedCount = (int) (Auth::user()?->ownedClasses()->count() ?? 0);
    $mcUsedPercent = $mcPlanMax > 0 ? min(100, (int) round($mcOwnedCount / max(1, $mcPlanMax) * 100)) : 0;
    $mcNearLimit = $mcPlanMax > 0 && $mcOwnedCount >= $mcPlanMax;
@endphp

<div class="w-full space-y-6 px-6 py-6 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Nhắc gói cước trong ngữ cảnh (Concept 1) --}}
        <div class="flex flex-col gap-3 rounded-2xl border border-outline-variant bg-white p-3 shadow-sm sm:flex-row sm:items-center sm:w-[500px]">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary">
                    <x-user.icon name="zap" :size="20" />
                </span>
                <div class="w-40">
                    <p class="truncate text-sm font-bold text-on-surface">Gói {{ $mcPlanName }}</p>
                    <p class="text-xs text-on-surface-variant">Đã dùng {{ $mcOwnedCount }}/{{ $mcPlanMax }} lớp</p>
                    <p class="mt-0.5 flex items-center gap-1 text-[11px] text-on-surface-variant">
                        <x-user.icon name="users" :size="12" />
                        {{ \App\Models\Plan::limitLabel($mcMaxStudents, 'Không giới hạn học viên/lớp', 'Tối đa ', ' học viên/lớp') }}
                    </p>
                </div>
            </div>
            <div class="flex flex-1 items-center gap-3">
                <div class="h-2 flex-1 overflow-hidden rounded-full bg-surface-container-highest">
                    <div class="{{ $mcNearLimit ? 'bg-error' : 'bg-primary' }} h-full rounded-full" style="width: {{ $mcUsedPercent }}%"></div>
                </div>
                @if ($mcNearLimit)
                    <a href="{{ route('upgrade') }}" wire:navigate class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white transition-colors hover:bg-primary-container">
                        <x-user.icon name="zap" :size="14" /> Nâng cấp
                    </a>
                @else
                    <a href="{{ route('upgrade') }}" wire:navigate class="shrink-0 text-xs font-bold text-primary hover:underline">Xem gói</a>
                @endif
            </div>
        </div>

        <a href="{{ route('create-class') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-container">
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
                $isArchived = $class->status === 'archived';

                $present  = $class->sum_present  ?? 0;
                $late     = $class->sum_late     ?? 0;
                $absent   = $class->sum_absent   ?? 0;
                $excused  = $class->sum_excused  ?? 0;

                $totalStudied    = $present + $late + $absent + $excused;
                $plannedSessions = \App\Services\AttendanceCalculator::baseSessions((int) ($class->total_sessions ?? 0), $totalStudied);

                $attendancePct = \App\Services\AttendanceCalculator::percentOfPlanned(
                    $plannedSessions,
                    ['late' => $late, 'absent' => $absent, 'excused' => $excused],
                );

                $allowedAbsent = \App\Services\AttendanceCalculator::allowedAbsentSessions($plannedSessions);

                $isBanned  = $plannedSessions > 0 && ($absent > $allowedAbsent || $attendancePct < \App\Services\AttendanceCalculator::MIN_ATTENDANCE_PERCENT);
                $isWarning = ! $isBanned && $attendancePct < 85;

                if ($isEnded || $isArchived) {
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

                $colorIndex = hexdec(substr(md5((string) $class->id), 0, 8));
                $themeColor = $colorOptions[$colorIndex % count($colorOptions)];
                $bgIcons = ['laptop', 'book', 'code', 'book-open', 'graduation-cap', 'layout-dashboard'];
                $bgIcon = $bgIcons[$colorIndex % count($bgIcons)];
            @endphp

            <article @class([
                'group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300',
                'hover:border-slate-300 hover:shadow-md hover:-translate-y-0.5' => ! $isEnded && ! $isArchived,
                'opacity-75' => $isEnded,
                'opacity-50 grayscale bg-slate-50' => $isArchived,
            ])>
                @if (! $isArchived)
                    <a href="{{ route('lecturer.classes.show', $class->id) }}" wire:navigate class="absolute inset-0 z-10"><span class="sr-only">Xem chi tiết lớp</span></a>
                @endif

                {{-- Header Theme Color --}}
                <div class="{{ ($isEnded || $isArchived) ? 'bg-on-surface-variant' : $themeColor }} h-24 px-5 py-4 relative">
                    <div class="relative z-10 w-3/4">
                        <h3 class="truncate font-normal text-white text-[22px] tracking-wide leading-tight" title="{{ $class->name }}">
                            @if ($isArchived)
                                <span class="focus:outline-none">{{ $class->name }}</span>
                            @else
                                <a href="{{ route('lecturer.classes.show', $class->id) }}" wire:navigate class="hover:underline focus:outline-none">{{ $class->name }}</a>
                            @endif
                        </h3>
                        <p class="mt-1 truncate text-[13px] font-light text-white/95 tracking-wide">Mã lớp: {{ $class->class_code ?? $class->join_key }}</p>
                    </div>

                    {{-- Background Icon --}}
                    <div class="absolute right-3 top-2 z-0 opacity-15">
                        <x-user.icon :name="$bgIcon" :size="76" class="text-white transform -rotate-12" stroke-width="1.5" />
                    </div>

                    {{-- Avatar overlapping --}}
                    <div class="absolute -bottom-6 right-5 z-20">
                        <span class="{{ ($isEnded || $isArchived) ? 'bg-on-surface-variant' : $themeColor }} grid h-14 w-14 place-items-center rounded-full border-2 border-white text-[22px] font-medium text-white shadow-sm" title="{{ auth()->user()->name }}">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    </div>
                </div>

                {{-- Body (White) --}}
                <div class="flex-1 px-4 pt-8 pb-3">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex flex-wrap gap-1.5">
                            <span @class([
                                'inline-flex items-center rounded-sm px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                'bg-blue-50 text-blue-600' => ! $isEnded && ! $isArchived,
                                'bg-slate-100 text-slate-500' => $isArchived,
                                'bg-surface-container text-on-surface-variant' => $isEnded,
                            ])>
                                @if ($isArchived)
                                    Đã lưu trữ
                                @elseif ($isEnded)
                                    Đã kết thúc
                                @else
                                    Đang hoạt động
                                @endif
                            </span>
                            <span class="inline-flex items-center rounded-sm bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-600">
                                <x-user.icon name="shield" :size="10" class="mr-1" /> Chủ lớp
                            </span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-700">Mã lớp: <span class="font-bold">{{ $class->class_code ?? $class->join_key }}</span></span>
                    </div>

                    <div class="flex gap-8 mb-3">
                        <div>
                            <p class="text-[10px] font-semibold text-slate-500 mb-1">HỌC VIÊN</p>
                            <p class="flex items-center gap-1.5 text-lg font-bold text-slate-700">
                                <x-user.icon name="users" :size="16" class="text-slate-400" />
                                {{ $class->students_count }}
                            </p>
                        </div>
                        <div class="w-px bg-slate-200"></div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-500 mb-1">ĐÃ HỌC</p>
                            <p class="flex items-center gap-1.5 text-lg font-bold text-slate-700">
                                <x-user.icon name="check-square" :size="16" class="text-slate-400" />
                                {{ $class->studied_sessions ?? 0 }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <div class="mb-1.5 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase">TB CHUYÊN CẦN</span>
                            <div class="flex items-center gap-2">
                                @if ($isBanned)
                                    <span class="rounded-sm bg-error/10 px-1.5 py-0.5 text-[9px] font-bold text-error uppercase">Cấm thi</span>
                                @elseif ($isWarning)
                                    <span class="rounded-sm bg-amber-100 px-1.5 py-0.5 text-[9px] font-bold text-amber-700 uppercase">Cảnh báo</span>
                                @endif
                                <span class="{{ $textClass }} text-xs font-bold">{{ $attendancePct }}%</span>
                            </div>
                        </div>
                        <div class="h-1 w-full overflow-hidden bg-surface-container-high rounded-sm">
                            <div class="{{ $barClass }} h-full transition-all duration-700" style="width: {{ $attendancePct }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="relative z-20 mt-4 flex items-center justify-end gap-0.5 border-t border-outline-variant px-3 py-2">
                    <div x-data="{ copied: false }" class="relative z-20">
                        <button
                            type="button"
                            title="Sao chép mã tham gia lớp: {{ $class->join_key }}"
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
                        <a href="{{ match ($action['label']) { 'Điểm danh QR' => route('lecturer.attendance.create', ['class_id' => $class->id]), 'Thủ công' => route('lecturer.attendance.create', ['class_id' => $class->id]), 'Quản lý SV' => route('lecturer.students.index', ['class_id' => $class->id]), 'Thống kê' => route('lecturer.class.statistics', ['class_id' => $class->id]), default => '#' } }}" wire:navigate @class([
                            'group/action rounded-lg p-2 transition-colors hover:bg-surface-container',
                            'pointer-events-none opacity-40' => $isArchived || ($isEnded && in_array($action['icon'], ['qr-code', 'check-square'], true)),
                        ]) title="{{ $action['label'] }}">
                            <x-user.icon :name="$action['icon']" class="text-on-surface-variant transition-colors group-hover/action:text-primary" :size="18"/>
                        </a>
                    @endforeach

                    @if (! $isArchived)
                        <div class="relative z-20" x-data="{ open: false }">
                            <button type="button" x-on:click.stop="open = ! open" class="rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container">
                                <x-user.icon name="more-vertical" :size="18" />
                            </button>
                            <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute right-0 bottom-full z-50 mb-1 w-44 overflow-hidden rounded-lg border border-outline-variant bg-white py-1 shadow-lg">
                                <a href="{{ route('lecturer.classes.show', $class->id) }}" wire:navigate class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container">Xem lớp học</a>
                                @if (! $isEnded)
                                    <a href="{{ route('lecturer.classes.settings', $class->id) }}" wire:navigate class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container">Cài đặt lớp</a>
                                    <button type="button" wire:click.stop.prevent="confirmEndClass('{{ $class->id }}')" class="block w-full px-4 py-2 text-left text-sm text-error hover:bg-error/10">Kết thúc lớp</button>
                                @endif
                            </div>
                        </div>
                    @endif
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
                        <button type="button" wire:click="endClass('{{ $confirmingEndClassId }}')" class="rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-amber-600">Kết thúc lớp</button>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
