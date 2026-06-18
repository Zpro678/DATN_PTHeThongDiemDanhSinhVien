@php
    $isClosed = $session->status === 'closed';
    $selectedSubject = $session->courseClass->subject_code ?: $session->courseClass->code;
    $sessionDateLabel = $session->date->format('d/m/Y');
    $openMinutes = max(1, (int) now()->diffInMinutes($session->token_expires_at ?? now()->addMinutes(15), false));
    $qrRefreshRate = 10;
    $startLesson = 1;
    $endLesson = max(1, (int) $session->courseClass->lessons_per_session);
    $statusMeta = [
        'pending' => ['label' => 'Chưa điểm danh', 'pill' => 'border-slate-200 bg-slate-100 text-slate-600'],
        'present' => ['label' => 'Có mặt', 'pill' => 'border-emerald-200 bg-emerald-100 text-emerald-700'],
        'late' => ['label' => 'Đi muộn', 'pill' => 'border-amber-200 bg-amber-100 text-amber-700'],
        'absent' => ['label' => 'Vắng mặt', 'pill' => 'border-rose-200 bg-rose-100 text-rose-700'],
        'excused' => ['label' => 'Có phép', 'pill' => 'border-sky-200 bg-sky-100 text-sky-700'],
    ];
@endphp

<div
    class="mx-auto max-w-[1400px] space-y-8 p-4 pb-24 sm:p-8"
    x-data="{
        timeLeft: {{ $qrRefreshRate }},
        refreshRate: {{ $qrRefreshRate }},
        showEndModal: false,
        showQrModal: false,
        showClassSettingsModal: false,
        showShareCodeModal: false,
        shareCopied: false,
        copyShareCode() {
            navigator.clipboard?.writeText(@js($attendanceLink));
            this.shareCopied = true;
            setTimeout(() => this.shareCopied = false, 1800);
        },
        tick() {
            this.timeLeft = this.timeLeft <= 1 ? this.refreshRate : this.timeLeft - 1;
        }
    }"
    x-init="setInterval(() => tick(), 1000)"
>
    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-[30px] font-black leading-tight tracking-tight text-slate-900">Trạm chờ điểm danh</h1>
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
                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                    <x-user.icon name="history" :size="16" class="text-slate-500" />
                    Tiết {{ $startLesson }} - {{ $endLesson }}
                </span>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('lecturer.attendance.qr.create') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Quay lại thiết lập
            </a>

            <button type="button" wire:click="refreshToken" @disabled($isClosed) class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-white px-5 py-2.5 text-sm font-bold text-blue-700 shadow-sm transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-60">
                <x-user.icon name="qr-code" :size="16" />
                Làm mới mã
            </button>

            <button type="button" @click="showEndModal = true" @disabled($isClosed) class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-amber-500/30 transition hover:bg-amber-600 disabled:cursor-not-allowed disabled:opacity-60">
                <x-user.icon name="calendar-check" :size="16" />
                Chốt phiên này
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid gap-4 sm:grid-cols-2">
        <button
            type="button"
            @click="showClassSettingsModal = true"
            class="group flex min-h-[112px] flex-col items-center justify-center gap-3 rounded-[1.5rem] border border-slate-100 bg-white px-5 py-6 text-center shadow-sm shadow-slate-900/5 transition hover:-translate-y-0.5 hover:border-blue-100 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/10"
        >
            <x-user.icon name="settings" :size="32" class="text-slate-600 transition group-hover:text-blue-600" />
            <span class="text-lg font-bold text-slate-800">Cài đặt lớp</span>
        </button>

        <button
            type="button"
            @click="showShareCodeModal = true"
            class="group flex min-h-[112px] flex-col items-center justify-center gap-3 rounded-[1.5rem] border border-slate-100 bg-white px-5 py-6 text-center shadow-sm shadow-slate-900/5 transition hover:-translate-y-0.5 hover:border-blue-100 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/10"
        >
            <x-user.icon name="send" :size="34" class="text-blue-600 transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
            <span class="text-lg font-bold text-slate-800">Chia sẻ mã</span>
        </button>
    </section>

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-4">
            <div class="flex h-full flex-col items-center">
                <div class="mb-6 flex w-full items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Liên kết</p>
                        <p class="mt-1 truncate text-sm font-bold text-blue-700">{{ $attendanceLink }}</p>
                    </div>
                    <span class="shrink-0 rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 ring-1 ring-blue-100">{{ \Illuminate\Support\Str::limit($session->qr_token, 8, '') }}</span>
                </div>

                <button type="button" @click="showQrModal = true" class="group relative my-4 flex h-64 w-64 items-center justify-center rounded-[28px] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/10 transition hover:scale-[1.02]">
                    <span class="absolute -inset-4 rounded-[36px] bg-blue-500/10 blur-2xl transition group-hover:bg-blue-500/20"></span>
                    @if ($qrSvg)
                        <span class="relative flex h-full w-full items-center justify-center rounded-2xl bg-white p-2 [&>svg]:h-full [&>svg]:w-full">
                            {!! $qrSvg !!}
                        </span>
                    @else
                        <span class="relative grid h-full w-full gap-[3px] rounded-2xl bg-white p-3" style="grid-template-columns: repeat(29, minmax(0, 1fr));">
                            @foreach ($qrCells as $isDark)
                                <span class="{{ $isDark ? 'bg-slate-900' : 'bg-white' }} aspect-square rounded-[1px]"></span>
                            @endforeach
                        </span>
                    @endif
                    <span class="absolute -bottom-3 -right-3 flex h-11 w-11 items-center justify-center rounded-2xl border-4 border-white bg-blue-600 text-white shadow-lg">
                        <x-user.icon name="qr-code" :size="20" />
                    </span>
                </button>

                <div class="mt-auto w-full space-y-3 pt-7">
                    <div class="flex items-end justify-between">
                        <span class="text-sm font-extrabold text-slate-500">Mã mới sau</span>
                        <span class="text-3xl font-black leading-none text-blue-600" x-text="String(timeLeft).padStart(2, '0') + 's'"></span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-blue-600 transition-all duration-1000 ease-linear" :style="'width: ' + ((timeLeft / refreshRate) * 100) + '%'"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-3 text-sm">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Thời lượng</p>
                            <p class="mt-1 font-black text-slate-900">{{ $openMinutes }} phút</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Làm mới QR</p>
                            <p class="mt-1 font-black text-slate-900">{{ $qrRefreshRate }} giây</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-6 xl:col-span-8">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <button type="button" wire:click="setStatusFilter('pending')" @class(['rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5', 'ring-2 ring-primary/30' => $statusFilter === 'pending'])>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Đang chờ</p>
                    <p class="mt-2 text-4xl font-black text-slate-900">{{ number_format($summary['pending']) }}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-400">Chưa quét QR</p>
                </button>

                <button type="button" wire:click="setStatusFilter('late')" @class(['rounded-2xl border border-amber-100 bg-amber-50 p-5 text-left transition hover:-translate-y-0.5', 'ring-2 ring-amber-300' => $statusFilter === 'late'])>
                    <p class="text-[11px] font-black uppercase tracking-wider text-amber-600">Đi muộn</p>
                    <p class="mt-2 text-4xl font-black text-amber-600">{{ number_format($summary['late']) }}</p>
                    <p class="mt-1 text-xs font-semibold text-amber-500">Cần ghi chú</p>
                </button>

                <button type="button" wire:click="setStatusFilter('absent')" @class(['rounded-2xl border border-rose-100 bg-rose-50 p-5 text-left transition hover:-translate-y-0.5', 'ring-2 ring-rose-300' => $statusFilter === 'absent'])>
                    <p class="text-[11px] font-black uppercase tracking-wider text-rose-600">Vắng</p>
                    <p class="mt-2 text-4xl font-black text-rose-600">{{ number_format($summary['absent']) }}</p>
                    <p class="mt-1 text-xs font-semibold text-rose-500">Chưa xác nhận</p>
                </button>

                <button type="button" wire:click="setStatusFilter('excused')" @class(['rounded-2xl border border-sky-100 bg-sky-50 p-5 text-left transition hover:-translate-y-0.5', 'ring-2 ring-sky-300' => $statusFilter === 'excused'])>
                    <p class="text-[11px] font-black uppercase tracking-wider text-sky-700">Có phép</p>
                    <p class="mt-2 text-4xl font-black text-sky-700">{{ number_format($summary['excused']) }}</p>
                    <p class="mt-1 text-xs font-semibold text-sky-600">Đã gửi lý do</p>
                </button>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-blue-600 p-6 text-white shadow-sm shadow-blue-500/30">
                <div class="relative z-10 grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)] lg:items-end">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-blue-50 ring-1 ring-white/20">Sĩ số hiện diện</span>
                            <span class="inline-flex rounded-full bg-white px-3 py-1 text-[11px] font-black uppercase tracking-wider text-blue-700">{{ $summary['checked_in_percent'] }}% lớp</span>
                        </div>

                        <div class="mt-5 flex flex-wrap items-end gap-3">
                            <span class="text-7xl font-black leading-none tracking-tight">{{ number_format($summary['checked_in']) }}</span>
                            <span class="mb-2 text-2xl font-bold text-blue-100">/ {{ number_format($summary['total']) }}</span>
                        </div>

                        <div class="mt-5 max-w-xl">
                            <div class="mb-2 flex items-center justify-between text-xs font-bold uppercase tracking-wider text-blue-100">
                                <span>Tiến độ điểm danh</span>
                                <span>{{ number_format($summary['checked_in']) }} đã xác nhận</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-white/20">
                                <div class="h-full rounded-full bg-white" style="width: {{ $summary['checked_in_percent'] }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-white/12 p-4 ring-1 ring-white/15 sm:col-span-2">
                            <p class="text-[11px] font-black uppercase tracking-wider text-blue-100">Thông tin phiên</p>
                            <div class="mt-3 grid gap-3 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,0.9fr)]">
                                <div class="min-w-0 rounded-xl bg-white/10 px-3 py-2.5">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-blue-100/80">Buổi học</p>
                                    <p class="mt-1 break-words text-sm font-bold leading-5 text-white">{{ $session->name }}</p>
                                </div>

                                <div class="min-w-0 rounded-xl bg-white/10 px-3 py-2.5">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-blue-100/80">Lớp / Tiết</p>
                                    <p class="mt-1 break-words text-sm font-bold leading-5 text-white">{{ $selectedSubject }} - Tiết {{ $startLesson }}-{{ $endLesson }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/12 p-4 ring-1 ring-white/15">
                            <p class="text-[11px] font-black uppercase tracking-wider text-blue-100">Mở phiên</p>
                            <p class="mt-1 text-sm font-bold text-white">{{ $openMinutes }} phút</p>
                        </div>

                        <div class="rounded-2xl bg-white/12 p-4 ring-1 ring-white/15">
                            <p class="text-[11px] font-black uppercase tracking-wider text-blue-100">QR refresh</p>
                            <p class="mt-1 text-sm font-bold text-white">{{ $qrRefreshRate }} giây/lần</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-center">
                    <div class="shrink-0 md:border-r md:border-slate-200 md:pr-5">
                        <div class="flex items-center gap-2">
                            <x-user.icon name="shield-alert" :size="20" class="text-amber-500" />
                            <h2 class="text-sm font-black text-slate-900">Rủi ro báo cáo</h2>
                        </div>
                        <p class="mt-1 text-xs font-semibold text-slate-400">Hệ thống ghi nhận tức thời</p>
                    </div>

                    <div class="grid flex-1 gap-3 sm:grid-cols-2">
                        <div class="flex items-center gap-3 rounded-xl border border-rose-100 bg-rose-50 p-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-rose-600">
                                <x-user.icon name="shield-alert" :size="20" />
                            </span>
                            <div>
                                <p class="text-sm font-black text-rose-700">Sai GPS (0)</p>
                                <p class="text-xs font-bold text-rose-500">Cần xem xét</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-xl border border-amber-100 bg-amber-50 p-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-amber-600">
                                <x-user.icon name="laptop" :size="20" />
                            </span>
                            <div>
                                <p class="text-sm font-black text-amber-700">Trùng máy (0)</p>
                                <p class="text-xs font-bold text-amber-500">Điểm danh hộ</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Danh sách quản lý</h2>
                <p class="text-xs font-medium text-slate-500">Sinh viên của phiên điểm danh đang mở</p>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[520px] sm:flex-row">
                <label class="relative flex-1">
                    <x-user.icon name="search" :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm MSSV, tên..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm font-semibold outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" />
                </label>
                @if ($search !== '' || $statusFilter !== 'all')
                    <button type="button" wire:click="clearSearch" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Xóa lọc</button>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">MSSV</th>
                        <th class="px-5 py-3">Học viên</th>
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
                            <td class="px-5 py-4 font-mono text-sm font-extrabold text-slate-600">{{ $record->classMember->student_code }}</td>
                            <td class="px-5 py-4">
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
                                Không tìm thấy sinh viên phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div x-cloak x-show="showClassSettingsModal" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.outside="showClassSettingsModal = false" x-transition.scale class="w-full max-w-2xl overflow-hidden rounded-[1.75rem] bg-white shadow-2xl">
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
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $session->courseClass->code }} - {{ $selectedSubject }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Buổi học</p>
                    <p class="mt-2 text-base font-black text-slate-900">{{ $session->name }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $sessionDateLabel }} · Tiết {{ $startLesson }}-{{ $endLesson }}</p>
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
                    <p class="mt-2 text-sm font-semibold text-emerald-800/80">{{ $isClosed ? 'Đã chốt, sinh viên không thể quét thêm mã.' : 'Đang mở, sinh viên có thể quét mã để điểm danh.' }}</p>
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

    <div x-cloak x-show="showShareCodeModal" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.outside="showShareCodeModal = false" x-transition.scale class="w-full max-w-xl overflow-hidden rounded-[1.75rem] bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-6">
                <div class="flex gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <x-user.icon name="send" :size="24" />
                    </span>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Chia sẻ mã điểm danh</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Gửi mã hoặc liên kết này cho sinh viên trong lớp.</p>
                    </div>
                </div>
                <button type="button" @click="showShareCodeModal = false" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900" title="Đóng">
                    <x-user.icon name="x" :size="20" />
                </button>
            </div>

            <div class="space-y-4 p-6">
                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 text-center">
                    <p class="text-[11px] font-black uppercase tracking-wider text-blue-500">Mã điểm danh</p>
                    <p class="mt-3 break-all font-mono text-2xl font-black text-blue-700">{{ $session->qr_token }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Liên kết sinh viên</p>
                    <p class="mt-2 break-all text-sm font-bold text-slate-700">{{ $attendanceLink }}</p>
                </div>

                <button type="button" @click="copyShareCode()" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm shadow-blue-500/20 transition hover:bg-blue-700">
                    <x-user.icon name="clipboard-check" :size="18" />
                    <span x-text="shareCopied ? 'Đã sao chép liên kết' : 'Sao chép liên kết'"></span>
                </button>
            </div>
        </div>
    </div>

    <div x-cloak x-show="showEndModal" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.outside="showEndModal = false" x-transition.scale class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                <x-user.icon name="calendar-check" :size="24" />
            </div>
            <h2 class="mt-4 text-lg font-black text-slate-900">Chốt phiên điểm danh?</h2>
            <p class="mt-2 text-sm font-medium leading-6 text-slate-500">
                Phiên {{ $session->name }} sẽ dừng nhận QR mới. Bạn vẫn có thể rà soát lại trạng thái sinh viên trước khi lưu báo cáo.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="showEndModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Hủy</button>
                <button type="button" wire:click="closeSession" @click="showEndModal = false" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-amber-600">Chốt phiên</button>
            </div>
        </div>
    </div>

    <div x-cloak x-show="showQrModal" x-transition.opacity class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-white p-6">
        <button type="button" @click="showQrModal = false" class="absolute right-6 top-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900" title="Đóng">
            <x-user.icon name="x" :size="24" />
        </button>

        <div class="flex w-[min(82vw,520px)] items-center justify-center rounded-[32px] bg-white p-6 shadow-2xl shadow-slate-900/10 ring-1 ring-slate-200 [&>svg]:h-full [&>svg]:w-full">
            @if ($qrSvg)
                {!! $qrSvg !!}
            @else
                <div class="grid w-full gap-[5px]" style="grid-template-columns: repeat(29, minmax(0, 1fr));">
                    @foreach ($qrCells as $isDark)
                        <span class="{{ $isDark ? 'bg-slate-900' : 'bg-white' }} aspect-square rounded-[2px]"></span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
