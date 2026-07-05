@php
    /**
     * Parse User-Agent string thành tên thiết bị thân thiện.
     */
    $parseDevice = function (?string $ua): array {
        if (!$ua) return ['name' => 'Thiết bị không xác định', 'icon' => 'monitor'];

        $browser = 'Trình duyệt';
        $os      = '';
        $icon    = 'monitor';

        // Detect browser
        if (str_contains($ua, 'Edg/') || str_contains($ua, 'Edge/'))  { $browser = 'Microsoft Edge'; }
        elseif (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera')) { $browser = 'Opera'; }
        elseif (str_contains($ua, 'Chrome/') && !str_contains($ua, 'Chromium')) { $browser = 'Google Chrome'; }
        elseif (str_contains($ua, 'Firefox/'))  { $browser = 'Firefox'; }
        elseif (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome')) { $browser = 'Safari'; }

        // Detect OS
        if (str_contains($ua, 'Windows'))      { $os = 'Windows'; $icon = 'monitor'; }
        elseif (str_contains($ua, 'iPhone'))   { $os = 'iPhone';  $icon = 'smartphone'; }
        elseif (str_contains($ua, 'iPad'))     { $os = 'iPad';    $icon = 'tablet'; }
        elseif (str_contains($ua, 'Android'))  { $os = 'Android'; $icon = 'smartphone'; }
        elseif (str_contains($ua, 'Mac OS'))   { $os = 'macOS';   $icon = 'monitor'; }
        elseif (str_contains($ua, 'Linux'))    { $os = 'Linux';   $icon = 'monitor'; }

        return [
            'name' => $os ? "{$browser} trên {$os}" : $browser,
            'icon' => $icon,
        ];
    };

    /**
     * Cấu hình hiển thị cho từng loại action:
     *   icon   – tên icon Lucide
     *   color  – màu nền badge + màu chấm timeline
     *   label  – nhãn tiếng Việt thân thiện
     */
    $actionMeta = [
        'login'                   => ['icon' => 'log-in',            'bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'dot' => 'bg-violet-500', 'label' => 'Đăng nhập'],
        'class_created'           => ['icon' => 'plus-circle',      'bg' => 'bg-blue-100',    'text' => 'text-blue-700',   'dot' => 'bg-blue-500',   'label' => 'Tạo lớp học'],
        'class_joined'            => ['icon' => 'log-in',           'bg' => 'bg-indigo-100',  'text' => 'text-indigo-700', 'dot' => 'bg-indigo-500', 'label' => 'Tham gia lớp'],
        'session_created'         => ['icon' => 'calendar-plus',    'bg' => 'bg-cyan-100',    'text' => 'text-cyan-700',   'dot' => 'bg-cyan-500',   'label' => 'Tạo buổi điểm danh'],
        'session_closed'          => ['icon' => 'calendar-check',   'bg' => 'bg-slate-100',   'text' => 'text-slate-700',  'dot' => 'bg-slate-400',  'label' => 'Chốt buổi điểm danh'],
        'attendance_check_in'     => ['icon' => 'check-circle',     'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700','dot' => 'bg-emerald-500','label' => 'Điểm danh QR'],
        'manual_attendance'       => ['icon' => 'clipboard-check',  'bg' => 'bg-teal-100',    'text' => 'text-teal-700',   'dot' => 'bg-teal-500',   'label' => 'Điểm danh thủ công'],
        'leave_request_submitted' => ['icon' => 'file-plus',        'bg' => 'bg-amber-100',   'text' => 'text-amber-700',  'dot' => 'bg-amber-500',  'label' => 'Gửi đơn xin nghỉ'],
        'leave_request_edited'    => ['icon' => 'file-edit',        'bg' => 'bg-orange-100',  'text' => 'text-orange-700', 'dot' => 'bg-orange-500', 'label' => 'Sửa đơn xin nghỉ'],
        'leave_request_approved'  => ['icon' => 'file-check',       'bg' => 'bg-green-100',   'text' => 'text-green-700',  'dot' => 'bg-green-500',  'label' => 'Duyệt đơn xin nghỉ'],
        'leave_request_rejected'  => ['icon' => 'file-x',           'bg' => 'bg-rose-100',    'text' => 'text-rose-700',   'dot' => 'bg-rose-500',   'label' => 'Từ chối đơn xin nghỉ'],
        'profile_updated'         => ['icon' => 'user-check',       'bg' => 'bg-purple-100',  'text' => 'text-purple-700', 'dot' => 'bg-purple-500', 'label' => 'Cập nhật hồ sơ'],
    ];

    $defaultMeta = ['icon' => 'activity', 'bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'dot' => 'bg-gray-400', 'label' => 'Hoạt động'];

    /**
     * Ánh xạ key tiếng Anh trong new_values → nhãn tiếng Việt thân thiện.
     */
    $fieldLabels = [
        // Chung
        'method'           => 'Phương thức',
        'user_agent'       => 'Trình duyệt',
        'ip'               => 'Địa chỉ IP',

        // Lớp học
        'name'             => 'Tên lớp',
        'join_key'         => 'Mã tham gia',
        'class_code'       => 'Mã lớp',
        'class_name'       => 'Tên lớp',

        // Buổi điểm danh
        'date'             => 'Ngày',
        'type'             => 'Loại buổi',
        'saved_records'    => 'Số học viên đã lưu',

        // Đơn xin nghỉ
        'reason'           => 'Lý do xin nghỉ',
        'class_session_id' => 'Mã buổi học',
        'student_code'     => 'Mã sinh viên',
        'rejected_reason'  => 'Lý do từ chối',
    ];
@endphp

<div class="w-full space-y-6 px-4 py-6 sm:px-8 lg:px-14 animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Tài khoản</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">Lịch sử hoạt động</h1>
            <p class="mt-0.5 text-sm font-medium text-slate-500">Các thao tác bạn đã thực hiện trên hệ thống.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary">
                <x-user.icon name="list" :size="13" />
                {{ $logs->total() }} hoạt động
            </span>
            @if($actionFilter !== 'all' || $search || $dateFrom || $dateTo)
                <button wire:click="clearFilters" type="button"
                    class="flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-600 transition hover:bg-rose-100">
                    <x-user.icon name="x" :size="12" />
                    Xóa bộ lọc
                </button>
            @endif
        </div>
    </div>

    {{-- ===== FILTER BAR ===== --}}
    <div class="rounded-2xl border border-outline-variant/10 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Tìm kiếm --}}
            <div class="relative sm:col-span-2 lg:col-span-1">
                <x-user.icon name="search" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                <input id="activity-search" wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Tìm theo hành động..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition">
            </div>

            {{-- Lọc theo hành động --}}
            <div class="relative">
                <x-user.icon name="filter" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <select id="activity-action-filter" wire:model.live="actionFilter"
                    class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-8 text-sm font-medium text-slate-700 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition">
                    @foreach($actionLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Từ ngày --}}
            <div class="relative">
                <x-user.icon name="calendar" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <input id="activity-date-from" wire:model.live="dateFrom" type="date"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition">
            </div>

            {{-- Đến ngày --}}
            <div class="relative">
                <x-user.icon name="calendar" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <input id="activity-date-to" wire:model.live="dateTo" type="date"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm font-medium text-slate-700 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 transition">
            </div>
        </div>
    </div>

    {{-- ===== TIMELINE ===== --}}
    <div class="rounded-2xl border border-outline-variant/10 bg-white shadow-sm overflow-hidden">
        @forelse($logs as $log)
            @php
                $meta     = $actionMeta[$log->action] ?? $defaultMeta;
                $values   = $log->new_values ?? [];
                $isLogin  = $log->action === 'login';

                // Parse device info cho login entries
                $device = $isLogin ? $parseDevice($log->user_agent) : null;

                $className = !$isLogin ? ($log->courseClass->name ?? ($values['class_name'] ?? null)) : null;
                $classCode = !$isLogin ? ($log->courseClass->join_key ?? ($values['join_key'] ?? null)) : null;
            @endphp
            <div wire:key="log-{{ $log->id }}"
                 x-data="{ open: false }"
                 class="border-b border-outline-variant/10 last:border-b-0">
                <div class="flex items-start gap-4 px-5 py-4 transition-colors hover:bg-slate-50/70 cursor-pointer"
                     @click="open = !open">

                    {{-- Icon (với login: icon theo device type) --}}
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $meta['bg'] }} {{ $meta['text'] }}">
                        <x-user.icon :name="$isLogin && $device ? $device['icon'] : $meta['icon']" :size="17" />
                    </div>

                    {{-- Nội dung chính --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Badge action --}}
                            <span class="inline-flex items-center gap-1.5 rounded-lg border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide
                                {{ $meta['bg'] }} {{ $meta['text'] }} border-transparent">
                                <span class="h-1.5 w-1.5 rounded-full {{ $meta['dot'] }}"></span>
                                {{ $meta['label'] }}
                            </span>

                            @if($isLogin && $device)
                                {{-- Tên thiết bị thân thiện --}}
                                <span class="text-xs font-semibold text-slate-700">
                                    {{ $device['name'] }}
                                </span>
                                @if(!empty($values['method']))
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold
                                        {{ $values['method'] === 'google' ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-600' }}">
                                        @if($values['method'] === 'google')
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                                        @else
                                            <x-user.icon name="mail" :size="10" />
                                        @endif
                                        {{ $values['method'] === 'google' ? 'Google' : 'Email' }}
                                    </span>
                                @endif
                            @elseif($className)
                                <span class="text-xs font-semibold text-slate-600 truncate max-w-[200px]" title="{{ $className }}">
                                    {{ $className }}
                                    @if($classCode)
                                        <span class="font-normal text-slate-400">({{ $classCode }})</span>
                                    @endif
                                </span>
                            @endif
                        </div>

                        <p class="mt-1 text-[13px] text-slate-500 font-medium flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                            <x-user.icon name="clock" :size="12" class="text-slate-400" />
                            <span>{{ $log->created_at?->format('H:i') }}</span>
                            <span class="text-slate-300">·</span>
                            <span>{{ $log->created_at?->format('d/m/Y') }}</span>
                            @if($log->ip_address)
                                <span class="text-slate-300">·</span>
                                <x-user.icon name="map-pin" :size="12" class="text-slate-400" />
                                <span>{{ $log->ip_address }}</span>
                            @endif
                        </p>
                    </div>

                    {{-- Chevron --}}
                    @if(!empty($values) || $log->user_agent)
                        <div class="shrink-0 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                            <x-user.icon name="chevron-down" :size="16" />
                        </div>
                    @endif
                </div>

                {{-- Chi tiết (expand) — cho login: hiện đầy đủ user agent --}}
                @if(!empty($values) || $log->user_agent)
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         style="display: none;"
                         x-cloak>
                        <div class="mx-5 mb-4 rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Chi tiết</p>
                            <dl class="space-y-1.5">
                                @if($isLogin && $log->user_agent)
                                    <div class="flex items-start gap-3 text-[13px]">
                                        <dt class="w-28 shrink-0 font-semibold text-slate-500">Thiết bị</dt>
                                        <dd class="font-medium text-slate-800">{{ $device['name'] ?? 'Không xác định' }}</dd>
                                    </div>
                                    <div class="flex items-start gap-3 text-[13px]">
                                        <dt class="w-28 shrink-0 font-semibold text-slate-500">Phương thức</dt>
                                        <dd class="font-medium text-slate-800">{{ ($values['method'] ?? '') === 'google' ? 'Đăng nhập bằng Google' : 'Đăng nhập bằng Email' }}</dd>
                                    </div>
                                    <div class="flex items-start gap-3 text-[13px]">
                                        <dt class="w-28 shrink-0 font-semibold text-slate-500">Địa chỉ IP</dt>
                                        <dd class="font-medium text-slate-800">{{ $log->ip_address ?? '--' }}</dd>
                                    </div>
                                    <div class="flex items-start gap-3 text-[13px]">
                                        <dt class="w-28 shrink-0 font-semibold text-slate-500">Thông tin trình duyệt</dt>
                                        <dd class="font-medium text-slate-600 break-all text-[11px]">{{ $log->user_agent }}</dd>
                                    </div>
                                @else
                                    @foreach($values as $key => $val)
                                        @if($val !== null && $val !== '')
                                            <div class="flex items-start gap-3 text-[13px]">
                                                <dt class="w-36 shrink-0 font-semibold text-slate-500">
                                                    {{ $fieldLabels[$key] ?? str_replace('_', ' ', $key) }}
                                                </dt>
                                                <dd class="font-medium text-slate-800 break-all">
                                                    @if($key === 'type')
                                                        {{ $val === 'qr' ? 'QR Code' : 'Thủ công' }}
                                                    @elseif($key === 'method')
                                                        {{ $val === 'google' ? 'Đăng nhập bằng Google' : 'Đăng nhập bằng Email' }}
                                                    @else
                                                        {{ is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val }}
                                                    @endif
                                                </dd>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <x-user.icon name="activity" :size="30" />
                </div>
                <h3 class="text-base font-bold text-slate-800">Chưa có hoạt động nào</h3>
                <p class="mt-1 text-sm text-slate-400">
                    @if($actionFilter !== 'all' || $search || $dateFrom || $dateTo)
                        Không tìm thấy hoạt động nào phù hợp với bộ lọc.
                    @else
                        Các thao tác của bạn sẽ xuất hiện tại đây.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Phân trang --}}
    @if($logs->hasPages())
        <div class="px-1">
            {{ $logs->links() }}
        </div>
    @endif

</div>
