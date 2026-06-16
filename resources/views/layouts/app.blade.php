@php
    $user = auth()->user();
    $userName = $user?->name ?: 'SAMS User';
    $nameParts = preg_split('/\s+/', trim($userName)) ?: [];
    $userShortName = $nameParts ? end($nameParts) : $userName;
    $userCode = $user?->student_code ?? $user?->code ?? $user?->email ?? 'SAMS';
    $firstCharacter = function_exists('mb_substr') ? mb_substr($userName, 0, 1, 'UTF-8') : substr($userName, 0, 1);
    $userInitials = function_exists('mb_strtoupper') ? mb_strtoupper($firstCharacter, 'UTF-8') : strtoupper($firstCharacter);
    $studentUnreadCount = (int) ($studentUnreadCount ?? 0);
    $isAttendanceCompleted = (bool) ($isAttendanceCompleted ?? false);

    $variantAliases = [
        'ad' => 'admin',
        'administrator' => 'admin',
        'quan_tri' => 'admin',
        'quan-tri' => 'admin',
        'gv' => 'lecturer',
        'giang_vien' => 'lecturer',
        'giang-vien' => 'lecturer',
        'teacher' => 'lecturer',
        'instructor' => 'lecturer',
        'sv' => 'student',
        'sinh_vien' => 'student',
        'sinh-vien' => 'student',
    ];

    $requestedVariant = strtolower($variant ?? 'auto');
    $requestedVariant = $variantAliases[$requestedVariant] ?? $requestedVariant;
    $validVariants = ['admin', 'lecturer', 'student'];

    $pathVariant = match (true) {
        request()->is('admin', 'admin/*') => 'admin',
        request()->is('student', 'student/*', 'students1', 'sinh-vien', 'sinh-vien/*') => 'student',
        request()->is('lecturer', 'lecturer/*', 'teacher', 'teacher/*', 'giang-vien', 'giang-vien/*', 'preview/lecturer', 'preview/lecturer/*') => 'lecturer',
        default => null,
    };

    $roleVariant = null;

    if ($user && method_exists($user, 'hasAnyRole')) {
        try {
            if ($user->hasAnyRole(['admin', 'administrator', 'quan_tri', 'quan-tri', 'quản trị'])) {
                $roleVariant = 'admin';
            } elseif ($user->hasAnyRole(['giang_vien', 'giang-vien', 'lecturer', 'teacher', 'instructor', 'giảng viên'])) {
                $roleVariant = 'lecturer';
            } elseif ($user->hasAnyRole(['sinh_vien', 'sinh-vien', 'student', 'sinh viên'])) {
                $roleVariant = 'student';
            }
        } catch (\Throwable $exception) {
            $roleVariant = null;
        }
    }

    $resolvedVariant = in_array($requestedVariant, $validVariants, true)
        ? $requestedVariant
        : ($pathVariant ?? $roleVariant ?? 'lecturer');

    $isActive = function ($patterns): bool {
        foreach ((array) $patterns as $pattern) {
            if ($pattern === '/' && (request()->path() === '/' || request()->path() === '')) {
                return true;
            }

            if (request()->is($pattern) || request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    };

    $adminMenuGroups = [
        [
            'title' => 'Hệ thống',
            'items' => [
                ['name' => 'Dashboard', 'href' => '/admin', 'icon' => 'layout-dashboard', 'active' => ['admin']],
                ['name' => 'Quản lý tài khoản', 'href' => '/admin/accounts', 'icon' => 'user-square', 'badge' => '99+', 'active' => ['admin/accounts', 'admin/accounts/*']],
                ['name' => 'Nhật ký hệ thống', 'href' => '/admin/logs', 'icon' => 'activity', 'active' => ['admin/logs', 'admin/logs/*']],
            ],
        ],
        [
            'title' => 'Quản lý đào tạo',
            'items' => [
                ['name' => 'Quản lý khoa', 'href' => '/admin/faculties', 'icon' => 'school', 'active' => ['admin/faculties', 'admin/faculties/*']],
                ['name' => 'Quản lý ngành & môn', 'href' => '/admin/subjects', 'icon' => 'folder', 'active' => ['admin/subjects', 'admin/subjects/*']],
                ['name' => 'Quản lý học kỳ', 'href' => '/admin/semesters', 'icon' => 'calendar', 'active' => ['admin/semesters', 'admin/semesters/*']],
                ['name' => 'Quản lý lớp học', 'href' => '/admin/classes', 'icon' => 'book-open', 'active' => ['admin/classes', 'admin/classes/*']],
            ],
        ],
        [
            'title' => 'Nhân sự & học viên',
            'items' => [
                ['name' => 'Quản lý giảng viên', 'href' => '/admin/instructors', 'icon' => 'users', 'active' => ['admin/instructors', 'admin/instructors/*']],
                ['name' => 'Quản lý sinh viên', 'href' => '/admin/students', 'icon' => 'users', 'active' => ['admin/students', 'admin/students/*']],
            ],
        ],
        [
            'title' => 'Nghiệp vụ',
            'items' => [
                ['name' => 'Quản lý điểm danh', 'href' => '/admin/attendance', 'icon' => 'calendar-check', 'active' => ['admin/attendance', 'admin/attendance/*']],
                ['name' => 'Báo cáo & thống kê', 'href' => '/admin/reports', 'icon' => 'bar-chart', 'active' => ['admin/reports', 'admin/reports/*']],
            ],
        ],
    ];

    $lecturerDashboardHref = request()->is('preview/lecturer', 'preview/lecturer/*')
        ? '/preview/lecturer/dashboard'
        : '/dashboard';

    $lecturerNavigation = [
        ['name' => 'Bảng điều khiển', 'href' => '/lecturer/dashboard', 'icon' => 'layout-dashboard', 'active' => ['dashboard']],
        ['name' => 'Sinh viên', 'href' => '/lecturer/students', 'icon' => 'users', 'active' => ['students', 'students/*']],
        ['name' => 'Điểm danh', 'href' => '/lecturer/attendance', 'icon' => 'calendar-check', 'active' => ['attendance', 'attendance/*']],
        ['name' => 'Lớp học của tôi', 'href' => '/lecturer/courses', 'icon' => 'book-open', 'active' => ['lecturer/courses', 'lecturer/courses/*', 'lecturer/classes', 'lecturer/classes/*']],
        ['name' => 'Báo cáo', 'href' => '/lecturer/analytics', 'icon' => 'bar-chart', 'active' => ['analytics', 'analytics/*']],
        ['name' => 'Giao diện sinh viên', 'href' => '/students1', 'icon' => 'graduation-cap', 'active' => ['students1']],
    ];

    $lecturerMobileNavigation = [
        ['name' => 'Dashboard', 'href' => '/lecturer/dashboard', 'icon' => 'layout-dashboard', 'active' => ['dashboard']],
        ['name' => 'Lớp học', 'href' => '/lecturer/courses', 'icon' => 'book-open', 'active' => ['lecturer/courses', 'lecturer/courses/*', 'lecturer/classes', 'lecturer/classes/*']],
        ['name' => 'Điểm danh', 'href' => '/lecturer/attendance', 'icon' => 'calendar-check', 'active' => ['attendance', 'attendance/*']],
        ['name' => 'Báo cáo', 'href' => '/lecturer/analytics', 'icon' => 'bar-chart', 'active' => ['analytics', 'analytics/*']],
        ['name' => 'Sinh viên', 'href' => '/lecturer/students', 'icon' => 'user', 'active' => ['students', 'students/*']],
    ];

    $studentNavigation = [
        ['name' => 'Bảng điều khiển', 'href' => '/student', 'icon' => 'layout-dashboard', 'active' => ['student', 'student/dashboard', 'students1']],
        ['name' => 'Lớp học của tôi', 'href' => '/student/classes', 'icon' => 'book-open', 'active' => ['student/classes', 'student/classes/*']],
        ['name' => 'Lịch sử điểm danh', 'href' => '/student/history', 'icon' => 'history', 'active' => ['student/history', 'student/history/*']],
        ['name' => 'Thống kê chuyên cần', 'href' => '/student/stats', 'icon' => 'bar-chart', 'active' => ['student/stats', 'student/stats/*']],
        ['name' => 'Thông báo', 'href' => '/student/notifications', 'icon' => 'bell', 'badgeCount' => $studentUnreadCount, 'active' => ['student/notifications', 'student/notifications/*']],
        ['name' => 'Hồ sơ cá nhân', 'href' => '/student/profile', 'icon' => 'user-check', 'active' => ['student/profile', 'student/profile/*']],
    ];

    $studentMobileNavigation = [
        ['shortName' => 'Trang chủ', 'href' => '/student', 'icon' => 'layout-dashboard', 'active' => ['student', 'student/dashboard', 'students1']],
        ['shortName' => 'Lớp học', 'href' => '/student/classes', 'icon' => 'book-open', 'active' => ['student/classes', 'student/classes/*']],
        ['shortName' => 'Thông báo', 'href' => '/student/notifications', 'icon' => 'bell', 'active' => ['student/notifications', 'student/notifications/*']],
        ['shortName' => 'Cá nhân', 'href' => '/student/profile', 'icon' => 'user', 'active' => ['student/profile', 'student/profile/*']],
    ];

    $currentPageTitle = $pageTitle;
    $titleNavigation = match ($resolvedVariant) {
        'admin' => array_merge(...array_column($adminMenuGroups, 'items')),
        'student' => $studentNavigation,
        default => $lecturerNavigation,
    };

    if (! $currentPageTitle) {
        foreach ($titleNavigation as $item) {
            if ($isActive($item['active'])) {
                $currentPageTitle = $item['name'] ?? $item['shortName'];
                break;
            }
        }
    }

    if (! $currentPageTitle && isset($header)) {
        $currentPageTitle = trim(strip_tags($header->toHtml()));
    }

    $currentPageTitle = $currentPageTitle ?: match ($resolvedVariant) {
        'admin' => 'Admin Center',
        'student' => 'Tổng quan hoạt động & học thuật',
        default => 'Bảng điều khiển',
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $currentPageTitle }} - {{ config('app.name', 'SAMS') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="font-sans antialiased"
        x-data="{ sidebarOpen: false, userMenuOpen: false, darkMode: localStorage.getItem('sams-dark-mode') === '1' }"
        x-init="$watch('darkMode', value => localStorage.setItem('sams-dark-mode', value ? '1' : '0'))"
        :class="{ 'dark': darkMode }"
        @keydown.escape.window="sidebarOpen = false; userMenuOpen = false"
    >
        @include('layouts.partials.' . $resolvedVariant . '-shell')
    </body>
</html>
