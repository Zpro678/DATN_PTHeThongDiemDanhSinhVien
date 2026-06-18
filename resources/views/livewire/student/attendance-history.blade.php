@php
    $statusMeta = [
        'present' => ['label' => 'Có mặt', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'dot' => 'bg-emerald-500'],
        'late' => ['label' => 'Muộn', 'badge' => 'bg-amber-50 text-amber-700 border-amber-100', 'dot' => 'bg-amber-500'],
        'excused' => ['label' => 'Có phép', 'badge' => 'bg-blue-50 text-blue-700 border-blue-100', 'dot' => 'bg-blue-500'],
        'absent' => ['label' => 'Vắng', 'badge' => 'bg-rose-50 text-rose-700 border-rose-100', 'dot' => 'bg-rose-500'],
        'pending' => ['label' => 'Chưa ĐD', 'badge' => 'bg-slate-100 text-slate-600 border-slate-200', 'dot' => 'bg-slate-400'],
    ];
@endphp

<div class="min-h-full bg-slate-50/60 px-4 py-6 pb-24 sm:px-6 xl:px-8">
    <div class="w-full max-w-none space-y-6">
        <section class="overflow-hidden rounded-[2rem] border border-blue-100 bg-white shadow-sm">
            <div class="grid gap-6 bg-gradient-to-br from-blue-600 via-indigo-600 to-slate-900 p-6 text-white lg:grid-cols-[1fr_auto] lg:items-end lg:p-8">
                <div>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] text-blue-100">
                        <x-user.icon name="history" :size="16" />
                        Không gian học viên
                    </div>
                    <h1 class="text-3xl font-black tracking-tight sm:text-4xl">Lịch sử điểm danh</h1>
                    <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-blue-100">Theo dõi nhật ký điểm danh, hình thức xác thực và trạng thái từng buổi học giống giao diện học viên của `develop_v1`.</p>
                </div>

                <div class="grid grid-cols-3 gap-3 rounded-3xl border border-white/15 bg-white/10 p-3 backdrop-blur sm:min-w-[420px]">
                    <div class="rounded-2xl bg-white/10 p-4 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-blue-100">Đã ghi nhận</p>
                        <p class="mt-2 text-2xl font-black">{{ $summary['total'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-blue-100">Có mặt</p>
                        <p class="mt-2 text-2xl font-black">{{ $summary['present'] + $summary['late'] + $summary['excused'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-blue-100">Vắng</p>
                        <p class="mt-2 text-2xl font-black">{{ $summary['absent'] }}</p>
                    </div>
                </div>
            </div>

            @if ($isDemo)
                <div class="border-t border-amber-200 bg-amber-50 px-6 py-3 text-sm font-semibold text-amber-800">
                    Đang hiển thị dữ liệu mẫu để bạn có thể test giao diện khi tài khoản chưa được gắn lớp học.
                </div>
            @endif
        </section>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2 border-b border-slate-100 pb-4">
                <span class="rounded-xl bg-blue-50 p-2 text-blue-600">
                    <x-user.icon name="filter" :size="18" />
                </span>
                <div>
                    <h2 class="text-base font-black text-slate-950">Bộ lọc tìm kiếm chuyên cần</h2>
                    <p class="text-xs font-medium text-slate-500">Lọc theo lớp, trạng thái hoặc từ khóa môn học.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <label class="space-y-1.5 lg:col-span-4">
                    <span class="block text-[11px] font-black uppercase tracking-wider text-slate-500">Theo lớp học</span>
                    <select wire:model.live="classFilter" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10">
                        <option value="all">Tất cả lớp học</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->code }} - {{ $class->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="space-y-1.5 lg:col-span-3">
                    <span class="block text-[11px] font-black uppercase tracking-wider text-slate-500">Theo trạng thái</span>
                    <select wire:model.live="statusFilter" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10">
                        <option value="all">Tất cả trạng thái</option>
                        @foreach ($statusMeta as $status => $meta)
                            <option value="{{ $status }}">{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="space-y-1.5 lg:col-span-4">
                    <span class="block text-[11px] font-black uppercase tracking-wider text-slate-500">Từ khóa</span>
                    <div class="relative">
                        <x-user.icon name="search" :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm môn học, mã lớp..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm font-bold text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10">
                    </div>
                </label>

                <div class="flex items-end lg:col-span-1">
                    <button type="button" wire:click="clearFilters" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">Xóa</button>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm xl:col-span-8 2xl:col-span-9">
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <div>
                        <h2 class="text-base font-black uppercase tracking-wide text-slate-950">Nhật ký điểm danh tích lũy</h2>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Hiển thị {{ $records->count() }} kết quả</p>
                    </div>
                    <a href="{{ route('student.attendance.stats') }}" class="hidden items-center gap-2 rounded-2xl bg-blue-50 px-4 py-2 text-sm font-black text-blue-700 transition hover:bg-blue-100 sm:inline-flex">
                        <x-user.icon name="bar-chart" :size="16" />
                        Xem thống kê
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] border-collapse text-left">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Ngày</th>
                                <th class="px-5 py-4">Môn học</th>
                                <th class="px-5 py-4">Lớp</th>
                                <th class="px-5 py-4">Trạng thái</th>
                                <th class="px-5 py-4">Hình thức</th>
                                <th class="px-5 py-4">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($records as $record)
                                @php($meta = $statusMeta[$record['status']] ?? $statusMeta['pending'])
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="whitespace-nowrap px-5 py-4 font-mono font-black text-slate-900">
                                        {{ $record['date']?->format('d/m/Y') ?? '--/--/----' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-black text-slate-950">{{ $record['class_name'] }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-400">{{ $record['session'] }} · {{ $record['semester'] ?? 'Chưa cập nhật' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-xl bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-700">{{ $record['class_code'] }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs font-black uppercase {{ $meta['badge'] }}">
                                            <span class="h-2 w-2 rounded-full {{ $meta['dot'] }}"></span>
                                            {{ $meta['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700">
                                            <x-user.icon :name="$record['method'] === 'QR + GPS' ? 'qr-code' : 'clipboard-check'" :size="14" />
                                            {{ $record['method'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-500">
                                        {{ $record['check_in_time'] ? $record['check_in_time']->format('H:i') : 'Chưa check-in' }}
                                        @if ($record['distance'])
                                            · {{ $record['distance'] }}m
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-sm font-bold text-slate-500">Chưa có lịch sử điểm danh phù hợp.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="space-y-6 xl:col-span-4 2xl:col-span-3">
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="flex items-center gap-2 text-base font-black text-slate-950">
                        <x-user.icon name="shield-check" :size="19" class="text-emerald-600" />
                        Trạng thái xác thực
                    </h3>
                    <div class="mt-5 space-y-3">
                        @foreach ([
                            ['label' => 'QR/GPS hợp lệ', 'value' => $records->where('verified', true)->count(), 'color' => 'text-emerald-600 bg-emerald-50'],
                            ['label' => 'Cần kiểm tra', 'value' => $records->where('verified', false)->count(), 'color' => 'text-amber-600 bg-amber-50'],
                            ['label' => 'Vắng không phép', 'value' => $summary['absent'], 'color' => 'text-rose-600 bg-rose-50'],
                        ] as $item)
                            <div class="flex items-center justify-between rounded-2xl border border-slate-100 p-4">
                                <span class="text-sm font-bold text-slate-600">{{ $item['label'] }}</span>
                                <span class="{{ $item['color'] }} rounded-xl px-3 py-1 text-sm font-black">{{ $item['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[2rem] border border-amber-200 bg-amber-50 p-6 text-amber-900 shadow-sm">
                    <h3 class="flex items-center gap-2 text-base font-black">
                        <x-user.icon name="alert-triangle" :size="19" />
                        Nhắc nhở chuyên cần
                    </h3>
                    <p class="mt-3 text-sm font-semibold leading-6">Nếu có buổi vắng, hãy gửi đơn xin nghỉ kèm minh chứng sớm để chủ lớp duyệt trước khi tổng kết điểm danh.</p>
                    <a href="{{ route('lecturer.leave-requests.index') }}" class="mt-4 inline-flex rounded-2xl bg-amber-500 px-4 py-2 text-sm font-black text-white transition hover:bg-amber-600">Xem đơn xin nghỉ</a>
                </div>
            </aside>
        </section>
    </div>
</div>
