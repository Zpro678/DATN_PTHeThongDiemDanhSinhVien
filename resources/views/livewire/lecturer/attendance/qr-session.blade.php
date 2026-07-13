@php
    $isClosed = $session->status === 'closed';
    $isAttendanceLocked = ! $session->meeting || $session->meeting->status === 'closed' || $session->meeting->isExpired();
    $selectedSubject = $session->courseClass->join_key;
    $sessionDateLabel = $session->date->format('d/m/Y');
    // "Phiên còn mở" tính theo GIỜ KẾT THÚC BUỔI (không phải hạn token QR — token nay xoay theo giây).
    $sessionEndsAt = $session->meeting?->endsAt() ?? now()->addMinutes(15);
    $openMinutes = max(1, (int) now()->diffInMinutes($sessionEndsAt, false));
    $qrRefreshRate = $session->qr_refresh_rate ?? 10;
    $statusMeta = [
        'present' => ['label' => 'CÓ MẶT', 'short' => 'Có mặt', 'card' => 'border border-slate-400 bg-white', 'text' => 'text-emerald-500', 'icon' => 'check-circle-2', 'activeBtn' => 'bg-emerald-600 text-white border-emerald-700 shadow-md ring-2 ring-emerald-600/20'],
        'late' => ['label' => 'ĐI MUỘN', 'short' => 'Đi muộn', 'card' => 'border border-slate-400 bg-white', 'text' => 'text-amber-500', 'icon' => 'clock', 'activeBtn' => 'bg-amber-500 text-white border-amber-600 shadow-md ring-2 ring-amber-500/20'],
        'absent' => ['label' => 'VẮNG', 'short' => 'Vắng', 'card' => 'border border-slate-400 bg-white', 'text' => 'text-rose-500', 'icon' => 'x-circle', 'activeBtn' => 'bg-rose-600 text-white border-rose-700 shadow-md ring-2 ring-rose-600/20'],
        'excused' => ['label' => 'CÓ PHÉP', 'short' => 'Có phép', 'card' => 'border border-slate-400 bg-white', 'text' => 'text-blue-500', 'icon' => 'clipboard-check', 'activeBtn' => 'bg-blue-600 text-white border-blue-700 shadow-md ring-2 ring-blue-600/20'],
        'pending' => ['label' => 'CHƯA ĐD', 'short' => 'Chưa ĐD', 'card' => 'border border-slate-400 bg-slate-50', 'text' => 'text-slate-400', 'icon' => 'help-circle', 'activeBtn' => 'bg-slate-50 text-slate-500 border-slate-200'],
    ];

    $classColor = 'bg-gradient-to-br from-orange-50/80 via-white to-white border-orange-100';
@endphp

<div
    class="w-full space-y-5 sm:space-y-8 px-6 py-6 sm:px-10 lg:px-16 pb-24"
    x-data="{
        isClosed: @entangle('isClosed').live,
        timeLeft: {{ $qrRefreshRate }},
        refreshRate: {{ $qrRefreshRate }},
        sessionTimeLeft: {{ max(0, (int) now()->diffInSeconds($sessionEndsAt, false)) }},
        showEndModal: false,
        showQrModal: false,
        showClassSettingsModal: false,
        showShareCodeModal: false,
        deleteModalOpen: false,
        shareCopied: false,
        copyShareCode() {
            if (this.isClosed) return;
            navigator.clipboard?.writeText(@js($attendanceLink));
            this.shareCopied = true;
            setTimeout(() => this.shareCopied = false, 1800);
        },
        tick() {
            if (this.isClosed) return;
            if (this.timeLeft <= 1) {
                this.timeLeft = this.refreshRate;
                $wire.refreshToken();
            } else {
                this.timeLeft--;
            }
            if (this.sessionTimeLeft > 0) {
                this.sessionTimeLeft--;
            }
        },
        formatSessionTime() {
            if (this.sessionTimeLeft <= 0) return '0 phút';
            let m = Math.floor(this.sessionTimeLeft / 60);
            let s = this.sessionTimeLeft % 60;
            if (m > 0) {
                return m + 'p ' + s + 's';
            }
            return s + 's';
        }
    }"
    x-init="setInterval(() => tick(), 1000); window.listenRealtime && window.listenRealtime(@js($this->realtimeChannel()), () => $wire.$refresh(), 400)"
>
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b border-slate-100 pb-5">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-black text-slate-800" title="{{ $session->name }}">
                    {{ Str::limit($session->name, 40) }}
                </h1>
                <span class="inline-flex items-center gap-1.5 rounded-full {{ $isClosed ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-700' }} px-3 py-1 text-xs font-bold">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isClosed ? 'bg-slate-400' : 'bg-emerald-500 animate-pulse' }}"></span>
                    {{ $isClosed ? 'Đã kết thúc' : 'Đang hoạt động' }}
                </span>
            </div>
            <p class="mt-2 text-sm font-medium text-slate-500">
                {{ $session->courseClass->name }} • {{ $sessionDateLabel }} @if($session->start_time) • {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('lecturer.attendance.qr.create', ['edit_session' => $session->id]) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 {{ $isClosed ? 'pointer-events-none opacity-60' : '' }}">
                Thiết lập
            </a>
            <button type="button" wire:click="refreshToken" @disabled($isClosed) class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50">
                Làm mới QR
            </button>
            <x-user.export-button action="exportExcel" label="Xuất Excel" :can="$canExportExcel" />
            @if(!$isClosed)
            <button type="button" @click="showEndModal = true" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-rose-700">
                Kết thúc phiên
            </button>
            @endif
        </div>
    </div>



    <div class="grid gap-6 lg:grid-cols-12 mt-6">
        <!-- Left Column: QR Code -->
        <div class="lg:col-span-4 rounded-2xl border-4 border-slate-200 bg-white p-6 shadow-sm flex flex-col h-full">
            <div class="w-full flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-800">Mã Điểm Danh</h2>
                <button type="button" @click="showQrModal = true" class="rounded-lg p-1.5 text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Trình chiếu QR lớn">
                    <x-user.icon name="projector" :size="20" />
                </button>
            </div>
            
            <button type="button" @click="showQrModal = true" class="w-[180px] aspect-square mx-auto bg-slate-50/50 border border-slate-100 rounded-2xl p-4 flex items-center justify-center mb-5 hover:scale-[1.02] transition">
                @if ($qrSvg)
                    <div class="w-full h-full [&>svg]:w-full [&>svg]:h-full">{!! $qrSvg !!}</div>
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                        <x-user.icon name="lock" :size="48" />
                        <span class="mt-2 text-sm font-semibold">Phiên đã chốt</span>
                    </div>
                @endif
            </button>

            <!-- Text Token -->
            <button type="button" @click="copyShareCode()" class="group w-full mt-auto bg-blue-50/50 border border-blue-100 rounded-xl p-3 text-center mb-4 transition hover:bg-blue-100/80 active:scale-[0.98]" :title="shareCopied ? 'Đã sao chép' : 'Sao chép liên kết'">
                <div class="flex items-center justify-center gap-2">
                    <span class="text-sm font-bold text-blue-600 truncate px-2">{{ $attendanceLink }}</span>
                    <div class="text-blue-400 group-hover:text-blue-600 transition shrink-0 flex items-center gap-1 bg-white/50 px-2 py-1 rounded-md">
                        <template x-if="!shareCopied">
                            <div class="flex items-center gap-1">
                                <x-user.icon name="copy" :size="16" />
                                <span class="text-xs font-bold">Copy</span>
                            </div>
                        </template>
                        <template x-if="shareCopied">
                            <div class="flex items-center gap-1 text-emerald-500">
                                <x-user.icon name="check" :size="16" />
                                <span class="text-xs font-bold">Đã copy</span>
                            </div>
                        </template>
                    </div>
                </div>
            </button>

            <!-- Progress Bar -->
            @if(!$isClosed)
            <div class="w-full">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-slate-500">Làm mới sau</span>
                    <span class="text-xs font-bold text-slate-800" x-text="String(timeLeft).padStart(2, '0') + 's'"></span>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-blue-600 transition-all duration-1000 ease-linear rounded-full" :style="'width: ' + ((timeLeft / refreshRate) * 100) + '%'"></div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Stats & Fraud -->
        <div class="lg:col-span-8 flex flex-col justify-between gap-6 h-full">
            <!-- Progress Card -->
            <div class="rounded-3xl bg-gradient-to-br from-indigo-50/80 via-white to-white border border-indigo-100 p-6 lg:p-8 text-slate-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6 overflow-hidden relative">
                <!-- Subtle decorative background -->
                <div class="absolute -top-32 -right-32 w-72 h-72 bg-blue-50 rounded-full blur-3xl pointer-events-none"></div>

                <div class="z-10 w-full">
                    <h3 class="text-slate-500 font-bold uppercase tracking-wider text-sm mb-3 flex items-center gap-2">
                        <x-user.icon name="users" :size="18" class="text-blue-500" />
                        Tiến độ điểm danh
                    </h3>
                    <div class="flex items-baseline gap-2 mb-4">
                        <span class="text-[5rem] font-black tracking-tight leading-none text-blue-600">{{ $summary['checked_in'] }}</span>
                        <span class="text-4xl font-black text-slate-500">/{{ $summary['total'] }}</span>
                        <span class="text-lg font-extrabold text-slate-600 ml-1">Sinh viên</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-sm text-slate-600">
                        <x-user.icon name="clock" :size="16" class="text-amber-500" />
                        Còn <span class="font-bold text-slate-800">{{ max(0, $summary['total'] - $summary['checked_in']) }}</span> sinh viên chưa có mặt
                    </div>
                </div>
                
                <!-- Circular Progress -->
                @php
                    $pct = $summary['total'] > 0 ? round(($summary['checked_in'] / $summary['total']) * 100) : 0;
                    $dasharray = 283; 
                    $dashoffset = $dasharray - ($dasharray * $pct) / 100;
                @endphp
                <div class="relative flex items-center justify-center h-36 w-36 shrink-0 z-10">
                    <svg class="h-full w-full -rotate-90 transform" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="45" class="stroke-slate-100" stroke-width="8" fill="none" />
                        <circle cx="50" cy="50" r="45" class="stroke-blue-500 transition-all duration-1000 ease-out" stroke-width="8" fill="none" stroke-linecap="round" stroke-dasharray="{{ $dasharray }}" stroke-dashoffset="{{ $dashoffset }}" />
                    </svg>
                    <div class="absolute flex flex-col items-center justify-center">
                        <span class="text-3xl font-black text-blue-600">{{ $pct }}%</span>
                    </div>
                </div>
            </div>

            <!-- Fraud Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Ngoài bán kính GPS (vẫn điểm danh, cảnh báo vàng) -->
                <button type="button" @if(($fraudStats['out_of_radius'] ?? 0) > 0) wire:click="setStatusFilter('{{ $statusFilter === 'out_of_radius' ? 'all' : 'out_of_radius' }}')" @endif
                    @class([
                        'rounded-xl border bg-white shadow-sm flex overflow-hidden transition hover:bg-slate-50 text-left w-full',
                        'border-amber-400 ring-2 ring-amber-200' => $statusFilter === 'out_of_radius',
                        'border-slate-200' => $statusFilter !== 'out_of_radius',
                    ])>
                    <div class="w-1.5 bg-gradient-to-b from-amber-400 to-orange-500"></div>
                    <div class="p-6 lg:py-8 flex gap-5 flex-1 items-center">
                        <div class="h-12 w-12 shrink-0 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500">
                            <x-user.icon name="map-pin" :size="24" />
                        </div>
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wider text-slate-600">Ngoài bán kính</p>
                            <h3 class="text-3xl font-black text-amber-500 mt-1">{{ $fraudStats['out_of_radius'] ?? 0 }}</h3>
                            @if(($fraudStats['out_of_radius'] ?? 0) > 0)
                                <p class="mt-0.5 text-xs font-semibold text-amber-600">Vẫn điểm danh · bấm để lọc</p>
                            @endif
                        </div>
                    </div>
                </button>

                <!-- Trùng máy (điểm danh cùng 1 máy) -->
                <button type="button" @if(($sameDeviceCount ?? 0) > 0) wire:click="setStatusFilter('{{ $statusFilter === 'same_device' ? 'all' : 'same_device' }}')" @endif
                    @class([
                        'rounded-xl border bg-white shadow-sm flex overflow-hidden transition hover:bg-slate-50 text-left w-full',
                        'border-rose-400 ring-2 ring-rose-200' => $statusFilter === 'same_device',
                        'border-slate-200' => $statusFilter !== 'same_device',
                    ])>
                    <div class="w-1.5 bg-gradient-to-b from-rose-400 to-rose-600"></div>
                    <div class="p-6 lg:py-8 flex gap-5 flex-1 items-center">
                        <div class="h-12 w-12 shrink-0 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500">
                            <x-user.icon name="laptop" :size="24" />
                        </div>
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wider text-slate-600">Điểm danh cùng 1 máy</p>
                            <h3 class="text-3xl font-black text-rose-500 mt-1">{{ $sameDeviceCount ?? 0 }}</h3>
                            @if(($sameDeviceCount ?? 0) > 0)
                                <p class="mt-0.5 text-xs font-semibold text-rose-500">Bấm để lọc các SV dùng chung máy</p>
                            @endif
                        </div>
                    </div>
                </button>
            </div>


        </div>
    </div>



    @include('components.lecturer.attendance.student-list', ['showMarkAllPresent' => false])



    <template x-teleport="body">
        <div x-cloak x-show="showClassSettingsModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/40 p-4">
            <div @click.outside="showClassSettingsModal = false" x-transition.scale class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-6">
                    <div class="flex gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <x-user.icon name="settings" :size="24" />
                        </span>
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Cài đặt lớp</h2>
                            <p class="mt-1 text-sm font-medium text-slate-500">Thông tin lớp và quy tắc đang áp dụng cho phiên điểm danh này.</p>
                        </div>
                    </div>
                    <button type="button" @click="showClassSettingsModal = false" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900" title="Đóng">
                        <x-user.icon name="x" :size="20" />
                    </button>
                </div>

                <div class="grid gap-4 p-6 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Lớp học</p>
                        <p class="mt-2 text-base font-black text-slate-900">{{ $session->courseClass->name }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ $session->courseClass->join_key }} - {{ $selectedSubject }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Buổi học</p>
                        <p class="mt-2 text-base font-black text-slate-900">{{ $session->name }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ $sessionDateLabel }}</p>
                    </div>

                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                        <p class="flex items-center gap-2 text-sm font-black text-blue-700">
                            <x-user.icon name="qr-code" :size="18" />
                            Làm mới mã
                        </p>
                        <p class="mt-2 text-sm font-semibold text-blue-800/80">QR tự đổi sau {{ $qrRefreshRate }} giây, phiên còn khoảng {{ $openMinutes }} phút.</p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="flex items-center gap-2 text-sm font-black text-emerald-700">
                            <x-user.icon name="shield-check" :size="18" />
                            Trạng thái phiên
                        </p>
                        <p class="mt-2 text-sm font-semibold text-emerald-800/80">{{ $isClosed ? 'Đã chốt, học viên không thể quét thêm mã.' : 'Đang mở, học viên có thể quét mã để điểm danh.' }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <a href="{{ route('lecturer.attendance.qr.create') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Chỉnh thiết lập
                    </a>
                    <button type="button" @click="showClassSettingsModal = false" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700">Hoàn tất</button>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-cloak x-show="showShareCodeModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/40 p-4">
            <div @click.outside="showShareCodeModal = false" x-transition.scale class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-6">
                    <div class="flex gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <x-user.icon name="send" :size="24" />
                        </span>
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Chia sẻ mã điểm danh</h2>
                            <p class="mt-1 text-sm font-medium text-slate-500">Gửi mã hoặc liên kết này cho học viên trong lớp.</p>
                        </div>
                    </div>
                    <button type="button" @click="showShareCodeModal = false" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900" title="Đóng">
                        <x-user.icon name="x" :size="20" />
                    </button>
                </div>

                <div class="space-y-4 p-6">
                    @if(!$isClosed)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Liên kết điểm danh</p>
                            <p class="mt-2 break-all text-sm font-bold text-slate-700">{{ $attendanceLink }}</p>
                        </div>

                        <button type="button" @click="copyShareCode()" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm shadow-blue-500/20 transition hover:bg-blue-700">
                            <x-user.icon name="clipboard-check" :size="18" />
                            <span x-text="shareCopied ? 'Đã sao chép liên kết' : 'Sao chép liên kết'"></span>
                        </button>
                    @else
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 text-slate-500">
                                <x-user.icon name="lock" :size="24" />
                            </div>
                            <p class="mt-3 text-sm font-bold text-slate-600">Phiên đã đóng. Không thể chia sẻ liên kết mới.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-cloak x-show="showEndModal" x-transition.opacity class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/40 p-4">
            <div @click.outside="showEndModal = false" x-transition.scale class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <h2 class="mt-2 text-lg font-black text-slate-900">Chốt phiên điểm danh?</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-500">
                    Phiên {{ $session->name }} sẽ dừng nhận QR mới. Bạn vẫn có thể rà soát lại trạng thái học viên trước khi lưu báo cáo.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showEndModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Hủy</button>
                    <button type="button" wire:click="closeSession" @click="showEndModal = false" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-amber-600">Chốt phiên</button>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-cloak x-show="showQrModal" x-transition.opacity class="fixed inset-0 z-[9999] overflow-y-auto bg-white">
            <button type="button" @click="showQrModal = false" class="absolute right-5 top-5 z-10 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-200 hover:text-slate-900" title="Đóng">
                <x-user.icon name="x" :size="26" />
            </button>

            <div class="grid min-h-screen w-full grid-cols-1 md:grid-cols-2">
                {{-- Bên trái: thông tin lớp + phiên buổi điểm danh --}}
                <div class="flex flex-col justify-center gap-8 border-b border-slate-100 p-10 sm:p-14 lg:p-20 md:border-b-0 md:border-r">
                    <div class="inline-flex w-fit items-center gap-2 rounded-full bg-blue-50 px-5 py-2 text-sm font-bold uppercase tracking-widest text-blue-600 ring-1 ring-blue-100">
                        <x-user.icon name="qr-code" :size="16" />
                        Điểm danh
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Lớp học</p>
                        <h2 class="mt-2 text-4xl font-black leading-tight text-slate-900 lg:text-6xl">{{ $session->courseClass->name }}</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                <x-user.icon name="calendar-check" :size="22" />
                            </span>
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Buổi điểm danh</p>
                                <p class="text-lg font-bold text-slate-800">{{ Str::limit($session->name, 40) }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                <x-user.icon name="clock" :size="22" />
                            </span>
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Thời gian</p>
                                <p class="text-lg font-bold text-slate-800">{{ $sessionDateLabel }} @if($session->start_time) • {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} @endif</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-100">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <x-user.icon name="scan-line" :size="22" />
                        </span>
                        <p class="text-base font-medium leading-relaxed text-slate-600">
                            Học viên mở ứng dụng, chọn <span class="font-bold text-slate-800">Quét QR</span> rồi đưa camera vào mã bên cạnh để được ghi nhận có mặt.
                        </p>
                    </div>
                </div>

                {{-- Bên phải: mã QR --}}
                <div class="flex flex-col items-center justify-center gap-8 bg-slate-50/50 p-10 sm:p-14 lg:p-20">
                    <div class="w-fit rounded-[32px] border border-slate-100 bg-white p-6 shadow-2xl shadow-slate-900/10 [&>svg]:h-64 [&>svg]:w-64 sm:[&>svg]:h-80 sm:[&>svg]:w-80 lg:[&>svg]:h-[28rem] lg:[&>svg]:w-[28rem]">
                        @if ($qrSvg)
                            {!! $qrSvg !!}
                        @else
                            <div class="flex h-64 w-64 flex-col items-center justify-center text-slate-400 sm:h-80 sm:w-80 lg:h-[28rem] lg:w-[28rem]">
                                <x-user.icon name="lock" :size="72" />
                                <span class="mt-3 text-base font-bold">Phiên đã chốt</span>
                            </div>
                        @endif
                    </div>

                    @if(!$isClosed)
                        <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-5 py-2.5 text-base font-bold text-blue-600 ring-1 ring-blue-100">
                            <span class="relative flex h-3 w-3">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex h-3 w-3 rounded-full bg-blue-500"></span>
                            </span>
                            Mã tự làm mới sau <span x-text="String(timeLeft).padStart(2, '0')"></span>s
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </template>

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
</div>
