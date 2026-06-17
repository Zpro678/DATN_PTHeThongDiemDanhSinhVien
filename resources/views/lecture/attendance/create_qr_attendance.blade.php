<x-app-layout variant="lecturer" page-title="Thiết lập điểm danh QR">
    @php
        $classes = $classes ?? collect();
        $students = $students ?? collect();
        $currentCourseTitle = $selectedClass?->name ?? 'Lập trình Web - CDTH23A';
        $selectedSubject = $selectedClass?->subject_code ?? $selectedClass?->code ?? 'WEB101';
        $selectedSemester = $selectedClass?->semester ?? 'HK1 - 2024';
        $defaultEndLesson = min(15, max(1, (int) ($selectedClass?->lessons_per_session ?: 3)));
        $nextSessionNumber = $selectedClass ? ($selectedClass->sessions()->count() + 1) : 5;

        $studentRows = $students->map(function ($student) {
            return [
                'code' => $student->student_code,
                'name' => $student->full_name,
            ];
        })->values();

        if ($studentRows->isEmpty() && ! $selectedClass) {
            $studentRows = collect([
                ['code' => 'SV200001', 'name' => 'Nguyen Minh Anh'],
                ['code' => 'SV200014', 'name' => 'Tran Gia Bao'],
                ['code' => 'SV200021', 'name' => 'Le Hoang Nam'],
                ['code' => 'SV200032', 'name' => 'Pham Thuy Linh'],
                ['code' => 'SV200045', 'name' => 'Vo Quoc Viet'],
            ]);
        }

        $studentCount = (int) ($selectedClass?->active_members_count ?? $studentRows->count());
        $currentClassOptionLabel = $selectedClass
            ? trim($selectedClass->name . ($selectedClass->subject_code ? ' - ' . $selectedClass->subject_code : ''))
            : 'Lập trình Web nâng cao - IT401';
    @endphp

    <div
        class="mx-auto max-w-[1400px] space-y-8"
        x-data="{
            classDropdownOpen: false,
            qrRefreshRate: 10,
            openMinutes: 15,
            startLesson: 1,
            endLesson: {{ $defaultEndLesson }},
            gpsEnabled: true,
            deviceCheck: true
        }"
    >
        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-[28px] font-bold tracking-tight text-slate-900">Thiết lập điểm danh</h1>
                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Mới</span>
                </div>

                <p class="mt-1 text-sm font-medium text-slate-500">
                    Cấu hình phiên điểm danh, thời gian đổi mã và các quy tắc bảo mật.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                    Lưu nháp
                </button>

                <button type="submit" form="qr-setup-form" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-500/30 transition hover:bg-blue-700">
                    Bắt đầu phát mã
                    <x-sams.icon name="qr-code" class="h-4 w-4" />
                </button>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-12">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-8">
                <form id="qr-setup-form" method="POST" action="{{ route('attendance.qr.setup.start-qr-attendance') }}" class="grid gap-8 lg:grid-cols-2">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClass?->id }}" />

                    <div class="space-y-6">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <x-sams.icon name="school" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900">Buổi học hôm nay</h2>
                                <p class="text-xs font-medium text-slate-500">Thông tin lớp và tiết học</p>
                            </div>
                        </div>

                        <div>
                            <label id="class_picker_label" class="mb-2 block text-sm font-bold text-slate-600">Chọn lớp học</label>
                            <div class="relative" @keydown.escape.window="classDropdownOpen = false">
                                <button
                                    type="button"
                                    aria-labelledby="class_picker_label"
                                    @click="classDropdownOpen = !classDropdownOpen"
                                    class="flex w-full items-center justify-between gap-3 rounded-2xl border-2 bg-white px-4 py-3.5 text-left text-sm font-extrabold text-slate-800 shadow-sm outline-none transition hover:border-blue-300 hover:bg-slate-50 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                                    :class="classDropdownOpen ? 'border-blue-500 ring-4 ring-blue-500/10' : 'border-slate-100'"
                                >
                                    <span class="min-w-0 truncate">{{ $currentClassOptionLabel }}</span>
                                    <x-sams.icon name="chevron-down" class="h-5 w-5 shrink-0 text-slate-400 transition" x-bind:class="classDropdownOpen ? 'rotate-180 text-blue-600' : ''" />
                                </button>

                                <div
                                    x-cloak
                                    x-show="classDropdownOpen"
                                    x-transition.origin.top
                                    @click.outside="classDropdownOpen = false"
                                    class="absolute left-0 right-0 top-full z-50 mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10"
                                >
                                    <div class="border-b border-slate-100 bg-slate-50 px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-slate-400">
                                        Lớp đang phụ trách
                                    </div>

                                    <div class="max-h-64 overflow-y-auto py-1">
                                        @forelse ($classes as $courseClass)
                                            @php
                                                $optionLabel = trim($courseClass->name . ($courseClass->subject_code ? ' - ' . $courseClass->subject_code : ''));
                                                $isSelectedCourse = $selectedClass?->id === $courseClass->id;
                                            @endphp

                                            <a
                                                href="{{ route('attendance.qr.setup', ['class_id' => $courseClass->id]) }}"
                                                class="{{ $isSelectedCourse ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-950' }} flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition"
                                            >
                                                <span class="min-w-0">
                                                    <span class="block truncate text-sm font-extrabold">{{ $optionLabel }}</span>
                                                    <span class="mt-0.5 block truncate text-xs font-semibold {{ $isSelectedCourse ? 'text-blue-500' : 'text-slate-400' }}">
                                                        {{ $courseClass->semester ?: 'Chưa gán học kỳ' }} · {{ number_format((int) ($courseClass->active_members_count ?? 0)) }} sinh viên
                                                    </span>
                                                </span>

                                                <span class="{{ $isSelectedCourse ? 'border-blue-500 bg-blue-600' : 'border-slate-300 bg-white' }} flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2">
                                                    @if ($isSelectedCourse)
                                                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                                    @endif
                                                </span>
                                            </a>
                                        @empty
                                            <div class="px-4 py-5 text-sm font-semibold text-slate-500">
                                                Chưa có lớp học để chọn.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="session_title" class="mb-2 block text-sm font-bold text-slate-600">Tiêu đề buổi học</label>
                            <input id="session_title" name="session_title" type="text" value="Buổi {{ $nextSessionNumber }} - Điểm danh QR" class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="session_date" class="mb-2 block text-sm font-bold text-slate-600">Ngày học</label>
                                <input id="session_date" name="session_date" type="date" value="{{ now()->toDateString() }}" class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-600">Tiết học</label>
                                <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-end gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-3">
                                    <div>
                                        <span class="mb-1 block text-xs font-bold text-slate-400">Bắt đầu</span>
                                        <div class="relative">
                                            <select name="start_lesson" x-model.number="startLesson" class="w-full appearance-none rounded-xl border-2 border-slate-100 bg-white px-4 py-3 pr-10 text-sm font-extrabold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                                @for ($lesson = 1; $lesson <= 15; $lesson++)
                                                    <option value="{{ $lesson }}">Tiết {{ $lesson }}</option>
                                                @endfor
                                            </select>
                                            <x-sams.icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        </div>
                                    </div>

                                    <span class="mb-3 text-lg font-black text-slate-300">-</span>

                                    <div>
                                        <span class="mb-1 block text-xs font-bold text-slate-400">Kết thúc</span>
                                        <div class="relative">
                                            <select name="end_lesson" x-model.number="endLesson" class="w-full appearance-none rounded-xl border-2 border-slate-100 bg-white px-4 py-3 pr-10 text-sm font-extrabold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                                @for ($lesson = 1; $lesson <= 15; $lesson++)
                                                    <option value="{{ $lesson }}">Tiết {{ $lesson }}</option>
                                                @endfor
                                            </select>
                                            <x-sams.icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                <x-sams.icon name="shield-check" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900">Bảo mật & Quy tắc</h2>
                                <p class="text-xs font-medium text-slate-500">Kiểm soát quét QR</p>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-600">Thời gian làm mới mã</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach ([5, 10, 15, 30] as $seconds)
                                    <label class="relative">
                                        <input type="radio" name="qr_refresh_rate" value="{{ $seconds }}" x-model.number="qrRefreshRate" class="peer sr-only" @checked($seconds === 10) />
                                        <span class="flex cursor-pointer items-center justify-center rounded-xl border-2 border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-500 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:border-slate-200">
                                            {{ $seconds }}s
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label for="open_minutes" class="mb-2 block text-sm font-bold text-slate-600">Thời lượng mở điểm danh (phút)</label>
                            <input id="open_minutes" name="open_minutes" type="number" min="1" max="180" x-model.number="openMinutes" class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        </div>

                        <div class="space-y-3 pt-1">
                            <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-500">
                                        <x-sams.icon name="shield-alert" class="h-5 w-5" />
                                    </span>
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-900">Xác minh tọa độ GPS</p>
                                        <p class="text-xs font-semibold text-slate-400">Giới hạn khoảng cách</p>
                                    </div>
                                </div>

                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="gps_enabled" value="1" x-model="gpsEnabled" class="peer sr-only" checked />
                                    <span class="h-7 w-12 rounded-full bg-slate-200 transition after:absolute after:left-[3px] after:top-[3px] after:h-[22px] after:w-[22px] after:rounded-full after:border after:border-slate-200 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-500 peer-checked:after:translate-x-5"></span>
                                </label>
                            </div>

                            <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-50 text-sky-500">
                                        <x-sams.icon name="laptop" class="h-5 w-5" />
                                    </span>
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-900">Khóa thiết bị</p>
                                        <p class="text-xs font-semibold text-slate-400">Ngăn 1 máy dùng nhiều tài khoản</p>
                                    </div>
                                </div>

                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="device_check" value="1" x-model="deviceCheck" class="peer sr-only" checked />
                                    <span class="h-7 w-12 rounded-full bg-slate-200 transition after:absolute after:left-[3px] after:top-[3px] after:h-[22px] after:w-[22px] after:rounded-full after:border after:border-slate-200 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-500 peer-checked:after:translate-x-5"></span>
                                </label>
                            </div>

                        </div>
                    </div>
                </form>
            </section>

            <aside class="space-y-6 xl:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-900 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold">Tóm tắt cấu hình</h2>
                            <p class="mt-1 text-xs font-medium text-slate-500">Phiên QR sắp tạo</p>
                        </div>

                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <x-sams.icon name="qr-code" class="h-6 w-6" />
                        </span>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Môn học</p>
                            <p class="mt-1 text-sm font-bold">{{ $selectedSubject }} - {{ $currentCourseTitle }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $selectedSemester }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Sinh viên</p>
                                <p class="mt-1 text-2xl font-black">{{ number_format($studentCount) }}</p>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tiết</p>
                                <p class="mt-1 text-2xl font-black">
                                    <span x-text="startLesson"></span>-<span x-text="endLesson"></span>
                                </p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Thời gian</p>
                            <p class="mt-1 text-sm font-bold" x-text="openMinutes + 'p (QR ' + qrRefreshRate + 's)'"></p>
                        </div>

                        <div class="flex flex-wrap gap-2 pt-1 text-xs font-bold">
                            <span x-show="gpsEnabled" class="rounded-full bg-red-50 px-3 py-1 text-red-600 ring-1 ring-red-100">GPS bật</span>
                            <span x-show="deviceCheck" class="rounded-full bg-sky-50 px-3 py-1 text-sky-600 ring-1 ring-sky-100">Khóa thiết bị</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Chưa điểm danh</p>
                        <p class="mt-2 text-4xl font-black text-slate-900">{{ number_format($studentCount) }}</p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 text-center">
                        <p class="text-[11px] font-black uppercase tracking-wider text-emerald-600">Đã hoàn thành</p>
                        <p class="mt-2 text-4xl font-black text-emerald-600">0</p>
                    </div>
                </div>
            </aside>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" x-data="{ query: '' }">
            <div class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Danh sách quản lý</h2>
                    <p class="text-xs font-medium text-slate-500">Sinh viên của lớp đang chọn</p>
                </div>

                <div class="relative w-full sm:w-72">
                    <x-sams.icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="text" x-model.debounce.150ms="query" placeholder="Tìm MSSV, tên..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm font-semibold outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" />
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
                        @forelse ($studentRows as $student)
                            @php
                                $searchSource = $student['code'] . ' ' . $student['name'];
                                $searchable = function_exists('mb_strtolower') ? mb_strtolower($searchSource, 'UTF-8') : strtolower($searchSource);
                                $nameParts = array_values(array_filter(preg_split('/\s+/', trim($student['name'])) ?: []));
                                $firstInitial = $nameParts[0] ?? 'S';
                                $lastInitial = count($nameParts) > 1 ? $nameParts[count($nameParts) - 1] : '';
                                $initials = function_exists('mb_strtoupper')
                                    ? mb_strtoupper(mb_substr($firstInitial, 0, 1, 'UTF-8') . mb_substr($lastInitial, 0, 1, 'UTF-8'), 'UTF-8')
                                    : strtoupper(substr($firstInitial, 0, 1) . substr($lastInitial, 0, 1));
                            @endphp

                            <tr
                                class="transition hover:bg-slate-50/70"
                                x-data="{ status: 'pending', searchable: @js($searchable) }"
                                x-show="searchable.includes(query.toLowerCase())"
                            >
                                <td class="px-5 py-4 font-mono text-sm font-extrabold text-slate-600">{{ $student['code'] }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-blue-100 bg-blue-50 text-xs font-black text-blue-700">
                                            {{ $initials }}
                                        </span>
                                        <span class="font-extrabold text-slate-900">{{ $student['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span x-show="status === 'pending'" class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Chưa điểm danh</span>
                                    <span x-show="status === 'present'" class="inline-flex rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Có mặt</span>
                                    <span x-show="status === 'absent'" class="inline-flex rounded-full border border-rose-200 bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Vắng mặt</span>
                                    <span x-show="status === 'excused'" class="inline-flex rounded-full border border-sky-200 bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700">Có phép</span>
                                    <span x-show="status === 'late'" class="inline-flex rounded-full border border-amber-200 bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">Đi muộn</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div x-show="status === 'pending'" class="flex items-center justify-center gap-2">
                                        <button type="button" @click="status = 'present'" title="Có mặt" class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100">
                                            <x-sams.icon name="calendar-check" class="h-4 w-4" />
                                        </button>

                                        <button type="button" @click="status = 'late'" title="Đi muộn" class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition hover:bg-amber-100">
                                            <x-sams.icon name="history" class="h-4 w-4" />
                                        </button>

                                        <button type="button" @click="status = 'absent'" title="Đánh vắng" class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100">
                                            <x-sams.icon name="x" class="h-4 w-4" />
                                        </button>

                                        <button type="button" @click="status = 'excused'" title="Có phép" class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600 transition hover:bg-sky-100">
                                            <x-sams.icon name="shield-check" class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <button type="button" x-show="status !== 'pending'" @click="status = 'pending'" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-800">
                                        Hoàn tác
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                        <x-sams.icon name="users" class="h-6 w-6" />
                                    </div>
                                    <h3 class="mt-3 text-sm font-bold text-slate-900">Chưa có sinh viên trong lớp</h3>
                                    <p class="mt-1 text-sm text-slate-500">Thêm hoặc import sinh viên trước khi mở phiên điểm danh.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
