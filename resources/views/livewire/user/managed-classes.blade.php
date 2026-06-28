@php
    $statuses = ['Tất cả', 'Đang hoạt động', 'Đã kết thúc'];
@endphp

<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-center">
        <div>
            <h1 class="flex items-center gap-3 text-2xl font-extrabold uppercase tracking-tight text-slate-900">
                <x-user.icon name="book-open" class="text-primary" />
                Lớp tôi quản lý
            </h1>
            <p class="mt-2 text-sm text-slate-500">Danh sách các lớp bạn đang làm chủ lớp</p>
        </div>
        <a href="{{ route('create-class') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 font-bold text-white transition-all hover:shadow-lg active:scale-95 md:w-auto">
            <x-user.icon name="plus" />
            Tạo lớp mới
        </a>
    </section>

    <section class="flex flex-col gap-4 lg:flex-row lg:items-center">
        <!-- Search Bar -->
        <label class="relative flex-1 group">
            <div class="pointer-events-none absolute left-5 top-1/2 -translate-y-1/2 text-on-surface-variant transition-colors group-focus-within:text-primary">
                <x-user.icon name="search" :size="20" />
            </div>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Tìm kiếm theo mã lớp, tên lớp, môn học..."
                class="w-full rounded-full bg-white py-3.5 pl-14 pr-6 text-sm text-on-surface shadow-sm ring-1 ring-outline-variant/20 transition-all hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30"
            >
        </label>

        <div class="flex gap-4 overflow-x-auto pb-2 lg:pb-0 hide-scrollbar shrink-0">
            <!-- Segmented Tabs -->
            <div class="flex shrink-0 items-center rounded-full bg-surface-container-low p-1.5 shadow-inner">
                @foreach ($statuses as $status)
                    <button
                        type="button"
                        wire:click="setStatusFilter('{{ $status }}')"
                        @class([
                            'whitespace-nowrap rounded-full px-6 py-2 text-sm font-bold transition-all',
                            'bg-white text-primary shadow-sm ring-1 ring-outline-variant/10' => $statusFilter === $status,
                            'text-on-surface-variant hover:text-on-surface' => $statusFilter !== $status,
                        ])
                    >
                        {{ $status }}
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 lg:grid-cols-3 xl:grid-cols-3">
        @forelse ($classes as $index => $class)
            @php
                $isEnded = $class->status === 'ended';

                $sessionsCompleted = $class->completed_sessions_count ?? 0;
                $studiedSessions    = $class->studied_sessions ?? 0;

                // Số tiết tổng hợp từ AttendanceSummary của tất cả học viên trong lớp.
                $present  = $class->sum_present  ?? 0; // Tổng tiết có mặt của cả lớp.
                $late     = $class->sum_late     ?? 0; // Tổng tiết đi muộn của cả lớp.
                $absent   = $class->sum_absent   ?? 0; // Tổng tiết vắng không phép của cả lớp.
                $excused  = $class->sum_excused  ?? 0; // Tổng tiết vắng có phép của cả lớp.

                // Tổng tiết kế hoạch cả khóa; fallback về tiết đã học nếu chưa cấu hình.
                $totalStudied   = $present + $late + $absent + $excused;
                $plannedSessions = max((int) ($class->total_sessions ?? 0), $totalStudied);

                // % trung bình chuyên cần lớp (suy từ điểm trừ: vắng −1, muộn −0.5).
                $attendancePct = \App\Services\AttendanceCalculator::percentOfPlanned(
                    $plannedSessions,
                    ['late' => $late, 'absent' => $absent, 'excused' => $excused],
                );

                // Số buổi tối đa được phép vắng (20% tổng buổi dự kiến).
                $allowedAbsent = \App\Services\AttendanceCalculator::allowedAbsentSessions($plannedSessions);

                // Cấm thi: vắng cả lớp vượt ngưỡng hoặc chuyên cần trung bình < 80%.
                $isBanned  = $plannedSessions > 0 && ($absent > $allowedAbsent || $attendancePct < \App\Services\AttendanceCalculator::MIN_ATTENDANCE_PERCENT);
                // Cảnh báo: chuyên cần trung bình 80–84%.
                $isWarning = ! $isBanned && $attendancePct < 85;

                // Màu progress bar và text theo trạng thái.
                if ($isEnded) {
                    $barClass  = 'bg-slate-400';
                    $textClass = 'text-slate-500';
                } elseif ($isBanned) {
                    $barClass  = 'bg-red-500';
                    $textClass = 'text-red-600';
                } elseif ($isWarning) {
                    $barClass  = 'bg-amber-400';
                    $textClass = 'text-amber-600';
                } else {
                    $barClass  = 'bg-blue-500';
                    $textClass = 'text-blue-600';
                }
            @endphp
            <article @class([
                'group relative flex flex-col overflow-hidden rounded-3xl bg-white transition-all duration-300 hover:-translate-y-1 cursor-pointer',
                'ring-1 ring-blue-500/20 shadow-lg shadow-blue-500/5 hover:shadow-xl hover:shadow-blue-500/10' => ! $isEnded,
                'ring-1 ring-outline-variant/20 shadow-md hover:shadow-xl opacity-80 hover:opacity-100' => $isEnded,
            ])>
                {{-- Phủ 1 link tàng hình lên toàn bộ thẻ để click được cả thẻ --}}
                <a href="{{ route('lecturer.classes.show', $class->id) }}" class="absolute inset-0 z-10"><span class="sr-only">Xem chi tiết lớp</span></a>

                <!-- Classroom-style Header -->
                <div @class([
                    'relative flex h-28 flex-col justify-between p-5',
                    'bg-blue-600/95' => ! $isEnded,
                    'bg-slate-600/95' => $isEnded,
                ])>
                    <div class="flex items-start justify-between">
                        <div class="pr-6">
                            <h4 class="line-clamp-2 text-[22px] font-medium text-white hover:underline cursor-pointer" title="{{ $class->name }}">
                                <a href="{{ route('lecturer.classes.show', $class->id) }}">{{ $class->name }}</a>
                            </h4>
                            <div class="mt-1 flex items-center gap-2 text-sm text-white/90">
                                <span>{{ $class->subject_code ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-2 top-2 z-20" x-data="{ open: false }">
                            <button type="button" x-on:click="open = ! open" class="rounded-full p-2 text-white transition-colors hover:bg-white/20 relative z-20">
                                <x-user.icon name="more-vertical" :size="20" />
                            </button>
                            <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute right-0 top-full z-50 mt-1 w-44 rounded-xl border border-outline-variant/20 bg-white py-2 shadow-lg">
                                <a href="{{ route('lecturer.classes.show', $class->id) }}" class="block w-full px-4 py-2 text-left text-sm font-medium hover:bg-surface-container">Xem lớp học</a>
                                @if (! $isEnded)
                                    <a href="{{ route('lecturer.classes.settings', $class->id) }}" class="block w-full px-4 py-2 text-left text-sm font-medium hover:bg-surface-container">Cài đặt lớp</a>
                                    <button type="button" wire:click="confirmEndClass({{ $class->id }})" class="block w-full px-4 py-2 text-left text-sm font-medium text-error hover:bg-error/10">Kết thúc lớp</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-1 flex-col p-4 pt-3">
                    <!-- Tags & Class Code -->
                    <div class="flex items-center justify-between gap-2 min-w-0">
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span @class([
                                'whitespace-nowrap rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                                'bg-blue-100 text-blue-700' => ! $isEnded,
                                'bg-surface-container text-on-surface-variant' => $isEnded,
                            ])>
                                @if($isEnded) ĐÃ KẾT THÚC @else ĐANG HOẠT ĐỘNG @endif
                            </span>
                            <span class="whitespace-nowrap flex items-center gap-1 rounded-full bg-[#F59E0B]/10 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#F59E0B]">
                                <x-user.icon name="shield" :size="10" />
                                Chủ lớp
                            </span>
                        </div>
                        <span class="whitespace-nowrap text-[11px] font-medium text-on-surface-variant shrink-0">Mã lớp: <span class="font-bold text-on-surface">{{ $class->code }}</span></span>
                    </div>

                    <!-- Stats Row -->
                    <div class="mt-4 flex gap-8">
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">Học viên</p>
                            <p class="mt-1 flex items-center gap-1.5">
                                <x-user.icon name="users" class="text-primary" :size="16"/>
                                <span class="text-xl font-black leading-none text-on-surface">{{ $class->students_count }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">Đã học</p>
                            <p class="mt-1 flex items-center gap-1.5">
                                <x-user.icon name="check-square" class="text-tertiary" :size="16"/>
                                <span class="text-xl font-black leading-none text-on-surface">{{ $class->studied_sessions ?? 0 }}/{{ $class->total_sessions }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-auto pt-4">
                        <div class="mb-1.5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-on-surface-variant">TB chuyên cần</span>
                                @if ($isBanned)
                                    <span class="rounded-full bg-red-100 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wide text-red-600">Cấm thi</span>
                                @elseif ($isWarning)
                                    <span class="rounded-full bg-amber-100 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wide text-amber-600">Cảnh báo</span>
                                @endif
                            </div>
                            <span class="{{ $textClass }} text-xs font-black">{{ $attendancePct }}%</span>
                        </div>
                        <div class="h-1 w-full overflow-hidden rounded-full bg-surface-container-highest">
                            <div class="{{ $barClass }} h-full rounded-full transition-all duration-1000" style="width: {{ $attendancePct }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-1 px-4 py-3 bg-surface-container-lowest/50 relative z-20">
                    {{-- Nút Copy mã lớp --}}
                    <div x-data="{ copied: false }" class="relative z-20">
                        <button
                            type="button"
                            title="Sao chép mã lớp: {{ $class->code }}"
                            class="group/action relative rounded-full p-2.5 transition-colors hover:bg-surface-container-low"
                            x-on:click="navigator.clipboard.writeText('{{ $class->code }}'); copied = true; setTimeout(() => copied = false, 2000); $event.stopPropagation()"
                        >
                            <template x-if="!copied">
                                <x-user.icon name="copy" :size="18" class="text-on-surface-variant transition-colors group-hover/action:text-primary" />
                            </template>
                            <template x-if="copied">
                                <x-user.icon name="check-circle" :size="18" class="text-green-600" />
                            </template>
                        </button>
                        <span
                            x-show="copied"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-slate-800 px-2 py-1 text-[10px] font-bold text-white"
                        >Đã copy!</span>
                    </div>
                    @foreach ([
                        ['label' => 'Điểm danh QR', 'icon' => 'qr-code'],
                        ['label' => 'Thủ công', 'icon' => 'check-square'],
                        ['label' => 'Quản lý SV', 'icon' => 'users'],
                        ['label' => 'Thống kê', 'icon' => 'bar-chart'],
                    ] as $action)
                        <a href="{{ match ($action['label']) { 'Điểm danh QR' => route('lecturer.attendance.create', ['class_id' => $class->id]), 'Thủ công' => route('lecturer.attendance.create', ['class_id' => $class->id]), 'Quản lý SV' => route('lecturer.students.index', ['class_id' => $class->id]), 'Thống kê' => route('lecturer.class.statistics', ['class_id' => $class->id]), default => '#' } }}" @class([
                            'group/action relative rounded-full p-2.5 transition-colors hover:bg-surface-container-low',
                            'cursor-not-allowed opacity-50' => $isEnded && in_array($action['icon'], ['qr-code', 'check-square'], true),
                        ]) title="{{ $action['label'] }}">
                            <x-user.icon :name="$action['icon']" :size="18" class="text-on-surface-variant transition-colors group-hover/action:text-primary" />
                        </a>
                    @endforeach
                </div>
            </article>
            @empty
                <div class="col-span-full py-12 text-center text-on-surface-variant">
                    <x-user.icon name="inbox" :size="48" class="mx-auto mb-4 opacity-50" />
                    <p class="text-lg">Không tìm thấy lớp học nào.</p>
                </div>
            @endforelse
    </section>

    {{-- Modal xác nhận Kết thúc lớp --}}
    @if ($confirmingEndClassId)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
                 x-data
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100">
                            <x-user.icon name="alert-triangle" :size="20" class="text-amber-600" />
                        </div>
                        <h3 class="text-lg font-bold text-on-surface">Xác nhận kết thúc lớp</h3>
                    </div>
                    <p class="text-sm text-on-surface-variant">Bạn có chắc chắn muốn kết thúc lớp học này? Hành động này sẽ khóa toàn bộ hoạt động điểm danh của lớp.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="cancelEndClass" class="rounded-xl border border-outline-variant/30 px-5 py-2.5 text-sm font-bold text-on-surface-variant transition-colors hover:bg-surface-container-low">
                            Hủy bỏ
                        </button>
                        <button type="button" wire:click="endClass({{ $confirmingEndClassId }})" class="rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-amber-600">
                            Kết thúc lớp
                        </button>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
