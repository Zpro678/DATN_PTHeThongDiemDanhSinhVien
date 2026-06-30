@php
    $classOptions = $classes
        ->map(fn ($class) => [
            'id' => (string) $class->id,
            'label' => $class->name.' - '.$class->join_key,
            'name' => $class->name,
            'code' => $class->join_key,
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
        }
    }"
>
    <div class="mb-10 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center sm:gap-5">
        <div class="flex items-center gap-5">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[20px] border border-blue-300 bg-primary/10 text-blue-600 shadow-md transition-transform hover:-translate-y-1">
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
            <div class="relative h-full overflow-visible rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:p-8">
                <form id="step1-form" wire:submit.prevent class="relative z-10 space-y-6">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-6 md:grid-cols-2">
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

                                {{-- Bảng chọn giờ kiểu scroll wheel (Thiết kế mới) --}}
                                <div
                                    x-show="timeOpen"
                                    x-cloak
                                    x-init="
                                        $watch('timeOpen', opened => {
                                            if (!opened) return;
                                            $nextTick(() => {
                                                if ($refs.hourWheel)   $refs.hourWheel.scrollTop   = endHour   * 44;
                                                if ($refs.minuteWheel) $refs.minuteWheel.scrollTop = endMinute * 44;
                                            });
                                        });
                                    "
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 -translate-y-3 scale-95"
                                    class="absolute left-0 top-full z-50 mt-3 w-full origin-top overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-2 shadow-2xl shadow-slate-900/10"
                                >
                                    <style>
                                        .tw-wheel::-webkit-scrollbar { display: none; }
                                        .tw-wheel { -ms-overflow-style: none; scrollbar-width: none; }
                                        .fade-mask {
                                            -webkit-mask-image: linear-gradient(to bottom, transparent, rgba(0,0,0,1) 15%, rgba(0,0,0,1) 85%, transparent);
                                            mask-image: linear-gradient(to bottom, transparent, rgba(0,0,0,1) 15%, rgba(0,0,0,1) 85%, transparent);
                                        }
                                    </style>

                                    {{-- Header --}}
                                    <div class="flex items-center justify-between rounded-t-2xl bg-slate-50/80 px-4 py-2 border-b border-slate-100">
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Giờ kết thúc</p>
                                            <p class="mt-1 text-2xl font-black tabular-nums tracking-tight text-slate-900" x-text="meetingEndTime || '--:--'"></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Trạng thái</p>
                                            <p class="mt-1 text-sm font-bold text-blue-600" x-text="durationLabel"></p>
                                        </div>
                                    </div>

                                    {{-- Quick presets --}}
                                    <div class="mt-2 grid grid-cols-4 gap-2 px-2">
                                        <template x-for="preset in [{l:'+30p',v:30},{l:'+1h',v:60},{l:'+1.5h',v:90},{l:'+2h',v:120}]" :key="preset.v">
                                            <button type="button"
                                                @click="addPreset(preset.v); $nextTick(() => {
                                                    $refs.hourWheel.scrollTo({top: endHour * 28, behavior: 'smooth'});
                                                    $refs.minuteWheel.scrollTo({top: endMinute * 28, behavior: 'smooth'});
                                                })"
                                                class="flex items-center justify-center rounded-lg bg-slate-50 px-2 py-1.5 text-[11px] font-bold text-slate-600 transition-colors hover:bg-blue-50 hover:text-blue-700 active:scale-95"
                                                x-text="preset.l">
                                            </button>
                                        </template>
                                    </div>

                                    {{-- Scroll wheels --}}
                                    <div class="relative mt-2 px-4 py-2">
                                        {{-- Wheel container --}}
                                        <div class="relative flex h-[84px] items-center justify-center fade-mask">
                                            
                                            {{-- Selection highlight strip --}}
                                            <div class="pointer-events-none absolute inset-x-2 rounded-xl bg-blue-50/60 ring-1 ring-blue-500/20"
                                                 style="top: calc(50% - 14px); height: 28px;"></div>

                                            {{-- Hour wheel --}}
                                            <div class="relative flex-1 h-full tw-wheel overflow-y-scroll"
                                                 x-ref="hourWheel"
                                                 @scroll.passive.debounce.50ms="applyTime(Math.min(23, Math.max(0, Math.round($event.target.scrollTop / 28))), endMinute)"
                                                 style="scroll-snap-type: y mandatory;">
                                                <div style="height: 28px; flex-shrink: 0;"></div>
                                                <template x-for="h in 24" :key="h">
                                                    <div @click="applyTime(h-1, endMinute); $refs.hourWheel.scrollTo({top:(h-1)*28, behavior:'smooth'})"
                                                         style="height: 28px; scroll-snap-align: center;"
                                                         class="flex cursor-pointer select-none items-center justify-center tabular-nums transition-all duration-200"
                                                         :class="endHour === (h-1)
                                                             ? 'text-blue-600 text-xl font-black scale-110'
                                                             : 'text-slate-400 text-sm font-bold opacity-60 hover:opacity-100 hover:text-slate-600'">
                                                        <span x-text="String(h-1).padStart(2,'0')"></span>
                                                    </div>
                                                </template>
                                                <div style="height: 28px; flex-shrink: 0;"></div>
                                            </div>

                                            {{-- Colon separator --}}
                                            <div class="relative z-10 flex w-4 shrink-0 items-center justify-center pb-1 text-lg font-black text-slate-300">
                                                :
                                            </div>

                                            {{-- Minute wheel --}}
                                            <div class="relative flex-1 h-full tw-wheel overflow-y-scroll"
                                                 x-ref="minuteWheel"
                                                 @scroll.passive.debounce.50ms="applyTime(endHour, Math.min(59, Math.max(0, Math.round($event.target.scrollTop / 28))))"
                                                 style="scroll-snap-type: y mandatory;">
                                                <div style="height: 28px; flex-shrink: 0;"></div>
                                                <template x-for="m in 60" :key="m">
                                                    <div @click="applyTime(endHour, m-1); $refs.minuteWheel.scrollTo({top:(m-1)*28, behavior:'smooth'})"
                                                         style="height: 28px; scroll-snap-align: center;"
                                                         class="flex cursor-pointer select-none items-center justify-center tabular-nums transition-all duration-200"
                                                         :class="endMinute === (m-1)
                                                             ? 'text-blue-600 text-xl font-black scale-110'
                                                             : 'text-slate-400 text-sm font-bold opacity-60 hover:opacity-100 hover:text-slate-600'">
                                                        <span x-text="String(m-1).padStart(2,'0')"></span>
                                                    </div>
                                                </template>
                                                <div style="height: 28px; flex-shrink: 0;"></div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Confirm Button --}}
                                    <div class="p-2 pt-0">
                                        <button type="button" @click="timeOpen = false"
                                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-2.5 text-sm font-bold text-white shadow-md shadow-slate-900/20 transition-all hover:bg-slate-800 active:scale-[0.98]">
                                            Xác nhận thời gian
                                        </button>
                                    </div>
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

                {{-- Nút hành động trong card --}}
                <div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                    <a
                        href="{{ route('lecturer.attendance.index') }}"
                        class="inline-flex shrink-0 items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
                    >
                        Hủy bỏ
                    </a>
                    <button
                        type="button"
                        wire:click="createManualSession"
                        wire:loading.attr="disabled"
                        class="group inline-flex shrink-0 items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container active:scale-[0.98] disabled:cursor-wait disabled:opacity-70"
                    >
                        <x-user.icon name="calendar-plus" :size="18" class="transition-transform group-hover:scale-110" />
                        Tạo buổi điểm danh
                        <span wire:loading wire:target="createManualSession" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4">
            <div class="relative flex h-full flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <div class="mb-6 flex items-center justify-between gap-3">
                    <h2 class="text-xl font-bold text-slate-900">Tổng quan</h2>
                </div>

                <div class="relative z-10 mb-4 flex flex-1 flex-col justify-start gap-5">
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-5 transition-colors hover:border-slate-300">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-blue-500 shadow-sm">
                                <x-user.icon name="book-open" :size="24" />
                            </div>
                            <div class="min-w-0">
                                <span class="block truncate text-sm font-bold text-slate-700" x-text="selectedClass.name || 'Chưa chọn lớp'"></span>
                                <span class="block truncate text-[11px] font-medium text-slate-500" x-text="selectedClass.subject_code ? 'Mã môn: ' + selectedClass.subject_code : 'Chưa có mã môn'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-5 transition-colors hover:border-slate-300">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-blue-500 shadow-sm">
                                <x-user.icon name="users" :size="24" />
                            </div>
                            <div class="min-w-0">
                                <span class="block text-sm font-bold text-slate-700">Sĩ số dự kiến</span>
                                <span class="block truncate text-[11px] font-medium text-slate-500" x-text="selectedClass.code ? 'Lớp ' + selectedClass.code : 'Chưa chọn lớp'"></span>
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


            </div>
        </div>
    </div>


</div>