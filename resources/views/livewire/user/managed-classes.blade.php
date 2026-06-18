@php
    $statuses = ['Tất cả', 'Đang hoạt động', 'Đã kết thúc'];
    $classes = [
        [
            'title' => 'Lập trình Web Frontend - Nhóm 1',
            'code' => 'WEB301',
            'semester' => 'HK2 2025-2026',
            'join_code' => 'QR-123A',
            'students' => 45,
            'sessions' => '8/15',
            'attendance' => 86,
            'state' => 'Đang hoạt động',
            'tone' => 'primary',
            'ended' => false,
        ],
        [
            'title' => 'Cơ sở dữ liệu - Nhóm 2',
            'code' => 'DB202',
            'semester' => 'HK2 2025-2026',
            'join_code' => 'QR-124B',
            'students' => 38,
            'sessions' => '4/15',
            'attendance' => 94,
            'state' => 'Đang hoạt động',
            'tone' => 'tertiary',
            'ended' => false,
        ],
        [
            'title' => 'Thiết kế UI/UX',
            'code' => 'UI401',
            'semester' => 'HK1 2025-2026',
            'join_code' => 'UI-102C',
            'students' => 42,
            'sessions' => '15/15',
            'attendance' => 91,
            'state' => 'Đã kết thúc',
            'tone' => 'outline-variant',
            'ended' => true,
        ],
    ];
@endphp

<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-end">
        <div>
            <h3 class="mb-2 flex items-center gap-2 text-2xl font-bold text-on-surface">
                <x-user.icon name="book-open" class="text-primary" />
                Lớp tôi quản lý
            </h3>
            <p class="text-body-md text-on-surface-variant">Danh sách các lớp bạn đang làm chủ lớp</p>
        </div>
        <a href="{{ route('create-class') }}" class="flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 font-bold text-white transition-all hover:shadow-lg active:scale-95 md:w-auto">
            <x-user.icon name="plus" />
            Tạo lớp mới
        </a>
    </section>

    <section class="flex flex-col gap-4 lg:flex-row">
        <label class="relative flex-1">
            <x-user.icon name="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-outline" />
            <input
                type="text"
                placeholder="Tìm kiếm theo mã lớp, tên lớp, môn học..."
                class="w-full rounded-2xl border border-outline-variant/30 bg-white py-3.5 pl-12 pr-4 text-on-surface shadow-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
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
                            'bg-primary-container text-on-primary-container' => $statusFilter === $status,
                            'text-on-surface-variant hover:bg-surface-container-lowest hover:text-on-surface' => $statusFilter !== $status,
                        ])
                    >
                        {{ $status }}
                    </button>
                @endforeach
            </div>

            <label class="relative flex shrink-0 items-center">
                <select
                    wire:model="semesterFilter"
                    class="min-w-[180px] cursor-pointer appearance-none rounded-2xl border border-outline-variant/30 bg-white py-3.5 pl-10 pr-10 text-sm font-bold text-on-surface shadow-sm outline-none focus:ring-2 focus:ring-primary/20"
                >
                    <option>Tất cả học kỳ</option>
                    <option>HK2 2025-2026</option>
                    <option>HK1 2025-2026</option>
                </select>
                <x-user.icon name="filter" :size="18" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant" />
                <x-user.icon name="chevron-down" :size="18" class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant" />
            </label>
        </div>
    </section>

    <section class="grid auto-rows-fr grid-cols-1 items-stretch gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($classes as $class)
            @php
                $isPrimary = $class['tone'] === 'primary';
                $isTertiary = $class['tone'] === 'tertiary';
                $barClass = $isTertiary ? 'bg-tertiary' : ($isPrimary ? 'bg-primary' : 'bg-outline-variant');
                $textClass = $isTertiary ? 'text-tertiary' : ($isPrimary ? 'text-primary' : 'text-on-surface-variant');
            @endphp
            <article @class([
                'group flex h-full min-h-[520px] flex-col overflow-hidden rounded-3xl border border-outline-variant/20 bg-white shadow-sm transition-all hover:shadow-xl',
                'opacity-80 hover:opacity-100' => $class['ended'],
            ])>
                <div class="relative min-h-[210px] overflow-hidden border-b border-outline-variant/10 bg-surface-container-lowest p-6">
                    <div @class([
                        'pointer-events-none absolute right-0 top-0 h-32 w-32 rounded-bl-full',
                        'bg-primary/5' => $isPrimary,
                        'bg-tertiary/5' => $isTertiary,
                        'bg-outline-variant/5' => ! $isPrimary && ! $isTertiary,
                    ])></div>

                    <div class="relative z-10 mb-4 flex items-start justify-between">
                        <div class="flex flex-wrap gap-2">
                            <span @class([
                                'rounded-lg px-2.5 py-1 text-xs font-bold uppercase tracking-wider border',
                                'border-primary/20 bg-primary/10 text-primary' => ! $class['ended'],
                                'border-outline-variant/20 bg-surface-container-high text-on-surface-variant' => $class['ended'],
                            ])>
                                {{ $class['state'] }}
                            </span>
                            <span class="flex items-center gap-1 rounded-lg border border-[#F59E0B]/20 bg-[#F59E0B]/10 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-[#F59E0B]">
                                <x-user.icon name="shield" :size="12" />
                                Chủ lớp
                            </span>
                        </div>
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" x-on:click="open = ! open" class="-m-2 rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface">
                                <x-user.icon name="more-vertical" />
                            </button>
                            <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute right-0 top-full z-50 mt-2 w-48 rounded-xl border border-outline-variant/20 bg-white py-2 shadow-lg">
                                <button type="button" class="w-full px-4 py-2 text-left text-sm font-medium hover:bg-surface-container">Xem chi tiết</button>
                                @if (! $class['ended'])
                                    <button type="button" class="w-full px-4 py-2 text-left text-sm font-medium hover:bg-surface-container">Chỉnh sửa lớp</button>
                                    <button type="button" class="w-full px-4 py-2 text-left text-sm font-medium hover:bg-surface-container">Sao chép mã lớp</button>
                                @endif
                                <button type="button" class="w-full px-4 py-2 text-left text-sm font-medium hover:bg-surface-container">Xuất báo cáo</button>
                            </div>
                        </div>
                    </div>

                    <div class="relative z-10 min-w-0">
                        <h4 class="mb-2 truncate text-2xl font-bold text-on-surface transition-colors group-hover:text-primary" title="{{ $class['title'] }}">{{ $class['title'] }}</h4>
                        <div class="mb-1 flex min-w-0 items-center gap-3 text-sm font-medium text-on-surface-variant">
                            <span class="shrink-0 rounded bg-surface-container-high px-2 py-0.5 text-on-surface">{{ $class['code'] }}</span>
                            <span class="h-1 w-1 rounded-full bg-outline-variant/50"></span>
                            <span class="truncate">{{ $class['semester'] }}</span>
                        </div>
                        <p class="text-sm font-medium text-on-surface-variant">Mã lớp: <span class="font-mono text-base font-bold text-on-surface">{{ $class['join_code'] }}</span></p>
                    </div>
                </div>

                <div @class([
                    'flex min-h-0 flex-1 flex-col space-y-5 p-6',
                    'grayscale transition-all duration-300 group-hover:grayscale-0' => $class['ended'],
                ])>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="min-h-[94px] rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-4">
                            <p class="mb-1 text-xs font-bold uppercase text-on-surface-variant">Sinh viên</p>
                            <div class="flex items-center gap-2 text-on-surface">
                                <x-user.icon name="users" :size="18" class="text-primary" />
                                <span class="text-xl font-bold">{{ $class['students'] }}</span>
                            </div>
                        </div>
                        <div class="min-h-[94px] rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-4">
                            <p class="mb-1 text-xs font-bold uppercase text-on-surface-variant">Đã điểm danh</p>
                            <div class="flex items-center gap-2 text-on-surface">
                                <x-user.icon name="check-square" :size="18" class="text-tertiary" />
                                <span class="text-lg font-bold leading-none">{{ $class['sessions'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-bold text-on-surface-variant">Chuyên cần trung bình</span>
                            <span class="{{ $textClass }} text-lg font-bold">{{ $class['attendance'] }}%</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-surface-container-highest">
                            <div class="{{ $barClass }} h-full rounded-full transition-all duration-1000" style="width: {{ $class['attendance'] }}%"></div>
                        </div>
                    </div>

                    <div class="mt-auto border-t border-outline-variant/20 pt-5">
                        <div class="grid grid-cols-4 gap-2 sm:gap-3">
                            @foreach ([
                                ['label' => 'Điểm danh QR', 'icon' => 'qr-code'],
                                ['label' => 'Thủ công', 'icon' => 'check-square'],
                                ['label' => 'Quản lý SV', 'icon' => 'users'],
                                ['label' => 'Thống kê', 'icon' => 'bar-chart'],
                            ] as $action)
                                <a href="{{ match ($action['label']) { 'Điểm danh QR' => route('lecturer.attendance.qr.create'), 'Thủ công' => route('lecturer.attendance.manual.create'), 'Quản lý SV' => route('lecturer.students.index'), default => '#' } }}" @class([
                                    'flex h-full min-h-[74px] flex-col items-center justify-start rounded-xl p-2 text-on-surface-variant transition-colors hover:bg-primary-container hover:text-on-primary-container sm:p-3',
                                    'cursor-not-allowed opacity-50 hover:bg-surface-container-high hover:text-on-surface-variant' => $class['ended'] && in_array($action['icon'], ['qr-code', 'check-square'], true),
                                ])>
                                    <x-user.icon :name="$action['icon']" class="mb-2 transition-transform group-hover:scale-110" />
                                    <span class="text-center text-[10px] font-bold leading-[1.15] sm:text-xs">{{ $action['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </section>
</div>
