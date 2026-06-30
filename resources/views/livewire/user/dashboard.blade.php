@php
    $totalClasses = $overview['total_classes'] ?? 0;
    $todayAttendanceSessions = $overview['today_attendance_sessions'] ?? 0;
    $unclosedAttendanceSessions = $overview['unclosed_attendance_sessions'] ?? 0;
    $attendanceWarningStudentsCount = $overview['attendance_warning_students_count'] ?? 0;
    $pendingLeaveRequestsCount = $overview['pending_leave_requests_count'] ?? 0;

    $adminStats = [
        ['label' => 'Lớp quản lý', 'value' => $totalClasses, 'icon' => 'book-open', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
        ['label' => 'Tổng học viên', 'value' => $overview['total_students'] ?? 0, 'icon' => 'users', 'color' => 'text-tertiary', 'bg' => 'bg-tertiary/10'],
        ['label' => 'Buổi điểm danh hôm nay', 'value' => $todayAttendanceSessions, 'icon' => 'calendar-check', 'color' => 'text-secondary', 'bg' => 'bg-secondary/10'],
        ['label' => 'Buổi chưa chốt sổ', 'value' => $unclosedAttendanceSessions, 'icon' => 'clock', 'color' => 'text-error', 'bg' => 'bg-error/10'],
        ['label' => 'SV gần/vượt ngưỡng', 'value' => $attendanceWarningStudentsCount, 'icon' => 'alert-triangle', 'color' => 'text-error', 'bg' => 'bg-error/10'],
        ['label' => 'Đơn nghỉ chờ duyệt', 'value' => $pendingLeaveRequestsCount, 'icon' => 'file-text', 'color' => 'text-secondary', 'bg' => 'bg-secondary/10'],
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

    $studentStats = [
        ['label' => 'Lớp tham gia', 'value' => $studentJoinedClassesCount, 'icon' => 'users', 'color' => 'text-tertiary', 'bg' => 'bg-tertiary/10'],
        ['label' => 'CC trung bình', 'value' => "{$studentAverageAttendance}%", 'icon' => 'check-circle', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
        ['label' => 'Buổi vắng', 'value' => $studentAbsentSessions, 'icon' => 'clock', 'color' => 'text-error', 'bg' => 'bg-error/10'],
        ['label' => 'Cảnh báo', 'value' => $studentWarningCount, 'icon' => 'alert-triangle', 'color' => 'text-error', 'bg' => 'bg-error/10'],
        ['label' => 'Đơn chờ duyệt', 'value' => $studentPendingLeaveRequests, 'icon' => 'file-text', 'color' => 'text-secondary', 'bg' => 'bg-secondary/10'],
        ['label' => 'Buổi gần nhất', 'value' => $studentLatestAttendanceLabel, 'icon' => 'calendar-check', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
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

<div class="mx-auto max-w-[1400px] p-4 pb-24 md:p-8 md:pb-12">
    @php
        $heroName = Auth::user()?->name ?? 'bạn';
        $heroTiles = [
            ['label' => 'Lớp quản lý', 'value' => $totalClasses, 'icon' => 'book-open', 'tone' => 'text-primary', 'ring' => 'bg-primary/10'],
            ['label' => 'Buổi đang mở', 'value' => $unclosedAttendanceSessions, 'icon' => 'clock', 'tone' => 'text-secondary', 'ring' => 'bg-secondary/10'],
            ['label' => 'Cần xử lý', 'value' => $attendanceWarningStudentsCount, 'icon' => 'alert-triangle', 'tone' => 'text-error', 'ring' => 'bg-error/10'],
            ['label' => 'Lớp tham gia', 'value' => $studentJoinedClassesCount, 'icon' => 'users', 'tone' => 'text-tertiary', 'ring' => 'bg-tertiary/10'],
        ];
    @endphp
    <section class="mb-8">
        <div class="overflow-hidden rounded-2xl border border-outline-variant bg-white shadow-sm">
            <div class="grid gap-8 p-6 md:grid-cols-[1.5fr_1fr] md:p-8">
                <div class="min-w-0">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                        <x-user.icon name="layout-dashboard" :size="14" />
                        Bảng điều khiển
                    </span>
                    <h2 class="mt-4 text-2xl font-bold leading-tight tracking-tight text-on-surface md:text-3xl">
                        Xin chào, {{ $heroName }} 👋
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-on-surface-variant md:text-base">
                        Quản lý lớp học, tổ chức điểm danh, theo dõi chuyên cần và tham gia lớp — tất cả trong một nơi.
                    </p>

                    <div class="mt-6 inline-flex rounded-xl border border-outline-variant bg-surface-container p-1">
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

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <a href="{{ route('create-class') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-container">
                            <x-user.icon name="plus" :size="18" />
                            Tạo lớp mới
                        </a>
                        <button type="button" x-on:click="$dispatch('open-join-class-modal')" class="inline-flex items-center gap-2 rounded-lg border border-outline-variant bg-white px-4 py-2.5 text-sm font-semibold text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface">
                            <x-user.icon name="key" :size="18" />
                            Tham gia bằng mã
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 content-start gap-3">
                    @foreach ($heroTiles as $tile)
                        <div class="rounded-xl border border-outline-variant bg-surface-container-low p-4">
                            <span class="{{ $tile['ring'] }} {{ $tile['tone'] }} mb-3 inline-flex h-9 w-9 items-center justify-center rounded-lg">
                                <x-user.icon :name="$tile['icon']" :size="18" />
                            </span>
                            <p class="text-2xl font-bold leading-none text-on-surface">{{ $tile['value'] }}</p>
                            <p class="mt-1.5 text-xs font-medium text-on-surface-variant">{{ $tile['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
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

                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
                    @foreach ($adminStats as $stat)
                        <div class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-outline-variant/10 transition-shadow hover:shadow-md overflow-hidden">
                            <div class="{{ $stat['bg'] }} {{ $stat['color'] }} flex h-10 w-10 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-full">
                                <x-user.icon :name="$stat['icon']" :size="20" class="sm:w-6 sm:h-6" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[11px] sm:text-xs font-semibold text-on-surface-variant" title="{{ $stat['label'] }}">{{ $stat['label'] }}</p>
                                <h3 class="truncate text-xl sm:text-2xl font-bold text-on-surface leading-tight">{{ $stat['value'] }}</h3>
                            </div>
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
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">
                        @forelse ($managedClassCards as $class)
                            <article class="group relative flex flex-col overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:ring-outline-variant/40">
                                <div class="absolute inset-x-0 top-0 h-1 {{ $class['bar'] }}"></div>
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
                                        <span class="font-bold">{{ $class['code'] }}</span>
                                        <span class="h-1 w-1 rounded-full bg-outline-variant"></span>
                                        <span>{{ $class['subject_code'] }}</span>
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
                                                <span class="{{ $class['color'] }} font-bold">{{ $class['attendance'] }}%</span>
                                            </div>
                                            <div class="h-2 w-full overflow-hidden rounded-full bg-surface-container-highest">
                                                <div class="{{ $class['bar'] }} h-full rounded-full" style="width: {{ $class['attendance'] }}%"></div>
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
                        <div class="relative rounded-3xl border border-outline-variant/10 bg-white p-6">
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

                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
                    @foreach ($studentStats as $stat)
                        <div class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-outline-variant/10 transition-shadow hover:shadow-md overflow-hidden">
                            <div class="{{ $stat['bg'] }} {{ $stat['color'] }} flex h-10 w-10 shrink-0 items-center justify-center rounded-full">
                                <x-user.icon :name="$stat['icon']" :size="20" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[11px] font-semibold uppercase tracking-wide text-on-surface-variant">{{ $stat['label'] }}</p>
                                <h3 class="truncate text-xl font-bold text-on-surface leading-tight">{{ $stat['value'] }}</h3>
                            </div>
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
                            <article class="group relative flex flex-col overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:ring-outline-variant/40">
                                <div class="absolute inset-x-0 top-0 h-1 {{ $class['bar'] }}"></div>
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
                                                <span class="{{ $class['color'] }} font-bold">{{ $class['attendance'] }}%</span>
                                            </div>
                                            <div class="h-2 w-full overflow-hidden rounded-full bg-surface-container-highest">
                                                <div class="{{ $class['bar'] }} h-full rounded-full" style="width: {{ $class['attendance'] }}%"></div>
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
            <div class="flex max-h-[90vh] w-full max-w-2xl animate-in zoom-in-95 flex-col overflow-y-auto rounded-[2rem] bg-white shadow-2xl duration-200">
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
