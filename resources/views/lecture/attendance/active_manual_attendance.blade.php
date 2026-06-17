@php
    $classInfo = $attendanceMeta['class'] ?? [];
    $totalStudents = (int) ($attendanceMeta['total_students'] ?? count($students ?? []));
@endphp

<x-app-layout variant="lecturer" page-title="Điểm danh thủ công">
    <div
        class="pb-24"
        x-data="{
            students: @js($students),
            search: '',
            statusFilter: 'all',
            modalOpen: false,
            confirmChecked: false,
            sessionClosed: false,
            get filteredStudents() {
                const keyword = this.search.trim().toLowerCase();

                return this.students.filter((student) => {
                    const matchesKeyword = ! keyword
                        || student.name.toLowerCase().includes(keyword)
                        || student.code.toLowerCase().includes(keyword);
                    const matchesStatus = this.statusFilter === 'all' || student.status === this.statusFilter;

                    return matchesKeyword && matchesStatus;
                });
            },
            get stats() {
                return {
                    present: this.countByStatus('present'),
                    late: this.countByStatus('late'),
                    excused: this.countByStatus('excused'),
                    unexcused: this.countByStatus('unexcused'),
                    unmarked: this.countByStatus('unmarked'),
                };
            },
            get total() {
                return this.students.length;
            },
            get presentPercent() {
                return this.total ? Math.round((this.stats.present / this.total) * 100) : 0;
            },
            countByStatus(status) {
                return this.students.filter((student) => student.status === status).length;
            },
            markAllPresent() {
                this.students.forEach((student) => {
                    if (student.status === 'unmarked') {
                        student.status = 'present';
                    }
                });
            },
            rowClass(status) {
                return {
                    present: 'bg-emerald-50/60 dark:bg-emerald-950/15',
                    late: 'bg-amber-50/70 dark:bg-amber-950/15',
                    excused: 'bg-blue-50/60 dark:bg-blue-950/15',
                    unexcused: 'bg-rose-50/70 dark:bg-rose-950/15',
                    unmarked: 'bg-white dark:bg-slate-900',
                }[status] || 'bg-white dark:bg-slate-900';
            },
            statusText(status) {
                return {
                    present: 'Có mặt',
                    late: 'Đi trễ',
                    excused: 'Vắng phép',
                    unexcused: 'Vắng KP',
                    unmarked: 'Chưa điểm danh',
                }[status] || 'Chưa điểm danh';
            }
        }"
    >
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <nav class="mb-3 flex items-center gap-2 text-sm font-medium text-slate-500">
                    <a href="{{ url('/lecturer/attendance') }}" class="hover:text-slate-900 dark:hover:text-white">Điểm danh</a>
                    <x-sams.icon name="chevron-right" class="h-4 w-4" />
                    <span class="font-bold text-orange-500">Điểm danh thủ công</span>
                </nav>

                <div class="mb-3 flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Điểm danh thủ công</h1>
                    <span
                        class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold"
                        :class="sessionClosed ? 'border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300' : 'border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300'"
                    >
                        <span class="h-2 w-2 rounded-full" :class="sessionClosed ? 'bg-slate-400' : 'animate-pulse bg-emerald-500'"></span>
                        <span x-text="sessionClosed ? 'Đã chốt sổ' : 'Đang điểm danh'"></span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                    <div class="flex items-center gap-2">
                        <x-sams.icon name="users" class="h-5 w-5 text-blue-600" />
                        <span class="font-bold text-slate-900 dark:text-white">{{ $classInfo['label'] ?? 'Lớp học' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-sams.icon name="clock" class="h-5 w-5" />
                        <span class="font-medium">
                            {{ $attendanceMeta['session_name'] ?? 'Buổi điểm danh' }}
                            • {{ $attendanceMeta['session_date_display'] ?? now()->format('d/m/Y') }}
                            • {{ $attendanceMeta['period_label'] ?? 'Tiết 1 - 3' }}
                            • {{ $attendanceMeta['time_range'] ?? '07:00 - 09:30' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('lecturer.attendance.manual') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    Thiết lập lại
                </a>
                <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-500/20 transition hover:bg-blue-700" @click="markAllPresent()" :disabled="sessionClosed">
                    <x-sams.icon name="check-circle-2" class="h-4 w-4" />
                    Đánh dấu tất cả có mặt
                </button>
            </div>
        </div>

        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-2xl border-l-4 border-emerald-500 bg-white p-5 shadow-sm dark:bg-slate-900">
                <div class="mb-2 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40">
                        <x-sams.icon name="check-circle-2" class="h-5 w-5" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Có mặt</span>
                </div>
                <p class="text-4xl font-extrabold text-slate-900 dark:text-white" x-text="stats.present"></p>
            </div>

            <div class="rounded-2xl border-l-4 border-amber-500 bg-white p-5 shadow-sm dark:bg-slate-900">
                <div class="mb-2 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-50 text-amber-600 dark:bg-amber-950/40">
                        <x-sams.icon name="clock" class="h-5 w-5" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Đi trễ</span>
                </div>
                <p class="text-4xl font-extrabold text-slate-900 dark:text-white" x-text="stats.late"></p>
            </div>

            <div class="rounded-2xl border-l-4 border-blue-500 bg-white p-5 shadow-sm dark:bg-slate-900">
                <div class="mb-2 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-950/40">
                        <x-sams.icon name="clipboard-check" class="h-5 w-5" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Vắng phép</span>
                </div>
                <p class="text-4xl font-extrabold text-slate-900 dark:text-white" x-text="stats.excused"></p>
            </div>

            <div class="rounded-2xl border-l-4 border-rose-500 bg-white p-5 shadow-sm dark:bg-slate-900">
                <div class="mb-2 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-950/40">
                        <x-sams.icon name="x-circle" class="h-5 w-5" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Vắng KP</span>
                </div>
                <p class="text-4xl font-extrabold text-slate-900 dark:text-white" x-text="stats.unexcused"></p>
            </div>

            <div class="rounded-2xl border-l-4 border-slate-400 bg-white p-5 shadow-sm dark:bg-slate-900">
                <div class="mb-2 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800">
                        <x-sams.icon name="more-horizontal" class="h-5 w-5" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Chưa ĐD</span>
                </div>
                <p class="text-4xl font-extrabold text-slate-900 dark:text-white" x-text="stats.unmarked"></p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 border-b border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Danh sách sinh viên</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Hiển thị <span class="font-bold" x-text="filteredStudents.length"></span> / <span x-text="total"></span> sinh viên
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative">
                        <x-sams.icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input type="text" x-model.debounce.150ms="search" placeholder="Tìm MSSV hoặc họ tên..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-medium outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white sm:w-64">
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="rounded-xl px-3 py-2 text-xs font-bold transition" :class="statusFilter === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300'" @click="statusFilter = 'all'">Tất cả</button>
                        <button type="button" class="rounded-xl px-3 py-2 text-xs font-bold transition" :class="statusFilter === 'unmarked' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300'" @click="statusFilter = 'unmarked'">Chưa ĐD</button>
                        <button type="button" class="rounded-xl px-3 py-2 text-xs font-bold transition" :class="statusFilter === 'present' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-300'" @click="statusFilter = 'present'">Có mặt</button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] border-collapse text-left">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400">
                        <tr>
                            <th class="w-16 px-5 py-4">STT</th>
                            <th class="w-32 px-5 py-4">MSSV</th>
                            <th class="px-5 py-4">Họ tên</th>
                            <th class="px-5 py-4 text-center">Trạng thái điểm danh</th>
                            <th class="w-64 px-5 py-4">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm dark:divide-slate-800">
                        <template x-for="(student, index) in filteredStudents" :key="student.id">
                            <tr class="transition-colors" :class="rowClass(student.status)">
                                <td class="px-5 py-4 font-bold text-slate-500" x-text="String(index + 1).padStart(2, '0')"></td>
                                <td class="px-5 py-4 font-mono font-bold text-slate-600 dark:text-slate-300" x-text="student.code"></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-extrabold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300" x-text="student.avatarChar"></div>
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-slate-900 dark:text-white" x-text="student.name"></p>
                                            <p class="text-xs font-medium text-slate-400" x-text="statusText(student.status)"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap justify-center gap-3">
                                        <label class="flex cursor-pointer items-center gap-2 rounded-full px-2.5 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-300 dark:hover:bg-emerald-950/30">
                                            <input type="radio" :name="'status-' + student.id" value="present" x-model="student.status" class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500" :disabled="sessionClosed">
                                            Có mặt
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 rounded-full px-2.5 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-amber-50 hover:text-amber-700 dark:text-slate-300 dark:hover:bg-amber-950/30">
                                            <input type="radio" :name="'status-' + student.id" value="late" x-model="student.status" class="h-4 w-4 border-slate-300 text-amber-500 focus:ring-amber-500" :disabled="sessionClosed">
                                            Đi trễ
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 rounded-full px-2.5 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-blue-50 hover:text-blue-700 dark:text-slate-300 dark:hover:bg-blue-950/30">
                                            <input type="radio" :name="'status-' + student.id" value="excused" x-model="student.status" class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500" :disabled="sessionClosed">
                                            Vắng phép
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 rounded-full px-2.5 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-rose-50 hover:text-rose-700 dark:text-slate-300 dark:hover:bg-rose-950/30">
                                            <input type="radio" :name="'status-' + student.id" value="unexcused" x-model="student.status" class="h-4 w-4 border-slate-300 text-rose-600 focus:ring-rose-500" :disabled="sessionClosed">
                                            Vắng KP
                                        </label>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <input type="text" x-model="student.note" placeholder="Thêm ghi chú..." class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm font-medium outline-none transition hover:border-slate-200 hover:bg-white focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:hover:border-slate-700 dark:hover:bg-slate-950 dark:focus:bg-slate-950" :disabled="sessionClosed">
                                </td>
                            </tr>
                        </template>

                        <tr x-show="filteredStudents.length === 0">
                            <td colspan="5" class="px-5 py-12 text-center text-sm font-semibold text-slate-500">Không tìm thấy sinh viên phù hợp.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/50 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm font-medium text-slate-500">
                    Tổng sĩ số: <span class="font-bold text-slate-900 dark:text-white" x-text="total"></span>
                    <span class="mx-2 text-slate-300">|</span>
                    Có mặt: <span class="font-bold text-emerald-600" x-text="stats.present"></span>
                </p>
                <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <x-sams.icon name="download" class="h-4 w-4" />
                    Xuất Excel
                </button>
            </div>
        </div>

        <div class="sticky bottom-0 z-30 -mx-4 mt-8 border-t border-slate-200 bg-white/85 px-4 py-4 backdrop-blur-md dark:border-slate-800 dark:bg-slate-950/85 md:-mx-8 md:px-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                <div class="text-left sm:text-right">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Tổng hợp nhanh</span>
                    <span class="text-lg font-extrabold text-slate-900 dark:text-white">
                        <span x-text="stats.present"></span>/<span x-text="total"></span> có mặt
                        (<span x-text="presentPercent"></span>%)
                    </span>
                </div>
                <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-8 py-3 text-lg font-bold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60" @click="modalOpen = true" :disabled="sessionClosed">
                    <x-sams.icon name="save" class="h-5 w-5" />
                    <span x-text="sessionClosed ? 'ĐÃ CHỐT SỔ' : 'LƯU & CHỐT SỔ'"></span>
                </button>
            </div>
        </div>

        <div x-cloak x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <button type="button" class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" @click="modalOpen = false" aria-label="Đóng"></button>

            <div x-show="modalOpen" x-transition.scale.origin.center.duration.150ms class="relative w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start gap-4 border-b border-slate-200 p-6 dark:border-slate-800">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-950/30">
                        <x-sams.icon name="lock" class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white">Xác nhận chốt sổ điểm danh?</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">Sau khi chốt sổ, giao diện sẽ khóa chỉnh sửa trong phiên hiện tại.</p>
                    </div>
                </div>

                <div class="space-y-6 p-6">
                    <div class="grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 sm:grid-cols-5">
                        <div class="border-b border-r border-slate-200 p-4 text-center dark:border-slate-800 sm:border-b-0">
                            <p class="mb-1 text-xs font-bold uppercase text-slate-500">Có mặt</p>
                            <p class="text-2xl font-extrabold text-emerald-600" x-text="stats.present"></p>
                        </div>
                        <div class="border-b border-r border-slate-200 p-4 text-center dark:border-slate-800 sm:border-b-0">
                            <p class="mb-1 text-xs font-bold uppercase text-slate-500">Đi trễ</p>
                            <p class="text-2xl font-extrabold text-amber-500" x-text="stats.late"></p>
                        </div>
                        <div class="border-b border-r border-slate-200 p-4 text-center dark:border-slate-800 sm:border-b-0">
                            <p class="mb-1 text-xs font-bold uppercase text-slate-500">Vắng phép</p>
                            <p class="text-2xl font-extrabold text-blue-600" x-text="stats.excused"></p>
                        </div>
                        <div class="border-r border-slate-200 p-4 text-center dark:border-slate-800">
                            <p class="mb-1 text-xs font-bold uppercase text-slate-500">Vắng KP</p>
                            <p class="text-2xl font-extrabold text-rose-600" x-text="stats.unexcused"></p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="mb-1 text-xs font-bold uppercase text-slate-500">Chưa ĐD</p>
                            <p class="text-2xl font-extrabold text-slate-500" x-text="stats.unmarked"></p>
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-transparent bg-slate-50 p-4 transition hover:border-slate-200 dark:bg-slate-950 dark:hover:border-slate-800">
                        <input type="checkbox" x-model="confirmChecked" class="h-5 w-5 rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                        <span class="select-none font-semibold text-slate-900 dark:text-white">Tôi đã kiểm tra lại danh sách điểm danh.</span>
                    </label>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-6 dark:border-slate-800 dark:bg-slate-950 sm:flex-row">
                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-6 py-3 font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 sm:flex-[0.4]" @click="modalOpen = false">
                        Quay lại kiểm tra
                    </button>
                    <button type="button" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-orange-500 px-6 py-3 font-bold text-white shadow-sm shadow-orange-500/20 transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50" :disabled="! confirmChecked" @click="sessionClosed = true; modalOpen = false">
                        <x-sams.icon name="user-check" class="h-5 w-5" />
                        Chốt sổ điểm danh
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
