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
        ['label' => 'Buổi điểm danh hôm nay', 'value' => $todayAttendanceSessions, 'icon' => 'calendar-check', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
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
    $studentAverageAttendanceLabel = $studentDashboardStats['attendance_percent_label'] ?? "{$studentAverageAttendance}%";
    $studentAbsentSessions = $studentDashboardStats['absent_sessions'] ?? 0;
    $studentWarningCount = $studentDashboardStats['warning_count'] ?? 0;
    $studentPendingLeaveRequests = $studentDashboardStats['pending_leave_requests'] ?? 0;
    $studentLatestAttendanceLabel = $studentDashboardStats['latest_attendance_label'] ?? 'Chưa có';

    // KPI học viên (4 thẻ). Đơn chờ duyệt + buổi gần nhất nằm trong panel "Chuyên cần của bạn".
    $studentStats = [
        ['label' => 'Lớp tham gia', 'value' => $studentJoinedClassesCount, 'icon' => 'users', 'color' => 'text-tertiary', 'bg' => 'bg-tertiary/10'],
        ['label' => 'CC trung bình', 'value' => $studentAverageAttendanceLabel, 'icon' => 'check-circle', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
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

<div class="dashboard-canvas min-h-[calc(100vh-4rem)] w-full">
    <div class="w-full px-4 py-6 pb-24 sm:px-6 md:py-8 lg:px-8 xl:px-12">
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
            ['label' => 'Buổi chưa chốt sổ', 'value' => $unclosedAttendanceSessions, 'icon' => 'clock', 'tone' => 'text-error', 'bg' => 'bg-error/10', 'href' => route('lecturer.attendance.index')],
            ['label' => 'Đơn nghỉ chờ duyệt', 'value' => $pendingLeaveRequestsCount, 'icon' => 'file-text', 'tone' => 'text-primary', 'bg' => 'bg-primary/10', 'href' => route('lecturer.leave-requests.index')],
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
            ['label' => 'Chuyên cần của tôi', 'hint' => "{$studentAverageAttendanceLabel} trung bình", 'icon' => 'bar-chart', 'href' => route('student.attendance.stats')],
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
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $isAdminWs ? 'bg-primary/10 text-primary' : 'bg-tertiary/10 text-tertiary' }}">
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
                    <a href="{{ route('lecturer.attendance.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-container">
                        <x-user.icon name="calendar-plus" :size="18" />
                        Tạo buổi điểm danh
                    </a>
                    <a href="{{ route('create-class') }}" class="inline-flex items-center gap-2 rounded-xl border border-outline-variant bg-white px-4 py-2.5 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface">
                        <x-user.icon name="plus" :size="18" />
                        Tạo lớp mới
                    </a>
                @else
                    <button type="button" x-on:click="$dispatch('open-join-class-modal')" class="inline-flex items-center gap-2 rounded-xl bg-tertiary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-tertiary/90">
                        <x-user.icon name="log-in" :size="18" />
                        Tham gia bằng mã
                    </button>
                    <a href="{{ route('student.attendance.stats') }}" class="inline-flex items-center gap-2 rounded-xl border border-outline-variant bg-white px-4 py-2.5 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface">
                        <x-user.icon name="bar-chart" :size="18" />
                        Xem chuyên cần
                    </a>
                @endif
            </div>

            {{-- Lối tắt — lấp khoảng trống, đồng bộ chiều cao với panel phải --}}
            <div class="mt-auto pt-5">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Lối tắt</p>
                <div class="grid gap-2 sm:grid-cols-3">
                    @foreach (($isAdminWs ? $adminShortcuts : $studentShortcuts) as $sc)
                        <a href="{{ $sc['href'] }}" wire:navigate class="group flex items-center gap-3 rounded-xl border border-outline-variant/60 bg-surface-container-low p-3 transition hover:bg-white sm:flex-col sm:items-start sm:gap-2 {{ $isAdminWs ? 'hover:border-primary/30' : 'hover:border-tertiary/30' }}">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-on-surface-variant shadow-sm ring-1 ring-outline-variant/30 transition {{ $isAdminWs ? 'group-hover:text-primary' : 'group-hover:text-tertiary' }}">
                                <x-user.icon :name="$sc['icon']" :size="18" />
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-on-surface">{{ $sc['label'] }}</span>
                                <span class="block truncate text-xs text-on-surface-variant">{{ $sc['hint'] }}</span>
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
                    <p class="flex items-center gap-2 text-sm font-bold text-on-surface">
                        <x-user.icon name="alert-circle" :size="16" class="text-primary" />
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
                <div class="mt-3 divide-y divide-outline-variant/50">
                    @foreach ($adminNeedRows as $row)
                        <a href="{{ $row['href'] }}" wire:navigate class="group -mx-2 flex items-center gap-3 rounded-xl px-2 py-2.5 transition hover:bg-surface-container-low">
                            <span class="{{ $row['bg'] }} {{ $row['tone'] }} grid h-9 w-9 shrink-0 place-items-center rounded-lg">
                                <x-user.icon :name="$row['icon']" :size="18" />
                            </span>
                            <span class="flex-1 text-sm font-medium text-on-surface">{{ $row['label'] }}</span>
                            <span @class([
                                'grid h-7 min-w-7 place-items-center rounded-lg px-2 text-sm font-bold tabular-nums',
                                $row['bg'].' '.$row['tone'] => $row['value'] > 0,
                                'text-on-surface-variant' => ! ($row['value'] > 0),
                            ])>{{ $row['value'] }}</span>
                            <x-user.icon name="chevron-right" :size="16" class="-ml-1 text-on-surface-variant transition group-hover:translate-x-0.5" />
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
                <p class="flex items-center gap-2 text-sm font-bold text-on-surface">
                    <x-user.icon name="bar-chart" :size="16" class="text-tertiary" />
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
                    <h3 class="flex items-center gap-2 font-headline-sm text-headline-sm text-on-surface">
                        <x-user.icon name="book-open" class="text-primary" />
                        Không gian Chủ lớp
                    </h3>
                    <p class="mt-1 text-body-md text-on-surface-variant">Quản lý lớp học, học viên, buổi điểm danh, báo cáo và cảnh báo chuyên cần.</p>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ($adminStats as $stat)
                        <div class="dash-card dash-card-hover rounded-2xl p-4 sm:p-5">
                            <span class="{{ $stat['bg'] }} {{ $stat['color'] }} mb-3 grid h-10 w-10 place-items-center rounded-xl">
                                <x-user.icon :name="$stat['icon']" :size="20" />
                            </span>
                            <div class="text-2xl font-bold leading-none text-on-surface sm:text-3xl">{{ $stat['value'] }}</div>
                            <p class="mt-1.5 text-xs text-on-surface-variant sm:text-sm">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div>
                    <h4 class="mb-4 text-[16px] font-bold text-on-surface">Thao tác nhanh</h4>
                    <div class="grid grid-cols-3 gap-2 sm:gap-3 md:grid-cols-3 lg:grid-cols-6">
                        @foreach ($adminActions as $action)
                            <a href="{{ $action['href'] }}" class="group flex flex-col items-center justify-center rounded-2xl p-3 transition-all hover:bg-surface-container-lowest hover:shadow-sm">
                                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white text-on-surface-variant shadow-sm ring-1 ring-outline-variant/20 group-hover:text-primary transition-all group-hover:scale-105">
                                    <x-user.icon :name="$action['icon']" :size="20" />
                                </div>
                                <span class="line-clamp-1 text-center text-xs font-semibold text-on-surface-variant group-hover:text-on-surface">{{ $action['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h4 class="mb-4 text-[16px] font-bold text-on-surface">Lớp tôi quản lý</h4>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @forelse ($managedClassCards as $class)
                            <article class="dash-card dash-card-hover group flex flex-col rounded-2xl p-5">
                                <div class="mb-4 flex items-start justify-between">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/20 {{ $class['color'] }}">
                                        <x-user.icon :name="$class['icon']" :size="24" />
                                    </div>
                                    <span class="rounded-full bg-surface-container-lowest px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant ring-1 ring-outline-variant/20">{{ $class['status_label'] }}</span>
                                </div>
                                <div class="flex flex-1 flex-col">
                                    <div class="mb-1 flex items-start justify-between">
                                        <h4 class="line-clamp-2 text-lg font-bold text-on-surface transition-colors group-hover:{{ $class['color'] }}" title="{{ $class['title'] }}">{{ $class['title'] }}</h4>
                                        <button type="button" class="rounded-full p-1 text-on-surface-variant hover:bg-surface-container hover:text-on-surface">
                                            <x-user.icon name="more-vertical" :size="20" />
                                        </button>
                                    </div>
                                    <p class="mb-4 flex items-center gap-2 text-sm text-on-surface-variant">
                                        <span class="font-bold">Mã lớp: {{ $class['code'] }}</span>
                                    </p>
                                    <div class="mb-6 space-y-3 rounded-2xl border border-outline-variant/20 bg-surface-container-low p-4">
                                        <div class="flex justify-between text-sm text-on-surface">
                                            <span class="flex items-center gap-2 font-bold">
                                                <x-user.icon name="users" :size="16" class="text-on-surface-variant" />
                                                {{ $class['students'] }} học viên
                                            </span>
                                            <span class="font-bold text-on-surface-variant">{{ $class['sessions'] }} buổi</span>
                                        </div>
                                        <div>
                                            <div class="mb-1 flex justify-between text-sm">
                                                <span class="text-on-surface-variant">Chuyên cần cả lớp</span>
                                                <span class="{{ $class['color'] }} font-bold">{{ $class['attendance_label'] ?? (($class['attendance'] ?? 0) . '%') }}</span>
                                            </div>
                                            <div class="h-2 w-full overflow-hidden rounded-full bg-surface-container-highest">
                                                <div class="{{ $class['bar'] }} h-full rounded-full" style="width: {{ $class['attendance_bar_width'] ?? ($class['attendance'] ?? 0) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-auto grid grid-cols-2 gap-3">
                                        <a href="{{ route('lecturer.attendance.create', ['class_id' => $class['id']]) }}" class="flex items-center justify-center gap-2 rounded-xl bg-primary py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary/90">
                                            <x-user.icon name="check-square" :size="18" />
                                            Điểm danh
                                        </a>
                                        <a href="{{ route('lecturer.classes.show', $class['id']) }}" class="flex items-center justify-center gap-2 rounded-xl border border-outline-variant py-2.5 text-sm font-bold text-on-surface-variant transition-colors hover:bg-surface-container">
                                            <x-user.icon name="eye" :size="18" />
                                            Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="col-span-full rounded-2xl border border-dashed border-outline-variant/30 bg-white p-8 text-center text-sm font-semibold text-on-surface-variant">
                                Chưa có lớp học nào để hiển thị.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div>
                        <h4 class="mb-4 flex items-center gap-2 text-[16px] font-bold text-on-surface">
                            <x-user.icon name="alert-triangle" class="text-error" />
                            Cảnh báo cần xử lý
                        </h4>
                        <div class="space-y-3">
                            @foreach ($alerts as $alert)
                                <div class="{{ $alert['border'] }} flex items-start gap-4 rounded-2xl border bg-white p-4">
                                    <div class="{{ $alert['bg'] }} {{ $alert['color'] }} flex h-10 w-10 shrink-0 items-center justify-center rounded-full">
                                        <x-user.icon :name="$alert['icon']" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-on-surface">{{ $alert['title'] }}</p>
                                        <p class="mt-1 text-xs text-on-surface-variant">{{ $alert['meta'] }}</p>
                                    </div>
                                    @if (! empty($alert['href']) && ! empty($alert['button']))
                                        <a href="{{ $alert['href'] }}" wire:navigate class="{{ $alert['button'] }} shrink-0 rounded-lg px-3 py-1.5 text-xs font-bold">{{ $alert['action'] }}</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h4 class="mb-4 flex items-center gap-2 text-[16px] font-bold text-on-surface">
                            <x-user.icon name="activity" class="text-tertiary" />
                            Hoạt động gần đây
                        </h4>
                        <div class="relative rounded-2xl border border-outline-variant/10 bg-white p-6">
                            <div class="absolute bottom-8 left-10 top-8 w-[2px] bg-surface-container-high"></div>
                            <div class="relative z-10 space-y-6">
                                @foreach ($activities as $activity)
                                    <div class="flex items-start gap-4">
                                        <div class="{{ $activity['bg'] }} flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-4 border-white text-white shadow-sm">
                                            <x-user.icon :name="$activity['icon']" :size="12" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-on-surface">{{ $activity['text'] }}</p>
                                            <p class="mt-0.5 text-xs text-on-surface-variant">{{ $activity['time'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @else
            <section id="student" class="mt-8 space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="mb-6">
                    <h3 class="flex items-center gap-2 font-headline-sm text-headline-sm text-on-surface">
                        <x-user.icon name="user" class="text-tertiary" />
                        Không gian Học viên
                    </h3>
                    <p class="mt-1 text-body-md text-on-surface-variant">Theo dõi lớp đã tham gia, lịch sử điểm danh cá nhân, chuyên cần và đơn xin nghỉ.</p>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ($studentStats as $stat)
                        <div class="dash-card dash-card-hover rounded-2xl p-4 sm:p-5">
                            <span class="{{ $stat['bg'] }} {{ $stat['color'] }} mb-3 grid h-10 w-10 place-items-center rounded-xl">
                                <x-user.icon :name="$stat['icon']" :size="20" />
                            </span>
                            <div class="text-2xl font-bold leading-none text-on-surface sm:text-3xl">{{ $stat['value'] }}</div>
                            <p class="mt-1.5 text-xs text-on-surface-variant sm:text-sm">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div>
                    <h4 class="mb-4 text-[16px] font-bold text-on-surface">Thao tác nhanh</h4>
                    <div class="grid grid-cols-3 gap-2 sm:gap-3 md:grid-cols-4 lg:grid-cols-8">
                        @foreach ($studentActions as $action)
                            @if(isset($action['action']))
                                <button type="button" x-on:click="{{ $action['action'] }}" class="group flex flex-col items-center justify-center rounded-2xl p-3 transition-all hover:bg-surface-container-lowest hover:shadow-sm">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white text-on-surface-variant shadow-sm ring-1 ring-outline-variant/20 group-hover:text-tertiary transition-all group-hover:scale-105">
                                        <x-user.icon :name="$action['icon']" :size="20" />
                                    </div>
                                    <span class="line-clamp-1 text-center text-xs font-semibold text-on-surface-variant group-hover:text-on-surface">{{ $action['label'] }}</span>
                                </button>
                            @else
                                <a href="{{ $action['href'] }}" class="group flex flex-col items-center justify-center rounded-2xl p-3 transition-all hover:bg-surface-container-lowest hover:shadow-sm">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white text-on-surface-variant shadow-sm ring-1 ring-outline-variant/20 group-hover:text-tertiary transition-all group-hover:scale-105">
                                        <x-user.icon :name="$action['icon']" :size="20" />
                                    </div>
                                    <span class="line-clamp-1 text-center text-xs font-semibold text-on-surface-variant group-hover:text-on-surface">{{ $action['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div>
                    <h4 class="mb-4 text-[16px] font-bold text-on-surface">Lớp tôi tham gia</h4>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @forelse ($joinedCards as $class)
                            <article class="dash-card dash-card-hover group flex flex-col rounded-2xl p-5">
                                <div class="mb-4 flex items-start justify-between">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-surface-container-lowest shadow-sm ring-1 ring-outline-variant/20 {{ $class['color'] }}">
                                        <span class="text-xl font-bold uppercase">{{ substr($class['title'], 0, 1) }}</span>
                                    </div>
                                    <span class="{{ $class['statusClass'] }} rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider ring-1 ring-outline-variant/20">{{ $class['status'] }}</span>
                                </div>
                                <div class="flex flex-1 flex-col">
                                    <h4 class="line-clamp-2 mb-1 text-lg font-bold text-on-surface transition-colors group-hover:{{ $class['color'] }}" title="{{ $class['title'] }}">{{ $class['title'] }}</h4>
                                    <p class="mb-4 text-sm text-on-surface-variant">Giảng viên: <span class="font-bold">{{ $class['teacher'] }}</span></p>
                                    <p class="mb-4 flex items-center gap-2 text-sm text-on-surface-variant">
                                        <span class="font-bold">{{ $class['code'] }}</span>
                                    </p>
                                    <div class="mb-6 space-y-3 rounded-2xl border border-outline-variant/20 bg-surface-container-low p-4">
                                        <div class="flex justify-between text-sm text-on-surface">
                                            <span class="text-on-surface-variant">Tổng buổi vắng</span>
                                            <span class="font-bold text-on-surface-variant">{{ $class['absent'] }}</span>
                                        </div>
                                        <div>
                                            <div class="mb-1 flex justify-between text-sm">
                                                <span class="text-on-surface-variant">Chuyên cần cá nhân</span>
                                                <span class="{{ $class['color'] }} font-bold">{{ $class['attendance_label'] ?? (($class['attendance'] ?? 0) . '%') }}</span>
                                            </div>
                                            <div class="h-2 w-full overflow-hidden rounded-full bg-surface-container-highest">
                                                <div class="{{ $class['bar'] }} h-full rounded-full" style="width: {{ $class['attendance_bar_width'] ?? ($class['attendance'] ?? 0) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-auto grid grid-cols-2 gap-3">
                                        <button type="button" class="flex items-center justify-center gap-2 rounded-xl bg-tertiary py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-tertiary/90">
                                            <x-user.icon name="history" :size="18" />
                                            Xem lịch sử
                                        </button>
                                        <button type="button" class="flex items-center justify-center gap-2 rounded-xl border border-outline-variant py-2.5 text-sm font-bold text-on-surface-variant transition-colors hover:bg-surface-container">
                                            <x-user.icon name="eye" :size="18" />
                                            Chi tiết
                                        </button>
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
