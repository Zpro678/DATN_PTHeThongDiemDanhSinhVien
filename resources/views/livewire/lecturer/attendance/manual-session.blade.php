@php
    $statusMeta = [
        'present' => ['label' => 'CÓ MẶT', 'short' => 'Có mặt', 'card' => 'border-l-4 border-l-emerald-400 border-t-slate-100 border-r-slate-100 border-b-slate-100', 'text' => 'text-emerald-500', 'icon' => 'check-circle-2', 'activeBtn' => 'bg-emerald-600 text-white border-emerald-700 shadow-md ring-2 ring-emerald-600/20'],
        'absent' => ['label' => 'VẮNG KP', 'short' => 'Vắng KP', 'card' => 'border-l-4 border-l-rose-400 border-t-slate-100 border-r-slate-100 border-b-slate-100', 'text' => 'text-rose-500', 'icon' => 'x-circle', 'activeBtn' => 'bg-rose-600 text-white border-rose-700 shadow-md ring-2 ring-rose-600/20'],
        'late' => ['label' => 'ĐI TRỄ', 'short' => 'Đi trễ', 'card' => 'border-l-4 border-l-amber-400 border-t-slate-100 border-r-slate-100 border-b-slate-100', 'text' => 'text-amber-500', 'icon' => 'clock', 'activeBtn' => 'bg-amber-500 text-white border-amber-600 shadow-md ring-2 ring-amber-500/20'],
        'excused' => ['label' => 'VẮNG PHÉP', 'short' => 'Vắng phép', 'card' => 'border-l-4 border-l-blue-400 border-t-slate-100 border-r-slate-100 border-b-slate-100', 'text' => 'text-blue-500', 'icon' => 'clipboard-check', 'activeBtn' => 'bg-blue-600 text-white border-blue-700 shadow-md ring-2 ring-blue-600/20'],
        'pending' => ['label' => 'CHƯA ĐD', 'short' => 'Chưa ĐD', 'card' => 'border border-slate-100', 'text' => 'text-slate-400', 'icon' => 'help-circle', 'activeBtn' => 'bg-slate-50 text-slate-500 border-slate-200'],
    ];

    $isClosed = $session->status === 'closed';
@endphp

<div class="w-full px-6 pt-6 sm:px-10 lg:px-16 sm:pt-8 min-h-screen" style="background-color: #f8fafc;" x-data="{ modalOpen: false, confirmChecked: false, deleteModalOpen: false, createSessionModalOpen: false }">
    
    {{-- SESSION INFO CARD --}}
    <div class="mb-8 flex flex-col gap-6 rounded-2xl border border-slate-100 bg-white p-7 shadow-sm xl:flex-row xl:justify-between">
        {{-- LEFT SIDE: INFO --}}
        <div class="flex-1 min-w-0 flex flex-col gap-3">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold tracking-tight text-slate-800">
                    {{ $session->name }}
                </h1>
                <span @class([
                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[12px] font-medium tracking-wide',
                    'bg-slate-100 text-slate-500' => $isClosed,
                    'bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100' => ! $isClosed,
                ])>
                    <span @class([
                        'h-2 w-2 rounded-full',
                        'bg-slate-400' => $isClosed,
                        'animate-pulse bg-emerald-500' => ! $isClosed,
                    ])></span>
                    {{ $isClosed ? 'Đã chốt sổ' : 'Đang mở' }}
                </span>
            </div>

            <div class="flex flex-col gap-3 mt-3 text-[15.5px] text-slate-600">
                <span class="inline-flex items-center gap-3">
                    <x-user.icon name="check-circle-2" :size="18" class="text-slate-400" />
                    Điểm danh thủ công
                </span>
                <span class="inline-flex items-center gap-3">
                    <x-user.icon name="laptop" :size="18" class="text-slate-400" />
                    {{ $session->courseClass->join_key }} - {{ $session->courseClass->name }}
                </span>
                <span class="inline-flex items-center gap-3">
                    <x-user.icon name="clock" :size="18" class="text-slate-400" />
                    {{ $session->date->format('d/m/Y') }} 
                    @if ($session->start_time || $session->end_time)
                        ({{ \Illuminate\Support\Str::of((string) $session->start_time)->substr(0, 5) }} - {{ \Illuminate\Support\Str::of((string) $session->end_time)->substr(0, 5) }})
                    @endif
                </span>
            </div>
        </div>

        {{-- RIGHT SIDE: ACTIONS & STATS --}}
        <div class="flex flex-col gap-10 xl:items-end w-full xl:w-auto mt-2 xl:mt-0">
            {{-- Buttons --}}
            <div class="flex items-center gap-3 overflow-x-auto scrollbar-hide">
                <button type="button" @click="deleteModalOpen = true" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white border border-rose-200 px-4 py-2.5 text-[14px] font-medium text-rose-600 shadow-sm transition hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700">
                    <x-user.icon name="trash-2" :size="18" />
                    Xóa phiên
                </button>
                
                @if ($canExportExcel)
                    <button type="button" wire:click="exportExcel" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-[14px] font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900">
                        <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-2">
                            <x-user.icon name="download" :size="18" /> Xuất dữ liệu
                        </span>
                        <span wire:loading wire:target="exportExcel" class="flex items-center gap-2">
                            <x-user.icon name="loader" :size="18" class="animate-spin" /> Đang xử lý...
                        </span>
                    </button>
                @else
                    <a href="{{ route('upgrade') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-[14px] font-medium text-amber-600 shadow-sm transition hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700">
                        <x-user.icon name="download" :size="18" />
                        Xuất dữ liệu (Pro)
                    </a>
                @endif

                <button type="button" @click="createSessionModalOpen = true" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 border border-transparent px-5 py-2.5 text-[14px] font-medium text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md">
                    <x-user.icon name="plus" :size="18" />
                    Tạo phiên mới
                </button>
            </div>
            
            {{-- Stat Cards inside the main card --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 w-full xl:w-[680px]">
                @foreach (['present', 'absent', 'late', 'excused'] as $key)
                    <div class="flex flex-col justify-center rounded-xl border {{ $statusMeta[$key]['card'] }} bg-slate-50/50 p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-2 mb-2">
                            <x-user.icon :name="$statusMeta[$key]['icon']" :size="16" class="{{ $statusMeta[$key]['text'] }}" />
                            <span class="text-[12px] font-bold tracking-wider text-slate-500">{{ $statusMeta[$key]['label'] }}</span>
                        </div>
                        <div>
                            <span class="text-3xl font-extrabold text-slate-800">{{ $summary[$key] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-notification.notification />

    {{-- TABLE SECTION --}}
    <div class="mb-20 flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        
        {{-- TABLE HEADER & FILTER --}}
        <div class="flex flex-col gap-4 border-b border-slate-100 bg-white p-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Danh sách học viên ({{ $records->count() }})
                </h2>
            </div>

            <div class="flex w-full flex-col gap-3 sm:flex-row xl:w-auto xl:justify-end">
                <label class="relative flex-1 sm:w-64 sm:flex-none">
                    <x-user.icon name="search" :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Tìm kiếm theo tên hoặc mã..."
                        class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20"
                    >
                </label>
                
                <div class="flex gap-3">
                    <select
                        wire:change="setStatusFilter($event.target.value)"
                        class="shrink-0 cursor-pointer appearance-none rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 outline-none transition focus:border-blue-500"
                        style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1rem 1rem; padding-right: 2.5rem;"
                    >
                        <option value="all" @selected($statusFilter === 'all')>Tất cả trạng thái</option>
                        <option value="pending" @selected($statusFilter === 'pending')>Chưa ĐD</option>
                        <option value="present" @selected($statusFilter === 'present')>Có mặt</option>
                        <option value="absent" @selected($statusFilter === 'absent')>Vắng KP</option>
                        <option value="late" @selected($statusFilter === 'late')>Đi trễ</option>
                        <option value="excused" @selected($statusFilter === 'excused')>Vắng phép</option>
                    </select>

                    <button
                        type="button"
                        wire:click="markAllPresent"
                        @disabled($isClosed)
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-800 disabled:opacity-50"
                    >
                        <x-user.icon name="check-square" :size="16" />
                        Tất cả có mặt
                    </button>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1024px] border-collapse text-left">
                <thead class="border-b border-slate-100 bg-slate-50/50 text-sm font-bold uppercase tracking-wider text-black">
                    <tr>
                        <th class="w-16 px-6 py-4 text-center">STT</th>
                        <th class="w-32 px-6 py-4">MSSV</th>
                        <th class="px-6 py-4 w-72">HỌ TÊN</th>
                        <th class="px-6 py-4">TRẠNG THÁI ĐIỂM DANH</th>
                        <th class="px-6 py-4 min-w-[350px]">GHI CHÚ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-[15px]">
                    @forelse ($records as $record)
                        @php
                            $current = $draftStatuses[$record->id] ?? $record->status;
                            $statusLabel = $statusMeta[$current]['short'] ?? 'Chưa ĐD';
                            $statusColor = $statusMeta[$current]['text'] ?? 'text-slate-400';
                            if ($current == 'pending') $statusLabel = 'Chưa điểm danh';
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors bg-white">
                            <td class="px-6 py-5 font-medium text-slate-500 text-center">{{ $loop->iteration }}</td>
                            <td class="px-6 py-5 font-bold text-slate-700">{{ $record->classMember?->student_code ?? 'N/A' }}</td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3.5">
                                    @if($record->classMember && $record->classMember->user_id && $record->classMember->user)
                                        <img src="{{ $record->classMember->user->avatar_url }}" alt="{{ $record->classMember->full_name }}" class="h-10 w-10 shrink-0 rounded-full object-cover shadow-sm">
                                    @else
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-[15px] font-bold text-blue-600">
                                            {{ \Illuminate\Support\Str::substr($record->classMember?->full_name ?? '?', 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate text-[15.5px] font-semibold text-slate-800">{{ $record->classMember?->full_name ?? 'Không xác định' }}</p>
                                        <p class="text-[12px] font-medium {{ $statusColor }} mt-0.5">{{ $statusLabel }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2">
                                    @foreach (['present', 'absent', 'late', 'excused'] as $option)
                                        @if($current === $option)
                                            <button
                                                type="button"
                                                disabled
                                                class="w-[84px] rounded-lg border py-2 text-center text-[13px] font-semibold transition cursor-default {{ $statusMeta[$option]['activeBtn'] }} shadow-sm"
                                            >
                                                {{ $statusMeta[$option]['short'] }}
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="setStatus({{ $record->id }}, '{{ $option }}')"
                                                @disabled($isClosed)
                                                class="w-[84px] rounded-lg border border-slate-200 bg-white py-2 text-center text-[13px] font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 disabled:opacity-50 transition"
                                            >
                                                {{ $statusMeta[$option]['short'] }}
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <input
                                    type="text"
                                    wire:model="draftNotes.{{ $record->id }}"
                                    placeholder="Nhập ghi chú..."
                                    @disabled($isClosed)
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-[14px] text-slate-700 placeholder-slate-400 outline-none transition focus:bg-white focus:border-blue-300 focus:ring-2 focus:ring-blue-100 disabled:opacity-60"
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

    </div>

    {{-- MODALS --}}
    <div x-cloak x-show="deleteModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="deleteModalOpen" x-transition.opacity.duration.200ms class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="deleteModalOpen = false" aria-label="Đóng"></div>
        <div x-show="deleteModalOpen" x-transition.scale.origin.center.duration.200ms class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <button type="button" @click="deleteModalOpen = false" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <x-user.icon name="x" :size="20" />
            </button>
            <div class="p-6">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                    <x-user.icon name="alert-triangle" :size="24" />
                </div>
                <h3 class="mb-2 text-lg font-bold text-slate-900">Xóa phiên điểm danh?</h3>
                <p class="mb-6 text-[13px] text-slate-500">Hành động này sẽ xóa vĩnh viễn phiên điểm danh này và không thể hoàn tác.</p>
                <div class="flex gap-3">
                    <button type="button" @click="deleteModalOpen = false" class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Hủy
                    </button>
                    <button type="button" wire:click="deleteSession" @click="deleteModalOpen = false" class="flex-1 rounded-xl bg-rose-600 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                        Xóa phiên
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="createSessionModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="createSessionModalOpen" x-transition.opacity.duration.200ms class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="createSessionModalOpen = false" aria-label="Đóng"></div>
        <div x-show="createSessionModalOpen" x-transition.scale.origin.center.duration.200ms class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <button type="button" @click="createSessionModalOpen = false" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <x-user.icon name="x" :size="20" />
            </button>
            <div class="p-6">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                    <x-user.icon name="plus" :size="24" />
                </div>
                <h3 class="mb-2 text-lg font-bold text-slate-900">Tạo phiên mới</h3>
                <p class="mb-6 text-[13px] text-slate-500">Bạn có muốn tạo một phiên điểm danh mới cho lớp này không?</p>
                <div class="flex gap-3">
                    <button type="button" @click="createSessionModalOpen = false" class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Hủy
                    </button>
                    <button type="button" wire:click="createNextSession" @click="createSessionModalOpen = false" class="flex-1 rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Tạo phiên
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
