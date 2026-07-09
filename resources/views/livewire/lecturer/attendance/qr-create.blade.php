@php
    $classOptions = $classes
        ->map(fn ($class) => [
            'id' => (string) $class->id,
            'label' => $class->name.' - '.$class->join_key,
            'name' => $class->name,
            'code' => $class->join_key,
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
    $selectedSubject = $selectedClass?->join_key ?: 'QR101';
@endphp

<div
    class="w-full space-y-8 px-6 py-6 pb-24 sm:px-10 lg:px-16"
    x-data="{
        classDropdownOpen: false,
        selectedClassId: @entangle('classId').live,
        sessionTitle: @entangle('name').live,
        sessionDate: @entangle('date').live,
        qrRefreshRate: @entangle('qrRefreshRate').live,
        openMinutes: @entangle('durationMinutes').live,
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
                if (value) {
                    this.fetchLocation();
                } else {
                    this.gpsLatitude = null;
                    this.gpsLongitude = null;
                }
            });
            this.$watch('selectedClassId', value => {
                if (this.gpsEnabled) {
                    this.fetchLocation();
                }
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
                        this.gpsLatitude = null;
                        this.gpsLongitude = null;
                    }
                );
            } else {
                alert('Trình duyệt của bạn không hỗ trợ định vị.');
                this.gpsEnabled = false;
                this.gpsLatitude = null;
                this.gpsLongitude = null;
            }
        }
    }"
    x-init="initGps()"
>


    <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between xl:grid xl:grid-cols-12 xl:gap-6">
        <div class="min-w-0 flex-1 xl:col-span-8">
            <h1 class="truncate text-2xl md:text-3xl font-bold tracking-tight text-slate-900" title="THIẾT LẬP ĐIỂM DANH @if($selectedClass) - {{ mb_strtoupper($selectedClass->name) }} ({{ mb_strtoupper($selectedClass->join_key) }}) @endif">
                THIẾT LẬP ĐIỂM DANH
                @if($selectedClass)
                    - {{ mb_strtoupper($selectedClass->name) }} ({{ mb_strtoupper($selectedClass->join_key) }})
                @endif
            </h1>
        </div>

        <div class="hidden md:flex shrink-0 flex-col gap-3 sm:flex-row xl:col-span-4 xl:justify-end">
            <button type="button" wire:click="saveConfig" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-slate-300 hover:bg-slate-50 hover:shadow">
                Lưu cấu hình
            </button>

            <button type="submit" form="qr-setup-form"
                x-bind:disabled="gpsEnabled && (!gpsLatitude || !gpsLongitude)"
                :class="(gpsEnabled && (!gpsLatitude || !gpsLongitude)) ? 'opacity-70 cursor-not-allowed' : 'hover:-translate-y-0.5 hover:from-blue-700 hover:to-blue-700 hover:shadow-blue-500/40'"
                class="group inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-8 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 transition-all">
                <span x-show="gpsEnabled && (!gpsLatitude || !gpsLongitude)">Đang lấy tọa độ...</span>
                <span x-show="!(gpsEnabled && (!gpsLatitude || !gpsLongitude))">{{ $editSessionId ? 'Cập nhật thiết lập' : 'Bắt đầu phát mã' }}</span>
                <x-user.icon name="qr-code" :size="18" class="transition-transform group-hover:scale-110" />
            </button>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="rounded-2xl border border-slate-200/60 bg-white p-6 shadow-xl shadow-slate-200/40 sm:p-8 xl:col-span-8">
            <form id="qr-setup-form" wire:submit="save" class="grid gap-10 lg:grid-cols-2">
                <div class="space-y-6">
                    <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-blue-600 shadow-sm ring-1 ring-inset ring-blue-100/50">
                            <x-user.icon name="school" :size="24" />
                        </span>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Buổi học hôm nay</h2>
                            <p class="mt-0.5 text-[13px] font-semibold text-slate-500">Thông tin chi tiết về lớp và buổi học</p>
                        </div>
                    </div>

                    <div>
                        <label id="class_picker_label" class="mb-2 block text-sm font-bold text-slate-600">Chọn lớp học</label>
                        @if($cloneSessionId || $editSessionId)
                        <div class="relative">
                            <input type="text" readonly class="w-full cursor-default rounded-2xl border-2 border-slate-100 bg-white px-4 py-3.5 text-sm font-extrabold text-slate-800 shadow-sm outline-none transition hover:border-blue-300 hover:bg-slate-50 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" :value="selectedClass.label || 'Chọn lớp học'" />
                        </div>
                        @else
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
                                                    Mã lớp: {{ $courseClass['code'] }} · {{ number_format($courseClass['members_count']) }} học viên
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
                        @endif
                        @error('classId')
                            <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="meeting_title" class="mb-2 block text-sm font-bold text-slate-600">Tên buổi điểm danh</label>
                        @if($cloneSessionId || $editSessionId)
                        <input id="meeting_title" wire:model.blur="meetingName" type="text" readonly class="w-full cursor-default rounded-xl border-2 border-slate-100 bg-white px-4 py-3 text-sm font-bold text-slate-800 outline-none transition hover:border-blue-300 hover:bg-slate-50 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        @else
                        <input id="meeting_title" wire:model.blur="meetingName" type="text" placeholder="Ví dụ: Buổi 1 - Lý thuyết..." class="w-full rounded-xl border-2 border-slate-100 bg-white px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        @endif
                        @error('meetingName')
                            <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="session_title" class="mb-2 block text-sm font-bold text-slate-600">Tên phiên</label>
                        @if($cloneSessionId || $editSessionId)
                        <input id="session_title" wire:model.blur="name" type="text" readonly class="w-full cursor-default rounded-xl border-2 border-slate-100 bg-white px-4 py-3 text-sm font-bold text-slate-800 outline-none transition hover:border-blue-300 hover:bg-slate-50 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        @else
                        <input id="session_title" wire:model.blur="name" type="text" placeholder="Ví dụ: Phiên 1, Quét QR lần 1..." class="w-full rounded-xl border-2 border-slate-100 bg-white px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        @endif
                        @error('name')
                            <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="session_date" class="mb-2 block text-sm font-bold text-slate-600">Ngày học</label>
                            @if($cloneSessionId || $editSessionId)
                            <input id="session_date" wire:model="date" type="date" readonly class="w-full cursor-default rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none transition hover:border-blue-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                            @else
                            <input id="session_date" wire:model="date" type="date" readonly class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                            @endif
                        </div>

                    </div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-blue-600 shadow-sm ring-1 ring-inset ring-blue-100/50">
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
                        <div class="flex items-center gap-3">
                            <div class="relative w-32 shrink-0">
                                <input type="number" wire:model="gpsRadius" readonly class="w-full cursor-default rounded-xl border-2 border-blue-500 bg-blue-50 py-2.5 pl-4 pr-12 text-sm font-bold text-blue-700 outline-none transition" />
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-bold text-blue-700">mét</span>
                            </div>
                            <div class="flex flex-1 gap-2">
                                @foreach ([50, 70, 100] as $r)
                                    <button type="button" wire:click="$set('gpsRadius', {{ $r }})" class="flex-1 whitespace-nowrap rounded-xl border-2 px-3 py-2.5 text-xs font-bold transition-all {{ $gpsRadius == $r ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-100 bg-white text-slate-600 hover:bg-slate-50 hover:border-blue-300' }}">
                                        {{ $r }}m
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('gpsRadius')
                            <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                        @enderror
                        @error('gpsLatitude')
                            <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                        @enderror
                        @error('gpsLongitude')
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
                        @error('gpsLatitude')
                            <span class="mt-2 block text-sm font-medium text-red-600">{{ $message }}</span>
                        @enderror

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
            <div class="flex h-full flex-col rounded-2xl border border-slate-200/60 bg-white p-6 text-slate-900 shadow-xl shadow-slate-200/40 sm:p-8">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Tóm tắt cấu hình</h2>
                        <p class="mt-0.5 text-[13px] font-semibold text-slate-500">Phiên điểm danh sắp tạo</p>
                    </div>

                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-blue-600 shadow-sm ring-1 ring-inset ring-blue-100/50">
                        <x-user.icon name="qr-code" :size="24" />
                    </span>
                </div>

                <div class="mt-6 space-y-5">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Lớp học</p>
                        <p class="mt-2 text-[17px] font-black text-slate-900 leading-snug"><span x-text="selectedClass.code || @js($selectedSubject)"></span> - <span x-text="selectedClass.name || @js($selectedClass?->name ?? 'Lớp demo QR')"></span></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Học viên</p>
                            <p class="mt-2 text-3xl font-black text-slate-900" x-text="selectedClass.members_count || @js($studentCount)"></p>
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

    {{-- Mobile action buttons (bottom of page) --}}
    <div class="flex md:hidden flex-col gap-3 mt-4">
        <button type="button" wire:click="saveConfig" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-slate-300 hover:bg-slate-50 hover:shadow">
            Lưu cấu hình
        </button>

        <button type="submit" form="qr-setup-form"
            x-bind:disabled="gpsEnabled && (!gpsLatitude || !gpsLongitude)"
            :class="(gpsEnabled && (!gpsLatitude || !gpsLongitude)) ? 'opacity-70 cursor-not-allowed' : 'hover:from-blue-700 hover:to-blue-700'"
            class="group inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 transition-all">
            <span x-show="gpsEnabled && (!gpsLatitude || !gpsLongitude)">Đang lấy tọa độ...</span>
            <span x-show="!(gpsEnabled && (!gpsLatitude || !gpsLongitude))">{{ $editSessionId ? 'Cập nhật thiết lập' : 'Bắt đầu phát mã' }}</span>
            <x-user.icon name="qr-code" :size="18" />
        </button>
    </div>

</div>
