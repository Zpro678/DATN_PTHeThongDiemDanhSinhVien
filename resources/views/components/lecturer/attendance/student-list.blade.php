<div class="mb-20 flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
    {{-- TABLE HEADER & FILTER --}}
    <div class="flex flex-col gap-4 border-b border-slate-100 bg-white p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-800">
                Danh sách học viên ({{ method_exists($records, 'total') ? $records->total() : $records->count() }})
            </h2>
        </div>

        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center xl:w-auto xl:justify-end">
            <label class="flex flex-1 flex-col gap-1 sm:w-64 sm:flex-none">
                <span class="relative">
                    <x-user.icon name="search" :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Tìm theo tên hoặc mã số..."
                        class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20"
                    >
                </span>
            </label>
            
            <div class="flex gap-3">
                <div class="w-44 shrink-0">
                    @php
                        $statusOptions = [
                            ['value' => 'all', 'label' => 'Tất cả trạng thái'],
                            ['value' => 'pending', 'label' => 'Chưa ĐD'],
                            ['value' => 'present', 'label' => 'Có mặt'],
                            ['value' => 'absent', 'label' => 'Vắng'],
                            ['value' => 'late', 'label' => 'Đi muộn'],
                            ['value' => 'excused', 'label' => 'Có phép'],
                        ];
                        // Chỉ hiện ở phiên QR khi có nhóm dùng chung máy (biến $sameDeviceCount do QrAttendanceSession truyền).
                        if (isset($sameDeviceCount) && $sameDeviceCount > 0) {
                            $statusOptions[] = ['value' => 'same_device', 'label' => 'Điểm danh cùng 1 máy'];
                        }

                        $statusRadioClasses = [
                            'present' => 'peer-checked:border-emerald-700 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-emerald-600/20',
                            'absent' => 'peer-checked:border-rose-700 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-rose-600/20',
                            'late' => 'peer-checked:border-amber-600 peer-checked:bg-amber-500 peer-checked:text-white peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-amber-500/20',
                            'excused' => 'peer-checked:border-blue-700 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-blue-600/20',
                        ];
                    @endphp
                    <x-custom-select wire:change="setStatusFilter($event.target.value)" placeholder="" :value="$statusFilter" :options="$statusOptions" />
                </div>

                @if($showMarkAllPresent ?? true)
                    <button
                        type="button"
                        wire:click="markAllPresent"
                        @disabled($isAttendanceLocked ?? $isClosed)
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-800 disabled:opacity-50"
                    >
                        <x-user.icon name="check-square" :size="16" />
                        Tất cả có mặt
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto overflow-y-auto {{ $records->count() > 30 ? 'max-h-[75vh]' : '' }}">
        <table class="w-full min-w-[1024px] border-collapse text-left relative">
            <thead class="sticky top-0 z-10 border-b border-slate-200 bg-slate-50 text-sm font-bold uppercase tracking-wider text-black shadow-sm">
                <tr>
                    <th class="w-16 px-6 py-4 text-center">STT</th>
                    <th class="px-6 py-4 w-72">HỌ TÊN</th>
                    <th class="px-6 py-4">TRẠNG THÁI ĐIỂM DANH</th>
                    <th class="px-6 py-4 min-w-[350px]">GHI CHÚ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-[15px]">
                @forelse ($records as $record)
                    @php
                        $usesDraftStatuses = $this instanceof \App\Livewire\Lecturer\Attendance\ManualAttendanceSession;
                        $current = $usesDraftStatuses ? ($draftStatuses[$record->id] ?? $record->status) : $record->status;
                        $statusLabel = $statusMeta[$current]['short'] ?? 'Chưa ĐD';
                        $statusColor = $statusMeta[$current]['text'] ?? 'text-slate-400';
                        if ($current == 'pending') $statusLabel = 'Chưa điểm danh';

                        // Điểm danh NGOÀI bán kính GPS: vẫn được ghi nhận nhưng tô VÀNG NHẠT + hiện số mét vượt.
                        $isOutOfRadius = $record->gps_fraud_flag === 'out_of_radius';
                        $metersOutside = ($isOutOfRadius && $record->distance_meters !== null && ($session->gps_radius ?? null))
                            ? max(0, (int) round($record->distance_meters - $session->gps_radius))
                            : null;
                    @endphp
                    <tr class="transition-colors {{ $isOutOfRadius ? 'bg-amber-50 hover:bg-amber-100/70' : 'bg-white hover:bg-slate-50/50' }}">
                        <td class="px-6 py-5 font-medium text-slate-500 text-center">
                            {{ method_exists($records, 'firstItem') ? ($records->firstItem() + $loop->index) : $loop->iteration }}
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3.5">
                                @if($record->classMember && $record->classMember->user_id && $record->classMember->user)
                                    <img src="{{ $record->classMember->user->avatar_url }}" alt="{{ $record->classMember->full_name }}" class="h-10 w-10 shrink-0 rounded-full object-cover shadow-sm">
                                @else
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-[15px] font-bold text-blue-600">
                                        {{ \Illuminate\Support\Str::substr($record->classMember?->full_name ?? '?', 0, 1) }}
                                    </div>
                                @endif
                                @php
                                    $nameColor = 'text-slate-800';
                                    $isSameDeviceGroup = isset($sharedDeviceIds)
                                        && $record->device_id !== null
                                        && in_array($record->device_id, $sharedDeviceIds, true);

                                    if ($isSameDeviceGroup || $record->gps_fraud_flag === 'device_duplicate' || str_contains($record->note ?? '', 'điểm danh hộ')) {
                                        $nameColor = 'text-red-600';
                                    } elseif ($record->gps_fraud_flag === 'out_of_radius' || $record->gps_fraud_flag === 'suspected_mock' || str_contains($record->note ?? '', 'Sai GPS') || str_contains($record->note ?? '', 'Fake GPS')) {
                                        $nameColor = 'text-amber-500';
                                    }
                                @endphp
                                <div class="min-w-0">
                                    <p class="truncate text-[15.5px] font-semibold {{ $nameColor }}">{{ $record->classMember?->full_name ?? 'Không xác định' }}</p>
                                    <p class="text-[12px] font-medium {{ $statusColor }} mt-0.5">{{ $statusLabel }}</p>
                                    @if($metersOutside !== null)
                                        <span class="mt-1 inline-flex items-center gap-1 rounded-md bg-amber-100 px-1.5 py-0.5 text-[11px] font-bold text-amber-700 ring-1 ring-amber-200" title="Điểm danh ở ngoài bán kính cho phép, vẫn được ghi nhận">
                                            <x-user.icon name="map-pin" :size="12" />
                                            Ngoài bán kính +{{ $metersOutside }}m
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-100/80 p-1" role="radiogroup" aria-label="Trạng thái điểm danh của {{ $record->classMember?->full_name ?? 'học viên' }}">
                                @foreach (['present', 'absent', 'late', 'excused'] as $option)
                                    @php $inputId = "attendance-status-{$session->id}-{$record->id}-{$option}"; @endphp
                                    <div class="relative">
                                        <input
                                            id="{{ $inputId }}"
                                            type="radio"
                                            name="attendance_status_{{ $record->id }}"
                                            value="{{ $option }}"
                                            wire:model="draftStatuses.{{ $record->id }}"
                                            wire:change="setStatus({{ $record->id }}, '{{ $option }}')"
                                            @checked($current === $option)
                                            @disabled($isAttendanceLocked ?? $isClosed)
                                            class="peer sr-only"
                                        >
                                        <label
                                            for="{{ $inputId }}"
                                            class="flex w-[84px] cursor-pointer select-none items-center justify-center rounded-lg border border-transparent bg-white px-2.5 py-2 text-center text-[13px] font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 hover:text-black peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-blue-500 peer-disabled:cursor-not-allowed peer-disabled:opacity-60 {{ $statusRadioClasses[$option] }}"
                                        >
                                            {{ $statusMeta[$option]['short'] }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <input
                                type="text"
                                wire:model="draftNotes.{{ $record->id }}"
                                placeholder="Nhập ghi chú..."
                                @disabled($isAttendanceLocked ?? $isClosed)
                                class="w-full rounded-lg border border-transparent bg-transparent px-3.5 py-2.5 text-[14px] text-slate-700 placeholder-slate-400 outline-none transition hover:bg-slate-50 focus:bg-white focus:border-blue-300 focus:ring-2 focus:ring-blue-100 disabled:opacity-60"
                            >
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm font-medium text-slate-500">
                            Không tìm thấy học viên phù hợp.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (method_exists($records, 'hasPages') && $records->hasPages())
        <div class="border-t border-slate-200 bg-white p-4">
            {{ $records->links() }}
        </div>
    @endif

    <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-5 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm font-medium text-slate-500">
            Tổng sĩ số: <span class="font-bold text-slate-900">{{ $summary['total'] }}</span>
            <span class="mx-2 text-slate-300">|</span>
            Có mặt: <span class="font-bold text-emerald-600">{{ $summary['present'] }}</span>
            <span class="mx-2 text-slate-300">|</span>
            Đi muộn: <span class="font-bold text-amber-600">{{ $summary['late'] }}</span>
            <span class="mx-2 text-slate-300">|</span>
            Vắng: <span class="font-bold text-rose-600">{{ $summary['absent'] }}</span>
            <span class="mx-2 text-slate-300">|</span>
            Có phép: <span class="font-bold text-sky-600">{{ $summary['excused'] }}</span>
        </p>

    </div>
</div>
