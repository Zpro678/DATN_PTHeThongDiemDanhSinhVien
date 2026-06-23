@php
    $classOptions = $classes
        ->map(fn ($class) => [
            'id' => (string) $class->id,
            'label' => $class->name.' - '.$class->code,
            'name' => $class->name,
            'code' => $class->code,
            'subject_code' => $class->subject_code,
            'members_count' => (int) ($class->members_count ?? 0),
        ])
        ->values();
@endphp

<div
    class="mx-auto max-w-6xl p-4 pb-24 sm:p-8"
    x-data="{
        selectedClassId: @entangle('classId').live,
        startPeriod: @entangle('startPeriod').live,
        endPeriod: @entangle('endPeriod').live,
        startTime: @entangle('startTime').live,
        endTime: @entangle('endTime').live,
        classDropdownOpen: false,
        startPeriodDropdownOpen: false,
        endPeriodDropdownOpen: false,
        classes: @js($classOptions),
        get selectedClass() {
            return this.classes.find((item) => String(item.id) === String(this.selectedClassId)) || this.classes[0] || {};
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
        },
    }"
>
    <div class="mb-10 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center sm:gap-5">
        <div class="flex items-center gap-5">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[20px] border border-amber-300 bg-gradient-to-br from-amber-100 to-amber-200 text-amber-600 shadow-md transition-transform hover:-translate-y-1">
                <x-user.icon name="edit" :size="30" />
            </div>
            <div>
                <h1 class="mb-1 text-3xl font-extrabold tracking-tight text-slate-900">Tạo buổi điểm danh</h1>
                <p class="text-base text-slate-500">Chuẩn bị thông tin cho buổi học để bắt đầu ghi nhận sĩ số thủ công.</p>
            </div>
        </div>
        
        <a href="{{ route('lecturer.attendance.create') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
            <x-user.icon name="arrow-left" :size="18" />
            Quay lại
        </a>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <div class="relative h-full overflow-visible rounded-[2rem] border border-slate-100 bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:p-10">
                <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-[2rem]">
                    <div class="absolute right-0 top-0 h-64 w-64 -translate-y-1/2 translate-x-1/3 rounded-full bg-gradient-to-bl from-sky-50 to-transparent opacity-70"></div>
                </div>

                <form id="manual-attendance-form" wire:submit="save" class="relative z-10 space-y-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 md:grid-cols-2">
                        <div class="group space-y-3">
                            <label for="class_id" class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-slate-600">
                                Chọn lớp học
                                <span class="text-lg leading-none text-red-500">*</span>
                            </label>
                            <div class="relative" @click.outside="classDropdownOpen = false">
                                <input id="class_id" type="hidden" wire:model="classId">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <x-user.icon name="users" :size="20" class="text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                </div>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 text-left font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    @click="classDropdownOpen = ! classDropdownOpen"
                                    :aria-expanded="classDropdownOpen.toString()"
                                >
                                    <span class="min-w-0 truncate" x-text="selectedClass.label || 'Chọn lớp học'"></span>
                                    <x-user.icon name="chevron-down" :size="20" class="shrink-0 text-slate-500 transition-transform duration-200" x-bind:class="classDropdownOpen ? 'rotate-180' : ''" />
                                </button>

                                <div
                                    x-cloak
                                    x-show="classDropdownOpen"
                                    x-transition.origin.top.duration.150ms
                                    class="absolute left-0 right-0 z-40 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10"
                                >
                                    @foreach ($classOptions as $classOption)
                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-3 text-left transition-all hover:bg-blue-50"
                                            @click="selectedClassId = @js((string) $classOption['id']); classDropdownOpen = false"
                                            :class="String(selectedClassId) === @js((string) $classOption['id']) ? 'bg-blue-50 text-blue-700' : 'text-slate-700'"
                                        >
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-bold">{{ $classOption['label'] }}</span>
                                                <span class="mt-0.5 block truncate text-xs font-medium text-slate-400">
                                                    {{ $classOption['subject_code'] ?: 'Chưa có mã môn' }} • {{ $classOption['members_count'] }} học viên
                                                </span>
                                            </span>
                                            <span class="h-2.5 w-2.5 shrink-0 rounded-full" :class="String(selectedClassId) === @js((string) $classOption['id']) ? 'bg-blue-500' : 'bg-transparent'"></span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @error('classId')
                                <span class="text-sm font-medium text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="group space-y-3">
                            <label for="session_name" class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-slate-600">
                                Tên buổi học
                                <span class="text-lg leading-none text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <x-user.icon name="edit" :size="20" class="text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                </div>
                                <input
                                    id="session_name"
                                    wire:model.blur="name"
                                    type="text"
                                    placeholder="Ví dụ: Buổi 1 - Lý thuyết..."
                                    class="w-full rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    required
                                >
                            </div>
                            @error('name')
                                <span class="text-sm font-medium text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="group space-y-3">
                            <label for="session_date" class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-slate-600">
                                Ngày học
                                <span class="text-lg leading-none text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <x-user.icon name="calendar" :size="20" class="text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                </div>
                                <input
                                    id="session_date"
                                    wire:model="date"
                                    type="date"
                                    readonly
                                    class="w-full rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 font-semibold text-slate-900 shadow-sm outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                    required
                                >
                            </div>
                            @error('date')
                                <span class="text-sm font-medium text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="group space-y-4 md:col-span-2">
                            <div class="flex items-center justify-between gap-3">
                                <label class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-slate-600">
                                    Thời gian (Tiết)
                                    <span class="text-lg leading-none text-red-500">*</span>
                                </label>
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600" x-text="periodLabel + ' • ' + timeRangeLabel"></span>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                <div class="space-y-2">
                                    <label for="start_period" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Tiết bắt đầu</label>
                                    <div class="relative" x-data="{ position: 'bottom' }" @click.outside="startPeriodDropdownOpen = false">
                                        <input id="start_period" type="hidden" wire:model="startPeriod">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                            <x-user.icon name="clock" :size="20" class="text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                        </div>
                                        <button
                                            type="button"
                                            x-ref="btnStart"
                                            class="flex w-full items-center justify-between gap-3 rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 text-left font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                            @click="
                                                startPeriodDropdownOpen = ! startPeriodDropdownOpen;
                                                if (startPeriodDropdownOpen) {
                                                    $nextTick(() => {
                                                        let rect = $refs.btnStart.getBoundingClientRect();
                                                        let menuRect = $refs.menuStart.getBoundingClientRect();
                                                        let spaceBelow = window.innerHeight - rect.bottom;
                                                        let spaceAbove = rect.top;
                                                        position = (spaceBelow < menuRect.height && spaceAbove > spaceBelow) ? 'top' : 'bottom';
                                                    });
                                                }
                                            "
                                            :aria-expanded="startPeriodDropdownOpen.toString()"
                                        >
                                            <span x-text="'Tiết ' + startPeriod"></span>
                                            <x-user.icon name="chevron-down" :size="20" class="shrink-0 text-slate-500 transition-transform duration-200" x-bind:class="startPeriodDropdownOpen ? 'rotate-180' : ''" />
                                        </button>

                                        <div
                                            x-cloak
                                            x-ref="menuStart"
                                            x-show="startPeriodDropdownOpen"
                                            x-transition.origin.top.duration.150ms
                                            :class="position === 'top' ? 'bottom-full mb-2 origin-bottom' : 'top-full mt-2 origin-top'"
                                            class="absolute left-0 right-0 z-50 grid max-h-64 grid-cols-3 gap-1 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10"
                                        >
                                            @for ($lesson = 1; $lesson <= 12; $lesson++)
                                                <button
                                                    type="button"
                                                    class="rounded-xl px-3 py-2.5 text-sm font-bold transition-all hover:bg-blue-50"
                                                    @click="startPeriod = {{ $lesson }}; syncEndPeriod(); startPeriodDropdownOpen = false"
                                                    :class="Number(startPeriod) === {{ $lesson }} ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-700'"
                                                >
                                                    {{ $lesson }}
                                                </button>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label for="end_period" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Tiết kết thúc</label>
                                    <div class="relative" x-data="{ position: 'bottom' }" @click.outside="endPeriodDropdownOpen = false">
                                        <input id="end_period" type="hidden" wire:model="endPeriod">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                            <x-user.icon name="clock" :size="20" class="text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                        </div>
                                        <button
                                            type="button"
                                            x-ref="btnEnd"
                                            class="flex w-full items-center justify-between gap-3 rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 text-left font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                            @click="
                                                endPeriodDropdownOpen = ! endPeriodDropdownOpen;
                                                if (endPeriodDropdownOpen) {
                                                    $nextTick(() => {
                                                        let rect = $refs.btnEnd.getBoundingClientRect();
                                                        let menuRect = $refs.menuEnd.getBoundingClientRect();
                                                        let spaceBelow = window.innerHeight - rect.bottom;
                                                        let spaceAbove = rect.top;
                                                        position = (spaceBelow < menuRect.height && spaceAbove > spaceBelow) ? 'top' : 'bottom';
                                                    });
                                                }
                                            "
                                            :aria-expanded="endPeriodDropdownOpen.toString()"
                                        >
                                            <span x-text="'Tiết ' + endPeriod"></span>
                                            <x-user.icon name="chevron-down" :size="20" class="shrink-0 text-slate-500 transition-transform duration-200" x-bind:class="endPeriodDropdownOpen ? 'rotate-180' : ''" />
                                        </button>

                                        <div
                                            x-cloak
                                            x-ref="menuEnd"
                                            x-show="endPeriodDropdownOpen"
                                            x-transition.origin.top.duration.150ms
                                            :class="position === 'top' ? 'bottom-full mb-2 origin-bottom' : 'top-full mt-2 origin-top'"
                                            class="absolute left-0 right-0 z-50 grid max-h-64 grid-cols-3 gap-1 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10"
                                        >
                                            @for ($lesson = 1; $lesson <= 12; $lesson++)
                                                <button
                                                    type="button"
                                                    class="rounded-xl px-3 py-2.5 text-sm font-bold transition-all disabled:cursor-not-allowed disabled:opacity-40"
                                                    @click="endPeriod = {{ $lesson }}; syncEndPeriod(); endPeriodDropdownOpen = false"
                                                    :disabled="{{ $lesson }} < Number(startPeriod)"
                                                    :class="Number(endPeriod) === {{ $lesson }} ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : ({{ $lesson }} < Number(startPeriod) ? 'text-slate-300' : 'text-slate-700 hover:bg-blue-50')"
                                                >
                                                    {{ $lesson }}
                                                </button>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label for="start_time" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Giờ bắt đầu</label>
                                    <input
                                        id="start_time"
                                        type="time"
                                        x-model="startTime"
                                        class="w-full cursor-pointer rounded-2xl border-2 border-transparent bg-slate-50 px-4 py-4 font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                        required
                                    >
                                    @error('startTime')
                                        <span class="text-sm font-medium text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="end_time" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Giờ kết thúc</label>
                                    <input
                                        id="end_time"
                                        type="time"
                                        x-model="endTime"
                                        class="w-full cursor-pointer rounded-2xl border-2 border-transparent bg-slate-50 px-4 py-4 font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                        required
                                    >
                                    @error('endTime')
                                        <span class="text-sm font-medium text-red-600">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            @error('endPeriod')
                                <span class="text-sm font-medium text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="group relative mt-8 cursor-default overflow-hidden rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50 p-4 shadow-sm transition-colors hover:bg-amber-100">
                        <div class="relative z-10 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-amber-600 shadow-sm transition-transform duration-300 group-hover:rotate-12">
                                        <x-user.icon name="sparkles" :size="20" />
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-amber-700">Mẹo rảnh tay!</h3>
                                    <p class="text-sm font-medium text-amber-800/90">
                                        Sử dụng <strong class="font-bold text-amber-950">Điểm danh QR</strong> để học viên tự động điểm danh.
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('lecturer.attendance.qr.create') }}" class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-amber-200 px-4 py-2 text-sm font-bold text-amber-800 transition hover:bg-amber-300 hover:text-amber-900">
                                Tạo QR điểm danh
                                <x-user.icon name="arrow-right" :size="16" />
                            </a>
                        </div>
                        <div class="absolute -bottom-4 -right-4 h-24 w-24 rounded-full bg-amber-200 opacity-50 blur-xl transition-transform duration-700 group-hover:scale-125"></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-4">
            <div class="relative flex h-full flex-col overflow-hidden rounded-[2rem] border border-slate-100 bg-white p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <div class="mb-8 flex items-center justify-between gap-3">
                    <h2 class="text-xl font-bold text-slate-900">Tổng quan</h2>
                    <div class="flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-600">
                        <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                        Sẵn sàng
                    </div>
                </div>

                <div class="relative z-10 mb-8 flex flex-1 flex-col justify-center gap-6">
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-5 transition-colors hover:border-slate-300">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-blue-500 shadow-sm">
                                <x-user.icon name="users" :size="24" />
                            </div>
                            <div class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-500">Sĩ số dự kiến</span>
                                <span class="block truncate text-[11px] font-medium text-slate-400" x-text="selectedClass.code ? 'Lớp ' + selectedClass.code : 'Chưa chọn lớp'"></span>
                            </div>
                        </div>
                        <span class="text-4xl font-extrabold text-slate-900 drop-shadow-sm" x-text="selectedClass.members_count || 0"></span>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="flex items-center gap-2 text-sm font-bold text-emerald-700">
                            <x-user.icon name="check-circle-2" :size="20" />
                            Thông tin đã hợp lệ
                        </p>
                        <p class="ml-7 mt-1.5 text-xs leading-relaxed text-emerald-800/80">Bạn có thể bắt đầu phiên điểm danh ngay bây giờ.</p>
                    </div>

                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                        <p class="flex items-center gap-2 text-sm font-bold text-blue-700">
                            <x-user.icon name="clock" :size="20" />
                            <span x-text="periodLabel"></span>
                        </p>
                        <p class="ml-7 mt-1.5 text-xs leading-relaxed text-blue-800/80">
                            <span x-text="timeRangeLabel"></span>
                            <span class="mx-1">•</span>
                            <span x-text="durationLabel"></span>
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    form="manual-attendance-form"
                    wire:loading.attr="disabled"
                    class="group relative mt-auto flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 text-lg font-bold text-white shadow-[0_8px_20px_rgba(249,115,22,0.25)] transition-all hover:from-orange-600 hover:to-orange-700 active:scale-[0.98] disabled:cursor-wait disabled:opacity-70"
                >
                    <span class="relative z-10 flex items-center gap-2">
                        Bắt đầu điểm danh
                        <x-user.icon name="arrow-right" :size="24" class="transition-transform group-hover:translate-x-1.5" />
                    </span>
                    <span class="absolute inset-0 -translate-x-full skew-x-12 bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 ease-in-out group-hover:translate-x-full"></span>
                </button>
            </div>




        </div>
    </div>
</div>
