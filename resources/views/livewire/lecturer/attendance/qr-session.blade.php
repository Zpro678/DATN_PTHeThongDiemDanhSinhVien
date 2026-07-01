@php
    $isClosed = $session->status === 'closed';
    $selectedSubject = $session->courseClass->subject_code ?: $session->courseClass->join_key;
    $sessionDateLabel = $session->date->format('d/m/Y');
    $openMinutes = max(1, (int) now()->diffInMinutes($session->token_expires_at ?? now()->addMinutes(15), false));
    $qrRefreshRate = $session->qr_refresh_rate ?? 10;
    $statusMeta = [
        'pending' => ['label' => 'Chưa điểm danh', 'pill' => 'border-slate-200 bg-slate-100 text-slate-600'],
        'present' => ['label' => 'Có mặt', 'pill' => 'border-emerald-200 bg-emerald-100 text-emerald-700'],
        'late' => ['label' => 'Đi muộn', 'pill' => 'border-amber-200 bg-amber-100 text-amber-700'],
        'absent' => ['label' => 'Vắng', 'pill' => 'border-rose-200 bg-rose-100 text-rose-700'],
        'excused' => ['label' => 'Có phép', 'pill' => 'border-sky-200 bg-sky-100 text-sky-700'],
    ];
@endphp

<div
    class="w-full space-y-5 sm:space-y-8 px-6 py-6 sm:px-10 lg:px-16 pb-24"
    x-data="{
        isClosed: @entangle('isClosed').live,
        timeLeft: {{ $qrRefreshRate }},
        refreshRate: {{ $qrRefreshRate }},
        sessionTimeLeft: {{ max(0, (int) now()->diffInSeconds($session->token_expires_at ?? now()->addMinutes($session->open_minutes ?? 15), false)) }},
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
    x-init="setInterval(() => tick(), 1000)"
>
    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-[30px] font-black leading-tight tracking-tight text-slate-900" title="{{ $session->name }}">
                    <span class="sr-only">Trạm chờ điểm danh</span>
                    {{ Str::limit($session->name, 40) }}
                </h1>
                <span @class([
                    'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider',
                    'border-slate-200 bg-slate-100 text-slate-600' => $isClosed,
                    'border-blue-200 bg-blue-50 text-blue-700' => ! $isClosed,
                ])>
                    <span @class([
                        'h-2 w-2 rounded-full',
                        'bg-slate-400' => $isClosed,
                        'animate-pulse bg-blue-600' => ! $isClosed,
                    ])></span>
                    {{ $isClosed ? 'Đã chốt' : 'Đang hoạt động' }}
                </span>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3 text-sm font-bold text-slate-600">
                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                    <x-user.icon name="school" :size="16" class="text-blue-600" />
                    {{ $session->courseClass->name }} - {{ $selectedSubject }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                    <x-user.icon name="calendar" :size="16" class="text-slate-500" />
                    {{ $sessionDateLabel }}
                </span>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            @if(!$isClosed)
                <button type="button" @click="deleteModalOpen = true" class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-5 py-2.5 text-sm font-semibold text-rose-600 shadow-sm transition hover:bg-rose-50">
                    <x-user.icon name="trash" :size="16" />
                    Xóa phiên
                </button>
            @endif

            <a href="{{ route('lecturer.attendance.qr.create', ['edit_session' => $session->id]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 {{ $isClosed ? 'pointer-events-none opacity-60' : '' }}">
                <x-user.icon name="settings" :size="16" />
                Chỉnh sửa thiết lập
            </a>

            <button type="button" wire:click="refreshToken" @disabled($isClosed) class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-white px-5 py-2.5 text-sm font-bold text-blue-700 shadow-sm transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-60">
                <x-user.icon name="qr-code" :size="16" />
                Làm mới mã
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif



    <div class="grid gap-4 sm:gap-6 xl:grid-cols-12">
        <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm xl:col-span-4 flex flex-col min-w-0 overflow-hidden">
            <div class="flex h-full w-full flex-col items-center flex-1 min-w-0">
                @if(!$isClosed)
                    <div class="mb-4 sm:mb-6 flex w-full gap-2 sm:gap-3">
                        <div class="flex flex-1 items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-3 sm:px-4 py-2 sm:py-3 min-w-0">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Liên kết</p>
                                    <button type="button" @click="copyShareCode()" class="shrink-0 rounded-md p-1 text-slate-400 transition hover:bg-slate-200 hover:text-blue-600" :title="shareCopied ? 'Đã sao chép' : 'Sao chép liên kết'">
                                        <x-user.icon name="copy" :size="14" x-show="!shareCopied" />
                                        <x-user.icon name="check" :size="14" x-show="shareCopied" class="text-emerald-500" x-cloak />
                                    </button>
                                </div>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <p class="truncate text-sm font-bold text-blue-700">{{ $attendanceLink }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-1 items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-3 sm:px-4 py-2 sm:py-3 min-w-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Mã phiên</p>
                                <p class="mt-1 text-sm font-black tracking-widest text-blue-700">{{ \Illuminate\Support\Str::limit($session->qr_token, 8, '') }}</p>
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="showQrModal = true" class="group relative my-4 flex h-52 w-52 sm:h-64 sm:w-64 items-center justify-center rounded-[24px] sm:rounded-[28px] border border-slate-200 bg-white p-4 sm:p-5 shadow-xl shadow-slate-900/10 transition hover:scale-[1.02]">
                        <span class="absolute -inset-4 rounded-[36px] bg-blue-500/10 blur-2xl transition group-hover:bg-blue-500/20"></span>
                        @if ($qrSvg)
                            <span class="relative flex h-full w-full min-w-0 min-h-0 items-center justify-center rounded-2xl bg-white p-2 [&>svg]:h-full [&>svg]:w-full [&>svg]:max-w-full [&>svg]:max-h-full">
                                {!! $qrSvg !!}
                            </span>
                        @else
                            <span class="relative grid h-full w-full gap-[1px] sm:gap-[3px] rounded-2xl bg-white p-1.5 sm:p-3" style="grid-template-columns: repeat(29, minmax(0, 1fr));">
                                @foreach ($qrCells as $isDark)
                                    <span class="{{ $isDark ? 'bg-slate-900' : 'bg-white' }} w-full aspect-square rounded-[1px] sm:rounded-sm"></span>
                                @endforeach
                            </span>
                        @endif
                        <span class="absolute -bottom-3 -right-3 flex h-11 w-11 items-center justify-center rounded-2xl border-4 border-white bg-blue-600 text-white shadow-lg">
                            <x-user.icon name="qr-code" :size="20" />
                        </span>
                    </button>
                @else
                    <div class="my-auto flex flex-col items-center justify-center text-center">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <x-user.icon name="lock" :size="36" />
                        </div>
                        <h3 class="mt-4 text-xl font-black text-slate-800">Phiên đã chốt</h3>
                        <p class="mt-2 max-w-xs text-sm font-semibold text-slate-500">Mã QR và Liên kết đã bị vô hiệu hóa. Học viên không thể tiếp tục điểm danh.</p>
                    </div>
                @endif

                @if(!$isClosed)
                    <div class="mt-auto w-full space-y-2.5 sm:space-y-3 pt-4 sm:pt-7">
                        <div class="flex items-end justify-between">
                            <span class="text-xs sm:text-sm font-extrabold text-slate-500">Mã mới sau</span>
                            <span class="text-2xl sm:text-3xl font-black leading-none text-blue-600" x-text="String(timeLeft).padStart(2, '0') + 's'"></span>
                        </div>
                        <div class="h-2 sm:h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-blue-600 transition-all duration-1000 ease-linear" :style="'width: ' + ((timeLeft / refreshRate) * 100) + '%'"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:gap-3 pt-2 sm:pt-3 text-sm">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-2 sm:p-3">
                                <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Thời lượng</p>
                                <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm font-black text-slate-900" x-text="formatSessionTime()">{{ $openMinutes }} phút</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-2 sm:p-3">
                                <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Làm mới QR</p>
                                <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm font-black text-slate-900">{{ $qrRefreshRate }} giây</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <section class="space-y-4 sm:space-y-6 xl:col-span-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <div class="flex flex-col gap-5 md:flex-row md:items-center">
                    <div class="shrink-0 md:border-r md:border-slate-200 md:pr-6">
                        <div class="flex items-center gap-3">
                            <x-user.icon name="shield-alert" :size="24" class="text-amber-500" />
                            <h2 class="text-base font-black text-slate-900">Rủi ro báo cáo</h2>
                        </div>
                        <p class="mt-1.5 text-sm font-semibold text-slate-400">Hệ thống ghi nhận tức thời</p>
                    </div>

                    <div class="grid flex-1 gap-2 sm:gap-4 grid-cols-2">
                        <div class="flex items-center gap-2 sm:gap-4 rounded-xl border border-rose-100 bg-rose-50 p-2 sm:p-4 overflow-hidden">
                            <span class="flex h-7 w-7 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl bg-white text-rose-600 shadow-sm">
                                <x-user.icon name="shield-alert" :size="24" class="hidden sm:block" />
                                <x-user.icon name="shield-alert" :size="14" class="block sm:hidden" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] sm:text-base font-black text-rose-700 truncate">Sai GPS (0)</p>
                                <p class="text-[9px] sm:text-sm font-bold text-rose-500 truncate">Cần xem xét</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-4 rounded-xl border border-amber-100 bg-amber-50 p-2 sm:p-4 overflow-hidden">
                            <span class="flex h-7 w-7 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl bg-white text-amber-600 shadow-sm">
                                <x-user.icon name="laptop" :size="24" class="hidden sm:block" />
                                <x-user.icon name="laptop" :size="14" class="block sm:hidden" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] sm:text-base font-black text-amber-700 truncate">Trùng máy (0)</p>
                                <p class="text-[9px] sm:text-sm font-bold text-amber-500 truncate">Điểm danh hộ</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            <div class="rounded-xl sm:rounded-2xl bg-primary p-5 sm:p-8 text-white shadow-xl shadow-blue-900/10 ring-1 ring-blue-800/50">
                <div class="grid gap-6 sm:gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.8fr)] lg:items-end">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-blue-200">Sĩ số hiện diện</span>
                            <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-blue-700 shadow-sm">{{ $summary['checked_in_percent'] }}% lớp</span>
                        </div>

                        <div class="mt-4 flex items-baseline gap-2">
                            <span class="text-6xl sm:text-[4.5rem] font-black leading-none tracking-tighter text-white drop-shadow-sm lg:text-7xl">{{ number_format($summary['checked_in']) }}</span>
                            <span class="text-xl sm:text-2xl font-bold text-blue-300 drop-shadow-sm">/ {{ number_format($summary['total']) }}</span>
                        </div>

                        <div class="mt-6 max-w-xl">
                            <div class="mb-2 flex items-center justify-between text-[11px] font-bold uppercase tracking-widest text-blue-200">
                                <span>Tiến độ điểm danh</span>
                                <span class="text-white">{{ number_format($summary['checked_in']) }} đã xác nhận</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-blue-800/50 ring-1 ring-inset ring-blue-900/30">
                                <div class="h-full rounded-full bg-white transition-all duration-500 shadow-[0_0_10px_rgba(255,255,255,0.5)]" style="width: {{ $summary['checked_in_percent'] }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:gap-4 grid-cols-2">
                        <div class="rounded-2xl bg-white/10 p-4 sm:p-5 backdrop-blur-md ring-1 ring-inset ring-white/20 col-span-2 shadow-sm">
                            <p class="mb-3 sm:mb-4 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-blue-200">Thông tin phiên</p>
                            <div class="grid gap-2 sm:gap-3 grid-cols-2 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,0.9fr)]">
                                <div class="min-w-0 rounded-xl bg-white/10 px-3 sm:px-4 py-2.5 sm:py-3 ring-1 ring-inset ring-white/10 transition-colors hover:bg-white/20">
                                    <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-blue-200 truncate">Buổi học</p>
                                    <p class="mt-1 text-xs sm:text-sm font-bold leading-snug text-white truncate">{{ $session->name }}</p>
                                </div>

                                <div class="min-w-0 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-inset ring-white/10 transition-colors hover:bg-white/20">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-blue-200">Lớp / Buổi</p>
                                    <p class="mt-1 text-sm font-bold leading-snug text-white">{{ $selectedSubject }} - {{ Str::limit($session->name, 30) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/10 p-4 sm:p-5 backdrop-blur-md ring-1 ring-inset ring-white/20 shadow-sm min-w-0">
                            <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-widest text-blue-200 truncate">Mở phiên</p>
                            <p class="mt-1.5 text-sm sm:text-lg font-bold text-white drop-shadow-sm truncate" x-text="formatSessionTime()">{{ $openMinutes }} phút</p>
                        </div>

                        <div class="rounded-2xl bg-white/10 p-4 sm:p-5 backdrop-blur-md ring-1 ring-inset ring-white/20 shadow-sm min-w-0">
                            <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-widest text-blue-200 truncate">Làm mới mã</p>
                            <p class="mt-1.5 text-sm sm:text-lg font-bold text-white drop-shadow-sm truncate">{{ $qrRefreshRate }}s/lần</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:gap-4 grid-cols-2 xl:grid-cols-4">
                <button type="button" wire:click="setStatusFilter('pending')" @class(['rounded-2xl border border-slate-200 bg-white p-2.5 sm:p-5 text-left shadow-sm transition hover:-translate-y-0.5 min-w-0', 'ring-2 ring-primary/30' => $statusFilter === 'pending'])>
                    <p class="text-[9px] sm:text-[11px] font-black uppercase tracking-wider text-slate-500 truncate">Đang chờ</p>
                    <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black text-slate-900">{{ number_format($summary['pending']) }}</p>
                    <p class="mt-0.5 sm:mt-1 text-[8px] sm:text-xs font-semibold text-slate-500 truncate">Chưa quét QR</p>
                </button>

                <button type="button" wire:click="setStatusFilter('late')" @class(['rounded-2xl border border-amber-100 bg-amber-50 p-2.5 sm:p-5 text-left transition hover:-translate-y-0.5 min-w-0', 'ring-2 ring-amber-300' => $statusFilter === 'late'])>
                    <p class="text-[9px] sm:text-[11px] font-black uppercase tracking-wider text-amber-700 truncate">Đi muộn</p>
                    <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black text-amber-700">{{ number_format($summary['late']) }}</p>
                    <p class="mt-0.5 sm:mt-1 text-[8px] sm:text-xs font-semibold text-amber-600 truncate">Cần ghi chú</p>
                </button>

                <button type="button" wire:click="setStatusFilter('absent')" @class(['rounded-2xl border border-rose-100 bg-rose-50 p-2.5 sm:p-5 text-left transition hover:-translate-y-0.5 min-w-0', 'ring-2 ring-rose-300' => $statusFilter === 'absent'])>
                    <p class="text-[9px] sm:text-[11px] font-black uppercase tracking-wider text-rose-700 truncate">Vắng</p>
                    <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black text-rose-700">{{ number_format($summary['absent']) }}</p>
                    <p class="mt-0.5 sm:mt-1 text-[8px] sm:text-xs font-semibold text-rose-600 truncate">Chưa xác nhận</p>
                </button>

                <button type="button" wire:click="setStatusFilter('excused')" @class(['rounded-2xl border border-sky-100 bg-sky-50 p-2.5 sm:p-5 text-left transition hover:-translate-y-0.5 min-w-0', 'ring-2 ring-sky-300' => $statusFilter === 'excused'])>
                    <p class="text-[9px] sm:text-[11px] font-black uppercase tracking-wider text-sky-800 truncate">Có phép</p>
                    <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black text-sky-800">{{ number_format($summary['excused']) }}</p>
                    <p class="mt-0.5 sm:mt-1 text-[8px] sm:text-xs font-semibold text-sky-700 truncate">Đã gửi lý do</p>
                </button>
            </div>
        </section>

    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900">Danh sách quản lý</h2>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[520px] sm:flex-row">
                <label class="relative flex-1">
                    <x-user.icon name="search" :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm MSSV, tên..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm font-semibold outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" />
                </label>
                @if ($search !== '' || $statusFilter !== 'all')
                    <button type="button" wire:click="clearSearch" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Xóa lọc</button>
                @endif
                @if ($canExportExcel)
                    <button type="button" wire:click="exportExcel" class="group relative inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md">
                        <x-user.icon name="download" :size="16" class="text-slate-400 transition-colors group-hover:text-blue-600" />
                        <span>Xuất Excel</span>
                        <span class="absolute -right-2 -top-2.5 flex items-center justify-center">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-200 opacity-30"></span>
                            <span class="relative flex h-7 w-7 items-center justify-center rounded-full border border-amber-200 bg-white shadow-sm">
                                <span class="text-base leading-none">👑</span>
                            </span>
                        </span>
                    </button>
                @else
                    <a href="{{ route('upgrade') }}" title="Nâng cấp lên gói Pro để xuất báo cáo Excel"
                        class="group relative inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-5 py-2.5 text-sm font-bold text-amber-700 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-amber-100">
                        <x-user.icon name="download" :size="16" />
                        <span>Xuất Excel</span>
                        <span class="rounded-full bg-amber-200 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800">Pro</span>
                    </a>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead class="bg-slate-50 text-sm font-black uppercase tracking-wider text-slate-900">
                    <tr>
                        <th class="py-3 pl-14 pr-5">MSSV</th>
                        <th class="py-3 pl-14 pr-5">Học viên</th>
                        <th class="px-5 py-3 text-center">Trạng thái</th>
                        <th class="px-5 py-3 text-center">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($records as $record)
                        @php
                            $status = $record->status;
                            $meta = $statusMeta[$status] ?? $statusMeta['pending'];
                            $nameParts = array_values(array_filter(preg_split('/\s+/', trim($record->classMember->full_name)) ?: []));
                            $firstInitial = $nameParts[0] ?? 'S';
                            $lastInitial = count($nameParts) > 1 ? $nameParts[count($nameParts) - 1] : '';
                            $initials = function_exists('mb_strtoupper')
                                ? mb_strtoupper(mb_substr($firstInitial, 0, 1, 'UTF-8').mb_substr($lastInitial, 0, 1, 'UTF-8'), 'UTF-8')
                                : strtoupper(substr($firstInitial, 0, 1).substr($lastInitial, 0, 1));
                        @endphp

                        <tr class="transition hover:bg-slate-50/70">
                            <td class="py-4 pl-14 pr-5 font-mono text-sm font-extrabold text-slate-600">{{ $record->classMember->student_code }}</td>
                            <td class="py-4 pl-14 pr-5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-blue-100 bg-blue-50 text-xs font-black text-blue-700">
                                        {{ $initials }}
                                    </span>
                                    <span class="font-extrabold text-slate-900">{{ $record->classMember->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="{{ $meta['pill'] }} inline-flex rounded-full border px-3 py-1 text-xs font-bold">{{ $meta['label'] }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @foreach ([
                                        'present' => ['icon' => 'calendar-check', 'title' => 'Có mặt', 'class' => 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'],
                                        'late' => ['icon' => 'history', 'title' => 'Đi muộn', 'class' => 'bg-amber-50 text-amber-600 hover:bg-amber-100'],
                                        'absent' => ['icon' => 'x', 'title' => 'Đánh vắng', 'class' => 'bg-rose-50 text-rose-600 hover:bg-rose-100'],
                                        'excused' => ['icon' => 'shield-check', 'title' => 'Có phép', 'class' => 'bg-sky-50 text-sky-600 hover:bg-sky-100'],
                                    ] as $option => $button)
                                        <button type="button" wire:click="updateStatus({{ $record->id }}, '{{ $option }}')" title="{{ $button['title'] }}" @disabled($isClosed) class="{{ $button['class'] }} flex h-9 w-9 items-center justify-center rounded-xl transition disabled:cursor-not-allowed disabled:opacity-50">
                                            <x-user.icon :name="$button['icon']" :size="16" />
                                        </button>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-sm font-semibold text-slate-500">
                                Không tìm thấy học viên phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="border-t border-slate-200 bg-white p-4">
                {{ $records->links() }}
            </div>
        @endif
    </section>

    <div class="mt-6 flex items-center justify-between gap-3">
        <a href="{{ route('lecturer.attendance.meeting.sessions', $session->meeting_id) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <x-user.icon name="arrow-left" :size="18" />
            Quay lại
        </a>
        <button type="button" wire:click="saveSession" wire:loading.attr="disabled" wire:target="saveSession" class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-500/30 transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-60">
            <x-user.icon name="save" :size="18" />
            <span wire:loading.remove wire:target="saveSession">Lưu phiên</span>
            <span wire:loading wire:target="saveSession">Đang lưu...</span>
        </button>
    </div>

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
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                    <x-user.icon name="calendar-check" :size="24" />
                </div>
                <h2 class="mt-4 text-lg font-black text-slate-900">Chốt phiên điểm danh?</h2>
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
        <div x-cloak x-show="showQrModal" x-transition.opacity class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-white p-6">
            <button type="button" @click="showQrModal = false" class="absolute right-6 top-6 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900" title="Đóng">
                <x-user.icon name="x" :size="32" />
            </button>

            <div class="flex w-[min(90vw,600px)] items-center justify-center rounded-[36px] bg-white p-8 shadow-2xl shadow-slate-900/10 ring-1 ring-slate-200 [&>svg]:h-full [&>svg]:w-full">
                @if ($qrSvg)
                    {!! $qrSvg !!}
                @else
                    <div class="grid w-full gap-[6px]" style="grid-template-columns: repeat(29, minmax(0, 1fr));">
                        @foreach ($qrCells as $isDark)
                            <span class="{{ $isDark ? 'bg-slate-900' : 'bg-white' }} aspect-square rounded-[3px]"></span>
                        @endforeach
                    </div>
                @endif
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
