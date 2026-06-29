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

                // Màu progress bar và text theo chuyên cần
                if ($attendancePct < 70) {
                    $barClass  = 'bg-[#EF4444]';
                    $textClass = 'text-[#EF4444]';
                } elseif ($attendancePct < 90) {
                    $barClass  = 'bg-[#F59E0B]';
                    $textClass = 'text-[#F59E0B]';
                } else {
                    $barClass  = 'bg-[#22C55E]';
                    $textClass = 'text-[#22C55E]';
                }
                
                if ($isEnded) {
                    $barClass  = 'bg-[#F1F5F9]';
                    $textClass = 'text-[#64748B]';
                }

                $colorOptions = [
                    'bg-[#475569]', // slate-600
                    'bg-[#1D4ED8]', // blue-700
                    'bg-[#0F766E]', // teal-700
                    'bg-[#4338CA]', // indigo-700
                    'bg-[#047857]', // emerald-700
                    'bg-[#0369A1]', // sky-700
                    'bg-[#6D28D9]', // violet-700
                    'bg-[#B45309]', // amber-700
                ];
                $themeColor = $colorOptions[$class->id % count($colorOptions)];

                $decorations = [
                    // 1: Orange Book with subtle background circles
                    '<svg class="absolute -right-2 -bottom-2 h-28 w-28 transform rotate-[10deg] opacity-90" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="85" cy="30" r="15" stroke="rgba(255,255,255,0.15)" stroke-width="4"/>
                        <circle cx="85" cy="55" r="10" stroke="rgba(255,255,255,0.15)" stroke-width="4"/>
                        <!-- Book -->
                        <rect x="25" y="25" width="55" height="70" rx="4" fill="#FF7A59"/>
                        <!-- Spine/Ribbon -->
                        <path d="M65 25 h12 v25 l-6 -6 l-6 6 z" fill="#D94025"/>
                        <!-- Lines -->
                        <rect x="35" y="45" width="20" height="4" rx="2" fill="#D94025"/>
                        <rect x="35" y="55" width="12" height="4" rx="2" fill="#D94025"/>
                        <!-- Edge -->
                        <path d="M25 25 h4 v70 h-4 z" fill="#FF9A85"/>
                    </svg>',

                    // 2: Tablet & Code
                    '<svg class="absolute -right-2 top-2 h-28 w-28 transform -rotate-12 opacity-90" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="15" y="20" width="70" height="50" rx="6" fill="#1A2B4C"/>
                        <rect x="20" y="25" width="60" height="40" rx="2" fill="#38BDF8"/>
                        <circle cx="50" cy="74" r="2" fill="#94A3B8"/>
                        <!-- Code lines -->
                        <rect x="25" y="35" width="20" height="3" rx="1.5" fill="#F97316"/>
                        <rect x="25" y="42" width="15" height="3" rx="1.5" fill="#F0F9FF"/>
                        <rect x="25" y="49" width="30" height="3" rx="1.5" fill="#F0F9FF"/>
                    </svg>',

                    // 3: Notebook & Pen
                    '<svg class="absolute right-0 -bottom-4 h-32 w-32 transform rotate-[15deg] opacity-90" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Notebook -->
                        <rect x="40" y="30" width="50" height="60" rx="2" fill="#A3E635"/>
                        <rect x="45" y="35" width="40" height="50" fill="#D9F99D"/>
                        <rect x="50" y="45" width="20" height="2" fill="#65A30D"/>
                        <rect x="50" y="55" width="30" height="2" fill="#65A30D"/>
                        <rect x="50" y="65" width="25" height="2" fill="#65A30D"/>
                        <!-- Pen -->
                        <path d="M20 70 L60 30 L65 35 L25 75 Z" fill="#3B82F6"/>
                        <path d="M60 30 L65 25 L70 30 L65 35 Z" fill="#1D4ED8"/>
                        <path d="M20 70 L15 78 L25 75 Z" fill="#FCA5A5"/>
                        <circle cx="17" cy="76" r="2" fill="#1E40AF"/>
                    </svg>',

                    // 4: Science Flask
                    '<svg class="absolute -right-2 top-0 h-32 w-32 transform rotate-6 opacity-90" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M45 20 h10 v20 l15 30 a5 5 0 0 1 -4 8 h-32 a5 5 0 0 1 -4 -8 l15 -30 z" fill="#E2E8F0" opacity="0.9"/>
                        <path d="M36 60 h28 l5 10 a5 5 0 0 1 -4 8 h-32 a5 5 0 0 1 -4 -8 l5 -10 z" fill="#10B981"/>
                        <circle cx="45" cy="65" r="3" fill="#ffffff"/>
                        <circle cx="55" cy="70" r="2" fill="#ffffff"/>
                        <circle cx="50" cy="55" r="2.5" fill="#ffffff"/>
                        <rect x="42" y="15" width="16" height="5" rx="2" fill="#94A3B8"/>
                    </svg>',
                ];
                $themeDecoration = $decorations[$class->id % count($decorations)];
            @endphp
            <article @class([
                'group relative flex flex-col overflow-hidden rounded-xl bg-white border border-gray-200 transition-shadow duration-200',
                "hover:shadow-md" => ! $isEnded,
                'opacity-80' => $isEnded,
            ])>
                {{-- Phủ 1 link tàng hình lên toàn bộ thẻ để click được cả thẻ --}}
                <a href="{{ route('lecturer.classes.show', $class->id) }}" class="absolute inset-0 z-10"><span class="sr-only">Xem chi tiết lớp</span></a>

                <!-- Classroom-style Header -->
                <div @class([
                    'relative overflow-hidden flex h-28 flex-col p-4',
                    "$themeColor" => ! $isEnded,
                    'bg-slate-500' => $isEnded,
                ])>
                    <!-- Background Decoration -->
                    {!! $themeDecoration !!}
                    <div class="relative z-10 flex items-start justify-between">
                        <div class="pr-8">
                            <h4 class="line-clamp-2 text-3xl font-extrabold text-white hover:underline cursor-pointer" title="{{ $class->name }}">
                                <a href="{{ route('lecturer.classes.show', $class->id) }}">{{ $class->name }}</a>
                            </h4>
                            <div class="mt-1 flex items-center gap-2 text-xl font-semibold text-white/90">
                                <span>{{ $class->subject_code ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-1 flex-col p-4 pt-3 relative">
                    <!-- Classroom Avatar Overlap -->
                    <div class="absolute -top-7 right-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-2xl font-normal text-white shadow-sm ring-2 ring-white">
                        {{ mb_strtoupper(mb_substr($class->name, 0, 1)) }}
                    </div>

                    <!-- Tags & Class Code -->
                    <div class="flex items-center justify-between gap-2 min-w-0 mt-8 px-4">
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span @class([
                                'whitespace-nowrap rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                                'bg-[#DBEAFE] text-[#2563EB]' => ! $isEnded,
                                'bg-[#F1F5F9] text-[#64748B]' => $isEnded,
                            ])>
                                @if($isEnded) ĐÃ KẾT THÚC @else ĐANG HOẠT ĐỘNG @endif
                            </span>
                            <span class="whitespace-nowrap flex items-center gap-1 rounded-full bg-[#FEF3C7] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#D97706]">
                                <x-user.icon name="shield" :size="10" />
                                Chủ lớp
                            </span>
                        </div>
                        <span class="whitespace-nowrap text-sm font-semibold text-on-surface-variant shrink-0">Mã lớp: <span class="font-bold text-on-surface">{{ $class->code }}</span></span>
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
                        <div class="flex-1 border-l border-outline-variant/30 pl-4">
                            <p class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">Đã học</p>
                            <p class="mt-1 flex items-center gap-1.5">
                                <x-user.icon name="check-square" class="text-tertiary" :size="16"/>
                                <span class="text-xl font-black leading-none text-on-surface">{{ $class->studied_sessions ?? 0 }}</span>
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
                        <div class="h-1.5 w-full bg-gray-100">
                            <div class="{{ $barClass }} h-full" style="width: {{ $attendancePct }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-1 px-4 py-2 border-t border-gray-100 bg-white relative z-20">
                    {{-- Nút Copy mã lớp --}}
                    <div x-data="{ copied: false }" class="relative z-20">
                        <button
                            type="button"
                            title="Sao chép mã lớp: {{ $class->code }}"
                            class="group/action relative rounded-full p-2.5 transition-colors hover:bg-black/5"
                            x-on:click="navigator.clipboard.writeText('{{ $class->code }}'); copied = true; setTimeout(() => copied = false, 2000); $event.stopPropagation()"
                        >
                            <template x-if="!copied">
                                <x-user.icon name="copy" :size="20" class="text-[#5F6368] transition-colors group-hover/action:text-primary" />
                            </template>
                            <template x-if="copied">
                                <x-user.icon name="check-circle" :size="20" class="text-green-600" />
                            </template>
                        </button>
                    </div>
                    @foreach ([
                        ['label' => 'Điểm danh QR', 'icon' => 'qr-code'],
                        ['label' => 'Thủ công', 'icon' => 'check-square'],
                        ['label' => 'Quản lý SV', 'icon' => 'users'],
                        ['label' => 'Thống kê', 'icon' => 'bar-chart'],
                    ] as $action)
                        <a href="{{ match ($action['label']) { 'Điểm danh QR' => route('lecturer.attendance.create', ['class_id' => $class->id]), 'Thủ công' => route('lecturer.attendance.create', ['class_id' => $class->id]), 'Quản lý SV' => route('lecturer.students.index', ['class_id' => $class->id]), 'Thống kê' => route('lecturer.class.statistics', ['class_id' => $class->id]), default => '#' } }}" @class([
                            'group/action relative rounded-full p-2.5 transition-colors hover:bg-black/5',
                            'cursor-not-allowed opacity-50' => $isEnded && in_array($action['icon'], ['qr-code', 'check-square'], true),
                        ]) title="{{ $action['label'] }}">
                            <x-user.icon :name="$action['icon']" class="text-[#5F6368] transition-colors group-hover/action:text-primary" :size="20"/>
                        </a>
                    @endforeach

                    <!-- Classroom style 3-dots menu -->
                    <div class="relative z-20 ml-1" x-data="{ open: false }">
                        <button type="button" x-on:click.stop="open = ! open" class="rounded-full p-2.5 text-[#5F6368] transition-colors hover:bg-black/5">
                            <x-user.icon name="more-vertical" :size="20" />
                        </button>
                        <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute right-0 bottom-full z-50 mb-1 w-44 rounded-md border border-gray-200 bg-white py-2 shadow-lg">
                            <a href="{{ route('lecturer.classes.show', $class->id) }}" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Xem lớp học</a>
                            @if (! $isEnded)
                                <a href="{{ route('lecturer.classes.settings', $class->id) }}" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Cài đặt lớp</a>
                                <button type="button" wire:click.stop.prevent="confirmEndClass({{ $class->id }})" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">Kết thúc lớp</button>
                            @endif
                        </div>
                    </div>
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
