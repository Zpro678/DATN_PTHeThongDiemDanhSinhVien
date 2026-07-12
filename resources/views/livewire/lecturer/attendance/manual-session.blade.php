@php
    $statusMeta = [
        'present' => ['label' => 'CÓ MẶT', 'short' => 'Có mặt', 'card' => 'border border-slate-400 bg-white', 'text' => 'text-emerald-500', 'icon' => 'check-circle-2', 'activeBtn' => 'bg-emerald-600 text-white border-emerald-700 shadow-md ring-2 ring-emerald-600/20'],
        'late' => ['label' => 'ĐI MUỘN', 'short' => 'Đi muộn', 'card' => 'border border-slate-400 bg-white', 'text' => 'text-amber-500', 'icon' => 'clock', 'activeBtn' => 'bg-amber-500 text-white border-amber-600 shadow-md ring-2 ring-amber-500/20'],
        'absent' => ['label' => 'VẮNG', 'short' => 'Vắng', 'card' => 'border border-slate-400 bg-white', 'text' => 'text-rose-500', 'icon' => 'x-circle', 'activeBtn' => 'bg-rose-600 text-white border-rose-700 shadow-md ring-2 ring-rose-600/20'],
        'excused' => ['label' => 'CÓ PHÉP', 'short' => 'Có phép', 'card' => 'border border-slate-400 bg-white', 'text' => 'text-blue-500', 'icon' => 'clipboard-check', 'activeBtn' => 'bg-blue-600 text-white border-blue-700 shadow-md ring-2 ring-blue-600/20'],
        'pending' => ['label' => 'CHƯA ĐD', 'short' => 'Chưa ĐD', 'card' => 'border border-slate-400 bg-slate-50', 'text' => 'text-slate-400', 'icon' => 'help-circle', 'activeBtn' => 'bg-slate-50 text-slate-500 border-slate-200'],
    ];

    $isClosed = $session->status === 'closed';
    $isAttendanceLocked = ! $session->meeting || $session->meeting->status === 'closed' || $session->meeting->isExpired();

    $classColor = 'bg-gradient-to-br from-orange-50/80 via-white to-white border-orange-100';
@endphp

<div class="w-full px-6 pt-6 sm:px-10 lg:px-16 sm:pt-8 min-h-screen" style="background-color: #f8fafc;" x-data="{ modalOpen: false, confirmChecked: false, deleteModalOpen: false, createSessionModalOpen: false }"
    x-init="window.listenRealtime && window.listenRealtime(@js($this->realtimeChannel()), () => $wire.$refresh(), 300)">
    
    {{-- SESSION INFO CARD --}}
    <div class="mb-8 flex flex-col gap-6 rounded-2xl border {{ $classColor }} p-7 shadow-sm xl:flex-row xl:justify-between">
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
                
                <x-user.export-button action="exportExcel" label="Xuất dữ liệu" :can="$canExportExcel" />

                <button type="button" @click="createSessionModalOpen = true" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 border border-transparent px-5 py-2.5 text-[14px] font-medium text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md">
                    <x-user.icon name="plus" :size="18" />
                    Tạo phiên mới
                </button>
            </div>
            
            {{-- Stat Cards inside the main card --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 w-full xl:w-[680px]">
                @foreach (['present', 'absent', 'late', 'excused'] as $key)
                    <div class="flex flex-col justify-center rounded-xl {{ $statusMeta[$key]['card'] }} p-4 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center gap-2 mb-2">
                            <x-user.icon :name="$statusMeta[$key]['icon']" :size="16" class="{{ $statusMeta[$key]['text'] }}" />
                            <span class="text-[12px] font-bold tracking-wider text-slate-500">{{ $statusMeta[$key]['label'] }}</span>
                        </div>
                        <div>
                            <span class="text-3xl font-extrabold {{ str_replace('-500', '-600', $statusMeta[$key]['text']) }}">{{ $summary[$key] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- TABLE SECTION --}}
    @include('components.lecturer.attendance.student-list')

    <div class="sticky bottom-0 z-30 -mx-6 mt-8 border-t border-slate-200 bg-white/90 px-6 py-2.5 sm:-mx-10 sm:px-10 lg:-mx-16 lg:px-16 backdrop-blur-md">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between w-full">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tổng hợp nhanh:</span>
                <span class="text-[15px] font-extrabold text-slate-900 tabular-nums">
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
                @disabled($isAttendanceLocked)
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-6 py-2 text-sm font-bold text-white shadow-sm shadow-orange-500/20 transition hover:bg-orange-600 active:scale-95 disabled:opacity-60"
            >
                <x-user.icon name="save" :size="18" />
                <span wire:loading.remove wire:target="saveSession">{{ $isAttendanceLocked ? 'BUỔI ĐÃ KẾT THÚC' : 'LƯU PHIÊN' }}</span>
                <span wire:loading wire:target="saveSession">ĐANG LƯU...</span>
            </button>
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
