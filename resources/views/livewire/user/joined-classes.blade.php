@php
    $statuses = ['Tất cả', 'Đang học', 'Đã kết thúc', 'Cảnh báo chuyên cần'];
@endphp

<div class="w-full space-y-6 px-6 py-6 pb-24 sm:px-10 lg:px-16">


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
                $themeColor = $colorOptions[$class['id'] % count($colorOptions)];
                
                $bgIcons = ['laptop', 'book', 'code', 'book-open', 'graduation-cap', 'layout-dashboard'];
                $bgIcon = $bgIcons[$class['id'] % count($bgIcons)];

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
                'group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300',
                'hover:border-slate-300 hover:shadow-md hover:-translate-y-0.5' => ! $isEnded && ! $isWarning,
                'border-error/40 ring-1 ring-error/20' => $isWarning,
                'opacity-75' => $isEnded,
            ])>
                <a href="{{ route('student.classes.show', $class['id']) }}" class="absolute inset-0 z-10"><span class="sr-only">Vào thông tin lớp</span></a>

                {{-- Header Theme Color --}}
                <div class="{{ $isEnded ? 'bg-on-surface-variant' : ($isWarning ? 'bg-error' : $themeColor) }} h-24 px-5 py-4 relative">
                    <div class="relative z-10 w-3/4">
                        <h3 class="truncate font-normal text-white text-[22px] tracking-wide leading-tight" title="{{ $class['title'] }}">
                            <a href="{{ route('student.classes.show', $class['id']) }}" class="hover:underline focus:outline-none">{{ $class['title'] }}</a>
                        </h3>
                        <p class="mt-1 truncate text-[13px] text-white/95 font-light tracking-wide">{{ $class['schedule'] }}</p>
                        <p class="mt-0.5 truncate text-[12px] text-white/80 font-light tracking-wide">{{ $class['teacher'] }}</p>
                    </div>

                    {{-- Background Icon --}}
                    <div class="absolute right-3 top-2 z-0 opacity-15">
                        <x-user.icon :name="$bgIcon" :size="76" class="text-white transform -rotate-12" stroke-width="1.5" />
                    </div>

                    {{-- Avatar overlapping --}}
                    <div class="absolute -bottom-6 right-5 z-20">
                        <span class="{{ $isEnded ? 'bg-on-surface-variant' : ($isWarning ? 'bg-error' : $themeColor) }} grid h-14 w-14 place-items-center rounded-full border-2 border-white text-[22px] font-medium text-white shadow-sm" title="{{ $class['teacher'] }}">
                            {{ mb_strtoupper(mb_substr($class['teacher'], 0, 1)) }}
                        </span>
                    </div>
                </div>

                {{-- Body (White) --}}
                <div class="flex-1 px-4 pt-8 pb-3">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex flex-wrap gap-1.5">
                            <span @class([
                                'inline-flex items-center rounded-sm px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                'bg-blue-50 text-blue-600' => ! $isEnded && ! $isWarning,
                                'bg-error/10 text-error' => ! $isEnded && $isWarning,
                                'bg-surface-container text-on-surface-variant' => $isEnded,
                            ])>{{ $isEnded ? 'Đã kết thúc' : 'Đang học' }}</span>
                            <span class="inline-flex items-center rounded-sm bg-secondary/10 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-secondary">
                                <x-user.icon name="graduation-cap" :size="10" class="mr-1" /> Học viên
                            </span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-700">Mã lớp: <span class="font-bold">{{ $class['join_code'] }}</span></span>
                    </div>

                    <div class="flex gap-4 mb-3">
                        <div>
                            <p class="text-[10px] font-semibold text-slate-500 mb-1">CÓ MẶT</p>
                            <p class="flex items-center gap-1.5 text-lg font-bold text-slate-700">
                                <x-user.icon name="check-circle" :size="16" class="text-tertiary" />
                                {{ $class['present'] }}
                            </p>
                        </div>
                        <div class="w-px bg-slate-200"></div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-500 mb-1">VẮNG</p>
                            <p class="flex items-center gap-1.5 text-lg font-bold text-slate-700">
                                <x-user.icon name="x-circle" :size="16" class="text-error" />
                                {{ $class['absent'] }}
                            </p>
                        </div>
                        <div class="w-px bg-slate-200"></div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-500 mb-1">ĐI TRỄ</p>
                            <p class="flex items-center gap-1.5 text-lg font-bold text-slate-700">
                                <x-user.icon name="clock" :size="16" class="text-amber-600" />
                                {{ $class['late'] }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <div class="mb-1.5 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase">TB CHUYÊN CẦN</span>
                            <div class="flex items-center gap-2">
                                @if ($isWarning)
                                    <span class="rounded-sm bg-error/10 px-1.5 py-0.5 text-[9px] font-bold text-error uppercase">Cảnh báo</span>
                                @endif
                                <span class="{{ $attendanceColor }} text-xs font-bold">{{ $class['attendance'] }}%</span>
                            </div>
                        </div>
                        <div class="h-1 w-full overflow-hidden bg-surface-container-high rounded-sm">
                            <div class="{{ $barColor }} h-full transition-all duration-700" style="width: {{ $class['attendance'] }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Bottom Action bar --}}
                <div class="relative z-20 flex items-center justify-end gap-0.5 border-t border-outline-variant px-3 py-2 bg-white">
                    <a href="{{ route('student.classes.show', $class['id']) }}" class="group/btn rounded-lg p-2 transition-colors hover:bg-surface-container" title="Hồ sơ môn học">
                        <x-user.icon name="book-open" class="text-on-surface-variant transition-colors group-hover/btn:text-primary" :size="18"/>
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
            <div class="col-span-full py-20 text-center">
                <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-surface-container text-on-surface-variant">
                    <x-user.icon name="search" :size="28" />
                </div>
                <h3 class="font-medium text-on-surface-variant">Bạn chưa có lớp học nào</h3>
            </div>
        @endforelse
    </section>
</div>
