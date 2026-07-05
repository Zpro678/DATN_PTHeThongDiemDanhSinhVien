@php
    $statusMeta = [
        'present' => ['label' => 'Có mặt', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60', 'dot' => 'bg-emerald-500'],
        'late' => ['label' => 'Muộn', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200/60', 'dot' => 'bg-amber-500'],
        'excused' => ['label' => 'Có phép', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200/60', 'dot' => 'bg-blue-500'],
        'absent' => ['label' => 'Vắng', 'badge' => 'bg-rose-50 text-rose-700 border-rose-200/60', 'dot' => 'bg-rose-500'],
        'pending' => ['label' => 'Chưa ĐD', 'badge' => 'bg-surface-container border-outline-variant/30 text-on-surface-variant', 'dot' => 'bg-outline'],
    ];
@endphp

<div class="w-full space-y-6 px-6 py-6 pb-24 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">
    
    <!-- Top Row (Stats) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Tổng buổi -->
        <div class="rounded-xl border border-outline-variant/20 bg-white p-4 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Tổng buổi</p>
                <p class="text-4xl font-black text-slate-800 mt-1">{{ $summary['total'] }}</p>
            </div>
            <div class="rounded-lg bg-indigo-50 p-2 text-indigo-500">
                <x-user.icon name="calendar" :size="20" />
            </div>
        </div>
        <!-- Có mặt -->
        <div class="rounded-xl border-l-4 border-l-emerald-500 border-y border-r border-outline-variant/20 bg-white p-4 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Có mặt</p>
                <p class="text-4xl font-black text-emerald-600 mt-1">{{ $summary['present'] + $summary['late'] }}</p>
            </div>
            <div class="rounded-full bg-emerald-50 p-1.5 text-emerald-500 border border-emerald-100">
                <x-user.icon name="check-circle" :size="20" />
            </div>
        </div>
        <!-- Vắng -->
        <div class="rounded-xl border-l-4 border-l-rose-500 border-y border-r border-outline-variant/20 bg-white p-4 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-rose-600">Vắng</p>
                <p class="text-4xl font-black text-rose-600 mt-1">{{ $summary['absent'] }}</p>
            </div>
            <div class="rounded-full bg-rose-50 p-1.5 text-rose-500 border border-rose-100">
                <x-user.icon name="x-circle" :size="20" />
            </div>
        </div>
        <!-- Có phép -->
        <div class="rounded-xl border-l-4 border-l-blue-500 border-y border-r border-outline-variant/20 bg-white p-4 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-blue-600">Có phép</p>
                <p class="text-4xl font-black text-blue-600 mt-1">{{ $summary['excused'] }}</p>
            </div>
            <div class="rounded-full bg-blue-50 p-1.5 text-blue-500 border border-blue-100">
                <x-user.icon name="file-text" :size="20" />
            </div>
        </div>
    </div>

    <!-- Middle Row (Xác thực & Nhắc nhở) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
        
        <!-- Xác thực hệ thống (col-span-2) -->
        <div class="lg:col-span-2">
            <div class="h-full rounded-xl border border-outline-variant/20 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between border-b border-outline-variant/10 pb-4">
                    <h3 class="flex items-center gap-2 text-[15px] font-bold text-slate-800">
                        <x-user.icon name="shield-check" :size="18" class="text-primary" />
                        XÁC THỰC HỆ THỐNG
                    </h3>
                    <span class="text-xs italic text-slate-400">Dữ liệu thời gian thực</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4">
                    <div class="flex items-center justify-between border-b-2 border-emerald-500 pb-2">
                        <span class="text-sm font-medium text-slate-600">QR/GPS hợp lệ</span>
                        <span class="text-lg font-bold text-emerald-600">{{ $records->where('verified', true)->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b-2 border-amber-500 pb-2">
                        <span class="text-sm font-medium text-slate-600">Cần kiểm tra</span>
                        <span class="text-lg font-bold text-amber-600">{{ $records->where('verified', false)->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b-2 border-rose-500 pb-2">
                        <span class="text-sm font-medium text-slate-600">Vắng không phép</span>
                        <span class="text-lg font-bold text-rose-600">{{ $summary['absent'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nhắc nhở (col-span-1) -->
        <div class="lg:col-span-1">
            <div class="h-full flex flex-col justify-center rounded-xl border border-amber-200/60 bg-[#fffdf0] p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="text-amber-500 bg-amber-100 rounded p-1">
                        <x-user.icon name="alert-triangle" :size="18" />
                    </div>
                    <h3 class="text-[17px] font-bold text-amber-900">Nhắc nhở</h3>
                </div>
                <p class="text-[13px] font-medium leading-relaxed text-amber-800/80 mb-5">
                    Gửi minh chứng sớm để được duyệt điểm danh bù hoặc xin phép nghỉ học đúng quy định.
                </p>
                <a href="{{ route('student.leave-requests.create') }}" class="mt-auto flex w-full items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-amber-600">
                    <x-user.icon name="plus-circle" :size="16" />
                    Tạo đơn ngay
                </a>
            </div>
        </div>
        
    </div>

    <!-- Bottom Row (Lịch sử điểm danh) -->
    <div class="rounded-xl border border-outline-variant/20 bg-white shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between p-5 border-b border-outline-variant/10 bg-white">
                    <div class="flex items-center gap-3">
                        <h3 class="text-xl font-bold text-slate-800">Lịch sử điểm danh</h3>
                        <span class="flex h-6 min-w-[24px] items-center justify-center rounded-full bg-slate-100 px-2 text-[13px] font-bold text-slate-600">{{ $records->total() }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="button" wire:click="$refresh" class="flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                            <x-user.icon name="refresh-cw" :size="16" />
                            Làm mới
                        </button>
                        <x-user.export-button action="exportExcel" label="Xuất báo cáo" :can="$canExportExcel" />
                    </div>
                </div>

                <!-- Filters -->
                <div class="p-5 bg-white border-b border-outline-variant/10">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="relative">
                            <x-user.icon name="search" :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm tên môn học, mã..." class="w-full rounded-xl bg-[#f4f5f9] border-none py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 outline-none transition focus:ring-2 focus:ring-[#0a46b5]/30">
                        </div>
                        <x-custom-select :value="$classFilter" wire:key="class-filter-select" wire:model.live="classFilter" placeholder="" :options="collect($classes)
                            ->map(fn ($class) => ['value' => (string) $class->id, 'label' => ($class->class_code ?? $class->join_key) . ' - ' . $class->name])
                            ->values()
                            ->prepend(['value' => 'all', 'label' => 'Tất cả các lớp'])
                            ->all()" />
                        <x-custom-select :value="$statusFilter" wire:key="status-filter-select" wire:model.live="statusFilter" placeholder="" :options="collect($statusMeta)
                            ->map(fn ($meta, $status) => ['value' => (string) $status, 'label' => $meta['label']])
                            ->values()
                            ->prepend(['value' => 'all', 'label' => 'Mọi trạng thái'])
                            ->all()" />
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto bg-white">
                    <table class="w-full min-w-[800px] border-collapse text-left">
                        <thead class="bg-white text-sm font-bold uppercase tracking-wider text-slate-500 border-b border-outline-variant/10">
                            <tr>
                                <th class="whitespace-nowrap px-6 py-4">Ngày</th>
                                <th class="whitespace-nowrap px-6 py-4">Môn học & Buổi</th>
                                <th class="whitespace-nowrap px-6 py-4">Lớp</th>
                                <th class="whitespace-nowrap px-6 py-4">Trạng thái</th>
                                <th class="whitespace-nowrap px-6 py-4">Hình thức</th>
                                <th class="whitespace-nowrap px-6 py-4">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10 text-sm">
                            @forelse ($records as $record)
                                @php($meta = $statusMeta[$record['status']] ?? $statusMeta['pending'])
                                <tr class="transition-all duration-200 hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-700">
                                        {{ $record['date']?->format('d/m/Y') ?? '--/--/----' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ $record['class_name'] }}</p>
                                        <p class="mt-1 flex items-center gap-2 text-[13px] font-medium text-slate-500">
                                            <span class="rounded bg-slate-100 px-1.5 py-0.5">{{ $record['session'] }}</span>
                                        </p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[12px] font-bold tracking-wider text-slate-600">
                                            {{ $record['class_code'] }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-[12px] font-bold uppercase tracking-wider {{ $meta['badge'] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $meta['dot'] }}"></span>
                                            {{ $meta['label'] }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-[12px] font-bold text-slate-600">
                                            <x-user.icon :name="$record['method'] === 'QR + GPS' ? 'qr-code' : 'clipboard-check'" :size="14" />
                                            {{ $record['method'] }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-500">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-[13px] font-semibold">
                                                <x-user.icon name="clock" :size="12" class="inline mr-1" />
                                                {{ $record['check_in_time'] ? $record['check_in_time']->format('H:i') : 'Chưa check-in' }}
                                            </span>
                                            @if ($record['distance'])
                                                <span class="text-[12px] font-bold text-primary/80">
                                                    <x-user.icon name="map-pin" :size="12" class="inline mr-1 text-primary/60" />
                                                    {{ $record['distance'] }}m
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-400">
                                            <div class="mb-4 rounded-2xl bg-indigo-50/50 p-4">
                                                <div class="flex items-center justify-center h-16 w-16 bg-slate-100 rounded-2xl text-slate-400 border border-slate-200">
                                                    <x-user.icon name="file-text" :size="32" />
                                                </div>
                                            </div>
                                            <h4 class="text-[17px] font-bold text-slate-800">Chưa có lịch sử điểm danh</h4>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if ($records->hasPages())
                    <div class="border-t border-outline-variant/10 p-4">
                        {{ $records->links() }}
                    </div>
                @endif

</div>
