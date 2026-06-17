<x-app-layout variant="lecturer" page-title="Trạm chờ điểm danh">
    @php
        $students = $students ?? collect();
        $currentCourseTitle = $selectedClass?->name ?? 'Lập trình Web nâng cao';
        $selectedSubject = $selectedClass?->subject_code ?? $selectedClass?->code ?? 'IT401';
        $sessionTitle = $sessionTitle ?? 'Buổi 5 - Điểm danh QR';
        $sessionDate = $sessionDate ?? now()->toDateString();
        $sessionDateLabel = \Illuminate\Support\Carbon::parse($sessionDate)->format('d/m/Y');
        $startLesson = (int) ($startLesson ?? 1);
        $endLesson = (int) ($endLesson ?? 3);
        $qrRefreshRate = (int) ($qrRefreshRate ?? 10);
        $openMinutes = (int) ($openMinutes ?? 15);
        $attendanceLink = $attendanceLink ?? url('/student/attendance/check-in/demo');
        $qrToken = $qrToken ?? 'DEMOQR01';
        $qrCells = $qrCells ?? array_fill(0, 29 * 29, false);

        $studentRows = $students->map(function ($student) {
            return [
                'code' => $student->student_code,
                'name' => $student->full_name,
            ];
        })->values();

        if ($studentRows->isEmpty()) {
            $studentRows = collect([
                ['code' => 'SV200001', 'name' => 'Nguyen Minh Anh'],
                ['code' => 'SV200014', 'name' => 'Tran Gia Bao'],
                ['code' => 'SV200021', 'name' => 'Le Hoang Nam'],
                ['code' => 'SV200032', 'name' => 'Pham Thuy Linh'],
                ['code' => 'SV200045', 'name' => 'Vo Quoc Viet'],
                ['code' => 'SV200052', 'name' => 'Dang Phuong Thao'],
                ['code' => 'SV200063', 'name' => 'Hoang Duc Huy'],
                ['code' => 'SV200074', 'name' => 'Bui Khanh Vy'],
            ]);
        }

        $statusCycle = ['present', 'present', 'late', 'pending', 'absent', 'excused', 'pending', 'present'];
        $attendanceRows = $studentRows->map(function ($student, $index) use ($statusCycle) {
            return [
                'code' => $student['code'],
                'name' => $student['name'],
                'status' => $statusCycle[$index % count($statusCycle)],
                'verified' => in_array($statusCycle[$index % count($statusCycle)], ['present', 'late'], true),
            ];
        })->values();

        $studentCount = max((int) ($selectedClass?->active_members_count ?? 0), $attendanceRows->count());
        $lateCount = $attendanceRows->where('status', 'late')->count();
        $presentOnlyCount = $attendanceRows->where('status', 'present')->count();
        $presentCount = $presentOnlyCount + $lateCount;
        $absentCount = $attendanceRows->where('status', 'absent')->count();
        $excusedCount = $attendanceRows->where('status', 'excused')->count();
        $pendingCount = max(0, $studentCount - $presentCount - $absentCount - $excusedCount);
        $presentRate = $studentCount > 0 ? min(100, round(($presentCount / $studentCount) * 100)) : 0;
    @endphp

    <div
        class="mx-auto max-w-[1400px] space-y-8"
        x-data="{
            timeLeft: {{ $qrRefreshRate }},
            refreshRate: {{ $qrRefreshRate }},
            showEndModal: false,
            showQrModal: false,
            query: '',
            filter: 'all',
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
                    <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider text-blue-700">
                        <span class="h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
                        Đang hoạt động
                    </span>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3 text-sm font-bold text-slate-600">
                    <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                        <x-sams.icon name="school" class="h-4 w-4 text-blue-600" />
                        {{ $currentCourseTitle }} - {{ $selectedSubject }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                        <x-sams.icon name="calendar" class="h-4 w-4 text-slate-500" />
                        {{ $sessionDateLabel }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                        <x-sams.icon name="history" class="h-4 w-4 text-slate-500" />
                        Tiết {{ $startLesson }} - {{ $endLesson }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('attendance.qr.setup') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Quay lại thiết lập
                </a>

                <button type="button" @click="showEndModal = true" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-amber-500/30 transition hover:bg-amber-600">
                    <x-sams.icon name="calendar-check" class="h-4 w-4" />
                    Chốt phiên này
                </button>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-12">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-4">
                <div class="flex h-full flex-col items-center">
                    <div class="mb-6 flex w-full items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Liên kết</p>
                            <p class="mt-1 truncate text-sm font-bold text-blue-700">{{ $attendanceLink }}</p>
                        </div>
                        <span class="shrink-0 rounded-xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 ring-1 ring-blue-100">{{ $qrToken }}</span>
                    </div>

                    <button type="button" @click="showQrModal = true" class="group relative my-4 flex h-64 w-64 items-center justify-center rounded-[28px] border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/10 transition hover:scale-[1.02]">
                        <span class="absolute -inset-4 rounded-[36px] bg-blue-500/10 blur-2xl transition group-hover:bg-blue-500/20"></span>
                        <span class="relative grid h-full w-full gap-[3px] rounded-2xl bg-white p-3" style="grid-template-columns: repeat(29, minmax(0, 1fr));">
                            @foreach ($qrCells as $isDark)
                                <span class="{{ $isDark ? 'bg-slate-900' : 'bg-white' }} aspect-square rounded-[1px]"></span>
                            @endforeach
                        </span>
                        <span class="absolute -bottom-3 -right-3 flex h-11 w-11 items-center justify-center rounded-2xl border-4 border-white bg-blue-600 text-white shadow-lg">
                            <x-sams.icon name="qr-code" class="h-5 w-5" />
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
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">Đang chờ</p>
                        <p class="mt-2 text-4xl font-black text-slate-900">{{ number_format($pendingCount) }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-400">Chưa quét QR</p>
                    </div>

                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5">
                        <p class="text-[11px] font-black uppercase tracking-wider text-amber-600">Đi muộn</p>
                        <p class="mt-2 text-4xl font-black text-amber-600">{{ number_format($lateCount) }}</p>
                        <p class="mt-1 text-xs font-semibold text-amber-500">Cần ghi chú</p>
                    </div>

                    <div class="rounded-2xl border border-rose-100 bg-rose-50 p-5">
                        <p class="text-[11px] font-black uppercase tracking-wider text-rose-600">Vắng</p>
                        <p class="mt-2 text-4xl font-black text-rose-600">{{ number_format($absentCount) }}</p>
                        <p class="mt-1 text-xs font-semibold text-rose-500">Chưa xác nhận</p>
                    </div>

                    <div class="rounded-2xl border border-sky-100 bg-sky-50 p-5">
                        <p class="text-[11px] font-black uppercase tracking-wider text-sky-700">Có phép</p>
                        <p class="mt-2 text-4xl font-black text-sky-700">{{ number_format($excusedCount) }}</p>
                        <p class="mt-1 text-xs font-semibold text-sky-600">Đã gửi lý do</p>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-blue-600 p-6 text-white shadow-sm shadow-blue-500/30">
                    <div class="relative z-10 grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)] lg:items-end">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-blue-50 ring-1 ring-white/20">Sĩ số hiện diện</span>
                                <span class="inline-flex rounded-full bg-white px-3 py-1 text-[11px] font-black uppercase tracking-wider text-blue-700">{{ $presentRate }}% lớp</span>
                            </div>

                            <div class="mt-5 flex flex-wrap items-end gap-3">
                                <span class="text-7xl font-black leading-none tracking-tight">{{ number_format($presentCount) }}</span>
                                <span class="mb-2 text-2xl font-bold text-blue-100">/ {{ number_format($studentCount) }}</span>
                            </div>

                            <div class="mt-5 max-w-xl">
                                <div class="mb-2 flex items-center justify-between text-xs font-bold uppercase tracking-wider text-blue-100">
                                    <span>Tiến độ điểm danh</span>
                                    <span>{{ number_format($presentCount) }} đã xác nhận</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-white/20">
                                    <div class="h-full rounded-full bg-white" style="width: {{ $presentRate }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white/12 p-4 ring-1 ring-white/15 sm:col-span-2">
                                <p class="text-[11px] font-black uppercase tracking-wider text-blue-100">Thông tin phiên</p>
                                <div class="mt-3 grid gap-3 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,0.9fr)]">
                                    <div class="min-w-0 rounded-xl bg-white/10 px-3 py-2.5">
                                        <p class="text-[10px] font-black uppercase tracking-wider text-blue-100/80">Buổi học</p>
                                        <p class="mt-1 break-words text-sm font-bold leading-5 text-white">{{ $sessionTitle }}</p>
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
                                <x-sams.icon name="shield-alert" class="h-5 w-5 text-amber-500" />
                                <h2 class="text-sm font-black text-slate-900">Rủi ro báo cáo</h2>
                            </div>
                            <p class="mt-1 text-xs font-semibold text-slate-400">Hệ thống ghi nhận tức thời</p>
                        </div>

                        <div class="grid flex-1 gap-3 sm:grid-cols-2">
                            <div class="flex items-center gap-3 rounded-xl border border-rose-100 bg-rose-50 p-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-rose-600">
                                    <x-sams.icon name="shield-alert" class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="text-sm font-black text-rose-700">Sai GPS (2)</p>
                                    <p class="text-xs font-bold text-rose-500">Cần xem xét</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-xl border border-amber-100 bg-amber-50 p-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-amber-600">
                                    <x-sams.icon name="laptop" class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="text-sm font-black text-amber-700">Trùng máy (1)</p>
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
                        @foreach ($attendanceRows as $student)
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
                                x-data="{ status: @js($student['status']), searchable: @js($searchable) }"
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
                                <td class="px-5 py-4">
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

                                    <div x-show="status !== 'pending'" class="text-center">
                                        <button type="button" @click="status = 'pending'" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-800">
                                            Hoàn tác
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div x-cloak x-show="showEndModal" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 p-4">
            <div @click.outside="showEndModal = false" x-transition.scale class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                    <x-sams.icon name="calendar-check" class="h-6 w-6" />
                </div>
                <h2 class="mt-4 text-lg font-black text-slate-900">Chốt phiên điểm danh?</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-500">
                    Phiên {{ $sessionTitle }} sẽ dừng nhận QR mới. Bạn vẫn có thể rà soát lại trạng thái sinh viên trước khi lưu báo cáo.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showEndModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Hủy</button>
                    <a href="{{ route('attendance') }}" class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-amber-600">Chốt phiên</a>
                </div>
            </div>
        </div>

        <div x-cloak x-show="showQrModal" x-transition.opacity class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-white p-6">
            <button type="button" @click="showQrModal = false" class="absolute right-6 top-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900" title="Đóng">
                <x-sams.icon name="x" class="h-6 w-6" />
            </button>

            <div class="grid w-[min(82vw,520px)] gap-[5px] rounded-[32px] bg-white p-6 shadow-2xl shadow-slate-900/10 ring-1 ring-slate-200" style="grid-template-columns: repeat(29, minmax(0, 1fr));">
                @foreach ($qrCells as $isDark)
                    <span class="{{ $isDark ? 'bg-slate-900' : 'bg-white' }} aspect-square rounded-[2px]"></span>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
