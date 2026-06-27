@php
    $statusMeta = [
        'present' => ['label' => 'Có mặt', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60', 'dot' => 'bg-emerald-500'],
        'late' => ['label' => 'Muộn', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200/60', 'dot' => 'bg-amber-500'],
        'excused' => ['label' => 'Có phép', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200/60', 'dot' => 'bg-blue-500'],
        'absent' => ['label' => 'Vắng', 'badge' => 'bg-rose-50 text-rose-700 border-rose-200/60', 'dot' => 'bg-rose-500'],
        'pending' => ['label' => 'Chưa ĐD', 'badge' => 'bg-surface-container border-outline-variant/30 text-on-surface-variant', 'dot' => 'bg-outline'],
    ];
@endphp

<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Header & Stats Card (Combined) -->
    <section class="overflow-hidden rounded-[2.5rem] border border-outline-variant/10 bg-white shadow-sm">
        <!-- Top Header -->
        <div class="flex flex-col justify-between gap-3 p-5 sm:px-6 sm:py-5 md:flex-row md:items-center">
            <div>
                <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-primary">
                    <x-user.icon name="history" :size="14" />
                    Lịch sử
                </div>
                <h1 class="text-2xl font-extrabold uppercase tracking-tight text-slate-900">Nhật ký điểm danh</h1>
            </div>

            <div class="flex gap-2 sm:gap-3 overflow-x-auto pb-2 md:pb-0 hide-scrollbar">
                <div class="flex flex-col items-center justify-center rounded-2xl border border-outline-variant/20 bg-surface-container-lowest px-3 py-2 min-w-[90px] shrink-0">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Tổng tiết</span>
                    <span class="text-2xl font-black text-primary">{{ $summary['total'] }}</span>
                </div>
                <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 min-w-[90px] shrink-0">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Chưa ĐD</span>
                    <span class="text-2xl font-black text-slate-700">{{ $summary['pending'] }}</span>
                </div>
                <div class="flex flex-col items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-2 min-w-[90px] shrink-0">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Có mặt</span>
                    <span class="text-2xl font-black text-emerald-600">{{ $summary['present'] + $summary['late'] + $summary['excused'] }}</span>
                </div>
                <div class="flex flex-col items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 px-3 py-2 min-w-[90px] shrink-0">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Vắng</span>
                    <span class="text-2xl font-black text-rose-600">{{ $summary['absent'] }}</span>
                </div>
            </div>
        </div>

        <!-- Bottom Stats & Reminders -->
        <div class="grid grid-cols-1 lg:grid-cols-3 items-stretch border-t border-outline-variant/10">
            <!-- Xác thực hệ thống -->
            <div class="flex flex-col p-5 sm:p-6 lg:col-span-2">
                <h3 class="flex items-center gap-2 text-base font-bold text-on-surface">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <x-user.icon name="shield-check" :size="18" />
                    </span>
                    Xác thực hệ thống
                </h3>
                <div class="mt-4 flex flex-1 flex-col justify-center space-y-3">
                    @foreach ([
                        ['label' => 'QR/GPS hợp lệ', 'value' => $records->where('verified', true)->count(), 'color' => 'text-emerald-700 bg-emerald-50 border-emerald-100'],
                        ['label' => 'Cần kiểm tra', 'value' => $records->where('verified', false)->count(), 'color' => 'text-amber-700 bg-amber-50 border-amber-100'],
                        ['label' => 'Vắng không phép', 'value' => $summary['absent'], 'color' => 'text-rose-700 bg-rose-50 border-rose-100'],
                    ] as $item)
                        <div class="flex items-center justify-between rounded-2xl border border-outline-variant/20 bg-surface-container-lowest px-4 py-2.5 transition-colors hover:bg-surface-container-low">
                            <span class="text-[15px] font-bold text-on-surface-variant">{{ $item['label'] }}</span>
                            <span class="rounded-xl border px-3 py-1 text-[15px] font-black {{ $item['color'] }}">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Nhắc nhở (Nested Small Card) -->
            <div class="flex flex-col border-t border-outline-variant/10 lg:border-l lg:border-t-0 p-5 sm:p-6 bg-surface-container-lowest/30">
                <div class="flex h-full flex-col justify-center rounded-[2rem] border border-amber-200/60 bg-gradient-to-br from-amber-50 via-amber-50/50 to-orange-50/80 p-6 sm:p-7 relative overflow-hidden shadow-sm">
                    <div class="absolute -right-4 -top-4 text-amber-500 opacity-[0.08] rotate-12 pointer-events-none transition-transform duration-700 hover:rotate-45 hover:scale-110">
                        <x-user.icon name="bell" :size="130" />
                    </div>
                    <div class="relative z-10 flex flex-col h-full justify-center">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600 shadow-sm shrink-0">
                                <x-user.icon name="alert-triangle" :size="18" />
                            </div>
                            <h3 class="text-[17px] font-extrabold text-amber-900 tracking-tight">
                                Nhắc nhở
                            </h3>
                        </div>
                        <p class="mt-4 text-[14px] sm:text-[15px] font-medium leading-relaxed text-amber-800/90">
                            Gửi minh chứng sớm để được duyệt điểm danh bù.
                        </p>
                        <div class="mt-5">
                            <a href="{{ route('student.leave-requests.create') }}" class="group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-2.5 text-[14px] font-bold text-white shadow-md shadow-amber-500/20 transition-all hover:shadow-lg hover:shadow-amber-500/30 active:scale-95 w-fit">
                                <x-user.icon name="plus" :size="18" />
                                Tạo đơn
                            </a>
                        </div>
                    </div>
                </div>
        </div>
    </section>

    <!-- Main Content: Table + Filters in one Card -->
    <section class="overflow-hidden rounded-[2rem] border border-outline-variant/10 bg-white shadow-sm w-full">
        <!-- Table Header -->
        <div class="flex items-center justify-between border-b border-outline-variant/10 p-5 sm:px-8 sm:py-5 bg-surface-container-lowest/30">
            <div>
                <h2 class="text-2xl font-bold text-on-surface">Bảng lịch sử điểm danh ({{ $records->total() }})</h2>
            </div>
            <a href="{{ route('student.attendance.stats') }}" class="hidden items-center gap-2 rounded-xl bg-primary/10 px-5 py-2.5 text-sm font-bold text-primary transition hover:bg-primary/20 sm:inline-flex">
                <x-user.icon name="bar-chart" :size="18" />
                Xem thống kê
            </a>
        </div>

        <!-- Filters (Below Header) -->
        <div class="p-4 sm:px-8 sm:py-5 border-b border-outline-variant/10 bg-surface-container-lowest/10">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 items-center">
                <div class="lg:col-span-4">
                    <select wire:model.live="classFilter" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-3 py-2.5 text-[15px] font-medium text-on-surface-variant shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="all">Tất cả lớp học</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->code }} - {{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <select wire:model.live="statusFilter" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-3 py-2.5 text-[15px] font-medium text-on-surface-variant shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="all">Tất cả trạng thái</option>
                        @foreach ($statusMeta as $status => $meta)
                            <option value="{{ $status }}">{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-4">
                    <div class="relative">
                        <x-user.icon name="search" :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-outline" />
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm môn học, mã lớp, ngày..." class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest py-2.5 pl-10 pr-3 text-[15px] font-medium text-on-surface-variant shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <button type="button" wire:click="clearFilters" class="w-full rounded-xl border border-outline-variant/30 bg-white px-3 py-2.5 text-[15px] font-medium text-on-surface-variant transition hover:bg-surface-container-low hover:text-on-surface shadow-sm">Bỏ lọc</button>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] border-collapse text-left">
                    <thead class="bg-surface-container-lowest text-base font-bold uppercase tracking-wider text-on-surface">
                        <tr>
                            <th class="whitespace-nowrap pl-[44px] pr-8 py-5">Ngày</th>
                            <th class="whitespace-nowrap px-8 py-5">Môn học & Buổi</th>
                            <th class="whitespace-nowrap px-8 py-5">Lớp</th>
                            <th class="whitespace-nowrap px-8 py-5">Trạng thái</th>
                            <th class="whitespace-nowrap px-8 py-5">Hình thức</th>
                            <th class="whitespace-nowrap px-8 py-5">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10 text-base">
                        @forelse ($records as $record)
                            @php($meta = $statusMeta[$record['status']] ?? $statusMeta['pending'])
                            <tr class="transition-all duration-200 hover:bg-surface-container-lowest/80 hover:shadow-sm group">
                                <td class="whitespace-nowrap pl-[44px] pr-8 py-5 font-medium text-[15px] text-on-surface">
                                    {{ $record['date']?->format('d/m/Y') ?? '--/--/----' }}
                                </td>
                                <td class="whitespace-nowrap px-8 py-5">
                                    <p class="text-base font-bold text-on-surface group-hover:text-primary transition-colors">{{ $record['class_name'] }}</p>
                                    <p class="mt-1.5 flex items-center gap-2 text-sm font-medium text-on-surface-variant">
                                        <span class="rounded-md bg-surface-container px-2.5 py-0.5">{{ $record['session'] }}</span>
                                        <span>{{ $record['semester'] ?? 'Chưa cập nhật' }}</span>
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-8 py-5">
                                    <span class="rounded-xl border border-outline-variant/20 bg-surface-container-lowest px-3 py-1.5 text-[13px] font-bold tracking-wider text-on-surface-variant">
                                        {{ $record['class_code'] }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-8 py-5">
                                    <span class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-[13px] font-bold uppercase tracking-wider {{ $meta['badge'] }}">
                                        <span class="h-2 w-2 rounded-full {{ $meta['dot'] }}"></span>
                                        {{ $meta['label'] }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-8 py-5">
                                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-surface-container px-3 py-1.5 text-[13px] font-bold text-on-surface-variant">
                                        <x-user.icon :name="$record['method'] === 'QR + GPS' ? 'qr-code' : 'clipboard-check'" :size="16" />
                                        {{ $record['method'] }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-8 py-5 text-on-surface-variant">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="text-[14px] font-semibold">
                                            <x-user.icon name="clock" :size="14" class="inline mr-1 text-outline" />
                                            {{ $record['check_in_time'] ? $record['check_in_time']->format('H:i') : 'Chưa check-in' }}
                                        </span>
                                        @if ($record['distance'])
                                            <span class="text-xs font-bold text-primary/80">
                                                <x-user.icon name="map-pin" :size="12" class="inline mr-1 text-primary/60" />
                                                Khoảng cách: {{ $record['distance'] }}m
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-on-surface-variant">
                                        <div class="mb-5 rounded-full bg-surface-container p-5 text-outline">
                                            <x-user.icon name="inbox" :size="40" />
                                        </div>
                                        <p class="text-lg font-bold text-on-surface">Không tìm thấy dữ liệu</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($records->hasPages())
                <div class="border-t border-outline-variant/10 p-4 sm:px-8 sm:py-5">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </section>
</div>
