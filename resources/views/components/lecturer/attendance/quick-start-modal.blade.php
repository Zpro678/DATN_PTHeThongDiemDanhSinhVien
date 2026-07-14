@props(['hideClassSelector' => false, 'hideMeetingSelector' => false])
@php
    $quickClassId = $this->quickClassId ?? null;
    $quickMeetingId = $this->quickMeetingId ?? null;
    $subtitle = $hideMeetingSelector
        ? 'Thiết lập phiên cho buổi hiện tại'
        : ($hideClassSelector ? 'Chọn buổi để bắt đầu' : 'Chọn lớp và buổi để bắt đầu');
@endphp

    <div x-data="{
        wire: $wire,
        showQuickStart: @entangle('showQuickStart').live,
        quickStartType: @entangle('quickStartType').live,
        quickClassId: @entangle('quickClassId').live,
        quickMeetingId: @entangle('quickMeetingId').live,
        newMeetingName: @entangle('newMeetingName').live,
        meetingEndTime: @entangle('meetingEndTime').live,
        sessionName: @entangle('sessionName').live,
        durationMinutes: @entangle('durationMinutes').live,
        qrRefreshRate: @entangle('qrRefreshRate').live,
        gpsEnabled: @entangle('gpsEnabled').live,
        gpsLatitude: @entangle('gpsLatitude').live,
        gpsLongitude: @entangle('gpsLongitude').live,
        gpsRadius: @entangle('gpsRadius').live,
        hasStudents: @entangle('selectedClassHasStudents').live,
        isRequestingGps: false,
        init() {
            // Khi mở modal hoặc chuyển sang QR mà đang bật GPS nhưng chưa có toạ độ -> xin quyền ngay.
            const maybeLocate = () => {
                if (this.showQuickStart && this.quickStartType === 'qr' && this.gpsEnabled && !this.gpsLatitude) {
                    this.getLocation();
                }
            };
            this.$watch('showQuickStart', () => maybeLocate());
            this.$watch('quickStartType', () => maybeLocate());
            maybeLocate();
        },
        // Trả về Promise: kích hoạt hộp thoại xin quyền định vị của trình duyệt.
        requestLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('unsupported'));
                    return;
                }
                this.isRequestingGps = true;
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.gpsLatitude = position.coords.latitude;
                        this.gpsLongitude = position.coords.longitude;
                        this.wire.set('gpsLatitude', position.coords.latitude);
                        this.wire.set('gpsLongitude', position.coords.longitude);
                        this.isRequestingGps = false;
                        resolve(position);
                    },
                    (error) => {
                        this.isRequestingGps = false;
                        reject(error);
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            });
        },
        getLocation() {
            if (!this.gpsEnabled) {
                this.gpsLatitude = null;
                this.gpsLongitude = null;
                this.wire.set('gpsLatitude', null);
                this.wire.set('gpsLongitude', null);
                return;
            }
            this.requestLocation().catch((error) => {
                if (error && error.message === 'unsupported') {
                    alert('Trình duyệt không hỗ trợ GPS.');
                } else {
                    alert('Không thể lấy vị trí. Vui lòng cấp quyền vị trí cho trình duyệt.');
                }
                this.gpsEnabled = false;
                this.wire.set('gpsEnabled', false);
            });
        },
        // Bấm tạo phiên: nếu bật GPS mà chưa có toạ độ thì xin quyền trước, có quyền mới submit.
        async submitQuick() {
            if (this.quickStartType === 'qr' && this.gpsEnabled && !this.gpsLatitude) {
                try {
                    await this.requestLocation();
                } catch (error) {
                    alert('Vui lòng cấp quyền truy cập vị trí GPS cho trình duyệt để tạo mã điểm danh.');
                    return;
                }
            }
            this.wire.startQuick();
        },
        updateMeeting(id, name) {
            this.quickMeetingId = id;
            this.newMeetingName = name;
        },
        updateClass(id) {
            this.quickClassId = id;
        }
    }">
    <template x-teleport="body">
        <div x-cloak x-show="showQuickStart" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            <div x-show="showQuickStart" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-slate-950/50" @click="showQuickStart = false" aria-label="Đóng"></div>
            <div x-show="showQuickStart" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95 translate-y-2" class="relative w-full max-w-[600px] max-h-[90vh] grid rounded-3xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-900/10" style="grid-template-rows: auto minmax(0, 1fr) auto;">
                
                <button type="button" @click="showQuickStart = false" class="absolute right-4 top-4 z-20 flex h-8 w-8 items-center justify-center text-black">
                    <x-user.icon name="x" :size="18" />
                </button>
                
                {{-- Fixed Header --}}
                <div class="px-7 pt-7 pb-4 bg-white rounded-t-3xl border-b border-slate-100 shrink-0 z-10">
                    {{-- Header with visual flair --}}
                    <div class="mb-5 flex items-center gap-4">
                        <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl shadow-sm transition-all duration-300" :class="quickStartType === 'manual' ? 'bg-gradient-to-br from-amber-100 to-orange-100 text-amber-600 shadow-amber-200/50' : 'bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-600 shadow-blue-200/50'">
                            <x-user.icon name="check-square" :size="26" x-show="quickStartType === 'manual'" />
                            <x-user.icon name="qr-code" :size="26" x-show="quickStartType === 'qr'" />
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900" x-text="quickStartType === 'manual' ? 'Điểm danh thủ công' : 'Điểm danh QR'"></h3>
                            <p class="text-sm text-slate-400 mt-0.5">{{ $subtitle }}</p>
                        </div>
                    </div>

                    {{-- Step indicator --}}
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-black transition-all duration-300" :class="quickClassId ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-200' : (quickStartType === 'manual' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700')">
                                <template x-if="quickClassId"><x-user.icon name="check" :size="12" /></template>
                                <template x-if="!quickClassId"><span>1</span></template>
                            </div>
                            <span class="text-xs font-bold transition-colors duration-200" :class="quickClassId ? 'text-emerald-600' : 'text-slate-500'">Lớp</span>
                        </div>
                        <div class="h-px flex-1 transition-colors duration-300" :class="quickClassId ? 'bg-emerald-200' : 'bg-slate-200'"></div>
                        <div class="flex items-center gap-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-black transition-all duration-300" :class="quickMeetingId ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-200' : (quickClassId ? (quickStartType === 'manual' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') : 'bg-slate-100 text-slate-400')">
                                <template x-if="quickMeetingId"><x-user.icon name="check" :size="12" /></template>
                                <template x-if="!quickMeetingId"><span>2</span></template>
                            </div>
                            <span class="text-xs font-bold transition-colors duration-200" :class="quickMeetingId ? 'text-emerald-600' : (quickClassId ? 'text-slate-500' : 'text-slate-400')">Buổi</span>
                        </div>
                        <div class="h-px flex-1 transition-colors duration-300" :class="quickMeetingId ? 'bg-emerald-200' : 'bg-slate-200'"></div>
                        <div class="flex items-center gap-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-[10px] font-black text-slate-400">3</div>
                            <span class="text-xs font-bold text-slate-400">Bắt đầu</span>
                        </div>
                    </div>
                </div>
                
                {{-- Scrollable Form --}}
                <div class="min-h-0 overflow-y-auto scrollbar-custom p-7 pt-5">
                    <div class="space-y-5">
                        
                        @if(!$hideClassSelector)
                        {{-- ═══ Class Selector ═══ --}}
                        <div x-data="{
                            open: false,
                            search: @js($quickClassId && $this->activeClasses->firstWhere('id', $quickClassId) ? $this->activeClasses->firstWhere('id', $quickClassId)->name : ''),
                            selectedId: '{{ $quickClassId }}',
                            get hasResults() {
                                if (this.search === '') return true;
                                let s = this.search.toLowerCase();
                                return {{ $this->activeClasses->map(fn($c) => trim(($c->class_code ?: $c->join_key) . ' ' . $c->name))->toJson() }}.some(text => text.toLowerCase().includes(s));
                            }
                        }" class="relative z-50" @click.outside="open = false">
                            <div class="mb-2 flex items-center justify-between">
                                <label class="block text-sm font-bold text-slate-700">Chọn lớp học</label>
                                <span x-show="selectedId" x-cloak class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-600 ring-1 ring-inset ring-emerald-500/20 transition-all duration-200">
                                    <x-user.icon name="check" :size="10" />
                                    Đã chọn
                                </span>
                            </div>
                            
                            <div class="relative z-20 group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 transition-all duration-200" :class="open ? 'text-blue-500' : ''">
                                    <x-user.icon name="search" :size="18" x-show="open" x-cloak />
                                    <x-user.icon name="book-open" :size="18" x-show="!open" />
                                </div>

                                <input
                                    wire:key="class-search-input"
                                    type="text"
                                    x-ref="searchInput"
                                    x-model="search"
                                    value="{{ $quickClassId && $this->activeClasses->firstWhere('id', $quickClassId) ? $this->activeClasses->firstWhere('id', $quickClassId)->name : '' }}"
                                    @input="open = true"
                                    @focus="open = true"
                                    @keydown.escape="open = false"
                                    placeholder="Nhập tên, mã lớp để tìm..."
                                    class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/80 py-3 pl-10 pr-10 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all duration-200 hover:border-slate-300 hover:bg-white focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-md focus:shadow-blue-500/5 placeholder:font-medium placeholder:text-slate-400"
                                    autocomplete="off"
                                />
                                
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                                    <div class="flex h-5 w-5 items-center justify-center rounded-md bg-slate-100 text-slate-400 transition-all duration-200" :class="open ? 'rotate-180 bg-blue-50 text-blue-500' : ''">
                                        <x-user.icon name="chevron-down" :size="14" />
                                    </div>
                                </div>
                            </div>

                            <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="absolute left-0 right-0 top-full mt-2 z-10 overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xl shadow-slate-200/50">
                                <div class="max-h-56 overflow-y-auto overscroll-contain scrollbar-custom">
                                    <div x-show="!hasResults" class="flex flex-col items-center justify-center px-4 py-8 text-center">
                                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                                            <x-user.icon name="inbox" :size="20" />
                                        </div>
                                        <p class="text-sm font-medium text-slate-500">Không tìm thấy lớp học nào khớp với từ khóa.</p>
                                    </div>

                                    @foreach($this->activeClasses as $cClass)
                                        @php($displayClassCode = $cClass->class_code ?: $cClass->join_key)
                                        <button wire:key="q-class-{{ $cClass->id }}" type="button" x-show="search === '' || '{{ mb_strtolower($displayClassCode . ' ' . $cClass->name, 'UTF-8') }}'.includes(search.toLowerCase())" 
                                            @click="updateClass('{{ $cClass->id }}'); search = @js($cClass->name); selectedId = '{{ $cClass->id }}'; open = false;" 
                                            class="flex w-full items-center justify-between gap-3 border-b border-slate-50 px-4 py-3 text-left transition-all duration-150 last:border-0 hover:bg-blue-50/50 focus:bg-blue-50/50 outline-none"
                                            :class="'{{ $quickClassId }}' == '{{ $cClass->id }}' ? 'bg-blue-50/60' : ''">
                                            
                                            <div class="flex min-w-0 items-center gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <span class="block truncate text-sm font-bold transition-colors duration-150" :class="'{{ $quickClassId }}' == '{{ $cClass->id }}' ? 'text-blue-800' : 'text-slate-900'">{{ $cClass->name }}</span>
                                                    <span class="mt-0.5 flex items-center gap-1 truncate text-xs font-medium transition-colors duration-150" :class="'{{ $quickClassId }}' == '{{ $cClass->id }}' ? 'text-blue-500' : 'text-slate-400'">
                                                        <x-user.icon name="hash" :size="10" /> {{ $displayClassCode }}
                                                    </span>
                                                </div>
                                            </div>

                                            @if($quickClassId == $cClass->id)
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 text-white shadow-sm shadow-blue-600/30">
                                                    <x-user.icon name="check" :size="14" />
                                                </div>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @error('quickClassId') <span class="mt-1.5 block text-xs font-bold text-red-500"><x-user.icon name="alert-circle" :size="12" class="inline pb-0.5" /> {{ $message }}</span> @enderror
                        </div>
                        @endif

                        @if(!$hideMeetingSelector)
                        {{-- ═══ Meeting Selector ═══ --}}
                        <div x-data="{
                            open: false,
                            search: @js($quickMeetingId && $this->classMeetings->firstWhere('id', (int)$quickMeetingId) ? $this->classMeetings->firstWhere('id', (int)$quickMeetingId)->name : ($newMeetingName ?? '')),
                            selectedId: '{{ $quickMeetingId }}',
                            updateMeetingFn: null,
                            setMeetingEndTimeFn: null,
                            init() {
                                this.updateMeetingFn = (id, name) => updateMeeting(id, name);
                                this.setMeetingEndTimeFn = (time) => { meetingEndTime = time; };
                                this.$watch('search', () => {
                                    this.syncSelection();
                                });
                            },
                            get meetingsList() {
                                return @js($this->classMeetings->map(fn($m) => ['id' => $m->id, 'name' => $m->name]));
                            },
                            get hasResults() {
                                if (this.search === '') return true;
                                let s = this.search.toLowerCase();
                                return this.meetingsList.some(m => m.name.toLowerCase().includes(s));
                            },
                            selectMeeting(id, name, endTime) {
                                this.search = name;
                                this.selectedId = String(id);
                                this.open = false;
                                if (this.updateMeetingFn) {
                                    this.updateMeetingFn(String(id), '');
                                }
                                if (endTime && this.setMeetingEndTimeFn) {
                                    this.setMeetingEndTimeFn(endTime);
                                }
                            },
                            syncSelection() {
                                let s = this.search.trim().toLowerCase();
                                if (s === '') {
                                    if (this.updateMeetingFn) {
                                        this.updateMeetingFn('', '');
                                    }
                                    this.selectedId = '';
                                    return;
                                }
                                let exact = this.meetingsList.find(m => m.name.toLowerCase() === s);
                                if (exact) {
                                    if (this.updateMeetingFn) {
                                        this.updateMeetingFn(String(exact.id), '');
                                    }
                                    this.selectedId = String(exact.id);
                                } else {
                                    if (this.updateMeetingFn) {
                                        this.updateMeetingFn('NEW', this.search.trim());
                                    }
                                    this.selectedId = 'NEW';
                                }
                            }
                        }" class="relative z-40" @click.outside="open = false; syncSelection()">
                            <script type="application/json" x-ref="meetingsData">
                                {!! $this->classMeetings->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->toJson() !!}
                            </script>
                            <div class="mb-2 flex items-center justify-between">
                                <label class="block text-sm font-bold text-slate-700">Chọn buổi điểm danh</label>
                                <span x-show="selectedId && selectedId !== 'NEW'" x-cloak class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-600 ring-1 ring-inset ring-emerald-500/20 transition-all duration-200">
                                    <x-user.icon name="check" :size="10" />
                                    Đã chọn
                                </span>
                                <span x-show="selectedId === 'NEW'" x-cloak class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-600 ring-1 ring-inset ring-amber-500/20 transition-all duration-200">
                                    <x-user.icon name="plus" :size="10" />
                                    Tạo mới
                                </span>
                            </div>

                            <div class="relative z-20 group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 transition-all duration-200" :class="open ? 'text-amber-500' : ''">
                                    <x-user.icon name="search" :size="18" x-show="open" x-cloak />
                                    <x-user.icon name="calendar" :size="18" x-show="!open" />
                                </div>

                                <input
                                    wire:key="meeting-search-input"
                                    type="text"
                                    x-ref="searchInput"
                                    x-model="search"
                                    value="{{ $quickMeetingId && $this->classMeetings->firstWhere('id', (int)$quickMeetingId) ? $this->classMeetings->firstWhere('id', (int)$quickMeetingId)->name : ($newMeetingName ?? '') }}"
                                    @input="open = true"
                                    @focus="open = true"
                                    @keydown.escape="open = false; syncSelection()"
                                    @keydown.enter.prevent="open = false; syncSelection()"
                                    placeholder="Nhập để tìm hoặc tạo tên buổi..."
                                    class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/80 py-3 pl-10 pr-10 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all duration-200 disabled:opacity-50 hover:border-slate-300 hover:bg-white focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-md focus:shadow-blue-500/5 placeholder:font-medium placeholder:text-slate-400"
                                    autocomplete="off"
                                    @if(!$quickClassId) disabled @endif
                                />
                                
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                                    <div class="flex h-5 w-5 items-center justify-center rounded-md bg-slate-100 text-slate-400 transition-all duration-200" :class="open ? 'rotate-180 bg-amber-50 text-amber-500' : ''">
                                        <x-user.icon name="chevron-down" :size="14" />
                                    </div>
                                </div>
                            </div>

                            <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="absolute left-0 right-0 top-full mt-2 z-10 overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xl shadow-slate-200/50">
                                <div class="max-h-56 overflow-y-auto overscroll-contain scrollbar-custom">

                                    @foreach($this->classMeetings as $cMeeting)
                                        <button wire:key="q-meeting-{{ $cMeeting->id }}" type="button" x-show="search === '' || '{{ mb_strtolower($cMeeting->name . ' ' . $cMeeting->date->format('d/m/Y'), 'UTF-8') }}'.includes(search.toLowerCase())" 
                                            @click="selectMeeting({{ $cMeeting->id }}, @js($cMeeting->name), '{{ $cMeeting->end_time ? \Carbon\Carbon::parse($cMeeting->end_time)->format('H:i') : '' }}')" 
                                            class="flex w-full items-center justify-between gap-3 border-b border-slate-50 px-4 py-3 text-left transition-all duration-150 last:border-0 hover:bg-blue-50/50 focus:bg-blue-50/50 outline-none"
                                            :class="'{{ $quickMeetingId }}' == '{{ $cMeeting->id }}' ? 'bg-blue-50/60' : ''">
                                            
                                            <div class="flex min-w-0 items-center gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <span class="block truncate text-sm font-bold transition-colors duration-150" :class="'{{ $quickMeetingId }}' == '{{ $cMeeting->id }}' ? 'text-blue-800' : 'text-slate-900'">{{ $cMeeting->name }}</span>
                                                </div>
                                            </div>

                                            @if($quickMeetingId == $cMeeting->id)
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 text-white shadow-sm shadow-blue-600/30">
                                                    <x-user.icon name="check" :size="14" />
                                                </div>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @error('quickMeetingId') <span class="mt-1.5 block text-xs font-bold text-red-500"><x-user.icon name="alert-circle" :size="12" class="inline pb-0.5" /> {{ $message }}</span> @enderror
                            @error('newMeetingName') <span class="mt-1.5 block text-xs font-bold text-red-500"><x-user.icon name="alert-circle" :size="12" class="inline pb-0.5" /> {{ $message }}</span> @enderror
                            

                        </div>
                        @endif

                        {{-- ═══ End Time ═══ --}}
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">Thời gian kết thúc (Có thể chỉnh sửa)</label>
                            <input type="time" x-model="meetingEndTime"
                                class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-bold text-slate-900 shadow-sm outline-none transition-all duration-200 hover:border-slate-300 hover:bg-white focus:bg-white focus:ring-4 focus:shadow-md"
                                :class="quickStartType === 'qr' ? 'focus:border-blue-500 focus:ring-blue-500/10 focus:shadow-blue-500/5' : 'focus:border-amber-500 focus:ring-amber-500/10 focus:shadow-amber-500/5'" />
                            <p class="mt-1 text-[13px] font-medium text-slate-500">Bạn có thể điều chỉnh thời gian chốt sổ điểm danh cho phiên này.</p>
                            @error('meetingEndTime') <span class="mt-1.5 block text-xs font-semibold text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- ═══ Session Name ═══ --}}
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">Tên phiên</label>
                            <input type="text" x-model="sessionName" placeholder="VD: Điểm danh đầu giờ" class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition-all duration-200 hover:border-slate-300 hover:bg-white focus:bg-white focus:ring-4 focus:shadow-md" :class="quickStartType === 'qr' ? 'focus:border-blue-500 focus:ring-blue-500/10 focus:shadow-blue-500/5' : 'focus:border-amber-500 focus:ring-amber-500/10 focus:shadow-amber-500/5'" />
                            @error('sessionName') <span class="mt-1.5 block text-xs font-semibold text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- ═══ QR Settings Panel ═══ --}}
                        <div x-cloak x-show="quickStartType === 'qr'" class="space-y-4 rounded-2xl border-2 border-blue-100/80 bg-gradient-to-br from-blue-50/50 to-indigo-50/30 p-5">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Thời gian mở mã (phút)</label>
                                <input type="number" x-model.number="durationMinutes" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-md focus:shadow-blue-500/5" />
                                @error('durationMinutes') <span class="mt-1.5 block text-xs font-semibold text-red-500">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-700">Làm mới mã (giây)</label>
                                    <select x-model.number="qrRefreshRate" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm outline-none transition-colors focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                        <option value="5">5 giây</option>
                                        <option value="10">10 giây</option>
                                        <option value="15">15 giây</option>
                                        <option value="30">30 giây</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-700">Bắt buộc GPS</label>
                                    <label class="flex cursor-pointer items-center gap-3">
                                        <input type="checkbox" x-model="gpsEnabled" @change="getLocation()" class="peer sr-only" />
                                        <div class="relative h-6 w-11 rounded-full bg-slate-200 after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:duration-200 after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300/50"></div>
                                        <span class="text-sm font-medium text-slate-600" x-text="gpsEnabled ? (isRequestingGps ? 'Đang định vị...' : 'Bật') : 'Tắt'"></span>
                                    </label>
                                </div>
                            </div>
                            
                            <div x-show="gpsEnabled" x-collapse>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Bán kính cho phép (mét)</label>
                                <input type="number" x-model.number="gpsRadius" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-md focus:shadow-blue-500/5" />
                                @error('gpsRadius') <span class="mt-1.5 block text-xs font-semibold text-red-500">{{ $message }}</span> @enderror
                                @error('gpsLatitude') <span class="mt-1.5 block text-xs font-semibold text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>
                </div>
                
                {{-- Fixed Footer --}}
                <div class="px-7 py-5 bg-slate-50/50 rounded-b-3xl border-t border-slate-100 shrink-0 z-10">
                    <div class="flex gap-3">
                        <button type="button" @click="showQuickStart = false" class="flex-1 rounded-xl bg-slate-200/50 py-3 text-sm font-bold text-slate-600 transition-all duration-200 hover:bg-slate-200 hover:text-slate-700 active:scale-95">Hủy</button>
                        <button type="button" @click="submitQuick()" wire:loading.attr="disabled" wire:target="startQuick" class="flex-1 rounded-xl py-3 text-sm font-bold text-white shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-md disabled:active:scale-100" :class="quickStartType === 'manual' ? 'bg-gradient-to-r from-amber-500 to-orange-500 shadow-amber-500/25 hover:from-amber-600 hover:to-orange-600 hover:shadow-lg hover:shadow-amber-500/30' : 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-blue-600/25 hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg hover:shadow-blue-600/30'" x-text="quickStartType === 'manual' ? 'Bắt đầu điểm danh' : 'Tạo mã & Trình chiếu'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    </div>
