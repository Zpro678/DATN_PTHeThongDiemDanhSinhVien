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
        ['label' => 'Điểm danh QR', 'icon' => 'qr-code', 'color' => 'text-tertiary', 'href' => route('lecturer.attendance.qr.create')],
        ['label' => 'Điểm danh thủ công', 'icon' => 'edit', 'color' => 'text-secondary', 'href' => route('lecturer.attendance.manual.create')],
        ['label' => 'Đơn xin nghỉ', 'icon' => 'file-text', 'color' => 'text-error', 'href' => route('lecturer.leave-requests.index')],
        ['label' => 'Quản lý SV', 'icon' => 'users', 'color' => 'text-tertiary', 'href' => route('lecturer.students.index')],
        ['label' => 'Thống kê', 'icon' => 'check-circle', 'color' => 'text-secondary', 'href' => '#'],
        ['label' => 'Xuất báo cáo', 'icon' => 'upload', 'color' => 'text-tertiary', 'href' => '#'],
    ];

    $alerts = collect($overview['attendance_warning_students'] ?? []) // Lấy danh sách học viên gần/vượt ngưỡng nghỉ không phép từ DashboardStatisticService.
        ->map(function (array $student): array {
            $isExceeded = ($student['status'] ?? null) === 'exceeded'; // exceeded = đã nghỉ không phép từ 20% số tiết đã học trở lên.
            $absencePercent = $student['unexcused_absence_percent'] ?? 0; // Phần trăm nghỉ không phép của học viên.

            return [
                'title' => $isExceeded
                    ? "{$student['full_name']} đã nghỉ {$absencePercent}%"
                    : "{$student['full_name']} sắp vượt ngưỡng nghỉ",
                'meta' => "{$student['student_code']} - {$student['class_name']} - Vắng {$student['unexcused_absent_lessons']}/{$student['studied_lessons']} tiết không phép",
                'icon' => $isExceeded ? 'alert-triangle' : 'user-check',
                'color' => $isExceeded ? 'text-error' : 'text-secondary',
                'bg' => $isExceeded ? 'bg-error/10' : 'bg-secondary/10',
                'border' => $isExceeded ? 'border-error/20' : 'border-secondary/30',
                'button' => $isExceeded ? 'bg-error text-white' : 'border border-secondary text-secondary',
                'action' => $isExceeded ? 'Xử lý' : 'Theo dõi',
                'href' => route('lecturer.students.show', $student['id']),
            ];
        })
        ->when($unclosedAttendanceSessions > 0, function ($items) use ($unclosedAttendanceSessions) {
            return $items->push([
                'title' => "{$unclosedAttendanceSessions} buổi điểm danh chưa chốt sổ",
                'meta' => 'Cần chốt sổ để dữ liệu chuyên cần được tính chính xác.',
                'icon' => 'clock',
                'color' => 'text-secondary',
                'bg' => 'bg-secondary/10',
                'border' => 'border-secondary/30',
                'button' => 'border border-secondary text-secondary',
                'action' => 'Xử lý',
                'href' => route('lecturer.attendance.index'),
            ]);
        })
        ->when($pendingLeaveRequestsCount > 0, function ($items) use ($pendingLeaveRequestsCount) {
            return $items->push([
                'title' => "{$pendingLeaveRequestsCount} đơn nghỉ đang chờ duyệt",
                'meta' => 'Kiểm tra đơn để cập nhật trạng thái vắng có phép cho học viên.',
                'icon' => 'file-text',
                'color' => 'text-primary',
                'bg' => 'bg-primary/10',
                'border' => 'border-primary/20',
                'button' => 'bg-primary text-white',
                'action' => 'Duyệt đơn',
                'href' => route('lecturer.leave-requests.index'),
            ]);
        })
        ->whenEmpty(fn ($items) => $items->push([
            'title' => 'Không có cảnh báo cần xử lý',
            'meta' => 'Chuyên cần, buổi điểm danh và đơn nghỉ hiện đang ổn định.',
            'icon' => 'check-circle',
            'color' => 'text-tertiary',
            'bg' => 'bg-tertiary/10',
            'border' => 'border-tertiary/20',
            'button' => 'border border-tertiary text-tertiary',
            'action' => 'Ổn định',
            'href' => null,
        ]))
        ->take(5)
        ->values()
        ->all();

    $activities = $overview['recent_activities'] ?? [
        ['text' => 'Chưa có hoạt động gần đây', 'time' => 'Khi có điểm danh hoặc đơn nghỉ mới, hệ thống sẽ hiển thị tại đây.', 'icon' => 'activity', 'bg' => 'bg-primary'],
    ];

    $studentDashboardStats = $studentDashboard['stats'] ?? [];
    $studentJoinedCards = $studentDashboard['joined_cards'] ?? [];
    $studentJoinedClassesCount = $studentDashboardStats['joined_classes'] ?? 0;
    $studentAverageAttendance = $studentDashboardStats['attendance_percent'] ?? 100;
    $studentAbsentLessons = $studentDashboardStats['absent_lessons'] ?? 0;
    $studentWarningCount = $studentDashboardStats['warning_count'] ?? 0;
    $studentPendingLeaveRequests = $studentDashboardStats['pending_leave_requests'] ?? 0;
    $studentLatestAttendanceLabel = $studentDashboardStats['latest_attendance_label'] ?? 'Chưa có';

    $studentStats = [
        ['label' => 'Lớp đang tham gia', 'value' => $studentJoinedClassesCount, 'icon' => 'users', 'color' => 'text-tertiary', 'bg' => 'bg-tertiary/10'],
        ['label' => 'Chuyên cần trung bình', 'value' => "{$studentAverageAttendance}%", 'icon' => 'check-circle', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
        ['label' => 'Tổng tiết vắng', 'value' => $studentAbsentLessons, 'icon' => 'clock', 'color' => 'text-error', 'bg' => 'bg-error/10'],
        ['label' => 'Cảnh báo chuyên cần', 'value' => $studentWarningCount, 'icon' => 'alert-triangle', 'color' => 'text-error', 'bg' => 'bg-error/10'],
        ['label' => 'Đơn nghỉ đang chờ', 'value' => $studentPendingLeaveRequests, 'icon' => 'file-text', 'color' => 'text-secondary', 'bg' => 'bg-secondary/10'],
        ['label' => 'Buổi điểm danh gần nhất', 'value' => $studentLatestAttendanceLabel, 'icon' => 'calendar-check', 'color' => 'text-primary', 'bg' => 'bg-primary/10'],
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
    <section class="mb-8 grid grid-cols-12 items-center gap-6">
        <div class="relative col-span-12 overflow-hidden rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:p-10 xl:col-span-7">
            <div class="absolute -left-24 -top-24 h-64 w-64 rounded-full bg-primary/5 blur-3xl transition-all duration-700"></div>
            <div class="relative z-10">
                <h2 class="mb-4 text-2xl font-bold leading-tight tracking-normal text-on-background md:text-[32px]">
                    Xin chào, hôm nay bạn muốn quản lý hay tham gia lớp học?
                </h2>
                <p class="mb-8 max-w-xl text-body-lg leading-relaxed text-on-surface-variant">
                    Quản lý lớp học, tổ chức điểm danh, theo dõi chuyên cần và tham gia lớp học trong một dashboard duy nhất.
                </p>
                <div class="mb-8 inline-flex rounded-full bg-surface-container-low p-1.5 shadow-inner">
                    <button
                        type="button"
                        wire:click="setWorkspace('admin')"
                        @class([
                            'flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-bold transition-all',
                            'bg-white text-primary shadow' => $workspace === 'admin',
                            'text-on-surface-variant hover:text-on-surface' => $workspace !== 'admin',
                        ])
                    >
                        <x-user.icon name="shield" :size="18" />
                        Không gian Chủ lớp
                    </button>
                    <button
                        type="button"
                        wire:click="setWorkspace('student')"
                        @class([
                            'flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-bold transition-all',
                            'bg-white text-tertiary shadow' => $workspace === 'student',
                            'text-on-surface-variant hover:text-on-surface' => $workspace !== 'student',
                        ])
                    >
                        <x-user.icon name="user" :size="18" />
                        Không gian Học viên
                    </button>
                </div>
                <div class="flex flex-wrap gap-6 border-t border-outline-variant/20 pt-6">
                    <a href="{{ route('create-class') }}" class="flex items-center gap-2 text-sm font-bold text-primary underline-offset-4 hover:underline">
                        <x-user.icon name="plus" :size="18" />
                        Tạo lớp mới
                    </a>
                    <button type="button" x-on:click="$dispatch('open-join-class-modal')" class="flex items-center gap-2 text-sm font-bold text-on-surface-variant transition-colors hover:text-primary">
                        <x-user.icon name="key" :size="18" />
                        Tham gia lớp bằng mã
                    </button>
                </div>
            </div>
        </div>

        <div class="col-span-12 hidden h-[350px] md:block xl:col-span-5 xl:h-full">
            <div class="relative flex h-full min-h-[350px] items-center justify-center overflow-hidden rounded-[2rem] border border-white bg-white/60 p-8 shadow-sm backdrop-blur-xl">
                <div class="relative mt-4 flex h-64 w-64 items-center justify-center">
                    <div class="z-20 flex h-28 w-28 flex-col items-center justify-center rounded-full border-4 border-primary-container/20 bg-white shadow-xl">
                        <span class="font-stat-lg text-3xl text-primary">{{ $studentAverageAttendance }}%</span>
                        <span class="mt-1 px-4 text-center text-[9px] font-bold uppercase leading-none text-outline">Chuyên cần TB</span>
                    </div>
                    <div class="orbit-animation absolute h-full w-full rounded-full border border-dashed border-outline-variant/40">
                        <div class="orbit-item absolute -top-5 left-1/2 -translate-x-1/2">
                            <div class="flex flex-col items-center rounded-2xl border border-outline-variant/10 bg-white px-4 py-2 shadow-md">
                                <span class="text-lg font-bold leading-none text-primary">{{ $totalClasses }}</span>
                                <span class="mt-1 text-[9px] font-bold uppercase text-on-surface-variant">Lớp quản lý</span>
                            </div>
                        </div>
                        <div class="orbit-item absolute -right-8 top-1/2 -translate-y-1/2">
                            <div class="flex flex-col items-center rounded-2xl bg-primary px-4 py-2 text-white shadow-md">
                                <span class="text-lg font-bold leading-none">{{ $unclosedAttendanceSessions }}</span>
                                <span class="mt-1 text-[9px] font-bold uppercase opacity-90">Đang mở</span>
                            </div>
                        </div>
                        <div class="orbit-item absolute -bottom-5 left-1/2 -translate-x-1/2">
                            <div class="flex flex-col items-center rounded-2xl border border-error/20 bg-error/10 px-4 py-2 text-error shadow-md">
                                <span class="text-lg font-bold leading-none">{{ $attendanceWarningStudentsCount }}</span>
                                <span class="mt-1 text-[9px] font-bold uppercase">Cần xử lý</span>
                            </div>
                        </div>
                        <div class="orbit-item absolute -left-8 top-1/2 -translate-y-1/2">
                            <div class="flex flex-col items-center rounded-2xl border border-outline-variant/10 bg-white px-4 py-2 shadow-md">
                                <span class="text-lg font-bold leading-none text-tertiary">{{ $studentJoinedClassesCount }}</span>
                                <span class="mt-1 text-[9px] font-bold uppercase text-on-surface-variant">Lớp tham gia</span>
                            </div>
                        </div>
                    </div>
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

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    @foreach ($adminStats as $stat)
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/10 transition-shadow hover:shadow-md">
                            <div class="{{ $stat['bg'] }} {{ $stat['color'] }} flex h-12 w-12 shrink-0 items-center justify-center rounded-full">
                                <x-user.icon :name="$stat['icon']" :size="24" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-on-surface-variant">{{ $stat['label'] }}</p>
                                <h3 class="text-2xl font-bold text-on-surface leading-tight">{{ $stat['value'] }}</h3>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div>
                    <h4 class="mb-4 text-[16px] font-bold text-on-surface">Thao tác nhanh</h4>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-8">
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
                                        <h4 class="line-clamp-1 text-lg font-bold text-on-surface transition-colors group-hover:{{ $class['color'] }}">{{ $class['title'] }}</h4>
                                        <button type="button" class="rounded-full p-1 text-on-surface-variant hover:bg-surface-container hover:text-on-surface">
                                            <x-user.icon name="more-vertical" :size="20" />
                                        </button>
                                    </div>
                                    <p class="mb-4 flex items-center gap-2 text-sm text-on-surface-variant">
                                        <span class="font-bold">{{ $class['code'] }}</span>
                                        <span class="h-1 w-1 rounded-full bg-outline-variant"></span>
                                        <span>{{ $class['subject_code'] }}</span>
                                        <span class="h-1 w-1 rounded-full bg-outline-variant"></span>
                                        <span>{{ $class['semester'] }}</span>
                                    </p>
                                    <div class="mb-6 space-y-3 rounded-2xl border border-outline-variant/20 bg-surface-container-low p-4">
                                        <div class="flex justify-between text-sm text-on-surface">
                                            <span class="flex items-center gap-2 font-bold">
                                                <x-user.icon name="users" :size="16" class="text-on-surface-variant" />
                                                {{ $class['students'] }} học viên
                                            </span>
                                            <span class="font-bold text-on-surface-variant">{{ $class['lessons'] }} tiết</span>
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
                                    @if (! empty($alert['href']))
                                        <a href="{{ $alert['href'] }}" class="{{ $alert['button'] }} rounded-lg px-3 py-1.5 text-xs font-bold">{{ $alert['action'] }}</a>
                                    @else
                                        <button type="button" class="{{ $alert['button'] }} rounded-lg px-3 py-1.5 text-xs font-bold">{{ $alert['action'] }}</button>
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

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    @foreach ($studentStats as $stat)
                        <div class="flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/10 transition-shadow hover:shadow-md">
                            <div class="{{ $stat['bg'] }} {{ $stat['color'] }} flex h-12 w-12 shrink-0 items-center justify-center rounded-full">
                                <x-user.icon :name="$stat['icon']" :size="24" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-on-surface-variant">{{ $stat['label'] }}</p>
                                <h3 class="text-2xl font-bold text-on-surface leading-tight">{{ $stat['value'] }}</h3>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div>
                    <h4 class="mb-4 text-[16px] font-bold text-on-surface">Thao tác nhanh</h4>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-8">
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
                                    <h4 class="line-clamp-1 mb-1 text-lg font-bold text-on-surface transition-colors group-hover:{{ $class['color'] }}">{{ $class['title'] }}</h4>
                                    <p class="mb-4 text-sm text-on-surface-variant">Giảng viên: <span class="font-bold">{{ $class['teacher'] }}</span></p>
                                    <p class="mb-4 flex items-center gap-2 text-sm text-on-surface-variant">
                                        <span class="font-bold">{{ $class['code'] }}</span>
                                        <span class="h-1 w-1 rounded-full bg-outline-variant"></span>
                                        <span>{{ $class['semester'] ?? 'Chưa cập nhật' }}</span>
                                    </p>
                                    <div class="mb-6 space-y-3 rounded-2xl border border-outline-variant/20 bg-surface-container-low p-4">
                                        <div class="flex justify-between text-sm text-on-surface">
                                            <span class="text-on-surface-variant">Tổng tiết vắng</span>
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
                                <p class="mt-2 text-sm text-on-surface-variant">Khi bạn tham gia lớp, thông tin chuyên cần và số tiết vắng sẽ hiển thị tại đây.</p>
                                <a href="{{ route('student.classes.join') }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-tertiary px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-tertiary/90">
                                    Tham gia lớp
                                </a>
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
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Mã môn học</span>
                            <input type="text" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="WEB301">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Học kỳ</span>
                            <input type="text" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="HK2 2025-2026">
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
                            <span class="mb-2 block text-sm font-bold text-on-surface">Số tiết mỗi buổi</span>
                            <input type="number" value="3" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
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
