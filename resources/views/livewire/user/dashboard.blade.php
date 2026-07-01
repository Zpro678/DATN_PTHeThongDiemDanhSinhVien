@php
    $totalClasses = $overview['total_classes'] ?? 0;
    $todayAttendanceSessions = $overview['today_attendance_sessions'] ?? 0;
    $unclosedAttendanceSessions = $overview['unclosed_attendance_sessions'] ?? 0;
    $attendanceWarningStudentsCount = $overview['attendance_warning_students_count'] ?? 0;
    $pendingLeaveRequestsCount = $overview['pending_leave_requests_count'] ?? 0;

    // KPI tổng quan (4 thẻ) — số liệu "quy mô". Các việc cần làm (chưa chốt sổ, đơn nghỉ)
    // được đưa vào panel "Cần xử lý" ở hero nên không lặp lại ở đây.
    $adminStats = [
        ['label' => 'Lớp quản lý', 'value' => $totalClasses, 'icon' => 'book-open', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
        ['label' => 'Tổng học viên', 'value' => $overview['total_students'] ?? 0, 'icon' => 'users', 'color' => 'text-tertiary', 'bg' => 'bg-tertiary/10'],
        ['label' => 'Điểm danh hôm nay', 'value' => $todayAttendanceSessions, 'icon' => 'calendar-check', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
        ['label' => 'SV gần/vượt ngưỡng', 'value' => $attendanceWarningStudentsCount, 'icon' => 'alert-triangle', 'color' => 'text-error', 'bg' => 'bg-error/10'],
    ];

    $adminActions = [
        ['label' => 'Tạo lớp học', 'icon' => 'plus-circle', 'color' => 'text-primary', 'href' => route('create-class')],
        ['label' => 'Tạo buổi DD', 'icon' => 'calendar-plus', 'color' => 'text-primary', 'href' => route('lecturer.attendance.create')],
        ['label' => 'Đơn xin nghỉ', 'icon' => 'file-text', 'color' => 'text-error', 'href' => route('lecturer.leave-requests.index')],
        ['label' => 'Quản lý SV', 'icon' => 'users', 'color' => 'text-tertiary', 'href' => route('lecturer.students.index')],
        ['label' => 'Thống kê', 'icon' => 'check-circle', 'color' => 'text-secondary', 'href' => '#'],
        ['label' => 'Xuất báo cáo', 'icon' => 'upload', 'color' => 'text-tertiary', 'href' => '#'],
    ];

    $alerts = collect();

    // 1. Cảnh báo chuyên cần — dùng AttendanceCalculator, so ngưỡng trên tổng tiết kế hoạch.
    foreach (($overview['attendance_warning_students'] ?? []) as $student) {
        $isExceeded   = ($student['status'] ?? null) === 'exceeded';
        $absenceRatio = $student['absence_ratio_percent'] ?? 0;
        $present      = $student['present_of_planned'] ?? 0;
        $planned      = $student['planned_sessions'] ?? 0;

        $alerts->push([
            'title'  => $isExceeded
                ? "{$student['full_name']} vắng {$absenceRatio}% tổng buổi"
                : "{$student['full_name']} sắp vượt ngưỡng nghỉ",
            'meta'   => "{$student['student_code']} - {$student['class_name']} · Có mặt {$present}/{$planned} buổi",
            'icon'   => $isExceeded ? 'alert-triangle' : 'alert-circle',
            'color'  => $isExceeded ? 'text-error' : 'text-secondary',
            'bg'     => $isExceeded ? 'bg-error/10' : 'bg-secondary/10',
            'border' => $isExceeded ? 'border-error/20' : 'border-secondary/30',
            'button' => $isExceeded ? 'bg-error text-white' : 'border border-secondary text-secondary',
            'action' => 'Xử lý',
            'href'   => route('lecturer.students.show', $student['id']),
        ]);
    }

    // 2. Buổi điểm danh chưa chốt — từng buổi riêng.
    foreach (($overview['unclosed_sessions_list'] ?? []) as $session) {
        $dateLabel = $session['date'] ? \Carbon\Carbon::parse($session['date'])->format('d/m/Y') : '';
        $alerts->push([
            'title'  => "Chưa chốt sổ: {$session['name']}",
            'meta'   => "{$session['class_name']} · {$dateLabel}",
            'icon'   => 'clock',
            'color'  => 'text-secondary',
            'bg'     => 'bg-secondary/10',
            'border' => 'border-secondary/30',
            'button' => 'border border-secondary text-secondary',
            'action' => 'Chốt sổ',
            'href'   => ($session['is_qr'] ?? false)
                ? route('lecturer.attendance.qr.session', $session['id'])
                : route('lecturer.attendance.manual.session', $session['id']),
        ]);
    }

    // 3. Đơn xin nghỉ đang chờ duyệt — từng đơn riêng.
    foreach (($overview['pending_leave_requests_list'] ?? []) as $req) {
        $sessionLabel = $req['session_date']
            ? \Carbon\Carbon::parse($req['session_date'])->format('d/m/Y')
            : ($req['session_name'] ?? '');
        $alerts->push([
            'title'  => "Đơn xin nghỉ: {$req['full_name']}",
            'meta'   => "{$req['student_code']} - {$req['class_name']}" . ($sessionLabel ? " · {$sessionLabel}" : ''),
            'icon'   => 'file-text',
            'color'  => 'text-primary',
            'bg'     => 'bg-primary/10',
            'border' => 'border-primary/20',
            'button' => 'bg-primary text-white',
            'action' => 'Duyệt',
            'href'   => route('lecturer.leave-requests.show', $req['id']),
        ]);
    }

    if ($alerts->isEmpty()) {
        $alerts->push([
            'title'  => 'Không có cảnh báo cần xử lý',
            'meta'   => 'Chuyên cần, buổi điểm danh và đơn nghỉ hiện đang ổn định.',
            'icon'   => 'check-circle',
            'color'  => 'text-tertiary',
            'bg'     => 'bg-tertiary/10',
            'border' => 'border-tertiary/20',
            'button' => null,
            'action' => null,
            'href'   => null,
        ]);
    }

    $alerts = $alerts->take(6)->values()->all();

    $activities = $overview['recent_activities'] ?? [
        ['text' => 'Chưa có hoạt động gần đây', 'time' => 'Khi có điểm danh hoặc đơn nghỉ mới, hệ thống sẽ hiển thị tại đây.', 'icon' => 'activity', 'bg' => 'bg-primary'],
    ];

    $studentDashboardStats = $studentDashboard['stats'] ?? [];
    $studentJoinedCards = $studentDashboard['joined_cards'] ?? [];
    $studentJoinedClassesCount = $studentDashboardStats['joined_classes'] ?? 0;
    $studentAverageAttendance = $studentDashboardStats['attendance_percent'] ?? 100;
    $studentAbsentSessions = $studentDashboardStats['absent_sessions'] ?? 0;
    $studentWarningCount = $studentDashboardStats['warning_count'] ?? 0;
    $studentPendingLeaveRequests = $studentDashboardStats['pending_leave_requests'] ?? 0;
    $studentLatestAttendanceLabel = $studentDashboardStats['latest_attendance_label'] ?? 'Chưa có';

    // KPI học viên (4 thẻ). Đơn chờ duyệt + buổi gần nhất nằm trong panel "Chuyên cần của bạn".
    $studentStats = [
        ['label' => 'Lớp tham gia', 'value' => $studentJoinedClassesCount, 'icon' => 'users', 'color' => 'text-tertiary', 'bg' => 'bg-tertiary/10'],
        ['label' => 'CC trung bình', 'value' => "{$studentAverageAttendance}%", 'icon' => 'check-circle', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
        ['label' => 'Buổi vắng', 'value' => $studentAbsentSessions, 'icon' => 'clock', 'color' => 'text-error', 'bg' => 'bg-error/10'],
        ['label' => 'Cảnh báo', 'value' => $studentWarningCount, 'icon' => 'alert-triangle', 'color' => 'text-error', 'bg' => 'bg-error/10'],
    ];

    $studentActions = [
        ['label' => 'Tham gia lớp', 'icon' => 'log-in', 'color' => 'text-primary', 'action' => '$dispatch(\'open-join-class-modal\')'],
        ['label' => 'Quét QR', 'icon' => 'qr-code', 'color' => 'text-tertiary', 'href' => '#'],
        ['label' => 'Lịch sử điểm danh', 'icon' => 'history', 'color' => 'text-secondary', 'href' => route('student.attendance.history')],
        ['label' => 'Xem chuyên cần', 'icon' => 'check-circle', 'color' => 'text-primary', 'href' => route('student.attendance.stats')],
        ['label' => 'Gửi đơn nghỉ', 'icon' => 'send', 'color' => 'text-secondary', 'href' => route('student.leave-requests.create')],
        ['label' => 'Theo dõi đơn', 'icon' => 'file-text', 'color' => 'text-tertiary', 'href' => '#'],
        ['label' => 'Thông báo', 'icon' => 'bell', 'color' => 'text-primary', 'href' => '#'],
        ['label' => 'Hồ sơ cá nhân', 'icon' => 'user', 'color' => 'text-on-surface-variant', 'href' => route('profile.edit')],
    ];

    $joinedCards = $studentJoinedCards;
@endphp

<div class="min-h-[calc(100vh-4rem)] w-full">
    <div class="w-full px-6 py-6 pb-24 sm:px-10 lg:px-16">
    @php
        $heroName = Auth::user()?->name ?? 'bạn';
        $isAdminWs = $workspace === 'admin';

        // Dữ liệu gói cước (dùng cho thẻ nhắc gói ở workspace Chủ lớp).
        $currentPlan = Auth::user()?->currentPlan();
        $planName = $currentPlan->name ?? 'Miễn phí';
        $planTier = $currentPlan->plan_tier ?? 'FREE';
        $planMax = (int) ($currentPlan->max_classes ?? 2);
        $planUsedPercent = $planMax > 0 ? min(100, (int) round($totalClasses / max(1, $planMax) * 100)) : 0;
        $planNearLimit = $planMax > 0 && $totalClasses >= $planMax;

        // Tổng việc cần xử lý phía Chủ lớp.
        $adminNeedsCount = $unclosedAttendanceSessions + $pendingLeaveRequestsCount + $attendanceWarningStudentsCount;

        $adminNeedRows = [
            ['label' => 'Buổi chưa chốt sổ', 'value' => $unclosedAttendanceSessions, 'icon' => 'clock', 'tone' => 'text-red-600', 'bg' => 'bg-red-50', 'href' => route('lecturer.attendance.index')],
            ['label' => 'Đơn nghỉ chờ duyệt', 'value' => $pendingLeaveRequestsCount, 'icon' => 'file-text', 'tone' => 'text-[#0b57d0]', 'bg' => 'bg-[#d3e3fd]', 'href' => route('lecturer.leave-requests.index')],
            ['label' => 'Học viên cảnh báo', 'value' => $attendanceWarningStudentsCount, 'icon' => 'alert-triangle', 'tone' => 'text-[#D97706]', 'bg' => 'bg-[#FEF3C7]', 'href' => route('lecturer.students.index')],
        ];

        $studentNeedRows = [
            ['label' => 'Buổi vắng', 'value' => $studentAbsentSessions, 'icon' => 'clock', 'tone' => 'text-error', 'bg' => 'bg-error/10'],
            ['label' => 'Nguy cơ cấm thi', 'value' => $studentWarningCount, 'icon' => 'alert-triangle', 'tone' => 'text-error', 'bg' => 'bg-error/10'],
            ['label' => 'Buổi gần nhất', 'value' => $studentLatestAttendanceLabel, 'icon' => 'calendar-check', 'tone' => 'text-tertiary', 'bg' => 'bg-tertiary/10'],
        ];

        // Lối tắt điều hướng trong thẻ "Bắt đầu nhanh" — lấp khoảng trống bằng thao tác hữu ích.
        $adminShortcuts = [
            ['label' => 'Quản lý học viên', 'hint' => ($overview['total_students'] ?? 0) . ' học viên', 'icon' => 'users', 'href' => route('lecturer.students.index')],
            ['label' => 'Buổi điểm danh', 'hint' => 'Danh sách buổi', 'icon' => 'calendar-check', 'href' => route('lecturer.attendance.index')],
            ['label' => 'Đơn xin nghỉ', 'hint' => $pendingLeaveRequestsCount > 0 ? "{$pendingLeaveRequestsCount} chờ duyệt" : 'Đã xử lý hết', 'icon' => 'file-text', 'href' => route('lecturer.leave-requests.index')],
        ];

        $studentShortcuts = [
            ['label' => 'Lịch sử điểm danh', 'hint' => 'Xem các buổi', 'icon' => 'history', 'href' => route('student.attendance.history')],
            ['label' => 'Chuyên cần của tôi', 'hint' => "{$studentAverageAttendance}% trung bình", 'icon' => 'bar-chart', 'href' => route('student.attendance.stats')],
            ['label' => 'Gửi đơn xin nghỉ', 'hint' => 'Tạo đơn mới', 'icon' => 'send', 'href' => route('student.leave-requests.create')],
        ];
    @endphp
    {{-- ===== Header chào: badge + lời chào + ngày/tóm tắt (trái) · công tắc workspace (phải) ===== --}}
    <section class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            @if ($isAdminWs)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                    <x-user.icon name="shield-check" :size="14" /> Không gian Chủ lớp
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-tertiary/10 px-3 py-1 text-xs font-semibold text-tertiary">
                    <x-user.icon name="user" :size="14" /> Không gian Học viên
                </span>
            @endif

            <h2 class="mt-3 text-2xl font-bold leading-tight tracking-tight text-on-surface md:text-3xl">
                Xin chào, {{ $heroName }} 👋
            </h2>
            <p class="mt-1.5 text-sm text-on-surface-variant">
                Hôm nay, {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                @if ($isAdminWs)
                    @if ($adminNeedsCount > 0)
                        · Bạn có <span class="font-semibold text-on-surface">{{ $adminNeedsCount }} việc</span> cần xử lý
                    @else
                        · Mọi việc đang ổn định, không có gì cần xử lý
                    @endif
                @else
                    · Chuyên cần trung bình <span class="font-semibold {{ $studentAverageAttendance >= 80 ? 'text-tertiary' : 'text-error' }}">{{ $studentAverageAttendance }}%</span>
                @endif
            </p>
        </div>

        {{-- Công tắc workspace (giữ nguyên cơ chế Livewire) --}}
        <div class="inline-flex shrink-0 rounded-xl border border-outline-variant bg-surface-container p-1">
            <button
                type="button"
                wire:click="setWorkspace('admin')"
                @class([
                    'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all',
                    'bg-white text-primary shadow-sm' => $workspace === 'admin',
                    'text-on-surface-variant hover:text-on-surface' => $workspace !== 'admin',
                ])
            >
                <x-user.icon name="shield" :size="16" />
                Chủ lớp
            </button>
            <button
                type="button"
                wire:click="setWorkspace('student')"
                @class([
                    'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all',
                    'bg-white text-tertiary shadow-sm' => $workspace === 'student',
                    'text-on-surface-variant hover:text-on-surface' => $workspace !== 'student',
                ])
            >
                <x-user.icon name="user" :size="16" />
                Học viên
            </button>
        </div>
    </section>

    {{-- ===== Hàng spotlight: thẻ bắt đầu nhanh (trái) + panel theo workspace (phải) ===== --}}
    <section class="mb-8 grid gap-4 md:grid-cols-[1.5fr_1fr]">
        {{-- Bắt đầu nhanh: CTA chính + lối tắt điều hướng --}}
        <div class="dash-card flex flex-col rounded-2xl p-5 md:p-6">
            <div class="flex items-start gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full {{ $isAdminWs ? 'bg-blue-50 text-blue-600' : 'bg-tertiary/10 text-tertiary' }}">
                    <x-user.icon :name="$isAdminWs ? 'zap' : 'sparkles'" :size="22" />
                </span>
                <div class="min-w-0">
                    <p class="text-sm text-on-surface-variant">Bắt đầu nhanh</p>
                    <p class="mt-0.5 text-base font-semibold leading-snug text-on-surface md:text-lg">
                        @if ($isAdminWs)
                            Tổ chức một buổi điểm danh hoặc mở lớp mới
                        @else
                            Tham gia lớp bằng mã hoặc theo dõi chuyên cần của bạn
                        @endif
                    </p>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                @if ($isAdminWs)
                    <a href="{{ route('lecturer.attendance.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#0b57d0] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700">
                        <x-user.icon name="calendar-plus" :size="18" />
                        Tạo buổi điểm danh
                    </a>
                    <a href="{{ route('create-class') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#f0f4f8] px-5 py-2.5 text-sm font-semibold text-[#3c4043] transition-colors hover:bg-gray-200">
                        <x-user.icon name="plus" :size="18" />
                        Tạo lớp mới
                    </a>
                @else
                    <button type="button" x-on:click="$dispatch('open-join-class-modal')" class="inline-flex items-center gap-2 rounded-xl bg-tertiary px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-tertiary/90">
                        <x-user.icon name="log-in" :size="18" />
                        Tham gia bằng mã
                    </button>
                    <a href="{{ route('student.attendance.stats') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#f0f4f8] px-5 py-2.5 text-sm font-semibold text-[#3c4043] transition-colors hover:bg-gray-200">
                        <x-user.icon name="bar-chart" :size="18" />
                        Xem chuyên cần
                    </a>
                @endif
            </div>

            {{-- Lối tắt — lấp khoảng trống, đồng bộ chiều cao với panel phải --}}
            <div class="mt-auto pt-5">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Lối tắt</p>
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach (($isAdminWs ? $adminShortcuts : $studentShortcuts) as $sc)
                        <a href="{{ $sc['href'] }}" wire:navigate class="group flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:border-gray-300 hover:shadow-md sm:flex-col sm:items-start sm:gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-100 text-gray-600 transition {{ $isAdminWs ? 'group-hover:bg-primary/10 group-hover:text-primary' : 'group-hover:bg-tertiary/10 group-hover:text-tertiary' }}">
                                <x-user.icon :name="$sc['icon']" :size="20" />
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-bold text-gray-900">{{ $sc['label'] }}</span>
                                <span class="block truncate text-xs text-gray-500">{{ $sc['hint'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Panel spotlight theo workspace ===== --}}
        @if ($isAdminWs)
            <div class="dash-card rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <p class="flex items-center gap-2 text-base font-bold text-on-surface">
                        <x-user.icon name="alert-circle" :size="18" class="text-error" />
                        Cần xử lý
                    </p>
                    @if ($adminNeedsCount > 0)
                        <span class="inline-flex items-center gap-1 rounded-full bg-error/10 px-2.5 py-0.5 text-xs font-bold text-error">{{ $adminNeedsCount }} việc</span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-tertiary/10 px-2.5 py-0.5 text-xs font-bold text-tertiary">
                            <x-user.icon name="check" :size="12" /> Ổn định
                        </span>
                    @endif
                </div>
                <div class="mt-4 space-y-2">
                    @foreach ($adminNeedRows as $row)
                        @php $hasItems = $row['value'] > 0; @endphp
                        <a href="{{ $row['href'] }}" wire:navigate class="group flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-[#eff4fd]">
                            <span class="{{ $row['bg'] }} {{ $row['tone'] }} grid h-8 w-8 shrink-0 place-items-center rounded-full">
                                <x-user.icon :name="$row['icon']" :size="16" />
                            </span>
                            <span class="flex-1 text-sm font-medium text-gray-900">{{ $row['label'] }}</span>
                            <span @class([
                                'grid h-6 min-w-6 place-items-center rounded-full px-2 text-xs font-bold tabular-nums',
                                $row['bg'].' '.$row['tone'] => $hasItems,
                                'text-gray-500' => ! $hasItems,
                            ])>{{ $row['value'] }}</span>
                            <x-user.icon name="chevron-right" :size="16" class="-ml-1 text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-gray-900" />
                        </a>
                    @endforeach
                </div>
                <div class="mt-4 rounded-xl border border-outline-variant/60 bg-surface-container-low p-3.5">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-on-surface">
                            <x-user.icon name="zap" :size="15" class="text-primary" /> Gói {{ $planName }}
                        </span>
                        <span class="text-xs font-semibold text-on-surface-variant">{{ $totalClasses }}/{{ $planMax }} lớp</span>
                    </div>
                    <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-surface-container-highest">
                        <div class="{{ $planNearLimit ? 'bg-error' : 'bg-primary' }} h-full rounded-full transition-all" style="width: {{ $planUsedPercent }}%"></div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs">
                        <span class="text-on-surface-variant">
                            @if ($planNearLimit)
                                Đã đạt giới hạn lớp của gói
                            @else
                                Còn {{ max(0, $planMax - $totalClasses) }} lớp có thể tạo
                            @endif
                        </span>
                        <a href="{{ route('upgrade') }}" wire:navigate class="font-semibold text-primary hover:underline">Nâng cấp</a>
                    </div>
                </div>
            </div>
                @else
            <div class="dash-card rounded-2xl p-5">
                <p class="flex items-center gap-2 text-base font-bold text-on-surface">
                    <x-user.icon name="bar-chart" :size="18" class="text-tertiary" />
                    Chuyên cần của bạn
                </p>
                <div class="mt-3 flex items-end justify-between">
                    <div class="flex items-end gap-2">
                        <span class="text-4xl font-bold leading-none {{ $studentAverageAttendance >= 80 ? 'text-tertiary' : 'text-error' }}">{{ $studentAverageAttendance }}%</span>
                        <span class="pb-1 text-xs font-medium text-on-surface-variant">trung bình {{ $studentJoinedClassesCount }} lớp</span>
                    </div>
                    <span @class([
                        'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                        'bg-tertiary/10 text-tertiary' => $studentAverageAttendance >= 80,
                        'bg-error/10 text-error' => $studentAverageAttendance < 80,
                    ])>{{ $studentAverageAttendance >= 80 ? 'An toàn' : 'Cần chú ý' }}</span>
                </div>
                <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-surface-container-highest">
                    <div class="h-full rounded-full transition-all {{ $studentAverageAttendance >= 80 ? 'bg-tertiary' : 'bg-error' }}" style="width: {{ min(100, (int) $studentAverageAttendance) }}%"></div>
                </div>
                <div class="mt-3 divide-y divide-outline-variant/50 border-t border-outline-variant/60">
                    @foreach ($studentNeedRows as $row)
                        <div class="flex items-center gap-3 py-2.5">
                            <span class="{{ $row['bg'] }} {{ $row['tone'] }} grid h-9 w-9 shrink-0 place-items-center rounded-lg">
                                <x-user.icon :name="$row['icon']" :size="18" />
                            </span>
                            <span class="flex-1 text-sm font-medium text-on-surface">{{ $row['label'] }}</span>
                            @if (is_numeric($row['value']))
                                <span @class([
                                    'grid h-7 min-w-7 place-items-center rounded-lg px-2 text-sm font-bold tabular-nums',
                                    $row['bg'].' '.$row['tone'] => $row['value'] > 0,
                                    'text-on-surface-variant' => ! ($row['value'] > 0),
                                ])>{{ $row['value'] }}</span>
                            @else
                                <span class="text-sm font-semibold text-on-surface-variant">{{ $row['value'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
                @endif
    </section>

    <div id="admin" class="relative">
        @if ($workspace === 'admin')
            <section class="mt-8 space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="mb-6">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">
                        Không gian Chủ lớp
                    </h3>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ($adminStats as $stat)
                        <div class="dash-card dash-card-hover flex flex-col justify-center rounded-2xl bg-white p-5 shadow-sm">
                            <div class="mb-3 flex items-center gap-3">
                                <span class="{{ $stat['bg'] }} {{ $stat['color'] }} grid h-10 w-10 shrink-0 place-items-center rounded-full">
                                    <x-user.icon :name="$stat['icon']" :size="18" />
                                </span>
                                <span class="text-base font-medium leading-snug text-gray-600">{{ $stat['label'] }}</span>
                            </div>
                            <div class="text-center text-3xl font-bold text-gray-900">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>

                @if(count($managedClassCards) > 0)
                    <div>
                        <h3 class="mb-4 font-headline-sm text-headline-sm text-on-surface">Lớp tôi quản lý</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($managedClassCards as $class)
                            @php
                                $isEnded = $class['status_label'] === 'Đã kết thúc';
                                $attendancePct = $class['attendance'];
                                
                                $isBanned = $attendancePct < \App\Services\AttendanceCalculator::MIN_ATTENDANCE_PERCENT;
                                $isWarning = ! $isBanned && $attendancePct < 85;

                                if ($isEnded) {
                                    $barClass = 'bg-on-surface-variant'; $textClass = 'text-on-surface-variant';
                                } elseif ($attendancePct < 70) {
                                    $barClass = 'bg-error'; $textClass = 'text-error';
                                } elseif ($attendancePct < 90) {
                                    $barClass = 'bg-amber-500'; $textClass = 'text-amber-600';
                                } else {
                                    $barClass = 'bg-tertiary'; $textClass = 'text-tertiary';
                                }

                                $colorOptions = [
                                    'bg-[#475569]', 'bg-[#1D4ED8]', 'bg-[#0F766E]', 'bg-[#4338CA]',
                                    'bg-[#047857]', 'bg-[#0369A1]', 'bg-[#6D28D9]', 'bg-[#B45309]',
                                ];
                                $themeColor = $colorOptions[$class['id'] % count($colorOptions)];
                                
                                $bgIcons = ['laptop', 'book', 'code', 'book-open', 'graduation-cap', 'layout-dashboard'];
                                $bgIcon = $bgIcons[$class['id'] % count($bgIcons)];
                            @endphp

                            <article @class([
                                'group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300',
                                'hover:border-slate-300 hover:shadow-md hover:-translate-y-0.5' => ! $isEnded,
                                'opacity-75' => $isEnded,
                            ])>
                                <a href="{{ route('lecturer.classes.show', $class['id']) }}" class="absolute inset-0 z-10"><span class="sr-only">Xem chi tiết lớp</span></a>

                                {{-- Header Theme Color --}}
                                <div class="{{ $isEnded ? 'bg-on-surface-variant' : $themeColor }} h-24 px-5 py-4 relative">
                                    <div class="relative z-10 w-3/4">
                                        <h3 class="truncate font-normal text-white text-[22px] tracking-wide leading-tight" title="{{ $class['title'] }}">
                                            <a href="{{ route('lecturer.classes.show', $class['id']) }}" class="hover:underline focus:outline-none">{{ $class['title'] }}</a>
                                        </h3>
                                        <p class="mt-1 truncate text-[13px] font-light text-white/95 tracking-wide">Mã học phần: {{ $class['subject_code'] ?? 'N/A' }}</p>
                                    </div>

                                    {{-- Background Icon --}}
                                    <div class="absolute right-3 top-2 z-0 opacity-15">
                                        <x-user.icon :name="$bgIcon" :size="76" class="text-white transform -rotate-12" stroke-width="1.5" />
                                    </div>

                                    {{-- Avatar overlapping --}}
                                    <div class="absolute -bottom-6 right-5 z-20">
                                        <span class="{{ $isEnded ? 'bg-on-surface-variant' : $themeColor }} grid h-14 w-14 place-items-center rounded-full border-2 border-white text-[22px] font-medium text-white shadow-sm" title="{{ auth()->user()->name }}">
                                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Body (White) --}}
                                <div class="flex-1 px-4 pt-8 pb-3 relative z-20 pointer-events-none">
                                    <div class="mb-3 flex items-center justify-between">
                                        <div class="flex flex-wrap gap-1.5">
                                            <span @class([
                                                'inline-flex items-center rounded-sm px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                                'bg-blue-50 text-blue-600' => ! $isEnded,
                                                'bg-surface-container text-on-surface-variant' => $isEnded,
                                            ])>{{ $isEnded ? 'Đã kết thúc' : 'Đang hoạt động' }}</span>
                                            <span class="inline-flex items-center rounded-sm bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-600">
                                                <x-user.icon name="shield" :size="10" class="mr-1" /> Chủ lớp
                                            </span>
                                        </div>
                                        <span class="text-[11px] font-medium text-slate-700">Mã lớp: <span class="font-bold">{{ $class['code'] }}</span></span>
                                    </div>

                                    <div class="flex gap-8 mb-3">
                                        <div>
                                            <p class="text-[10px] font-semibold text-slate-500 mb-1">HỌC VIÊN</p>
                                            <p class="flex items-center gap-1.5 text-lg font-bold text-slate-700">
                                                <x-user.icon name="users" :size="16" class="text-slate-400" />
                                                {{ $class['students'] }}
                                            </p>
                                        </div>
                                        <div class="w-px bg-slate-200"></div>
                                        <div>
                                            <p class="text-[10px] font-semibold text-slate-500 mb-1">ĐÃ HỌC</p>
                                            <p class="flex items-center gap-1.5 text-lg font-bold text-slate-700">
                                                <x-user.icon name="check-square" :size="16" class="text-slate-400" />
                                                {{ intval($class['sessions']) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="pt-2">
                                        <div class="mb-1.5 flex items-center justify-between">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase">TB CHUYÊN CẦN</span>
                                            <div class="flex items-center gap-2">
                                                @if ($isBanned)
                                                    <span class="rounded-sm bg-error/10 px-1.5 py-0.5 text-[9px] font-bold text-error uppercase">Cấm thi</span>
                                                @elseif ($isWarning)
                                                    <span class="rounded-sm bg-amber-100 px-1.5 py-0.5 text-[9px] font-bold text-amber-700 uppercase">Cảnh báo</span>
                                                @endif
                                                <span class="{{ $textClass }} text-xs font-bold">{{ $attendancePct }}%</span>
                                            </div>
                                        </div>
                                        <div class="h-1 w-full overflow-hidden bg-surface-container-high rounded-sm">
                                            <div class="{{ $barClass }} h-full transition-all duration-700" style="width: {{ $attendancePct }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative z-20 mt-4 flex items-center justify-end gap-0.5 border-t border-outline-variant px-3 py-2">
                                    <div x-data="{ copied: false }" class="relative z-20">
                                        <button
                                            type="button"
                                            title="Sao chép mã lớp: {{ $class['code'] }}"
                                            class="group/action rounded-lg p-2 transition-colors hover:bg-surface-container"
                                            x-on:click="navigator.clipboard.writeText('{{ $class['code'] }}'); copied = true; setTimeout(() => copied = false, 2000); $event.stopPropagation()"
                                        >
                                            <template x-if="!copied"><x-user.icon name="copy" :size="18" class="text-on-surface-variant transition-colors group-hover/action:text-primary" /></template>
                                            <template x-if="copied"><x-user.icon name="check-circle" :size="18" class="text-tertiary" /></template>
                                        </button>
                                    </div>
                                    @foreach ([
                                        ['label' => 'Điểm danh QR', 'icon' => 'qr-code'],
                                        ['label' => 'Thủ công', 'icon' => 'check-square'],
                                        ['label' => 'Quản lý SV', 'icon' => 'users'],
                                        ['label' => 'Thống kê', 'icon' => 'bar-chart'],
                                    ] as $action)
                                        <a href="{{ match ($action['label']) { 'Điểm danh QR' => route('lecturer.attendance.create', ['class_id' => $class['id']]), 'Thủ công' => route('lecturer.attendance.create', ['class_id' => $class['id']]), 'Quản lý SV' => route('lecturer.students.index', ['class_id' => $class['id']]), 'Thống kê' => route('lecturer.class.statistics', ['class_id' => $class['id']]), default => '#' } }}" @class([
                                            'group/action rounded-lg p-2 transition-colors hover:bg-surface-container',
                                            'pointer-events-none opacity-40' => $isEnded && in_array($action['icon'], ['qr-code', 'check-square'], true),
                                        ]) title="{{ $action['label'] }}">
                                            <x-user.icon :name="$action['icon']" class="text-on-surface-variant transition-colors group-hover/action:text-primary" :size="18"/>
                                        </a>
                                    @endforeach
                                    
                                    <div class="relative z-20" x-data="{ open: false }">
                                        <button type="button" x-on:click.stop="open = ! open" class="rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container">
                                            <x-user.icon name="more-vertical" :size="18" />
                                        </button>
                                        <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute right-0 bottom-full z-50 mb-1 w-44 overflow-hidden rounded-lg border border-outline-variant bg-white py-1 shadow-lg">
                                            <div class="px-3 py-2 border-b border-outline-variant/30 text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Thao tác</div>
                                            <a href="{{ route('lecturer.classes.show', $class['id']) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container">
                                                <x-user.icon name="eye" :size="16" />
                                                Chi tiết
                                            </a>
                                            <a href="{{ route('lecturer.class.statistics', ['class_id' => $class['id']]) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container">
                                                <x-user.icon name="bar-chart-2" :size="16" />
                                                Thống kê
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                            @endforeach
                        </div>
                    </div>
                @endif


            </section>
        @else
            <section id="student" class="mt-8 space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="mb-6">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">
                        Không gian Học viên
                    </h3>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ($studentStats as $stat)
                        <div class="dash-card dash-card-hover flex flex-col justify-center rounded-2xl bg-white p-4 shadow-sm transition sm:p-5">
                            <div class="mb-3 flex items-center gap-2 lg:gap-3">
                                <span class="{{ $stat['bg'] }} {{ $stat['color'] }} grid h-10 w-10 shrink-0 place-items-center rounded-xl">
                                    <x-user.icon :name="$stat['icon']" :size="20" />
                                </span>
                                <span class="text-base font-medium leading-snug text-gray-600">{{ $stat['label'] }}</span>
                            </div>
                            <div class="text-center text-3xl font-bold text-gray-900">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>



                <div>
                    <h4 class="mb-4 text-[16px] font-bold text-on-surface">Lớp tôi tham gia</h4>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @forelse ($joinedCards as $class)
                            @php
                                $attendancePct = $class['attendance'];
                                
                                $colorOptions = [
                                    'bg-[#475569]', 'bg-[#1D4ED8]', 'bg-[#0F766E]', 'bg-[#4338CA]',
                                    'bg-[#047857]', 'bg-[#0369A1]', 'bg-[#6D28D9]', 'bg-[#B45309]',
                                ];
                                $themeColor = $colorOptions[($class['class_id'] ?? 1) % count($colorOptions)];
                                
                                $bgIcons = ['laptop', 'book', 'code', 'book-open', 'graduation-cap', 'layout-dashboard'];
                                $bgIcon = $bgIcons[($class['class_id'] ?? 1) % count($bgIcons)];
                            @endphp

                            <article @class([
                                'group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300',
                                'hover:border-slate-300 hover:shadow-md hover:-translate-y-0.5'
                            ])>
                                {{-- Header Theme Color --}}
                                <div class="{{ $themeColor }} h-24 px-5 py-4 relative">
                                    <div class="relative z-10 w-3/4">
                                        <h3 class="truncate font-normal text-white text-[22px] tracking-wide leading-tight" title="{{ $class['title'] }}">
                                            {{ $class['title'] }}
                                        </h3>
                                        <p class="mt-1 truncate text-[13px] font-light text-white/95 tracking-wide">Mã học phần: {{ $class['code'] ?? 'N/A' }}</p>
                                    </div>

                                    {{-- Background Icon --}}
                                    <div class="absolute right-3 top-2 z-0 opacity-15">
                                        <x-user.icon :name="$bgIcon" :size="76" class="text-white transform -rotate-12" stroke-width="1.5" />
                                    </div>

                                    {{-- Avatar overlapping --}}
                                    <div class="absolute -bottom-6 right-5 z-20">
                                        <span class="{{ $themeColor }} grid h-14 w-14 place-items-center rounded-full border-2 border-white text-[22px] font-medium text-white shadow-sm" title="Giảng viên: {{ $class['teacher'] }}">
                                            {{ mb_strtoupper(mb_substr($class['teacher'] ?? 'GV', 0, 1)) }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Body (White) --}}
                                <div class="flex-1 px-4 pt-8 pb-3 relative z-20 pointer-events-none">
                                    <div class="mb-3 flex items-center justify-between">
                                        <div class="flex flex-wrap gap-1.5">
                                            <span class="inline-flex items-center rounded-sm px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $class['statusClass'] }}">
                                                {{ $class['status'] }}
                                            </span>
                                            <span class="inline-flex items-center rounded-sm bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-blue-600">
                                                <x-user.icon name="user" :size="10" class="mr-1" /> Học viên
                                            </span>
                                        </div>
                                        <span class="text-[11px] font-medium text-slate-700">Mã lớp: <span class="font-bold">{{ $class['class_code'] }}</span></span>
                                    </div>

                                    <div class="flex gap-8 mb-3">
                                        <div>
                                            <p class="text-[10px] font-semibold text-slate-500 mb-1">TỔNG BUỔI VẮNG</p>
                                            <p class="flex items-center gap-1.5 text-lg font-bold text-slate-700">
                                                <x-user.icon name="clock" :size="16" class="text-slate-400" />
                                                <span class="text-[15px]">{{ $class['absent'] }}</span>
                                            </p>
                                        </div>
                                        <div class="w-px bg-slate-200"></div>
                                        <div>
                                            <p class="text-[10px] font-semibold text-slate-500 mb-1">ĐÃ HỌC</p>
                                            <p class="flex items-center gap-1.5 text-lg font-bold text-slate-700">
                                                <x-user.icon name="check-square" :size="16" class="text-slate-400" />
                                                {{ $class['studied_sessions'] }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="pt-2">
                                        <div class="mb-1.5 flex items-center justify-between">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase">CHUYÊN CẦN CÁ NHÂN</span>
                                            <div class="flex items-center gap-2">
                                                <span class="{{ $class['color'] }} text-xs font-bold">{{ $attendancePct }}%</span>
                                            </div>
                                        </div>
                                        <div class="h-1 w-full overflow-hidden bg-surface-container-high rounded-sm">
                                            <div class="{{ $class['bar'] }} h-full transition-all duration-700" style="width: {{ $attendancePct }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative z-20 mt-4 flex items-center justify-end gap-0.5 border-t border-outline-variant px-3 py-2">
                                    <div x-data="{ copied: false }" class="relative z-20">
                                        <button
                                            type="button"
                                            title="Sao chép mã lớp: {{ $class['class_code'] }}"
                                            class="group/action rounded-lg p-2 transition-colors hover:bg-surface-container"
                                            x-on:click="navigator.clipboard.writeText('{{ $class['class_code'] }}'); copied = true; setTimeout(() => copied = false, 2000); $event.stopPropagation()"
                                        >
                                            <template x-if="!copied"><x-user.icon name="copy" :size="18" class="text-on-surface-variant transition-colors group-hover/action:text-primary" /></template>
                                            <template x-if="copied"><x-user.icon name="check-circle" :size="18" class="text-tertiary" /></template>
                                        </button>
                                    </div>
                                    <a href="{{ route('student.attendance.history', ['class_id' => $class['class_id']]) }}" class="group/action rounded-lg p-2 transition-colors hover:bg-surface-container" title="Xem lịch sử">
                                        <x-user.icon name="history" class="text-on-surface-variant transition-colors group-hover/action:text-primary" :size="18"/>
                                    </a>
                                    <div class="relative z-20" x-data="{ open: false }">
                                        <button type="button" x-on:click.stop="open = ! open" class="rounded-lg p-2 text-on-surface-variant transition-colors hover:bg-surface-container">
                                            <x-user.icon name="more-vertical" :size="18" />
                                        </button>
                                        <div x-cloak x-show="open" x-on:click.outside="open = false" class="absolute right-0 bottom-full z-50 mb-1 w-44 overflow-hidden rounded-lg border border-outline-variant bg-white py-1 shadow-lg">
                                            <div class="px-3 py-2 border-b border-outline-variant/30 text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Thao tác</div>
                                            <a href="{{ route('student.classes.show', $class['class_id']) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container">
                                                <x-user.icon name="eye" :size="16" />
                                                Chi tiết
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="col-span-full rounded-2xl border border-dashed border-outline-variant/30 bg-white p-8 text-center">
                                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-tertiary/10 text-tertiary">
                                    <x-user.icon name="book-open" :size="24" />
                                </div>
                                <h5 class="text-base font-bold text-on-surface">Chưa tham gia lớp nào</h5>
                                <p class="mt-2 text-sm text-on-surface-variant">Khi bạn tham gia lớp, thông tin chuyên cần và số buổi vắng sẽ hiển thị tại đây.</p>
                                <button type="button" x-on:click="$dispatch('open-join-class-modal')" class="mt-4 inline-flex items-center justify-center rounded-xl bg-tertiary px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-tertiary/90">
                                    Tham gia lớp
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>


            </section>
        @endif
    </div>

    @if ($showCreateModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-on-background/40 p-4 backdrop-blur-sm" wire:click.self="closeCreateModal">
            <div class="flex max-h-[90vh] w-full max-w-2xl animate-in zoom-in-95 flex-col overflow-y-auto rounded-2xl bg-white shadow-2xl duration-200">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-outline-variant/20 bg-white/90 p-6 backdrop-blur">
                    <h3 class="flex items-center gap-2 text-xl font-bold text-on-surface">
                        <x-user.icon name="plus" class="text-primary" />
                        Tạo lớp học mới
                    </h3>
                    <button type="button" wire:click="closeCreateModal" class="rounded-full p-2 transition-colors hover:bg-surface-container">
                        <x-user.icon name="x" :size="20" class="text-on-surface-variant" />
                    </button>
                </div>
                <div class="space-y-6 p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <label class="col-span-2 block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Tên lớp / môn học *</span>
                            <input type="text" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Nhập tên môn học">
                        </label>
                        <label class="col-span-2 block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Mã môn học</span>
                            <input type="text" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="WEB301">
                        </label>

                        <label class="col-span-2 block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Mô tả lớp học</span>
                            <textarea class="h-24 w-full resize-none rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Nhập mô tả..."></textarea>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Tổng số buổi</span>
                            <input type="number" value="15" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Ngưỡng cảnh báo vắng (%)</span>
                            <input type="number" value="20" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Bán kính GPS mặc định (m)</span>
                            <input type="number" value="50" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                        </label>
                        <div class="col-span-2 flex items-center justify-between rounded-xl border border-outline-variant/10 bg-surface-container-low p-4">
                            <span class="text-sm font-bold text-on-surface">Yêu cầu duyệt khi tham gia lớp</span>
                            <span class="relative h-6 w-12 rounded-full bg-primary">
                                <span class="absolute right-1 top-1 h-4 w-4 rounded-full bg-white"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="sticky bottom-0 flex justify-end gap-3 border-t border-outline-variant/20 bg-surface-container-lowest p-6">
                    <button type="button" wire:click="closeCreateModal" class="rounded-xl px-6 py-2.5 text-sm font-bold text-on-surface-variant transition-colors hover:bg-surface-container">Hủy</button>
                    <a href="{{ route('create-class') }}" class="rounded-xl bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-primary/20 transition-colors hover:bg-primary-container">
                        Tạo lớp học
                    </a>
                </div>
            </div>
        </div>
    @endif

    </div>
</div>
