@php
    $statusMeta = [
        'present' => ['label' => 'Có mặt', 'short' => 'Có mặt', 'card' => 'border-emerald-500', 'iconBg' => 'bg-emerald-50 text-emerald-600', 'button' => 'bg-emerald-600 text-white', 'soft' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100', 'row' => 'bg-white'],
        'late' => ['label' => 'Đi muộn', 'short' => 'Đi muộn', 'card' => 'border-amber-500', 'iconBg' => 'bg-amber-50 text-amber-600', 'button' => 'bg-amber-500 text-white', 'soft' => 'bg-amber-50 text-amber-700 hover:bg-amber-100', 'row' => 'bg-white'],
        'excused' => ['label' => 'Có phép', 'short' => 'Có phép', 'card' => 'border-blue-500', 'iconBg' => 'bg-blue-50 text-blue-600', 'button' => 'bg-blue-600 text-white', 'soft' => 'bg-blue-50 text-blue-700 hover:bg-blue-100', 'row' => 'bg-white'],
        'absent' => ['label' => 'Vắng', 'short' => 'Vắng', 'card' => 'border-rose-500', 'iconBg' => 'bg-rose-50 text-rose-600', 'button' => 'bg-rose-600 text-white', 'soft' => 'bg-rose-50 text-rose-700 hover:bg-rose-100', 'row' => 'bg-white'],
        'pending' => ['label' => 'Chưa ĐD', 'short' => 'Chưa ĐD', 'card' => 'border-slate-400', 'iconBg' => 'bg-slate-100 text-slate-500', 'button' => 'bg-slate-900 text-white', 'soft' => 'bg-slate-100 text-slate-600 hover:bg-slate-200', 'row' => 'bg-white'],
    ];

    $isClosed = $session->status === 'closed';
@endphp

<div class="mx-auto max-w-[1400px] px-4 pt-4 sm:px-8 sm:pt-8" x-data="{ modalOpen: false, confirmChecked: false, deleteModalOpen: false, createSessionModalOpen: false }">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <div class="mb-3 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900" title="{{ $session->name }}">
                    {{ \Illuminate\Support\Str::limit($session->name, 40) }}
                </h1>
                <span @class([
                    'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold',
                    'border-slate-200 bg-slate-100 text-slate-600' => $isClosed,
                    'border-emerald-200 bg-emerald-50 text-emerald-600' => ! $isClosed,
                ])>
                    <span @class([
                        'h-2 w-2 rounded-full',
                        'bg-slate-400' => $isClosed,
                        'animate-pulse bg-emerald-500' => ! $isClosed,
                    ])></span>
                    {{ $isClosed ? 'Đã chốt sổ' : 'Đang điểm danh' }}
                </span>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3 text-sm font-bold text-slate-600">
                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                    <x-user.icon name="check-square" :size="16" class="text-emerald-600" />
                    Điểm danh thủ công
                </span>
                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                    <x-user.icon name="school" :size="16" class="text-blue-600" />
                    {{ $session->courseClass->join_key }} - {{ $session->courseClass->name }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                    <x-user.icon name="clock" :size="16" class="text-amber-500" />
                    {{ $session->date->format('d/m/Y') }}
                    @if ($session->start_time || $session->end_time)
                        ({{ \Illuminate\Support\Str::of((string) $session->start_time)->substr(0, 5) }} - {{ \Illuminate\Support\Str::of((string) $session->end_time)->substr(0, 5) }})
                    @endif
                </span>
            </div>
        </div>

        <div class="flex w-full items-center gap-3 overflow-x-auto pb-2 md:w-auto md:shrink-0 md:pb-0 scrollbar-hide">
            <a href="{{ route('lecturer.attendance.meeting.sessions', $session->meeting_id) }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 whitespace-nowrap">
                <x-user.icon name="arrow-left" :size="18" />
                Quay lại
            </a>
            @if(!$isClosed)
                <button type="button" @click="deleteModalOpen = true" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-bold text-rose-600 transition hover:bg-rose-50 whitespace-nowrap">
                    <x-user.icon name="x-circle" :size="18" />
                    Xóa phiên
                </button>
            @endif
            <button type="button" @click="createSessionModalOpen = true" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 whitespace-nowrap">
                <x-user.icon name="plus" :size="18" />
                Tạo phiên mới
            </button>
            <button
                type="button"
                wire:click="markAllPresent"
                @disabled($isClosed)
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-500/20 transition hover:bg-blue-700 disabled:opacity-60 whitespace-nowrap"
            >
                <x-user.icon name="check-circle-2" :size="18" />
                Tất cả có mặt
            </button>
        </div>
    </div>

    <x-notification.notification />

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['key' => 'present', 'icon' => 'check-circle-2'],
            ['key' => 'absent', 'icon' => 'x-circle'],
            ['key' => 'late', 'icon' => 'clock'],
            ['key' => 'excused', 'icon' => 'clipboard-check'],
            ['key' => 'pending', 'icon' => 'more-horizontal'],
        ] as $card)
            <div
                @class([
                    'rounded-2xl border-l-4 bg-white p-5 text-left shadow-sm',
                    $statusMeta[$card['key']]['card']
                ])
            >
                <div class="mb-2 flex items-center gap-3">
                    <div class="{{ $statusMeta[$card['key']]['iconBg'] }} flex h-10 w-10 items-center justify-center rounded-full">
                        <x-user.icon :name="$card['icon']" :size="20" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $statusMeta[$card['key']]['label'] }}</span>
                </div>
                <p class="text-4xl font-extrabold text-slate-900">{{ $summary[$card['key']] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-32 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-5 border-b border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        Danh sách học viên ({{ $records->count() }})
                    </h2>
                </div>

                <form wire:submit.prevent="searchStudents" class="flex w-full flex-col gap-2 sm:flex-row xl:w-auto xl:justify-end">
                    <label class="relative flex-1 sm:w-64 sm:flex-none">
                        <x-user.icon name="search" :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            type="text"
                            wire:model="search"
                            placeholder="Tìm theo tên hoặc mã số..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm font-medium outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20"
                        >
                    </label>
                    <button type="submit" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container">
                        <x-user.icon name="search" :size="16" />
                        Tìm kiếm
                    </button>
                    <select
                        wire:change="setStatusFilter($event.target.value)"
                        class="shrink-0 cursor-pointer appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20"
                        style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25em 1.25em; padding-right: 2.5rem;"
                    >
                        <option value="all" @selected($statusFilter === 'all')>Tất cả trạng thái</option>
                        <option value="pending" @selected($statusFilter === 'pending')>Chưa ĐD</option>
                        <option value="present" @selected($statusFilter === 'present')>Có mặt</option>
                        <option value="absent" @selected($statusFilter === 'absent')>Vắng</option>
                        <option value="late" @selected($statusFilter === 'late')>Đi muộn</option>
                        <option value="excused" @selected($statusFilter === 'excused')>Có phép</option>
                    </select>

                    @if ($search !== '' || $statusFilter !== 'all')
                        <button type="button" wire:click="clearSearch" class="shrink-0 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                            Xóa lọc
                        </button>
                    @endif

                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] border-collapse text-left">
                <thead class="border-b border-slate-200 bg-slate-50 text-sm font-bold uppercase tracking-wider text-slate-900">
                    <tr>
                        <th class="w-16 px-5 py-4">STT</th>
                        <th class="w-32 px-5 py-4">MSSV</th>
                        <th class="px-5 py-4">Họ tên</th>
                        <th class="px-5 py-4">Trạng thái điểm danh</th>
                        <th class="w-64 px-5 py-4">Ghi chú</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($records as $record)
                        @php($current = $draftStatuses[$record->id] ?? $record->status)
                        <tr class="{{ $statusMeta[$current]['row'] ?? $statusMeta['pending']['row'] }} transition-colors">
                            <td class="px-5 py-4 text-base font-bold text-slate-500">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-5 py-4 font-mono text-base font-bold text-slate-600">{{ $record->classMember?->student_code ?? 'N/A' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-base font-extrabold text-blue-700">
                                        {{ \Illuminate\Support\Str::substr($record->classMember?->full_name ?? '?', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-base font-bold text-slate-900">{{ $record->classMember?->full_name ?? 'Không xác định' }}</p>
                                        <p class="text-sm font-medium text-slate-400">{{ $statusMeta[$current]['label'] ?? $statusMeta['pending']['label'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-3">
                                    @foreach (['present', 'absent', 'late', 'excused'] as $option)
                                        <button
                                            type="button"
                                            wire:click="setStatus({{ $record->id }}, '{{ $option }}')"
                                            @disabled($isClosed)
                                            @class([
                                                'rounded-full px-4 py-2 text-sm font-bold transition disabled:opacity-60',
                                                $statusMeta[$option]['button'] . ' shadow-sm ring-1 ring-black/5' => $current === $option,
                                                'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900' => $current !== $option,
                                            ])
                                        >
                                            {{ $statusMeta[$option]['short'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <input
                                    type="text"
                                    wire:model="draftNotes.{{ $record->id }}"
                                    placeholder="Thêm ghi chú..."
                                    @disabled($isClosed)
                                    class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-base font-medium outline-none transition hover:border-slate-200 hover:bg-white focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 disabled:text-slate-400"
                                >
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm font-semibold text-slate-500">
                                Không tìm thấy học viên phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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
            @if ($canExportExcel)
                <button type="button" wire:click="exportExcel" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    <x-user.icon name="download" :size="16" />
                    <span wire:loading.remove wire:target="exportExcel">Xuất Excel</span>
                    <span wire:loading wire:target="exportExcel">Đang xử lý...</span>
                </button>
            @else
                <a href="{{ route('upgrade') }}" title="Nâng cấp lên gói Pro để xuất báo cáo Excel"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-bold text-amber-700 transition hover:bg-amber-100">
                    <x-user.icon name="download" :size="16" />
                    Xuất Excel
                    <span class="rounded-full bg-amber-200 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800">Pro</span>
                </a>
            @endif
        </div>
    </div>

    <div class="sticky bottom-0 z-30 -mx-4 mt-8 border-t border-slate-200 bg-white/85 px-4 py-4 backdrop-blur-md md:-mx-8 md:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-end sm:gap-6">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold uppercase tracking-wider text-slate-500">Tổng hợp nhanh:</span>
                <span class="text-lg font-extrabold text-slate-900 tabular-nums">
                    <span class="text-emerald-600">{{ $summary['present'] }}</span> có mặt ·
                    <span class="text-amber-600">{{ $summary['late'] }}</span> đi muộn ·
                    <span class="text-rose-600">{{ $summary['absent'] }}</span> vắng ·
                    <span class="text-sky-600">{{ $summary['excused'] }}</span> có phép
                </span>
            </div>
            <button
                type="button"
                wire:click="saveSession"
                wire:loading.attr="disabled"
                wire:target="saveSession"
                @disabled($isClosed)
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-8 py-3 text-lg font-bold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600 active:scale-95 disabled:opacity-60"
            >
                <x-user.icon name="save" :size="20" />
                <span wire:loading.remove wire:target="saveSession">{{ $isClosed ? 'ĐÃ CHỐT SỔ' : 'LƯU PHIÊN' }}</span>
                <span wire:loading wire:target="saveSession">ĐANG LƯU...</span>
            </button>
        </div>
    </div>

    <div x-cloak x-show="deleteModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="deleteModalOpen" x-transition.opacity.duration.200ms class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" @click="deleteModalOpen = false" aria-label="Đóng"></div>
        <div x-show="deleteModalOpen" x-transition.scale.origin.center.duration.200ms class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <button type="button" @click="deleteModalOpen = false" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <x-user.icon name="x" :size="20" />
            </button>
            <div class="p-6 pb-2 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                    <x-user.icon name="alert-triangle" :size="32" />
                </div>
                <h3 class="mb-2 text-xl font-extrabold text-slate-900">Xóa phiên điểm danh?</h3>
                <p class="text-sm font-medium leading-relaxed text-slate-500">
                    Bạn có chắc chắn muốn xóa phiên điểm danh này không? Kết quả điểm danh của học viên trong phiên này sẽ bị mất.
                </p>
            </div>
            <div class="flex flex-col gap-3 px-6 pb-6 pt-4 sm:flex-row">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 sm:flex-1" @click="deleteModalOpen = false">
                    Hủy
                </button>
                <button type="button" wire:click="deleteSession" @click="deleteModalOpen = false" class="rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-rose-600/20 transition hover:bg-rose-700 sm:flex-1">
                    Xóa phiên
                </button>
            </div>
        </div>
    </div>

    <!-- Create Session Modal -->
    <div x-cloak x-show="createSessionModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="createSessionModalOpen" x-transition.opacity.duration.200ms class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" @click="createSessionModalOpen = false" aria-label="Đóng"></div>
        <div x-show="createSessionModalOpen" x-transition.scale.origin.center.duration.200ms class="relative w-full max-w-[380px] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <button type="button" @click="createSessionModalOpen = false" class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <x-user.icon name="x" :size="20" />
            </button>
            <div class="p-5 pb-2 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                    <x-user.icon name="plus-circle" :size="28" />
                </div>
                <h3 class="mb-3 text-lg font-extrabold text-slate-900">Tạo phiên điểm danh mới</h3>
                <div class="text-sm text-slate-700">
                    <div class="flex flex-col gap-2.5 text-left bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-sm">
                        <div class="flex justify-between items-center border-b border-slate-200/60 pb-2.5">
                            <span class="font-medium text-slate-500">Lớp:</span>
                            <span class="font-bold text-slate-900 text-right max-w-[65%] truncate" title="{{ $session->courseClass->join_key }} - {{ $session->courseClass->name }}">{{ $session->courseClass->join_key }} - {{ $session->courseClass->name }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 pb-2.5">
                            <span class="font-medium text-slate-500">Buổi:</span>
                            <span class="font-bold text-slate-900">{{ $session->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-slate-500">Ngày:</span>
                            <span class="font-bold text-slate-900">{{ $session->date->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-3 px-5 pb-5 pt-3 sm:flex-row">
                <button type="button" wire:click="createDuplicateManualSession" wire:loading.attr="disabled" class="inline-flex justify-center items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 disabled:opacity-50 sm:flex-1">
                    <x-user.icon name="check-square" :size="18" class="text-emerald-600" />
                    Thủ công
                </button>
                <a href="{{ route('lecturer.attendance.qr.create', ['class_id' => $session->class_id, 'date' => $session->date->format('Y-m-d'), 'clone_session' => $session->id]) }}" class="inline-flex justify-center items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 sm:flex-1">
                    <x-user.icon name="qr-code" :size="18" />
                    QR/Link
                </a>
            </div>
        </div>
    </div>
</div>
