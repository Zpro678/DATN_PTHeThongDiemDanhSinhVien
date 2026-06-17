@php
    $fallbackClasses = collect([
        [
            'id' => 'demo-web',
            'label' => 'Lập trình Web - CDTH23A',
            'name' => 'Lập trình Web',
            'code' => 'CDTH23A',
            'subject_code' => 'WEB101',
            'members_count' => 50,
        ],
        [
            'id' => 'demo-db',
            'label' => 'Cơ sở dữ liệu - CDTH23B',
            'name' => 'Cơ sở dữ liệu',
            'code' => 'CDTH23B',
            'subject_code' => 'DB101',
            'members_count' => 46,
        ],
        [
            'id' => 'demo-php',
            'label' => 'PHP nâng cao - CDTH23C',
            'name' => 'PHP nâng cao',
            'code' => 'CDTH23C',
            'subject_code' => 'PHP201',
            'members_count' => 42,
        ],
    ]);

    $classOptions = isset($classes) && $classes->isNotEmpty() ? $classes : $fallbackClasses;
    $defaultClass = $classOptions->first();
    $selectedClassId = (string) request('class_id', $defaultClass['id'] ?? '');
    $periodParts = explode('-', (string) request('period', '1-3'));
    $selectedStartPeriod = (int) old('start_period', request('start_period', $periodParts[0] ?? 1));
    $selectedEndPeriod = (int) old('end_period', request('end_period', $periodParts[1] ?? 3));
    $selectedStartTime = old('start_time', request('start_time', '07:00'));
    $selectedEndTime = old('end_time', request('end_time', '09:30'));
@endphp

<x-app-layout variant="lecturer" page-title="Tạo buổi điểm danh">
    <div
        class="mx-auto max-w-6xl pb-12"
        x-data="{
            selectedClassId: @js($selectedClassId),
            startPeriod: @js($selectedStartPeriod),
            endPeriod: @js($selectedEndPeriod),
            startTime: @js($selectedStartTime),
            endTime: @js($selectedEndTime),
            classDropdownOpen: false,
            startPeriodDropdownOpen: false,
            endPeriodDropdownOpen: false,
            classes: @js($classOptions->values()),
            get selectedClass() {
                return this.classes.find((item) => String(item.id) === String(this.selectedClassId)) || this.classes[0] || {};
            },
            get periodRange() {
                return `${this.startPeriod}-${this.endPeriod}`;
            },
            get periodLabel() {
                return `Tiết ${this.startPeriod} - ${this.endPeriod}`;
            },
            get timeRangeLabel() {
                return `${this.startTime || '--:--'} - ${this.endTime || '--:--'}`;
            },
            get durationMinutes() {
                if (! this.startTime || ! this.endTime) {
                    return 0;
                }

                const [startHour, startMinute] = this.startTime.split(':').map(Number);
                const [endHour, endMinute] = this.endTime.split(':').map(Number);
                const start = (startHour * 60) + startMinute;
                const end = (endHour * 60) + endMinute;

                return end > start ? end - start : 0;
            },
            get durationLabel() {
                return this.durationMinutes ? `${this.durationMinutes} phút` : 'Chưa xác định';
            },
            syncEndPeriod() {
                if (Number(this.endPeriod) < Number(this.startPeriod)) {
                    this.endPeriod = Number(this.startPeriod);
                }
            }
        }"
    >
        <div class="mb-10">
            
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-5">
                <div class="flex h-16 w-16 shrink-0 rotate-3 items-center justify-center rounded-[20px] border border-amber-300 bg-gradient-to-br from-amber-100 to-amber-200 text-amber-600 shadow-md transition-transform hover:rotate-6">
                    <x-sams.icon name="edit-3" class="h-8 w-8" />
                </div>
                <div>
                    <h1 class="mb-1 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Tạo buổi điểm danh</h1>
                    <p class="text-base text-slate-500 dark:text-slate-400">Chuẩn bị thông tin cho buổi học để bắt đầu ghi nhận sĩ số.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <div class="lg:col-span-8">
                <div class="relative overflow-visible rounded-[2rem] border border-slate-100 bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:border-slate-800 dark:bg-slate-900 sm:p-10">
                    <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-[2rem]">
                        <div class="absolute right-0 top-0 h-64 w-64 -translate-y-1/2 translate-x-1/3 rounded-full bg-gradient-to-bl from-sky-50 to-transparent opacity-50 dark:from-sky-950/30"></div>
                    </div>

                    <form id="manual-attendance-form" method="POST" action="{{ route('lecturer.attendance.manual.manual-active') }}" class="relative z-10 space-y-8">
                        @csrf

                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 md:grid-cols-2">
                            <div class="group space-y-3">
                                <label for="class_id" class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    Chọn lớp học
                                    <span class="text-lg leading-none text-red-500">*</span>
                                </label>
                                <div class="relative" @click.outside="classDropdownOpen = false">
                                    <input id="class_id" type="hidden" name="class_id" :value="selectedClassId">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                        <x-sams.icon name="users" class="h-5 w-5 text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                    </div>
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between gap-3 rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 text-left font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:bg-slate-950 dark:text-white dark:hover:border-slate-700"
                                        @click="classDropdownOpen = ! classDropdownOpen"
                                        :aria-expanded="classDropdownOpen.toString()"
                                    >
                                        <span class="min-w-0 truncate" x-text="selectedClass.label || 'Chọn lớp học'"></span>
                                        <x-sams.icon name="chevron-down" class="h-5 w-5 shrink-0 text-slate-500 transition-transform duration-200" x-bind:class="classDropdownOpen ? 'rotate-180' : ''" />
                                    </button>

                                    <div
                                        x-cloak
                                        x-show="classDropdownOpen"
                                        x-transition.origin.top.duration.150ms
                                        class="absolute left-0 right-0 z-40 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900"
                                    >
                                        @foreach ($classOptions as $classOption)
                                            <button
                                                type="button"
                                                class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-3 text-left transition-all hover:bg-blue-50 dark:hover:bg-blue-950/40"
                                                @click="selectedClassId = @js((string) $classOption['id']); classDropdownOpen = false"
                                                :class="String(selectedClassId) === @js((string) $classOption['id']) ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300' : 'text-slate-700 dark:text-slate-200'"
                                            >
                                                <span class="min-w-0">
                                                    <span class="block truncate text-sm font-bold">{{ $classOption['label'] }}</span>
                                                    <span class="mt-0.5 block truncate text-xs font-medium text-slate-400">
                                                        {{ $classOption['subject_code'] ?: 'Chưa có mã môn' }} • {{ $classOption['members_count'] }} sinh viên
                                                    </span>
                                                </span>
                                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" :class="String(selectedClassId) === @js((string) $classOption['id']) ? 'bg-blue-500' : 'bg-transparent'"></span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="group space-y-3">
                                <label for="session_name" class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    Tên buổi học
                                    <span class="text-lg leading-none text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                        <x-sams.icon name="edit-3" class="h-5 w-5 text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                    </div>
                                    <input
                                        id="session_name"
                                        name="session_name"
                                        type="text"
                                        value="{{ old('session_name', request('session_name', 'Buổi 5 - Điểm danh trên lớp')) }}"
                                        class="w-full rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:bg-slate-950 dark:text-white dark:hover:border-slate-700"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="group space-y-3">
                                <label for="session_date" class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    Ngày học
                                    <span class="text-lg leading-none text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                        <x-sams.icon name="calendar" class="h-5 w-5 text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                    </div>
                                    <input
                                        id="session_date"
                                        name="session_date"
                                        type="date"
                                        value="{{ old('session_date', request('session_date', now()->toDateString())) }}"
                                        class="w-full cursor-pointer rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:bg-slate-950 dark:text-white dark:hover:border-slate-700"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="group space-y-4 md:col-span-2">
                                <div class="flex items-center justify-between gap-3">
                                    <label class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                        Thời gian (Tiết)
                                        <span class="text-lg leading-none text-red-500">*</span>
                                    </label>
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600 dark:bg-blue-950/40 dark:text-blue-300" x-text="periodLabel + ' • ' + timeRangeLabel"></span>
                                </div>

                                <input type="hidden" name="period" :value="periodRange">
                                <input type="hidden" name="duration_minutes" :value="durationMinutes">

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                    <div class="space-y-2">
                                        <label for="start_period" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tiết bắt đầu</label>
                                        <div class="relative" @click.outside="startPeriodDropdownOpen = false">
                                            <input id="start_period" type="hidden" name="start_period" :value="startPeriod">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                                <x-sams.icon name="clock" class="h-5 w-5 text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                            </div>
                                            <button
                                                type="button"
                                                class="flex w-full items-center justify-between gap-3 rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 text-left font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:bg-slate-950 dark:text-white dark:hover:border-slate-700"
                                                @click="startPeriodDropdownOpen = ! startPeriodDropdownOpen"
                                                :aria-expanded="startPeriodDropdownOpen.toString()"
                                            >
                                                <span x-text="'Tiết ' + startPeriod"></span>
                                                <x-sams.icon name="chevron-down" class="h-5 w-5 shrink-0 text-slate-500 transition-transform duration-200" x-bind:class="startPeriodDropdownOpen ? 'rotate-180' : ''" />
                                            </button>

                                            <div
                                                x-cloak
                                                x-show="startPeriodDropdownOpen"
                                                x-transition.origin.top.duration.150ms
                                                class="absolute left-0 right-0 z-30 mt-2 grid max-h-64 grid-cols-3 gap-1 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900"
                                            >
                                                @for ($lesson = 1; $lesson <= 12; $lesson++)
                                                    <button
                                                        type="button"
                                                        class="rounded-xl px-3 py-2.5 text-sm font-bold transition-all hover:bg-blue-50 dark:hover:bg-blue-950/40"
                                                        @click="startPeriod = {{ $lesson }}; syncEndPeriod(); startPeriodDropdownOpen = false"
                                                        :class="Number(startPeriod) === {{ $lesson }} ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-700 dark:text-slate-200'"
                                                    >
                                                        {{ $lesson }}
                                                    </button>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="end_period" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tiết kết thúc</label>
                                        <div class="relative" @click.outside="endPeriodDropdownOpen = false">
                                            <input id="end_period" type="hidden" name="end_period" :value="endPeriod">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                                <x-sams.icon name="clock" class="h-5 w-5 text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                            </div>
                                            <button
                                                type="button"
                                                class="flex w-full items-center justify-between gap-3 rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 text-left font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:bg-slate-950 dark:text-white dark:hover:border-slate-700"
                                                @click="endPeriodDropdownOpen = ! endPeriodDropdownOpen"
                                                :aria-expanded="endPeriodDropdownOpen.toString()"
                                            >
                                                <span x-text="'Tiết ' + endPeriod"></span>
                                                <x-sams.icon name="chevron-down" class="h-5 w-5 shrink-0 text-slate-500 transition-transform duration-200" x-bind:class="endPeriodDropdownOpen ? 'rotate-180' : ''" />
                                            </button>

                                            <div
                                                x-cloak
                                                x-show="endPeriodDropdownOpen"
                                                x-transition.origin.top.duration.150ms
                                                class="absolute left-0 right-0 z-30 mt-2 grid max-h-64 grid-cols-3 gap-1 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900"
                                            >
                                                @for ($lesson = 1; $lesson <= 12; $lesson++)
                                                    <button
                                                        type="button"
                                                        class="rounded-xl px-3 py-2.5 text-sm font-bold transition-all disabled:cursor-not-allowed disabled:opacity-40"
                                                        @click="endPeriod = {{ $lesson }}; syncEndPeriod(); endPeriodDropdownOpen = false"
                                                        :disabled="{{ $lesson }} < Number(startPeriod)"
                                                        :class="Number(endPeriod) === {{ $lesson }} ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : ({{ $lesson }} < Number(startPeriod) ? 'text-slate-300 dark:text-slate-700' : 'text-slate-700 hover:bg-blue-50 dark:text-slate-200 dark:hover:bg-blue-950/40')"
                                                    >
                                                        {{ $lesson }}
                                                    </button>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="start_time" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Giờ bắt đầu</label>
                                        <input
                                            id="start_time"
                                            name="start_time"
                                            type="time"
                                            x-model="startTime"
                                            class="w-full cursor-pointer rounded-2xl border-2 border-transparent bg-slate-50 px-4 py-4 font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:bg-slate-950 dark:text-white dark:hover:border-slate-700"
                                            required
                                        >
                                    </div>

                                    <div class="space-y-2">
                                        <label for="end_time" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Giờ kết thúc</label>
                                        <input
                                            id="end_time"
                                            name="end_time"
                                            type="time"
                                            x-model="endTime"
                                            class="w-full cursor-pointer rounded-2xl border-2 border-transparent bg-slate-50 px-4 py-4 font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:bg-slate-950 dark:text-white dark:hover:border-slate-700"
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="group space-y-3 pt-4">
                            <label for="note" class="flex items-center justify-between text-[13px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                <span>Lời nhắn / Ghi chú</span>
                                <span class="text-[11px] font-medium normal-case text-slate-400">Không bắt buộc</span>
                            </label>
                            <textarea
                                id="note"
                                name="note"
                                rows="4"
                                placeholder="Ghi chú thêm về buổi học cho sinh viên hoặc giảng viên khác..."
                                class="w-full resize-none rounded-2xl border-2 border-transparent bg-slate-50 px-5 py-4 font-medium leading-relaxed text-slate-900 shadow-sm outline-none transition-all placeholder:text-slate-400 hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:bg-slate-950 dark:text-white dark:hover:border-slate-700"
                            >{{ old('note', request('note')) }}</textarea>
                        </div>
                    </form>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-4">
                <div class="relative overflow-hidden rounded-[2rem] border border-slate-100 bg-white p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-8 flex items-center justify-between gap-3">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Tổng quan</h2>
                        <div class="flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-600 dark:border-emerald-900/50 dark:bg-emerald-950/30">
                            <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                            Sẵn sàng
                        </div>
                    </div>

                    <div class="relative z-10 mb-8 space-y-5">
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-5 transition-colors hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950">
                            <div class="flex min-w-0 items-center gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-blue-500 shadow-sm dark:bg-slate-900">
                                    <x-sams.icon name="users" class="h-6 w-6" />
                                </div>
                                <div class="min-w-0">
                                    <span class="block text-sm font-semibold text-slate-500 dark:text-slate-400">Sĩ số dự kiến</span>
                                    <span class="block truncate text-[11px] font-medium text-slate-400" x-text="selectedClass.code ? 'Lớp ' + selectedClass.code : 'Chưa chọn lớp'"></span>
                                </div>
                            </div>
                            <span class="text-4xl font-extrabold text-slate-900 drop-shadow-sm dark:text-white" x-text="selectedClass.members_count || 0"></span>
                        </div>

                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/30">
                            <p class="flex items-center gap-2 text-sm font-bold text-emerald-700 dark:text-emerald-300">
                                <x-sams.icon name="check-circle-2" class="h-5 w-5" />
                                Thông tin đã hợp lệ
                            </p>
                            <p class="ml-7 mt-1.5 text-xs leading-relaxed text-emerald-800/80 dark:text-emerald-200/80">Bạn có thể bắt đầu phiên điểm danh ngay bây giờ.</p>
                        </div>

                        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 dark:border-blue-900/50 dark:bg-blue-950/30">
                            <p class="flex items-center gap-2 text-sm font-bold text-blue-700 dark:text-blue-300">
                                <x-sams.icon name="clock" class="h-5 w-5" />
                                <span x-text="periodLabel"></span>
                            </p>
                            <p class="ml-7 mt-1.5 text-xs leading-relaxed text-blue-800/80 dark:text-blue-200/80">
                                <span x-text="timeRangeLabel"></span>
                                <span class="mx-1">•</span>
                                <span x-text="durationLabel"></span>
                            </p>
                        </div>
                    </div>

                    <button
                        type="submit"
                        form="manual-attendance-form"
                        class="group relative flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 text-lg font-bold text-white shadow-[0_8px_20px_rgba(249,115,22,0.25)] transition-all hover:from-orange-600 hover:to-orange-700 active:scale-[0.98]"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            Bắt đầu điểm danh
                            <x-sams.icon name="arrow-right" class="h-6 w-6 transition-transform group-hover:translate-x-1.5" />
                        </span>
                        <span class="absolute inset-0 -translate-x-full skew-x-12 bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 ease-in-out group-hover:translate-x-full"></span>
                    </button>
                </div>

                <div class="group relative cursor-default overflow-hidden rounded-[2rem] border-2 border-dashed border-amber-300 bg-amber-50 p-7 shadow-sm transition-colors hover:bg-amber-100 dark:border-amber-900/60 dark:bg-amber-950/20">
                    <div class="relative z-10 flex gap-4">
                        <div class="shrink-0">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-amber-600 shadow-sm transition-transform duration-300 group-hover:rotate-12 dark:bg-slate-900">
                                <x-sams.icon name="sparkles" class="h-6 w-6" />
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-2 text-base font-bold text-amber-700 dark:text-amber-300">Mẹo rảnh tay!</h3>
                            <p class="text-[13px] font-medium leading-relaxed text-amber-800/90 dark:text-amber-200/80">
                                Sử dụng tính năng <strong class="font-bold text-amber-950 dark:text-amber-100">Quét mã QR</strong> trên bảng điều khiển để sinh viên có thể tự động điểm danh dễ dàng bằng điện thoại cá nhân.
                            </p>
                        </div>
                    </div>
                    <div class="absolute -bottom-4 -right-4 h-24 w-24 rounded-full bg-amber-200 opacity-50 blur-xl transition-transform duration-700 group-hover:scale-125"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
