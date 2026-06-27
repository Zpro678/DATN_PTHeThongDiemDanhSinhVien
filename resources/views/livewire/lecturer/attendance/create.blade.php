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
        step: @entangle('step').live,
        selectedClassId: @entangle('classId').live,
        meetingEndTime: @entangle('meetingEndTime').live,
        timeOpen: false,

        // QR variables
        qrRefreshRate: @entangle('qrRefreshRate').live,
        openMinutes: @entangle('durationMinutes').live,
        gpsEnabled: @entangle('gpsEnabled').live,
        gpsLatitude: @entangle('gpsLatitude'),
        gpsLongitude: @entangle('gpsLongitude'),
        deviceCheck: @entangle('deviceCheck').live,
        
        classDropdownOpen: false,
        classes: @js($classOptions),

        get selectedClass() {
            return this.classes.find((item) => String(item.id) === String(this.selectedClassId)) || this.classes[0] || {};
        },
        get timeRangeLabel() {
            return this.meetingEndTime ? ('Kết thúc lúc ' + this.meetingEndTime) : 'Chưa đặt giờ';
        },
        get durationLabel() {
            if (! this.meetingEndTime) {
                return 'Chưa xác định';
            }
            const now = new Date();
            const [endHour, endMinute] = this.meetingEndTime.split(':').map(Number);
            const end = new Date(now);
            end.setHours(endHour, endMinute, 0, 0);
            const diff = Math.round((end - now) / 60000);
            if (diff <= 0) {
                return 'Giờ kết thúc đã qua';
            }
            return 'Mở điểm danh ~' + diff + ' phút';
        },
        get endHour() {
            return parseInt((this.meetingEndTime || '00:00').split(':')[0]) || 0;
        },
        get endMinute() {
            return parseInt((this.meetingEndTime || '00:00').split(':')[1]) || 0;
        },
        get endMeridiem() {
            return this.endHour < 12 ? 'SA' : 'CH';
        },
        applyTime(h, m) {
            const hh = String(((h % 24) + 24) % 24).padStart(2, '0');
            const mm = String(((m % 60) + 60) % 60).padStart(2, '0');
            this.meetingEndTime = hh + ':' + mm;
        },
        addPreset(mins) {
            const d = new Date();
            d.setSeconds(0, 0);
            d.setMinutes(d.getMinutes() + mins);
            const rounded = Math.round(d.getMinutes() / 5) * 5;
            this.applyTime(d.getHours() + Math.floor(rounded / 60), rounded % 60);
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
                    }
                );
            } else {
                alert('Trình duyệt của bạn không hỗ trợ định vị.');
                this.gpsEnabled = false;
            }
        }
    }"
    x-init="initGps()"
>
    <div class="mb-10 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center sm:gap-5">
        <div class="flex items-center gap-5">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[20px] border border-blue-300 bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 shadow-md transition-transform hover:-translate-y-1">
                <x-user.icon name="calendar-plus" :size="30" />
            </div>
            <div>
                <h1 class="mb-1 text-3xl font-extrabold tracking-tight text-slate-900">Tạo buổi điểm danh</h1>
                <p class="text-base text-slate-500">
                    <span x-show="step === 1">Chuẩn bị thông tin cơ bản cho buổi học.</span>
                    <span x-show="step === 3" x-cloak>Thiết lập các cấu hình mã QR.</span>
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <button x-show="step > 1" x-cloak wire:click="backToStep(step - 1)" class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                <x-user.icon name="arrow-left" :size="18" /> Quay lại
            </button>
            <a x-show="step === 1" href="{{ route('lecturer.attendance.index') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                <x-user.icon name="arrow-left" :size="18" /> Hủy bỏ
            </a>
        </div>
    </div>

    @if (session('success_config'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success_config') }}
        </div>
    @endif

    {{-- BƯỚC 1: THÔNG TIN CƠ BẢN --}}
    <div x-show="step === 1" x-transition.opacity.duration.300ms class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <div class="relative h-full overflow-visible rounded-[2rem] border border-slate-100 bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:p-10">
                <form id="step1-form" wire:submit.prevent class="relative z-10 space-y-8">
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
                                @if($sessionId || $cloneSessionId)
                                    <input type="text" readonly class="w-full cursor-default rounded-2xl border-2 border-slate-100 bg-slate-50 py-4 pl-12 pr-4 font-semibold text-slate-700 outline-none" value="{{ $classOptions->firstWhere('id', $classId)['label'] ?? '' }}">
                                @else
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between gap-3 rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 text-left font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                        @click="classDropdownOpen = ! classDropdownOpen"
                                    >
                                        <span class="min-w-0 truncate" x-text="selectedClass.label || 'Chọn lớp học'"></span>
                                        <x-user.icon name="chevron-down" :size="20" class="shrink-0 text-slate-500 transition-transform duration-200" x-bind:class="classDropdownOpen ? 'rotate-180' : ''" />
                                    </button>
                                @endif

                                <div
                                    x-cloak
                                    x-show="classDropdownOpen"
                                    x-transition
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
                            @error('classId')<span class="text-sm font-medium text-red-600">{{ $message }}</span>@enderror
                        </div>

                        <div class="group space-y-3">
                            <label for="session_name" class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-slate-600">
                                Tên buổi học <span class="text-lg leading-none text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <x-user.icon name="edit" :size="20" class="text-slate-400 transition-colors group-focus-within:text-blue-500" />
                                </div>
                                @if($sessionId || $cloneSessionId)
                                    <input
                                        id="session_name"
                                        type="text"
                                        readonly
                                        value="{{ $name }}"
                                        class="w-full cursor-default rounded-2xl border-2 border-slate-100 bg-slate-50 py-4 pl-12 pr-4 font-semibold text-slate-700 outline-none"
                                    >
                                @else
                                    <input
                                        id="session_name"
                                        wire:model.blur="name"
                                        type="text"
                                        placeholder="Ví dụ: Buổi 1 - Lý thuyết..."
                                        class="w-full rounded-2xl border-2 border-transparent bg-slate-50 py-4 pl-12 pr-4 font-semibold text-slate-900 shadow-sm outline-none transition-all hover:border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                        required
                                    >
                                @endif
                            </div>
                            @error('name')<span class="text-sm font-medium text-red-600">{{ $message }}</span>@enderror
                        </div>

                        <div class="group space-y-3 md:col-span-2">
                            <div class="flex items-center justify-between gap-3">
                                <label for="meeting_end_time" class="flex items-center gap-2 text-[13px] font-bold uppercase tracking-wider text-slate-600">
                                    Giờ kết thúc điểm danh <span class="text-lg leading-none text-red-500">*</span>
                                </label>
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600" x-text="timeRangeLabel"></span>
                            </div>

                            <div class="relative" @click.outside="timeOpen = false" @keydown.escape.window="timeOpen = false">
                                {{-- Nút hiển thị giờ đã chọn --}}
                                <button
                                    type="button"
                                    @click="timeOpen = !timeOpen"
                                    class="group/clock flex w-full items-center justify-between gap-3 rounded-2xl border-2 bg-slate-50 px-4 py-3 text-left shadow-sm outline-none transition-all duration-200 hover:border-slate-200"
                                    :class="timeOpen ? 'border-blue-500 bg-white ring-4 ring-blue-500/10' : 'border-transparent'"
                                >
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 transition-all duration-300 group-hover/clock:scale-105"
                                              :class="timeOpen ? 'scale-105 bg-blue-500 text-white shadow-md shadow-blue-500/25' : ''">
                                            <x-user.icon name="clock" :size="22" />
                                        </span>
                                        <span class="leading-tight">
                                            <span class="block text-2xl font-extrabold tabular-nums tracking-tight text-slate-900" x-text="meetingEndTime || '--:--'"></span>
                                            <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400" x-text="endMeridiem === 'SA' ? 'Buổi sáng' : 'Buổi chiều / tối'"></span>
                                        </span>
                                    </span>
                                    <x-user.icon name="chevron-down" :size="20" class="shrink-0 text-slate-400 transition-transform duration-300" x-bind:class="timeOpen ? 'rotate-180 text-blue-500' : ''" />
                                </button>

                                {{-- Bảng chọn giờ tùy biến --}}
                                <div
                                    x-show="timeOpen"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                    class="absolute left-0 right-0 z-40 mt-2 origin-top rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-900/10"
                                >
                                    {{-- Chọn nhanh tương đối từ hiện tại --}}
                                    <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Chọn nhanh từ bây giờ</p>
                                    <div class="mb-4 flex flex-wrap gap-2">
                                        <template x-for="preset in [{l:'+30 phút',v:30},{l:'+1 giờ',v:60},{l:'+1 giờ 30',v:90},{l:'+2 giờ',v:120}]" :key="preset.v">
                                            <button type="button" @click="addPreset(preset.v)"
                                                class="rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 active:scale-95"
                                                x-text="preset.l"></button>
                                        </template>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        {{-- Cột giờ --}}
                                        <div>
                                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Giờ</p>
                                            <div class="grid grid-cols-4 gap-1.5">
                                                <template x-for="h in 24" :key="h">
                                                    <button type="button" @click="applyTime(h - 1, endMinute)"
                                                        class="rounded-lg py-2 text-sm font-bold tabular-nums transition-all duration-150 hover:bg-blue-50 hover:text-blue-600 active:scale-90"
                                                        :class="endHour === (h - 1) ? 'scale-105 bg-blue-500 text-white shadow-md shadow-blue-500/30' : 'text-slate-600'"
                                                        x-text="String(h - 1).padStart(2, '0')"></button>
                                                </template>
                                            </div>
                                        </div>
                                        {{-- Cột phút --}}
                                        <div>
                                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Phút</p>
                                            <div class="grid grid-cols-3 gap-1.5">
                                                <template x-for="i in 12" :key="i">
                                                    <button type="button" @click="applyTime(endHour, (i - 1) * 5)"
                                                        class="rounded-lg py-2 text-sm font-bold tabular-nums transition-all duration-150 hover:bg-indigo-50 hover:text-indigo-600 active:scale-90"
                                                        :class="endMinute === ((i - 1) * 5) ? 'scale-105 bg-indigo-500 text-white shadow-md shadow-indigo-500/30' : 'text-slate-600'"
                                                        x-text="String((i - 1) * 5).padStart(2, '0')"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" @click="timeOpen = false"
                                        class="mt-4 w-full rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:from-blue-600 hover:to-indigo-700 active:scale-[0.98]">
                                        Xong
                                    </button>
                                </div>
                            </div>
                            <p class="flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                <x-user.icon name="info" :size="14" />
                                Buổi diễn ra hôm nay, bắt đầu ngay khi tạo. Giờ kết thúc phải cách hiện tại tối thiểu 10 phút. Hết giờ buổi sẽ tự động chốt.
                            </p>
                            @error('meetingEndTime')<span class="text-sm font-medium text-red-600">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-4">
            <div class="relative flex h-full flex-col overflow-hidden rounded-[2rem] border border-slate-100 bg-white p-7 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <div class="mb-8 flex items-center justify-between gap-3">
                    <h2 class="text-xl font-bold text-slate-900">Tổng quan</h2>
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

                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                        <p class="flex items-center gap-2 text-sm font-bold text-blue-700">
                            <x-user.icon name="clock" :size="20" />
                            <span x-text="timeRangeLabel"></span>
                        </p>
                        <p class="ml-7 mt-1.5 text-xs leading-relaxed text-blue-800/80">
                            <span x-text="durationLabel"></span>
                        </p>
                    </div>
                </div>

                <div class="mt-auto space-y-3">
                    <button
                        type="button"
                        wire:click="createManualSession"
                        wire:loading.attr="disabled"
                        class="group relative flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl bg-amber-100 px-6 py-3.5 text-base font-bold text-amber-700 shadow-sm transition-all hover:bg-amber-200 hover:shadow-md active:scale-[0.98] disabled:cursor-wait disabled:opacity-70 border border-amber-200"
                    >
                        <x-user.icon name="check-square" :size="20" class="transition-transform group-hover:scale-110" />
                        Điểm danh thủ công
                    </button>
                    
                    <button
                        type="button"
                        wire:click="createQrSession"
                        wire:loading.attr="disabled"
                        class="group relative flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4 text-lg font-bold text-white shadow-[0_8px_20px_rgba(59,130,246,0.25)] transition-all hover:from-blue-600 hover:to-indigo-700 active:scale-[0.98] disabled:cursor-wait disabled:opacity-70"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            <x-user.icon name="qr-code" :size="24" class="transition-transform group-hover:scale-110" />
                            Điểm danh QR / Link
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>



    {{-- BƯỚC 3: CẤU HÌNH QR --}}
    <div x-show="step === 3" x-cloak x-transition.opacity.duration.300ms class="grid gap-6 xl:grid-cols-12">
        <section class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-xl shadow-slate-200/40 sm:p-8 xl:col-span-8">
            <form id="step3-form" wire:submit="setupQr" class="grid gap-10 lg:grid-cols-2">
                <div class="space-y-6">
                    <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-600 shadow-sm ring-1 ring-inset ring-blue-100/50">
                            <x-user.icon name="shield-check" :size="24" />
                        </span>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Bảo mật & Quy tắc</h2>
                            <p class="mt-0.5 text-[13px] font-semibold text-slate-500">Thiết lập kiểm soát mã QR</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-600">Thời gian làm mới mã QR</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ([5, 10, 15, 30] as $seconds)
                                <label class="relative">
                                    <input type="radio" wire:model.live="qrRefreshRate" value="{{ $seconds }}" class="peer sr-only" />
                                    <span class="flex cursor-pointer items-center justify-center rounded-xl border-2 border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-500 transition peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 hover:border-slate-200">
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
                                    <span class="flex cursor-pointer items-center justify-center rounded-xl border-2 border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-500 transition peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 hover:border-slate-200">
                                        {{ $minutes }}p
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('durationMinutes')<span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-600">Bán kính GPS (m)</label>
                        <div class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <input type="number" wire:model.live="gpsRadius" placeholder="Nhập bán kính (m)" class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10" />
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">mét</span>
                            </div>
                            <div class="flex gap-1.5" x-data>
                                @foreach ([50, 100, 200] as $r)
                                    <button type="button" @click="$wire.set('gpsRadius', {{ $r }})" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:border-indigo-300 transition-all">
                                        {{ $r }}m
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('gpsRadius')<span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>@enderror
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
                    </div>

                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-50 text-indigo-600 shadow-sm ring-1 ring-inset ring-indigo-100/50">
                        <x-user.icon name="qr-code" :size="24" />
                    </span>
                </div>

                <div class="mt-6 space-y-5">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Thời gian mở QR</p>
                        <p class="mt-2 text-base font-black text-slate-900" x-text="openMinutes + ' phút (Làm mới ' + qrRefreshRate + 's)'"></p>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2 text-xs font-bold">
                        <span x-show="gpsEnabled" class="rounded-full bg-red-50 px-3.5 py-1.5 text-red-600 ring-1 ring-red-100">GPS bật</span>
                        <span x-show="deviceCheck" class="rounded-full bg-sky-50 px-3.5 py-1.5 text-sky-600 ring-1 ring-sky-100">Khóa thiết bị</span>
                    </div>
                </div>

                <button
                    type="submit"
                    form="step3-form"
                    wire:loading.attr="disabled"
                    class="group relative mt-auto flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-4 text-lg font-bold text-white shadow-[0_8px_20px_rgba(79,70,229,0.25)] transition-all hover:from-indigo-700 hover:to-violet-700 active:scale-[0.98] disabled:cursor-wait disabled:opacity-70"
                >
                    <span class="relative z-10 flex items-center gap-2">
                        Bắt đầu phát mã
                        <x-user.icon name="qr-code" :size="24" class="transition-transform group-hover:scale-110" />
                    </span>
                </button>
            </div>
        </aside>
    </div>
</div>
