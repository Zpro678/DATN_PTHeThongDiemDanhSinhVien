<x-app-layout page-title="Bảng điều khiển">
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white md:text-3xl">
                    Bảng điều khiển Giảng viên
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Tổng quan sinh viên, buổi học và tình hình có mặt/vắng theo dữ liệu điểm danh.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('classes.index') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <x-sams.icon name="book-open" class="h-4 w-4" />
                    Lớp học
                </a>
                <a href="{{ route('classes.create') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700">
                    <x-sams.icon name="calendar-check" class="h-4 w-4" />
                    Tạo lớp mới
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300">
                        <x-sams.icon name="users" class="h-5 w-5" />
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ number_format($totalClasses) }} lớp
                    </span>
                </div>
                <div class="mt-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tổng sinh viên</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($totalStudents) }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300">
                        <x-sams.icon name="calendar-check" class="h-5 w-5" />
                    </div>
                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                        {{ $sessionProgress }}%
                    </span>
                </div>
                <div class="mt-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tổng buổi</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($totalPlannedSessions) }}</p>
                    <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Đã tạo {{ number_format($totalCreatedSessions) }} phiên điểm danh</p>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-green-50 text-green-600 dark:bg-green-950/40 dark:text-green-300">
                        <x-sams.icon name="shield-check" class="h-5 w-5" />
                    </div>
                    <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-950/40 dark:text-green-300">
                        {{ $attendanceRate }}%
                    </span>
                </div>
                <div class="mt-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tổng có mặt</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($totalPresent) }}</p>
                    <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Gồm các lượt đúng giờ và đi muộn</p>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-300">
                        <x-sams.icon name="shield-alert" class="h-5 w-5" />
                    </div>
                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-300">
                        {{ $absenceRate }}%
                    </span>
                </div>
                <div class="mt-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tổng vắng</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($totalAbsent) }}</p>
                    <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Gồm vắng và vắng có phép</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if($activeSession)
                    @php
                        $activeRecorded = $activeSession->present_count + $activeSession->absent_count;
                        $activeRate = $activeRecorded > 0 ? round(($activeSession->present_count / $activeRecorded) * 100) : 0;
                    @endphp

                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                        <span class="text-xs font-extrabold uppercase tracking-wide text-blue-600 dark:text-blue-400">Phiên đang diễn ra</span>
                    </div>

                    <div class="mt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ $activeSession->courseClass?->subject_code ?: 'Môn học' }}
                        </p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">{{ $activeSession->name }}</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $activeSession->courseClass?->name }}</p>
                    </div>

                    <div class="mt-6 rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-500 dark:text-slate-400">Đã ghi nhận</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ number_format($activeRecorded) }} lượt</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-blue-600" style="width: {{ $activeRate }}%"></div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Có mặt</p>
                                <p class="font-bold text-green-600 dark:text-green-400">{{ number_format($activeSession->present_count) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Vắng</p>
                                <p class="font-bold text-red-600 dark:text-red-400">{{ number_format($activeSession->absent_count) }}</p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('classes.show', $activeSession->class_id) }}" class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-blue-700">
                        Xem lớp học
                    </a>
                @else
                    <div class="flex h-full min-h-64 flex-col justify-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                            <x-sams.icon name="activity" class="h-6 w-6" />
                        </div>
                        <h2 class="mt-5 text-lg font-extrabold text-slate-900 dark:text-white">Chưa có phiên đang diễn ra</h2>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Khi TV3 mở phiên điểm danh, dữ liệu có mặt/vắng sẽ xuất hiện ngay tại đây.
                        </p>
                        <a href="{{ route('classes.index') }}" class="mt-5 inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-slate-800">
                            Xem danh sách lớp
                        </a>
                    </div>
                @endif
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 xl:col-span-2">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Phiên điểm danh gần đây</h2>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Theo các lớp của giảng viên hiện tại</p>
                    </div>
                    <a href="{{ route('classes.index') }}" class="text-sm font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400">Xem lớp</a>
                </div>

                @if($recentSessions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left">
                            <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500 dark:bg-slate-950/60 dark:text-slate-400">
                                <tr>
                                    <th class="px-5 py-3">Buổi học</th>
                                    <th class="px-5 py-3">Ngày</th>
                                    <th class="px-5 py-3">Có mặt / Vắng</th>
                                    <th class="px-5 py-3">Tỷ lệ</th>
                                    <th class="px-5 py-3">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                @foreach($recentSessions as $session)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-950/60">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300">
                                                    <x-sams.icon name="book-open" class="h-4 w-4" />
                                                </div>
                                                <div>
                                                    <p class="font-bold text-slate-900 dark:text-white">{{ $session['name'] }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                                        {{ $session['subject_code'] ?: 'Môn học' }} · {{ $session['class_name'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 font-medium text-slate-600 dark:text-slate-300">{{ $session['date'] }}</td>
                                        <td class="px-5 py-4">
                                            <span class="font-bold text-green-600 dark:text-green-400">{{ number_format($session['present_count']) }}</span>
                                            <span class="mx-1 text-slate-300">/</span>
                                            <span class="font-bold text-red-600 dark:text-red-400">{{ number_format($session['absent_count']) }}</span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-2">
                                                <span class="w-10 text-sm font-bold text-slate-900 dark:text-white">{{ $session['rate'] }}%</span>
                                                <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                                    <div class="h-full rounded-full bg-blue-600" style="width: {{ $session['rate'] }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $session['status_class'] }}">
                                                {{ $session['status_label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-5 py-12 text-center">
                        <x-sams.icon name="calendar" class="mx-auto h-8 w-8 text-slate-400" />
                        <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Chưa có buổi điểm danh</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Các phiên gần đây sẽ hiển thị sau khi lớp có buổi học.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Xu hướng có mặt theo buổi</h2>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Tỷ lệ có mặt của các phiên gần nhất</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">Biểu đồ</span>
                </div>

                @if($chartData->count() > 0)
                    <div class="mt-6 flex h-72 items-end gap-3 border-b border-slate-200 pb-6 dark:border-slate-800">
                        @foreach($chartData as $point)
                            @php
                                $barColor = $point['rate'] < 70 ? '#93c5fd' : '#2563eb';
                            @endphp
                            <div class="flex h-full min-w-0 flex-1 flex-col justify-end gap-2">
                                <div class="flex h-full items-end rounded-t-lg bg-slate-50 px-1 dark:bg-slate-950">
                                    <div class="w-full rounded-t-md transition-all" style="height: {{ max(6, $point['rate']) }}%; background-color: {{ $barColor }}"></div>
                                </div>
                                <div class="text-center">
                                    <p class="truncate text-xs font-extrabold text-slate-900 dark:text-white">{{ $point['rate'] }}%</p>
                                    <p class="truncate text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $point['label'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-6 flex h-72 items-center justify-center rounded-lg border border-dashed border-slate-300 dark:border-slate-800">
                        <div class="text-center">
                            <x-sams.icon name="bar-chart" class="mx-auto h-8 w-8 text-slate-400" />
                            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">Chưa đủ dữ liệu biểu đồ</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Lớp học nổi bật</h2>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Sĩ số và tiến độ tạo buổi theo từng lớp</p>
                    </div>
                    <a href="{{ route('classes.index') }}" class="text-sm font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400">Tất cả</a>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse($classCards as $class)
                        @php
                            $planned = max((int) $class->total_sessions, 0);
                            $created = (int) $class->created_sessions_count;
                            $progress = $planned > 0 ? min(100, round(($created / $planned) * 100)) : 0;
                        @endphp
                        <a href="{{ route('classes.show', $class) }}" class="block rounded-lg border border-slate-200 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50/30 dark:border-slate-800 dark:hover:border-blue-800 dark:hover:bg-blue-950/10">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide text-blue-600 dark:text-blue-400">{{ $class->subject_code ?: 'Môn học' }}</p>
                                    <h3 class="mt-1 truncate text-sm font-extrabold text-slate-900 dark:text-white">{{ $class->name }}</h3>
                                </div>
                                <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ number_format($class->active_members_count) }} SV
                                </span>
                            </div>
                            <div class="mt-4">
                                <div class="mb-1 flex justify-between text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    <span>{{ number_format($created) }} / {{ number_format($planned) }} buổi</span>
                                    <span>{{ $progress }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-blue-600" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 p-8 text-center dark:border-slate-800">
                            <x-sams.icon name="book-open" class="mx-auto h-8 w-8 text-slate-400" />
                            <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Chưa có lớp học</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tạo lớp đầu tiên để bắt đầu thống kê.</p>
                            <a href="{{ route('classes.create') }}" class="mt-4 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Tạo lớp học
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Thống kê từng sinh viên</h2>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Top 10 sinh viên theo danh sách lớp đang hoạt động</p>
                </div>
                <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Có mặt / Vắng / Tỷ lệ</span>
            </div>

            @if($studentRows->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500 dark:bg-slate-950/60 dark:text-slate-400">
                            <tr>
                                <th class="px-5 py-3">Sinh viên</th>
                                <th class="px-5 py-3">Lớp</th>
                                <th class="px-5 py-3">Có mặt</th>
                                <th class="px-5 py-3">Vắng</th>
                                <th class="px-5 py-3">Tỷ lệ</th>
                                <th class="px-5 py-3">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            @foreach($studentRows as $student)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-950/60">
                                    <td class="px-5 py-4">
                                        <p class="font-extrabold text-slate-900 dark:text-white">{{ $student['full_name'] }}</p>
                                        <p class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $student['student_code'] }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $student['subject_code'] ?: 'Môn học' }}</p>
                                        <p class="max-w-xs truncate text-xs text-slate-500 dark:text-slate-400">{{ $student['class_name'] }}</p>
                                    </td>
                                    <td class="px-5 py-4 font-bold text-green-600 dark:text-green-400">{{ number_format($student['present']) }}</td>
                                    <td class="px-5 py-4 font-bold text-red-600 dark:text-red-400">{{ number_format($student['absent']) }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-10 font-bold text-slate-900 dark:text-white">{{ $student['attendance_rate'] }}%</span>
                                            <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                                <div class="h-full rounded-full bg-blue-600" style="width: {{ $student['attendance_rate'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $student['risk_class'] }}">
                                            {{ $student['risk_label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-5 py-12 text-center">
                    <x-sams.icon name="users" class="mx-auto h-8 w-8 text-slate-400" />
                    <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Chưa có sinh viên để thống kê</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Thêm hoặc import sinh viên vào lớp để xem bảng chuyên cần.</p>
                    <a href="{{ route('classes.index') }}" class="mt-4 inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-slate-800">
                        Quản lý lớp học
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
