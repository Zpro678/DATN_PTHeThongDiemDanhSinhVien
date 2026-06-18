@php
    $statuses = ['Tất cả', 'Đang học', 'Đã kết thúc', 'Cảnh báo chuyên cần'];
    $classes = [
        [
            'title' => 'Lập trình Web Frontend - Nhóm 1',
            'code' => 'WEB301',
            'join_code' => 'QR-123A',
            'teacher' => 'TS. Nguyễn Văn A',
            'schedule' => 'Thứ 3, 07:00 - 11:30 (Phòng A1.203)',
            'attendance' => 90,
            'present' => '9/10',
            'absent' => 1,
            'late' => 0,
            'warning' => false,
            'ended' => false,
        ],
        [
            'title' => 'Cơ sở dữ liệu - Nhóm 2',
            'code' => 'DB202',
            'join_code' => 'QR-124B',
            'teacher' => 'ThS. Lê Hữu B',
            'schedule' => 'Thứ 5, 13:00 - 15:30 (Phòng B2.101)',
            'attendance' => 70,
            'present' => '7/10',
            'absent' => 3,
            'late' => 1,
            'warning' => true,
            'ended' => false,
        ],
        [
            'title' => 'Thiết kế UI/UX',
            'code' => 'UI401',
            'join_code' => 'UI-102C',
            'teacher' => 'ThS. Trần Phương C',
            'schedule' => 'HK1 2025-2026',
            'attendance' => 100,
            'present' => '15/15',
            'absent' => 0,
            'late' => 0,
            'warning' => false,
            'ended' => true,
        ],
    ];
@endphp

<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-end">
        <div>
            <h3 class="mb-2 flex items-center gap-2 text-2xl font-bold text-on-surface">
                <x-user.icon name="graduation-cap" class="text-secondary" />
                Lớp tôi tham gia
            </h3>
            <p class="text-body-md text-on-surface-variant">Danh sách các lớp bạn đang học và theo dõi điểm danh</p>
        </div>
        <button type="button" class="flex w-full items-center justify-center gap-2 rounded-full bg-secondary px-6 py-3 font-bold text-white transition-all hover:shadow-lg active:scale-95 md:w-auto">
            <x-user.icon name="plus" />
            Tham gia lớp bằng mã
        </button>
    </section>

    <section class="flex flex-col gap-4 lg:flex-row">
        <label class="relative flex-1">
            <x-user.icon name="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-outline" />
            <input
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

    <section class="grid auto-rows-fr grid-cols-1 items-stretch gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($classes as $class)
            @php
                $attendanceColor = $class['warning'] ? 'text-error' : ($class['ended'] ? 'text-on-surface-variant' : 'text-secondary');
                $barColor = $class['warning'] ? 'bg-error' : ($class['ended'] ? 'bg-outline-variant' : 'bg-secondary');
            @endphp

            <article @class([
                'group flex h-full min-h-[580px] flex-col overflow-hidden rounded-3xl border shadow-sm transition-all hover:shadow-xl',
                'border-outline-variant/20 bg-white' => ! $class['warning'],
                'border-error/30 bg-alert-container/30' => $class['warning'],
                'opacity-80 hover:opacity-100' => $class['ended'],
            ])>
                <div @class([
                    'relative min-h-[250px] overflow-hidden border-b p-5 sm:p-6',
                    'border-outline-variant/10 bg-surface-container-lowest' => ! $class['warning'],
                    'border-error/10 bg-white/50' => $class['warning'],
                    'bg-white' => $class['ended'],
                ])>
                    <div @class([
                        'pointer-events-none absolute right-0 top-0 h-32 w-32 rounded-bl-full',
                        'bg-secondary/5' => ! $class['warning'] && ! $class['ended'],
                        'bg-error/5' => $class['warning'],
                        'bg-outline-variant/5' => $class['ended'],
                    ])></div>

                    <div class="relative z-10 mb-4 flex items-start justify-between gap-3">
                        <div class="flex flex-wrap gap-2">
                            <span @class([
                                'rounded-lg border px-2.5 py-1 text-xs font-bold uppercase tracking-wider',
                                'border-secondary/20 bg-secondary/10 text-secondary' => ! $class['ended'],
                                'border-outline-variant/20 bg-surface-container-high text-on-surface-variant' => $class['ended'],
                            ])>
                                {{ $class['ended'] ? 'Đã kết thúc' : 'Đang học' }}
                            </span>
                            <span class="flex items-center gap-1 rounded-lg border border-outline-variant/20 bg-surface-container-highest px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                <x-user.icon name="graduation-cap" :size="12" />
                                Học viên
                            </span>
                        </div>
                        <span class="shrink-0 rounded bg-surface-container-high px-2 py-0.5 text-xs font-bold text-on-surface">{{ $class['code'] }}</span>
                    </div>

                    <div class="relative z-10 min-w-0">
                        <h4 class="mb-2 truncate text-xl font-bold text-on-surface transition-colors group-hover:text-secondary sm:text-2xl" title="{{ $class['title'] }}">{{ $class['title'] }}</h4>
                        <div class="mb-2 space-y-1.5">
                            <p class="flex min-w-0 items-center gap-2 text-sm font-medium text-on-surface">
                                <x-user.icon name="user" :size="16" class="shrink-0 text-on-surface-variant" />
                                <span class="truncate" title="{{ $class['teacher'] }}">{{ $class['teacher'] }}</span>
                            </p>
                            <p class="flex min-w-0 items-center gap-2 text-sm font-medium text-on-surface">
                                <x-user.icon name="clock" :size="16" class="shrink-0 text-on-surface-variant" />
                                <span class="truncate" title="{{ $class['schedule'] }}">{{ $class['schedule'] }}</span>
                            </p>
                        </div>
                        <p class="text-sm font-medium text-on-surface-variant">Mã lớp: <span class="font-mono text-base font-bold text-on-surface">{{ $class['join_code'] }}</span></p>
                    </div>
                </div>

                <div @class([
                    'flex min-h-0 flex-1 flex-col space-y-5 p-5 sm:p-6',
                    'bg-white' => ! $class['warning'] && ! $class['ended'],
                    'bg-white/40' => $class['warning'],
                    'bg-white/50 grayscale transition-all duration-300 group-hover:grayscale-0' => $class['ended'],
                ])>
                    @if ($class['warning'])
                        <div class="flex items-start gap-3 rounded-xl border border-error/20 bg-error/10 p-3">
                            <x-user.icon name="alert-triangle" :size="18" class="mt-0.5 text-error" />
                            <div>
                                <h5 class="text-sm font-bold text-error">Cảnh báo chuyên cần</h5>
                                <p class="mt-0.5 text-xs text-error/80">Tỷ lệ chuyên cần của bạn dưới 80%. Nếu vắng thêm 1 buổi sẽ bị cấm thi.</p>
                            </div>
                        </div>
                    @endif

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-bold text-on-surface-variant">Chuyên cần cá nhân</span>
                            <span class="{{ $attendanceColor }} text-lg font-bold">{{ $class['attendance'] }}%</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-surface-container-highest">
                            <div class="{{ $barColor }} h-full rounded-full transition-all duration-1000" style="width: {{ $class['attendance'] }}%"></div>
                        </div>
                    </div>

                    <div class="flex justify-between gap-2 overflow-x-auto pb-1 hide-scrollbar">
                        <div class="min-h-[82px] min-w-[90px] flex-1 shrink-0 rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-3 text-center">
                            <p class="mb-1 text-[10px] font-bold uppercase text-on-surface-variant sm:text-xs">Có mặt</p>
                            <span class="text-lg font-bold text-secondary">{{ $class['present'] }}</span>
                        </div>
                        <div @class([
                            'min-h-[82px] min-w-[90px] flex-1 shrink-0 rounded-2xl border p-3 text-center',
                            'border-error/20 bg-error/5' => $class['warning'],
                            'border-outline-variant/20 bg-surface-container-lowest' => ! $class['warning'],
                        ])>
                            <p @class([
                                'mb-1 text-[10px] font-bold uppercase sm:text-xs',
                                'text-error' => $class['warning'],
                                'text-on-surface-variant' => ! $class['warning'],
                            ])>Vắng</p>
                            <span @class([
                                'text-lg font-bold',
                                'text-error' => $class['warning'],
                                'text-on-surface' => ! $class['warning'],
                            ])>{{ $class['absent'] }}</span>
                        </div>
                        <div class="min-h-[82px] min-w-[90px] flex-1 shrink-0 rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-3 text-center">
                            <p class="mb-1 text-[10px] font-bold uppercase text-on-surface-variant sm:text-xs">Đi trễ</p>
                            <span class="text-lg font-bold text-[#F59E0B]">{{ $class['late'] }}</span>
                        </div>
                    </div>

                    <div class="mt-auto space-y-3 border-t border-outline-variant/20 pt-5">
                        @if ($class['ended'])
                            <button type="button" class="flex min-h-[52px] w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-surface-container-high py-3.5 font-bold text-on-surface-variant">
                                <x-user.icon name="check-square" />
                                Lớp đã kết thúc
                            </button>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" class="flex min-h-[68px] flex-col items-center justify-center rounded-xl border border-outline-variant/30 bg-white p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-on-surface">
                                    <x-user.icon name="log-in" :size="18" class="mb-1.5" />
                                    <span class="text-center text-[10px] font-bold leading-tight">Vào thông tin</span>
                                </button>
                                <button type="button" class="flex min-h-[68px] flex-col items-center justify-center rounded-xl border border-outline-variant/30 bg-white p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-on-surface">
                                    <x-user.icon name="history" :size="18" class="mb-1.5" />
                                    <span class="text-center text-[10px] font-bold leading-tight">Lịch sử</span>
                                </button>
                            </div>
                        @else
                            <button type="button" class="flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-secondary py-3.5 font-bold text-white shadow-sm transition-colors hover:bg-secondary/90 active:scale-[0.98]">
                                <x-user.icon name="qr-code" />
                                Quét QR Điểm danh
                            </button>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ([
                                    ['label' => 'Vào lớp', 'icon' => 'log-in'],
                                    ['label' => 'Lịch sử', 'icon' => 'history'],
                                    ['label' => 'Xin nghỉ', 'icon' => 'file-text'],
                                ] as $action)
                                    <button type="button" class="flex min-h-[68px] cursor-pointer flex-col items-center justify-center rounded-xl border border-outline-variant/30 p-2 text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-on-surface">
                                        <x-user.icon :name="$action['icon']" :size="18" class="mb-1.5" />
                                        <span class="text-center text-[10px] font-bold leading-tight">{{ $action['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </section>
</div>
