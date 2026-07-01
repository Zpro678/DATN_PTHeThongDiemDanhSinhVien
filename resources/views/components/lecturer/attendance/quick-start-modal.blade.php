@props(['hideClassSelector' => false])
@php
    $quickClassId = $this->quickClassId ?? null;
    $quickMeetingId = $this->quickMeetingId ?? null;
@endphp

    <template x-teleport="body">
        <div x-cloak x-show="$wire.showQuickStart" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            <div x-show="$wire.showQuickStart" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-slate-950/50" @click="$wire.set('showQuickStart', false)" aria-label="Đóng"></div>
            <div x-show="$wire.showQuickStart" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95 translate-y-2" class="relative w-full max-w-[600px] max-h-[90vh] grid rounded-3xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-900/10" style="grid-template-rows: auto minmax(0, 1fr) auto;">
                
                <button type="button" @click="$wire.set('showQuickStart', false)" class="absolute right-4 top-4 z-20 flex h-8 w-8 items-center justify-center text-black">
                    <x-user.icon name="x" :size="18" />
                </button>
                
                {{-- Fixed Header --}}
                <div class="px-7 pt-7 pb-4 bg-white rounded-t-3xl border-b border-slate-100 shrink-0 z-10">
                    {{-- Header with visual flair --}}
                    <div class="mb-5 flex items-center gap-4">
                        <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl shadow-sm transition-all duration-300" :class="$wire.quickStartType === 'manual' ? 'bg-gradient-to-br from-amber-100 to-orange-100 text-amber-600 shadow-amber-200/50' : 'bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-600 shadow-blue-200/50'">
                            <x-user.icon name="check-square" :size="26" x-show="$wire.quickStartType === 'manual'" />
                            <x-user.icon name="qr-code" :size="26" x-show="$wire.quickStartType === 'qr'" />
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900" x-text="$wire.quickStartType === 'manual' ? 'Điểm danh thủ công' : 'Điểm danh QR'"></h3>
                            <p class="text-sm text-slate-400 mt-0.5">@if($hideClassSelector) Chọn buổi để bắt đầu @else Chọn lớp và buổi để bắt đầu @endif</p>
                        </div>
                    </div>

                    {{-- Step indicator --}}
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-black transition-all duration-300" :class="$wire.quickClassId ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-200' : ($wire.quickStartType === 'manual' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700')">
                                <template x-if="$wire.quickClassId"><x-user.icon name="check" :size="12" /></template>
                                <template x-if="!$wire.quickClassId"><span>1</span></template>
                            </div>
                            <span class="text-xs font-bold transition-colors duration-200" :class="$wire.quickClassId ? 'text-emerald-600' : 'text-slate-500'">Lớp</span>
                        </div>
                        <div class="h-px flex-1 transition-colors duration-300" :class="$wire.quickClassId ? 'bg-emerald-200' : 'bg-slate-200'"></div>
                        <div class="flex items-center gap-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-black transition-all duration-300" :class="$wire.quickMeetingId ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-200' : ($wire.quickClassId ? ($wire.quickStartType === 'manual' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') : 'bg-slate-100 text-slate-400')">
                                <template x-if="$wire.quickMeetingId"><x-user.icon name="check" :size="12" /></template>
                                <template x-if="!$wire.quickMeetingId"><span>2</span></template>
                            </div>
                            <span class="text-xs font-bold transition-colors duration-200" :class="$wire.quickMeetingId ? 'text-emerald-600' : ($wire.quickClassId ? 'text-slate-500' : 'text-slate-400')">Buổi</span>
                        </div>
                        <div class="h-px flex-1 transition-colors duration-300" :class="$wire.quickMeetingId ? 'bg-emerald-200' : 'bg-slate-200'"></div>
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
                            search: '{{ $quickClassId && $this->activeClasses->firstWhere('id', (int)$quickClassId) ? addslashes($this->activeClasses->firstWhere('id', (int)$quickClassId)->name) : '' }}',
                            selectedId: '{{ $quickClassId }}',
                            get hasResults() {
                                if (this.search === '') return true;
                                let s = this.search.toLowerCase();
                                return {{ $this->activeClasses->map(fn($c) => $c->code . ' ' . $c->name)->toJson() }}.some(text => text.toLowerCase().includes(s));
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
                                    value="{{ $quickClassId && $this->activeClasses->firstWhere('id', (int)$quickClassId) ? $this->activeClasses->firstWhere('id', (int)$quickClassId)->name : '' }}"
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
                                        <button type="button" x-show="search === '' || '{{ mb_strtolower($cClass->code . ' ' . $cClass->name, 'UTF-8') }}'.includes(search.toLowerCase())" 
                                            @click="$wire.set('quickClassId', '{{ $cClass->id }}'); search = '{{ addslashes($cClass->name) }}'; selectedId = '{{ $cClass->id }}'; open = false;" 
                                            class="flex w-full items-center justify-between gap-3 border-b border-slate-50 px-4 py-3 text-left transition-all duration-150 last:border-0 hover:bg-blue-50/50 focus:bg-blue-50/50 outline-none"
                                            :class="'{{ $quickClassId }}' == '{{ $cClass->id }}' ? 'bg-blue-50/60' : ''">
                                            
                                            <div class="flex min-w-0 items-center gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <span class="block truncate text-sm font-bold transition-colors duration-150" :class="'{{ $quickClassId }}' == '{{ $cClass->id }}' ? 'text-blue-800' : 'text-slate-900'">{{ $cClass->name }}</span>
                                                    <span class="mt-0.5 flex items-center gap-1 truncate text-xs font-medium transition-colors duration-150" :class="'{{ $quickClassId }}' == '{{ $cClass->id }}' ? 'text-blue-500' : 'text-slate-400'">
                                                        <x-user.icon name="hash" :size="10" /> {{ $cClass->code }}
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

                        {{-- ═══ Meeting Selector ═══ --}}
                        <div x-data="{
                            open: false,
                            search: '{{ $quickMeetingId && $this->classMeetings->firstWhere('id', (int)$quickMeetingId) ? addslashes($this->classMeetings->firstWhere('id', (int)$quickMeetingId)->name) : ($newMeetingName ?? '') }}',
                            selectedId: '{{ $quickMeetingId }}',
                            get meetingsList() {
                                return JSON.parse(this.$refs.meetingsData.textContent || '[]');
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
                                $wire.set('quickMeetingId', id);
                                $wire.set('newMeetingName', '');
                                if (endTime) {
                                    $wire.set('meetingEndTime', endTime);
                                }
                            },
                            syncSelection() {
                                let s = this.search.trim().toLowerCase();
                                if (s === '') {
                                    $wire.set('quickMeetingId', '');
                                    $wire.set('newMeetingName', '');
                                    this.selectedId = '';
                                    return;
                                }
                                let exact = this.meetingsList.find(m => m.name.toLowerCase() === s);
                                if (exact) {
                                    $wire.set('quickMeetingId', exact.id);
                                    $wire.set('newMeetingName', '');
                                    this.selectedId = String(exact.id);
                                } else {
                                    $wire.set('quickMeetingId', 'NEW');
                                    $wire.set('newMeetingName', this.search.trim());
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
                                        <button type="button" x-show="search === '' || '{{ mb_strtolower($cMeeting->name . ' ' . $cMeeting->date->format('d/m/Y'), 'UTF-8') }}'.includes(search.toLowerCase())" 
                                            @click="selectMeeting({{ $cMeeting->id }}, '{{ addslashes($cMeeting->name) }}', '{{ $cMeeting->end_time ? \Carbon\Carbon::parse($cMeeting->end_time)->format('H:i') : '' }}')" 
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
                            
                            @if($quickClassId && $this->classMeetings->isEmpty())
                                <div class="mt-3 overflow-hidden rounded-xl border-2 border-dashed border-amber-300/60 bg-gradient-to-r from-amber-50 to-orange-50/30">
                                    <button type="button" wire:click="createTodayMeeting" class="group flex w-full flex-col items-center justify-center px-4 py-5 text-center transition-all duration-200 hover:from-amber-100/50 hover:to-orange-50/50">
                                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600 transition-transform duration-200 group-hover:scale-110">
                                            <x-user.icon name="plus" :size="20" />
                                        </div>
                                        <span class="block text-xs font-medium text-amber-600">Chưa có buổi học nào trong hôm nay</span>
                                        <span class="mt-0.5 block text-sm font-extrabold text-amber-700 transition-colors duration-200 group-hover:text-amber-800">Tạo nhanh buổi học hôm nay</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- ═══ End Time ═══ --}}
                        <div x-data="{
                            time: @entangle('meetingEndTime'),
                            _hour: '00',
                            _minute: '00',
                            init() {
                                this.syncFromTime();
                                $watch('time', () => this.syncFromTime());
                            },
                            syncFromTime() {
                                if (this.time && this.time.includes(':')) {
                                    this._hour = this.time.split(':')[0];
                                    this._minute = this.time.split(':')[1];
                                }
                            },
                            updateTime() {
                                let h = parseInt(this._hour);
                                let m = parseInt(this._minute);
                                if (isNaN(h)) h = 0;
                                if (isNaN(m)) m = 0;
                                if (h > 23) h = 23;
                                if (h < 0) h = 0;
                                if (m > 59) m = 59;
                                if (m < 0) m = 0;
                                this.time = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
                                this.syncFromTime();
                            }
                        }">
                            <label class="mb-2 block text-sm font-bold text-slate-700">Thời gian kết thúc</label>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1 group">
                                    <input type="number" min="0" max="23" x-model="_hour" @blur="updateTime()" @keydown.enter.prevent="updateTime()" placeholder="Giờ" class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/80 px-4 py-3 text-center text-sm font-bold text-slate-900 shadow-sm outline-none transition-all duration-200 hover:border-slate-300 hover:bg-white focus:bg-white focus:ring-4 focus:shadow-md disabled:opacity-60 disabled:bg-slate-100" :class="$wire.quickStartType === 'qr' ? 'focus:border-blue-500 focus:ring-blue-500/10 focus:shadow-blue-500/5' : 'focus:border-amber-500 focus:ring-amber-500/10 focus:shadow-amber-500/5'" :disabled="$wire.quickMeetingId && $wire.quickMeetingId !== 'NEW'" />
                                </div>
                                <span class="text-xl font-extrabold text-slate-400 mb-1">:</span>
                                <div class="relative flex-1 group">
                                    <input type="number" min="0" max="59" x-model="_minute" @blur="updateTime()" @keydown.enter.prevent="updateTime()" placeholder="Phút" class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/80 px-4 py-3 text-center text-sm font-bold text-slate-900 shadow-sm outline-none transition-all duration-200 hover:border-slate-300 hover:bg-white focus:bg-white focus:ring-4 focus:shadow-md disabled:opacity-60 disabled:bg-slate-100" :class="$wire.quickStartType === 'qr' ? 'focus:border-blue-500 focus:ring-blue-500/10 focus:shadow-blue-500/5' : 'focus:border-amber-500 focus:ring-amber-500/10 focus:shadow-amber-500/5'" :disabled="$wire.quickMeetingId && $wire.quickMeetingId !== 'NEW'" />
                                </div>
                            </div>
                            @error('meetingEndTime') <span class="mt-1.5 block text-xs font-semibold text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- ═══ Session Name ═══ --}}
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">Tên phiên</label>
                            <input type="text" wire:model="sessionName" placeholder="VD: Điểm danh đầu giờ" class="w-full rounded-xl border-2 border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition-all duration-200 hover:border-slate-300 hover:bg-white focus:bg-white focus:ring-4 focus:shadow-md" :class="$wire.quickStartType === 'qr' ? 'focus:border-blue-500 focus:ring-blue-500/10 focus:shadow-blue-500/5' : 'focus:border-amber-500 focus:ring-amber-500/10 focus:shadow-amber-500/5'" />
                            @error('sessionName') <span class="mt-1.5 block text-xs font-semibold text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- ═══ QR Settings Panel ═══ --}}
                        <div x-cloak x-show="$wire.quickStartType === 'qr'" class="space-y-4 rounded-2xl border-2 border-blue-100/80 bg-gradient-to-br from-blue-50/50 to-indigo-50/30 p-5">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Thời gian mở mã (phút)</label>
                                <input type="number" wire:model="durationMinutes" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-md focus:shadow-blue-500/5" />
                                @error('durationMinutes') <span class="mt-1.5 block text-xs font-semibold text-red-500">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-700">Làm mới mã (giây)</label>
                                    <select wire:model="qrRefreshRate" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                        <option value="5">5 giây</option>
                                        <option value="10">10 giây</option>
                                        <option value="15">15 giây</option>
                                        <option value="30">30 giây</option>
                                    </select>
                                </div>
                                
                                <div x-data="{
                                    isRequestingGps: false,
                                    getLocation() {
                                        if (!$wire.gpsEnabled) {
                                            $wire.set('gpsLatitude', null);
                                            $wire.set('gpsLongitude', null);
                                            return;
                                        }
                                        this.isRequestingGps = true;
                                        if (navigator.geolocation) {
                                            navigator.geolocation.getCurrentPosition(
                                                (position) => {
                                                    $wire.set('gpsLatitude', position.coords.latitude);
                                                    $wire.set('gpsLongitude', position.coords.longitude);
                                                    this.isRequestingGps = false;
                                                },
                                                (error) => {
                                                    alert('Không thể lấy vị trí. Vui lòng cấp quyền vị trí cho trình duyệt.');
                                                    $wire.set('gpsEnabled', false);
                                                    this.isRequestingGps = false;
                                                }
                                            );
                                        } else {
                                            alert('Trình duyệt không hỗ trợ GPS.');
                                            $wire.set('gpsEnabled', false);
                                            this.isRequestingGps = false;
                                        }
                                    }
                                }">
                                    <label class="mb-2 block text-sm font-bold text-slate-700">Bắt buộc GPS</label>
                                    <label class="flex cursor-pointer items-center gap-3">
                                        <input type="checkbox" wire:model="gpsEnabled" @change="getLocation()" class="peer sr-only" />
                                        <div class="relative h-6 w-11 rounded-full bg-slate-200 after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:duration-200 after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300/50"></div>
                                        <span class="text-sm font-medium text-slate-600" x-text="$wire.gpsEnabled ? (isRequestingGps ? 'Đang định vị...' : 'Bật') : 'Tắt'"></span>
                                    </label>
                                </div>
                            </div>
                            
                            <div x-show="$wire.gpsEnabled" x-collapse>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Bán kính cho phép (mét)</label>
                                <input type="number" wire:model="gpsRadius" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-md focus:shadow-blue-500/5" />
                                @error('gpsRadius') <span class="mt-1.5 block text-xs font-semibold text-red-500">{{ $message }}</span> @enderror
                                @error('gpsLatitude') <span class="mt-1.5 block text-xs font-semibold text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>
                </div>
                
                {{-- Fixed Footer --}}
                <div class="px-7 py-5 bg-slate-50/50 rounded-b-3xl border-t border-slate-100 shrink-0 z-10">
                    <div class="flex gap-3">
                        <button type="button" @click="$wire.set('showQuickStart', false)" class="flex-1 rounded-xl bg-slate-200/50 py-3 text-sm font-bold text-slate-600 transition-all duration-200 hover:bg-slate-200 hover:text-slate-700 active:scale-95">Hủy</button>
                        <button type="button" wire:click="startQuick" class="flex-1 rounded-xl py-3 text-sm font-bold text-white shadow-md transition-all duration-200 active:scale-95" :class="$wire.quickStartType === 'manual' ? 'bg-gradient-to-r from-amber-500 to-orange-500 shadow-amber-500/25 hover:from-amber-600 hover:to-orange-600 hover:shadow-lg hover:shadow-amber-500/30' : 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-blue-600/25 hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg hover:shadow-blue-600/30'" x-text="$wire.quickStartType === 'manual' ? 'Bắt đầu điểm danh' : 'Tạo mã & Trình chiếu'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>