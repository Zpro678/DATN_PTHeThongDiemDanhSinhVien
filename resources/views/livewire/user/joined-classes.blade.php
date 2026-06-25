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
                $isWarning = $class['warning'];
                if ($isEnded) {
                    $isPrimary = false;
                    $isTertiary = false;
                } else {
                    $isPrimary = $loop->iteration % 3 === 1;
                    $isTertiary = $loop->iteration % 3 === 2;
                }
                
                $attendanceColor = $isWarning ? 'text-error' : ($isEnded ? 'text-on-surface-variant' : ($isTertiary ? 'text-tertiary' : 'text-primary'));
                $barColor = $isWarning ? 'bg-error' : ($isEnded ? 'bg-outline-variant' : ($isTertiary ? 'bg-tertiary' : 'bg-primary'));
            @endphp

            <article @class([
                'group relative flex flex-col overflow-hidden rounded-3xl bg-white transition-all duration-300 hover:-translate-y-1',
                'ring-1 ring-primary/20 shadow-lg shadow-primary/5 hover:shadow-xl hover:shadow-primary/10' => $isPrimary && !$isWarning,
                'ring-1 ring-tertiary/20 shadow-lg shadow-tertiary/5 hover:shadow-xl hover:shadow-tertiary/10' => $isTertiary && !$isWarning,
                'ring-1 ring-error/30 shadow-lg shadow-error/5 hover:shadow-xl hover:shadow-error/10 bg-alert-container/30' => $isWarning,
                'ring-1 ring-outline-variant/20 shadow-md hover:shadow-xl' => ! $isPrimary && ! $isTertiary && !$isWarning,
                'opacity-80 hover:opacity-100' => $isEnded,
            ])>
                <!-- Classroom-style Header -->
                <div @class([
                    'relative flex h-20 flex-col justify-between p-4',
                    'bg-primary/95' => $isPrimary && !$isWarning,
                    'bg-tertiary/95' => $isTertiary && !$isWarning,
                    'bg-error/90' => $isWarning,
                    'bg-slate-600/95' => ! $isPrimary && ! $isTertiary && !$isWarning,
                ])>
                    <div class="flex items-start justify-between">
                        <div class="pr-6">
                            <h4 class="line-clamp-2 text-lg font-bold text-white hover:underline cursor-pointer sm:text-xl" title="{{ $class->name }}">
                                <a href="{{ route('student.classes.show', $class['id']) }}">{{ $class['title'] }}</a>
                            </h4>
                            <div class="mt-0.5 flex items-center gap-2 text-xs text-white/90 sm:text-sm">
                                <span>{{ $class['schedule'] }} - {{ $class['teacher'] }}</span>
                            </div>
                        </div>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-2 top-2" x-data="{ open: false }">
                            <button type="button" x-on:click="open = ! open" class="rounded-full p-1.5 text-white transition-colors hover:bg-white/20">
                                <x-user.icon name="more-vertical" :size="18" />
                            </button>
                            <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute right-0 top-full z-50 mt-1 w-44 rounded-xl border border-outline-variant/20 bg-white py-2 shadow-lg">
                                <a href="{{ route('student.classes.show', $class['id']) }}" class="block w-full px-4 py-2 text-left text-sm font-medium hover:bg-surface-container">Vào thông tin</a>
                                <a href="{{ route('student.attendance.history', ['classFilter' => $class['id']]) }}" class="block w-full px-4 py-2 text-left text-sm font-medium hover:bg-surface-container">Lịch sử</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div @class([
                    'flex flex-1 flex-col p-4',
                    'bg-white' => ! $isWarning && ! $isEnded,
                    'bg-white/40' => $isWarning,
                    'bg-white/50 grayscale transition-all duration-300 group-hover:grayscale-0' => $isEnded,
                ])>
                    <!-- Tags & Class Code -->
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span @class([
                                'rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                                'bg-primary/10 text-primary' => ! $isEnded && $isPrimary && !$isWarning,
                                'bg-tertiary/10 text-tertiary' => ! $isEnded && $isTertiary && !$isWarning,
                                'bg-error/10 text-error' => ! $isEnded && $isWarning,
                                'bg-surface-container text-on-surface-variant' => $isEnded,
                            ])>
                                {{ $isEnded ? 'Đã kết thúc' : 'Đang học' }}
                            </span>
                            <span class="flex items-center gap-1 rounded-full border border-outline-variant/20 bg-surface-container-highest px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">
                                <x-user.icon name="graduation-cap" :size="10" />
                                Học viên
                            </span>
                        </div>
                        <span class="text-xs font-medium text-on-surface-variant">Mã lớp: <span class="font-bold text-on-surface">{{ $class['join_code'] }}</span></span>
                    </div>

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
                        <div class="text-right w-24 sm:w-28">
                            <div class="mb-1.5 flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase text-on-surface-variant">Chuyên cần</span>
                                <span class="{{ $attendanceColor }} text-sm font-black">{{ $class['attendance'] }}%</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-container-highest">
                                <div class="{{ $barColor }} h-full rounded-full transition-all duration-1000" style="width: {{ $class['attendance'] }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="mt-auto flex items-center justify-between border-t border-outline-variant/20 pt-3">
                        @if ($isEnded)
                            <span class="flex items-center gap-1.5 text-xs font-bold text-on-surface-variant">
                                <x-user.icon name="check-square" :size="16" />
                                Lớp đã kết thúc
                            </span>
                            <div class="flex gap-1">
                                <a href="{{ route('student.classes.show', $class['id']) }}" class="group/btn inline-block rounded-full p-2 transition-colors hover:bg-surface-container-low" title="Vào thông tin">
                                    <x-user.icon name="log-in" :size="18" class="text-on-surface-variant transition-colors group-hover/btn:text-on-surface" />
                                </a>
                                <a href="{{ route('student.attendance.history', ['classFilter' => $class['id']]) }}" class="group/btn inline-block rounded-full p-2 transition-colors hover:bg-surface-container-low" title="Lịch sử">
                                    <x-user.icon name="history" :size="18" class="text-on-surface-variant transition-colors group-hover/btn:text-on-surface" />
                                </a>
                            </div>
                        @else
                            <button type="button" onclick="alert('Tính năng quét QR đang được phát triển')" class="flex items-center gap-1.5 rounded-full bg-secondary px-5 py-2 text-xs font-bold text-white shadow-sm transition-colors hover:bg-secondary/90 active:scale-[0.98]">
                                <x-user.icon name="qr-code" :size="16" />
                                Điểm danh
                            </button>
                            <div class="flex gap-1">
                                <a href="{{ route('student.classes.show', $class['id']) }}" class="group/btn inline-block rounded-full p-2 transition-colors hover:bg-surface-container-low" title="Vào lớp">
                                    <x-user.icon name="log-in" :size="18" class="text-on-surface-variant transition-colors group-hover/btn:text-on-surface" />
                                </a>
                                <a href="{{ route('student.attendance.history', ['classFilter' => $class['id']]) }}" class="group/btn inline-block rounded-full p-2 transition-colors hover:bg-surface-container-low" title="Lịch sử">
                                    <x-user.icon name="history" :size="18" class="text-on-surface-variant transition-colors group-hover/btn:text-on-surface" />
                                </a>
                                <a href="{{ route('student.leave-requests.create') }}" class="group/btn inline-block rounded-full p-2 transition-colors hover:bg-surface-container-low" title="Xin nghỉ">
                                    <x-user.icon name="file-text" :size="18" class="text-on-surface-variant transition-colors group-hover/btn:text-on-surface" />
                                </a>
                            </div>
                        @endif
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
