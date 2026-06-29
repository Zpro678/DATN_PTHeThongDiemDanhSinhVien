@php
    $statuses = ['Tất cả', 'Đang học', 'Đã kết thúc', 'Cảnh báo chuyên cần'];
@endphp

<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-center">
        <div>
            <h1 class="flex items-center gap-3 text-2xl font-extrabold uppercase tracking-tight text-slate-900">
                <x-user.icon name="graduation-cap" class="text-secondary" />
                Lớp tôi tham gia
            </h1>
            <p class="mt-2 text-sm text-slate-500">Danh sách các lớp bạn đang học và theo dõi điểm danh</p>
        </div>
        <button type="button" x-on:click="$dispatch('open-join-class-modal')" class="flex w-full items-center justify-center gap-2 rounded-xl bg-secondary px-6 py-3 font-bold text-white transition-all hover:bg-secondary/90 active:scale-95 md:w-auto">
            <x-user.icon name="plus" />
            Tham gia lớp bằng mã
        </button>
    </section>

    <section class="flex flex-col gap-4 lg:flex-row">
        <label class="relative flex-1">
            <x-user.icon name="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-outline" />
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Tìm kiếm theo mã lớp, tên lớp, môn học..."
                class="w-full rounded-2xl border border-outline-variant/30 bg-white py-3.5 pl-12 pr-4 text-on-surface shadow-sm transition-all focus:border-secondary focus:outline-none focus:ring-2 focus:ring-secondary/20"
            >
        </label>

        <div class="flex gap-3 overflow-x-auto pb-1 lg:pb-0 hide-scrollbar">
            <div class="flex shrink-0 rounded-2xl border border-outline-variant/30 bg-white p-1 shadow-sm">
                @foreach ($statuses as $status)
                    <button
                        type="button"
                        wire:click="setStatusFilter('{{ $status }}')"
                        @class([
                            'whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-bold transition-all',
                            'bg-error/10 text-error' => $statusFilter === $status && $status === 'Cảnh báo chuyên cần',
                            'bg-secondary-container text-on-secondary-container' => $statusFilter === $status && $status !== 'Cảnh báo chuyên cần',
                            'text-on-surface-variant hover:bg-surface-container-lowest hover:text-on-surface' => $statusFilter !== $status,
                        ])
                    >
                        {{ $status }}
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($classes as $class)
            @php
                $isEnded = $class['ended'];
                $isWarning = $class['warning']; // Chuyên cần 80–84%: sắp đến ngưỡng cấm thi.
                $isBanned  = $class['banned'];  // Chuyên cần < 80% hoặc vắng > 20%: nguy cơ cấm thi.

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
                $themeColor = $colorOptions[$class['id'] % count($colorOptions)];

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
                $themeDecoration = $decorations[$class['id'] % count($decorations)];

                // Tỷ lệ chuyên cần
                if ($class['attendance'] < 70) {
                    $attendanceColor = 'text-[#EF4444]';
                    $barColor        = 'bg-[#EF4444]';
                } elseif ($class['attendance'] < 90) {
                    $attendanceColor = 'text-[#F59E0B]';
                    $barColor        = 'bg-[#F59E0B]';
                } else {
                    $attendanceColor = 'text-[#22C55E]';
                    $barColor        = 'bg-[#22C55E]';
                }

                if ($isEnded) {
                    $attendanceColor = 'text-[#64748B]';
                    $barColor        = 'bg-[#F1F5F9]';
                }
            @endphp

            <article @class([
                'group relative flex flex-col overflow-hidden rounded-xl bg-white border border-gray-200 transition-shadow duration-200 hover:-translate-y-1',
                "hover:shadow-md" => !$isEnded && !$isWarning,
                'ring-1 ring-error/30 shadow-lg shadow-error/5 hover:shadow-xl bg-alert-container/30' => $isWarning,
                'opacity-80' => $isEnded,
            ])>
                {{-- Phủ 1 link tàng hình lên toàn bộ thẻ để click được cả thẻ --}}
                <a href="{{ route('student.classes.show', $class['id']) }}" class="absolute inset-0 z-10"><span class="sr-only">Vào thông tin lớp</span></a>

                <!-- Classroom-style Header -->
                <div @class([
                    'relative overflow-hidden flex h-28 flex-col p-4',
                    "$themeColor" => !$isEnded && !$isWarning,
                    'bg-error/90' => $isWarning,
                    'bg-slate-500' => $isEnded,
                ])>
                    <!-- Background Decoration -->
                    {!! $themeDecoration !!}

                    <div class="relative z-10 flex items-start justify-between">
                        <div class="pr-8">
                            <h4 class="line-clamp-2 text-3xl font-extrabold text-white hover:underline cursor-pointer" title="{{ $class['title'] }}">
                                <a href="{{ route('student.classes.show', $class['id']) }}">{{ $class['title'] }}</a>
                            </h4>
                            <div class="mt-1 flex items-center gap-2 text-xl font-semibold text-white/90">
                                <span>{{ $class['schedule'] }} - {{ $class['teacher'] }}</span>
                            </div>
                    </div>
                </div>

                <div @class([
                    'flex flex-1 flex-col p-4 pt-3 relative',
                    'bg-white' => ! $isWarning && ! $isEnded,
                    'bg-white/40' => $isWarning,
                    'bg-white/50 grayscale transition-all duration-300 group-hover:grayscale-0' => $isEnded,
                ])>
                    <!-- Classroom Avatar Overlap -->
                    <div class="absolute -top-7 right-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-2xl font-normal text-white shadow-sm ring-2 ring-white">
                        {{ mb_strtoupper(mb_substr($class['teacher'], 0, 1)) }}
                    </div>

                    <!-- Tags & Class Code -->
                    <div class="mb-4 mt-8 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span @class([
                                'rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                                'bg-[#DBEAFE] text-[#2563EB]' => ! $isEnded && !$isWarning,
                                'bg-error/10 text-error' => ! $isEnded && $isWarning,
                                'bg-[#F1F5F9] text-[#64748B]' => $isEnded,
                            ])>
                                {{ $isEnded ? 'Đã kết thúc' : 'Đang học' }}
                            </span>
                            <span class="flex items-center gap-1 rounded-full border border-outline-variant/20 bg-surface-container-highest px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                                <x-user.icon name="graduation-cap" :size="10" />
                                Học viên
                            </span>
                        </div>
                        <span class="text-sm font-semibold text-on-surface-variant">Mã lớp: <span class="font-bold text-on-surface">{{ $class['join_code'] }}</span></span>

                    @if ($isWarning)
                        <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-error/20 bg-error/10 p-2.5">
                            <x-user.icon name="alert-triangle" :size="16" class="mt-0.5 text-error shrink-0" />
                            <div>
                                <h5 class="text-[13px] font-bold text-error">Cảnh báo chuyên cần</h5>
                                <p class="mt-0.5 text-xs text-error/80 leading-snug">Tỷ lệ chuyên cần của bạn dưới 80%. Nếu vắng thêm 1 buổi sẽ bị cấm thi.</p>
                            </div>
                        </div>
                    @endif

                    <!-- Stats & Progress -->
                    <div class="mb-4 flex items-end justify-between">
                        <div class="flex gap-4 sm:gap-6">
                            <div>
                                <p class="text-[10px] font-bold uppercase text-on-surface-variant">Có mặt</p>
                                <p class="text-base font-black text-secondary leading-none mt-1">{{ $class['present'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-on-surface-variant">Vắng</p>
                                <p class="text-base font-black text-error leading-none mt-1">{{ $class['absent'] }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-on-surface-variant">Đi trễ</p>
                                <p class="text-base font-black text-[#F59E0B] leading-none mt-1">{{ $class['late'] }}</p>
                            </div>
                        </div>
                        <div class="text-right w-28 sm:w-32 shrink-0">
                            <div class="mb-1.5 flex items-center justify-between gap-2">
                                <span class="text-[10px] font-bold uppercase text-on-surface-variant whitespace-nowrap">Chuyên cần</span>
                                <span class="{{ $attendanceColor }} text-sm font-black">{{ $class['attendance'] }}%</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-container-highest">
                                <div class="{{ $barColor }} h-full rounded-full transition-all duration-1000" style="width: {{ $class['attendance'] }}%"></div>
                            </div>
                            @if ($isBanned)
                                <span class="mt-1 inline-block text-[9px] font-bold uppercase tracking-wide text-red-500">Nguy cơ cấm thi</span>
                            @elseif ($isWarning)
                                <span class="mt-1 inline-block text-[9px] font-bold uppercase tracking-wide text-amber-500">Cảnh báo</span>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="mt-auto flex items-center justify-end gap-1 border-t border-gray-100 pt-2 relative z-20">
                        <a href="{{ route('student.classes.show', $class['id']) }}" class="group/btn relative rounded-full p-2.5 transition-colors hover:bg-black/5" title="Hồ sơ môn học">
                            <x-user.icon name="folder" class="text-[#5F6368] transition-colors group-hover/btn:text-primary" :size="20"/>
                        </a>
                        <a href="{{ route('student.attendance.history', ['classFilter' => $class['id']]) }}" class="group/btn relative rounded-full p-2.5 transition-colors hover:bg-black/5" title="Lịch sử điểm danh">
                            <x-user.icon name="list" class="text-[#5F6368] transition-colors group-hover/btn:text-primary" :size="20"/>
                        </a>

                        <!-- Classroom style 3-dots menu -->
                        <div class="relative z-20 ml-1" x-data="{ open: false }">
                            <button type="button" x-on:click.stop="open = ! open" class="rounded-full p-2.5 text-[#5F6368] transition-colors hover:bg-black/5">
                                <x-user.icon name="more-vertical" :size="20" />
                            </button>
                            <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute right-0 bottom-full z-50 mb-1 w-44 rounded-md border border-gray-200 bg-white py-2 shadow-lg">
                                <a href="{{ route('student.classes.show', $class['id']) }}" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Vào thông tin</a>
                                <a href="{{ route('student.attendance.history', ['classFilter' => $class['id']]) }}" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Lịch sử</a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center rounded-3xl border border-dashed border-outline-variant/30 bg-surface-container-lowest py-16 text-center">
                <div class="mb-4 rounded-full bg-secondary-container p-4 text-on-secondary-container">
                    <x-user.icon name="search" :size="32" />
                </div>
                <h3 class="text-lg font-bold text-on-surface">Không tìm thấy lớp học nào</h3>
                <p class="mt-2 max-w-sm text-sm text-on-surface-variant">Không có lớp học nào khớp với bộ lọc hoặc từ khóa tìm kiếm của bạn. Hãy thử thay đổi bộ lọc.</p>
                <button type="button" wire:click="$set('search', ''); $set('statusFilter', 'Tất cả')" class="mt-6 font-bold text-secondary hover:underline">Xóa bộ lọc</button>
            </div>
        @endforelse
    </section>
</div>
