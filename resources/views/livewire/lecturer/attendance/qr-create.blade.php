@php
    $classOptions = $classes
        ->map(fn ($class) => [
            'id' => (string) $class->id,
            'label' => trim($class->name.($class->subject_code ? ' - '.$class->subject_code : '')),
            'name' => $class->name,
            'code' => $class->code,
            'subject_code' => $class->subject_code ?: $class->code,
            'semester' => $class->semester ?: 'Chưa gán học kỳ',
            'members_count' => (int) ($class->members_count ?? 0),
        ])
        ->values();

    $studentRows = $students
        ->map(fn ($student) => [
            'code' => $student->student_code,
            'name' => $student->full_name,
        ])
        ->values();

    $studentCount = (int) ($selectedClass?->members_count ?? $studentRows->count());
    $selectedSubject = $selectedClass?->subject_code ?: $selectedClass?->code ?: 'QR101';
    $selectedSemester = $selectedClass?->semester ?: 'HK2 2025-2026';
@endphp

<div
    class="mx-auto max-w-[1400px] space-y-8 p-4 pb-24 sm:p-8"
    x-data="{
        classDropdownOpen: false,
        selectedClassId: @entangle('classId').live,
        sessionTitle: @entangle('name').live,
        sessionDate: @entangle('date').live,
        qrRefreshRate: @entangle('qrRefreshRate').live,
        openMinutes: @entangle('durationMinutes').live,
        startLesson: @entangle('startLesson').live,
        endLesson: @entangle('endLesson').live,
        gpsEnabled: @entangle('gpsEnabled').live,
        gpsLatitude: @entangle('gpsLatitude'),
        gpsLongitude: @entangle('gpsLongitude'),
        deviceCheck: @entangle('deviceCheck').live,
        query: '',
        classes: @js($classOptions),
        get selectedClass() {
            return this.classes.find((item) => String(item.id) === String(this.selectedClassId)) || this.classes[0] || {};
        },
        initGps() {
            if (this.gpsEnabled && (!this.gpsLatitude || !this.gpsLongitude)) {
                this.fetchLocation();
            }
            this.$watch('gpsEnabled', value => {
                if (value) this.fetchLocation();
            });
        },
        fetchLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.gpsLatitude = position.coords.latitude;
                        this.gpsLongitude = position.coords.longitude;
                    },
                    (error) => {
                        console.warn('Cannot get location', error);
                        alert('Không thể lấy tọa độ GPS. Vui lòng cấp quyền vị trí cho trình duyệt.');
                        this.gpsEnabled = false;
                    },
                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                );
            } else {
                alert('Trình duyệt của bạn không hỗ trợ định vị.');
                this.gpsEnabled = false;
            }
        }
    }"
    x-init="initGps()"
>
    <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between xl:grid xl:grid-cols-12 xl:gap-6">
        <div class="min-w-0 flex-1 xl:col-span-8">
            <h1 class="truncate text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 uppercase" title="THIẾT LẬP ĐIỂM DANH @if($selectedClass) - {{ mb_strtoupper($selectedClass->name) }} @if($selectedClass->subject_code) ({{ mb_strtoupper($selectedClass->subject_code) }}) @endif @endif">
                THIẾT LẬP ĐIỂM DANH
                @if($selectedClass)
                    - {{ mb_strtoupper($selectedClass->name) }} @if($selectedClass->subject_code) ({{ mb_strtoupper($selectedClass->subject_code) }}) @endif
                @endif
            </h1>
        </div>

        <div class="flex shrink-0 flex-col gap-3 sm:flex-row xl:col-span-4 xl:justify-end">
            <button type="button" wire:click="saveConfig" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-slate-300 hover:bg-slate-50 hover:shadow">
                Lưu cấu hình
            </button>

            <button type="submit" form="qr-setup-form" class="group inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-indigo-700 hover:shadow-blue-500/40">
                {{ $editSessionId ? 'Cập nhật thiết lập' : 'Bắt đầu phát mã' }}
                <x-user.icon name="qr-code" :size="18" class="transition-transform group-hover:scale-110" />
            </button>
        </div>
    </div>

    @if(session('success_config'))
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
            <x-user.icon name="check-circle" :size="20" />
            {{ session('success_config') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-xl shadow-slate-200/40 sm:p-8 xl:col-span-8">
            <form id="qr-setup-form" wire:submit="save" class="grid gap-10 lg:grid-cols-2">
                <div class="space-y-6">
                    <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-600 shadow-sm ring-1 ring-inset ring-blue-100/50">
                            <x-user.icon name="school" :size="24" />
                        </span>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Buổi học hôm nay</h2>
                            <p class="mt-0.5 text-[13px] font-semibold text-slate-500">Thông tin chi tiết về lớp và tiết học</p>
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
                                <span class="min-w-0 truncate" x-text="selectedClass.label || 'Chọn lớp học'"></span>
                                <x-user.icon name="chevron-down" :size="20" class="shrink-0 text-slate-400 transition" x-bind:class="classDropdownOpen ? 'rotate-180 text-blue-600' : ''" />
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
                                    @forelse ($classOptions as $courseClass)
                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-slate-50 hover:text-slate-950"
                                            @click="selectedClassId = @js((string) $courseClass['id']); classDropdownOpen = false"
                                            :class="String(selectedClassId) === @js((string) $courseClass['id']) ? 'bg-blue-50 text-blue-700' : 'text-slate-700'"
                                        >
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-extrabold">{{ $courseClass['label'] }}</span>
                                                <span class="mt-0.5 block truncate text-xs font-semibold text-slate-400">
                                                    {{ $courseClass['semester'] }} · {{ number_format($courseClass['members_count']) }} sinh viên
                                                </span>
                                            </span>

                                            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2" :class="String(selectedClassId) === @js((string) $courseClass['id']) ? 'border-blue-500 bg-blue-600' : 'border-slate-300 bg-white'">
                                                <span x-show="String(selectedClassId) === @js((string) $courseClass['id'])" class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                            </span>
                                        </button>
                                    @empty
                                        <div class="px-4 py-5 text-sm font-semibold text-slate-500">
                                            Chưa có lớp học để chọn.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @error('classId')
                            <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="session_title" class="mb-2 block text-sm font-bold text-slate-600">Tiêu đề buổi học</label>
                        <input id="session_title" wire:model.blur="name" type="text" placeholder="Ví dụ: Buổi 1 - Lý thuyết..." class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        @error('name')
                            <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="session_date" class="mb-2 block text-sm font-bold text-slate-600">Ngày học</label>
                            <input id="session_date" wire:model="date" type="date" readonly class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-600">Tiết học</label>
                            <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                                <div>
                                    <span class="mb-1 block text-xs font-bold text-slate-400">Bắt đầu</span>
                                    <div class="relative" x-data="{ openStart: false, position: 'bottom' }" @click.outside="openStart = false">
                                        <button 
                                            type="button" 
                                            x-ref="btnStart"
                                            @click="
                                                openStart = !openStart;
                                                if(openStart) {
                                                    $nextTick(() => {
                                                        let rect = $refs.btnStart.getBoundingClientRect();
                                                        let menuRect = $refs.menuStart.getBoundingClientRect();
                                                        let spaceBelow = window.innerHeight - rect.bottom;
                                                        let spaceAbove = rect.top;
                                                        position = (spaceBelow < menuRect.height && spaceAbove > spaceBelow) ? 'top' : 'bottom';
                                                    });
                                                }
                                            " 
                                            class="flex w-full items-center justify-between gap-2 rounded-xl border-2 bg-white px-3 py-3 text-sm font-extrabold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" 
                                            :class="openStart ? 'border-blue-500 ring-4 ring-blue-500/10' : 'border-slate-100'"
                                        >
                                            <div class="flex items-center gap-2">
                                                <x-user.icon name="clock" :size="16" class="text-blue-500" />
                                                <span x-text="'Tiết ' + startLesson"></span>
                                            </div>
                                            <x-user.icon name="chevron-down" :size="16" class="text-slate-400 transition" x-bind:class="openStart ? 'rotate-180 text-blue-500' : ''" />
                                        </button>
                                        
                                        <div 
                                            x-show="openStart" 
                                            x-cloak 
                                            x-ref="menuStart"
                                            x-transition.opacity
                                            class="absolute left-0 z-[60] w-full min-w-[140px] rounded-2xl border border-slate-200 bg-white p-3 shadow-xl shadow-slate-900/10"
                                            :class="position === 'top' ? 'bottom-full mb-2' : 'top-full mt-2'"
                                        >
                                            <div class="grid grid-cols-3 gap-1">
                                                @for ($lesson = 1; $lesson <= 15; $lesson++)
                                                    <button 
                                                        type="button" 
                                                        @click="
                                                            startLesson = {{ $lesson }};
                                                            if (startLesson > endLesson) endLesson = startLesson;
                                                            openStart = false;
                                                        " 
                                                        class="flex h-10 w-full items-center justify-center rounded-xl text-sm font-bold transition" 
                                                        :class="startLesson == {{ $lesson }} ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/30' : 'text-slate-700 hover:bg-slate-100'"
                                                    >
                                                        {{ $lesson }}
                                                    </button>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <span class="mt-4 text-lg font-black text-slate-300">-</span>

                                <div>
                                    <span class="mb-1 block text-xs font-bold text-slate-400">Kết thúc</span>
                                    <div class="relative" x-data="{ openEnd: false, position: 'bottom' }" @click.outside="openEnd = false">
                                        <button 
                                            type="button" 
                                            x-ref="btnEnd"
                                            @click="
                                                openEnd = !openEnd;
                                                if(openEnd) {
                                                    $nextTick(() => {
                                                        let rect = $refs.btnEnd.getBoundingClientRect();
                                                        let menuRect = $refs.menuEnd.getBoundingClientRect();
                                                        let spaceBelow = window.innerHeight - rect.bottom;
                                                        let spaceAbove = rect.top;
                                                        position = (spaceBelow < menuRect.height && spaceAbove > spaceBelow) ? 'top' : 'bottom';
                                                    });
                                                }
                                            " 
                                            class="flex w-full items-center justify-between gap-2 rounded-xl border-2 bg-white px-3 py-3 text-sm font-extrabold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" 
                                            :class="openEnd ? 'border-blue-500 ring-4 ring-blue-500/10' : 'border-slate-100'"
                                        >
                                            <div class="flex items-center gap-2">
                                                <x-user.icon name="clock" :size="16" class="text-blue-500" />
                                                <span x-text="'Tiết ' + endLesson"></span>
                                            </div>
                                            <x-user.icon name="chevron-down" :size="16" class="text-slate-400 transition" x-bind:class="openEnd ? 'rotate-180 text-blue-500' : ''" />
                                        </button>
                                        
                                        <div 
                                            x-show="openEnd" 
                                            x-cloak 
                                            x-ref="menuEnd"
                                            x-transition.opacity 
                                            class="absolute right-0 z-[60] w-full min-w-[140px] rounded-2xl border border-slate-200 bg-white p-3 shadow-xl shadow-slate-900/10"
                                            :class="position === 'top' ? 'bottom-full mb-2' : 'top-full mt-2'"
                                        >
                                            <div class="grid grid-cols-3 gap-1">
                                                @for ($lesson = 1; $lesson <= 15; $lesson++)
                                                    <button 
                                                        type="button" 
                                                        @click="
                                                            endLesson = {{ $lesson }};
                                                            if (endLesson < startLesson) startLesson = endLesson;
                                                            openEnd = false;
                                                        " 
                                                        class="flex h-10 w-full items-center justify-center rounded-xl text-sm font-bold transition" 
                                                        :class="endLesson == {{ $lesson }} ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/30' : 'text-slate-700 hover:bg-slate-100'"
                                                    >
                                                        {{ $lesson }}
                                                    </button>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('endLesson')
                                <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-50 text-indigo-600 shadow-sm ring-1 ring-inset ring-indigo-100/50">
                            <x-user.icon name="shield-check" :size="24" />
                        </span>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Bảo mật & Quy tắc</h2>
                            <p class="mt-0.5 text-[13px] font-semibold text-slate-500">Thiết lập kiểm soát mã QR</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-600">Thời gian làm mới mã</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ([5, 10, 15, 30] as $seconds)
                                <label class="relative">
                                    <input type="radio" wire:model.live="qrRefreshRate" value="{{ $seconds }}" class="peer sr-only" />
                                    <span class="flex cursor-pointer items-center justify-center rounded-xl border-2 border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-500 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:border-slate-200">
                                        {{ $seconds }}s
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-600">Thời lượng mở điểm danh (phút)</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ([10, 15, 20] as $minutes)
                                <label class="relative">
                                    <input type="radio" wire:model.live="durationMinutes" value="{{ $minutes }}" class="peer sr-only" />
                                    <span class="flex cursor-pointer items-center justify-center rounded-xl border-2 border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-500 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:border-slate-200">
                                        {{ $minutes }}p
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('durationMinutes')
                            <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-600">Bán kính GPS (m)</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ([10, 50, 70, 100] as $radius)
                                <label class="relative">
                                    <input type="radio" wire:model.live="gpsRadius" value="{{ $radius }}" class="peer sr-only" />
                                    <span class="flex cursor-pointer items-center justify-center rounded-xl border-2 border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-500 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:border-slate-200">
                                        {{ $radius }}m
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('gpsRadius')
                            <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-3 pt-1">
                        <label class="group relative flex cursor-pointer items-center justify-between gap-3 rounded-2xl border-2 px-4 py-3 transition-all duration-300"
                            :class="gpsEnabled ? 'border-red-500 bg-red-50/50 shadow-sm shadow-red-500/10' : 'border-slate-100 bg-white hover:border-red-200'">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors duration-300"
                                    :class="gpsEnabled ? 'bg-red-500 text-white shadow-md shadow-red-500/20' : 'bg-red-50 text-red-500 group-hover:bg-red-100'">
                                    <x-user.icon name="shield-alert" :size="18" />
                                </span>
                                <div>
                                    <p class="text-[13px] font-black transition-colors duration-300" :class="gpsEnabled ? 'text-red-900' : 'text-slate-800'">Xác minh tọa độ GPS</p>
                                    <p class="text-[11px] font-semibold leading-tight transition-colors duration-300" :class="gpsEnabled ? 'text-red-700/80' : 'text-slate-400'">Giới hạn khoảng cách</p>
                                </div>
                            </div>
                            <div class="relative inline-flex shrink-0 items-center">
                                <input type="checkbox" wire:model.live="gpsEnabled" class="peer sr-only" />
                                <span class="h-6 w-10 rounded-full bg-slate-200 transition-colors duration-300 peer-checked:bg-red-500"></span>
                                <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-300 peer-checked:translate-x-4"></span>
                            </div>
                        </label>

                        <label class="group relative flex cursor-pointer items-center justify-between gap-3 rounded-2xl border-2 px-4 py-3 transition-all duration-300"
                            :class="deviceCheck ? 'border-sky-500 bg-sky-50/50 shadow-sm shadow-sky-500/10' : 'border-slate-100 bg-white hover:border-sky-200'">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors duration-300"
                                    :class="deviceCheck ? 'bg-sky-500 text-white shadow-md shadow-sky-500/20' : 'bg-sky-50 text-sky-500 group-hover:bg-sky-100'">
                                    <x-user.icon name="laptop" :size="18" />
                                </span>
                                <div>
                                    <p class="text-[13px] font-black transition-colors duration-300" :class="deviceCheck ? 'text-sky-900' : 'text-slate-800'">Khóa thiết bị</p>
                                    <p class="text-[11px] font-semibold leading-tight transition-colors duration-300" :class="deviceCheck ? 'text-sky-700/80' : 'text-slate-400'">Ngăn 1 máy dùng nhiều tài khoản</p>
                                </div>
                            </div>
                            <div class="relative inline-flex shrink-0 items-center">
                                <input type="checkbox" wire:model.live="deviceCheck" class="peer sr-only" />
                                <span class="h-6 w-10 rounded-full bg-slate-200 transition-colors duration-300 peer-checked:bg-sky-500"></span>
                                <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-300 peer-checked:translate-x-4"></span>
                            </div>
                        </label>
                    </div>
                </div>
            </form>
        </section>

        <aside class="h-full xl:col-span-4">
            <div class="flex h-full flex-col rounded-3xl border border-slate-200/60 bg-white p-6 text-slate-900 shadow-xl shadow-slate-200/40 sm:p-8">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Tóm tắt cấu hình</h2>
                        <p class="mt-0.5 text-[13px] font-semibold text-slate-500">Phiên điểm danh sắp tạo</p>
                    </div>

                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-600 shadow-sm ring-1 ring-inset ring-blue-100/50">
                        <x-user.icon name="qr-code" :size="24" />
                    </span>
                </div>

                <div class="mt-6 space-y-5">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Môn học</p>
                        <p class="mt-2 text-[17px] font-black text-slate-900 leading-snug"><span x-text="selectedClass.subject_code || @js($selectedSubject)"></span> - <span x-text="selectedClass.name || @js($selectedClass?->name ?? 'Lớp demo QR')"></span></p>
                        <p class="mt-1 text-sm font-bold text-slate-500" x-text="selectedClass.semester || @js($selectedSemester)"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Sinh viên</p>
                            <p class="mt-2 text-3xl font-black text-slate-900" x-text="selectedClass.members_count || @js($studentCount)"></p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Tiết</p>
                            <p class="mt-2 text-3xl font-black text-slate-900">
                                <span x-text="startLesson"></span>-<span x-text="endLesson"></span>
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Thời gian</p>
                        <p class="mt-2 text-base font-black text-slate-900" x-text="openMinutes + 'p (QR ' + qrRefreshRate + 's)'"></p>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2 text-xs font-bold">
                        <span x-show="gpsEnabled" class="rounded-full bg-red-50 px-3.5 py-1.5 text-red-600 ring-1 ring-red-100">GPS bật</span>
                        <span x-show="deviceCheck" class="rounded-full bg-sky-50 px-3.5 py-1.5 text-sky-600 ring-1 ring-sky-100">Khóa thiết bị</span>
                    </div>
                </div>
            </div>


        </aside>
    </div>


</div>
