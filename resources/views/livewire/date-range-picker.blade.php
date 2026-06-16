<div class="relative" x-data="{
    open: false,
    startDate: '{{ $startDate }}',
    endDate: '{{ $endDate }}',
    
    currentMonth: {{ (int) explode('/', $startDate)[1] - 1 }},
    currentYear: {{ (int) explode('/', $startDate)[2] }},
    
    selecting: 'start',
    tempStart: '{{ explode("/", $startDate)[2] }}-{{ explode("/", $startDate)[1] }}-{{ explode("/", $startDate)[0] }}',
    tempEnd: '{{ explode("/", $endDate)[2] }}-{{ explode("/", $endDate)[1] }}-{{ explode("/", $endDate)[0] }}',
    hoveredDate: null,
    
    monthNames: ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'],
    dayNames: ['CN','T2','T3','T4','T5','T6','T7'],

    displayText: '{{ $startDate }} - {{ $endDate }}',

    toKey(y, m, d) {
        return y + '-' + String(m + 1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
    },

    keyToDisplay(k) {
        if (!k) return '';
        let p = k.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    },

    todayKey() {
        let n = new Date();
        return this.toKey(n.getFullYear(), n.getMonth(), n.getDate());
    },

    addDays(key, days) {
        let p = key.split('-');
        let d = new Date(parseInt(p[0]), parseInt(p[1]) - 1, parseInt(p[2]));
        d.setDate(d.getDate() + days);
        return this.toKey(d.getFullYear(), d.getMonth(), d.getDate());
    },

    prevMonth() {
        this.currentMonth--;
        if (this.currentMonth < 0) { this.currentMonth = 11; this.currentYear--; }
    },

    nextMonth() {
        this.currentMonth++;
        if (this.currentMonth > 11) { this.currentMonth = 0; this.currentYear++; }
    },

    getDays(month, year) {
        let days = [];
        let firstDow = new Date(year, month, 1).getDay();
        let total = new Date(year, month + 1, 0).getDate();
        let prevTotal = new Date(year, month, 0).getDate();
        for (let i = firstDow - 1; i >= 0; i--) days.push({ d: prevTotal - i, ok: false, k: '' });
        for (let i = 1; i <= total; i++) days.push({ d: i, ok: true, k: this.toKey(year, month, i) });
        while (days.length < 42) { days.push({ d: days.length - firstDow - total + 1, ok: false, k: '' }); }
        return days;
    },

    pick(k) {
        if (!k) return;
        if (k === this.tempStart) {
            this.tempStart = this.tempEnd;
            this.tempEnd = null;
            this.selecting = this.tempStart ? 'end' : 'start';
            return;
        }
        if (k === this.tempEnd) {
            this.tempEnd = null;
            this.selecting = 'end';
            return;
        }
        if (this.selecting === 'start') {
            this.tempStart = k;
            this.tempEnd = null;
            this.selecting = 'end';
        } else {
            if (k < this.tempStart) {
                this.tempEnd = this.tempStart;
                this.tempStart = k;
            } else {
                this.tempEnd = k;
            }
            this.selecting = 'start';
        }
    },

    // Chọn nhanh preset
    setPreset(startKey, endKey) {
        this.tempStart = startKey;
        this.tempEnd = endKey;
        this.selecting = 'start';
        // Nhảy lịch về tháng của ngày bắt đầu
        let p = startKey.split('-');
        this.currentMonth = parseInt(p[1]) - 1;
        this.currentYear = parseInt(p[0]);
    },

    preset7() {
        let today = this.todayKey();
        this.setPreset(this.addDays(today, -6), today);
    },
    preset30() {
        let today = this.todayKey();
        this.setPreset(this.addDays(today, -29), today);
    },
    presetThisMonth() {
        let n = new Date();
        let start = this.toKey(n.getFullYear(), n.getMonth(), 1);
        let end = this.toKey(n.getFullYear(), n.getMonth(), new Date(n.getFullYear(), n.getMonth() + 1, 0).getDate());
        this.setPreset(start, end);
    },
    presetLastMonth() {
        let n = new Date();
        let pm = n.getMonth() - 1;
        let py = n.getFullYear();
        if (pm < 0) { pm = 11; py--; }
        let start = this.toKey(py, pm, 1);
        let end = this.toKey(py, pm, new Date(py, pm + 1, 0).getDate());
        this.setPreset(start, end);
    },

    isSel(k) { return k && (k === this.tempStart || k === this.tempEnd); },

    inRange(k) {
        if (!k || !this.tempStart) return false;
        let e = this.tempEnd || this.hoveredDate;
        if (!e) return false;
        let s = this.tempStart;
        if (e < s) { let t = s; s = e; e = t; }
        return k > s && k < e;
    },

    isToday(k) { return k === this.todayKey(); },

    apply() {
        if (this.tempStart && this.tempEnd) {
            this.startDate = this.keyToDisplay(this.tempStart);
            this.endDate = this.keyToDisplay(this.tempEnd);
            this.displayText = this.startDate + ' - ' + this.endDate;
            $wire.set('startDate', this.startDate);
            $wire.set('endDate', this.endDate);
            this.open = false;
        }
    },

    cancel() {
        let ps = this.startDate.split('/');
        let pe = this.endDate.split('/');
        this.tempStart = ps[2]+'-'+ps[1]+'-'+ps[0];
        this.tempEnd = pe[2]+'-'+pe[1]+'-'+pe[0];
        this.selecting = 'start';
        this.open = false;
    }
}" @click.outside="if(open) cancel()" @keydown.escape.window="if(open) cancel()">

    {{-- Button --}}
    <button @click="open = !open" type="button"
        class="group inline-flex items-center gap-2.5 px-4 py-2.5 bg-white border border-slate-200 rounded-lg shadow-sm text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-blue-300 hover:text-blue-600 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/20">
        <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
        </svg>
        <span x-text="displayText">{{ $startDate }} - {{ $endDate }}</span>
        <svg class="w-4 h-4 text-slate-400 ml-1 transition-transform duration-200 group-hover:text-blue-500" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 z-50 bg-white rounded-xl shadow-2xl border border-slate-200/80 overflow-hidden" style="min-width: 380px;">

        <div class="flex">
            {{-- Sidebar chọn nhanh --}}
            <div class="w-[130px] bg-slate-50 border-r border-slate-100 py-3 px-2 flex flex-col gap-1">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1">Chọn nhanh</p>
                <button @click="preset7()" type="button"
                    class="text-left px-3 py-2 text-xs font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                    7 ngày qua
                </button>
                <button @click="preset30()" type="button"
                    class="text-left px-3 py-2 text-xs font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                    30 ngày qua
                </button>
                <button @click="presetThisMonth()" type="button"
                    class="text-left px-3 py-2 text-xs font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                    Tháng này
                </button>
                <button @click="presetLastMonth()" type="button"
                    class="text-left px-3 py-2 text-xs font-medium text-slate-600 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                    Tháng trước
                </button>
            </div>

            {{-- Calendar --}}
            <div class="flex-1 p-4">
                {{-- Header tháng --}}
                <div class="flex items-center justify-between mb-3">
                    <button @click="prevMonth()" type="button" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                    </button>
                    <span class="text-sm font-bold text-slate-800" x-text="monthNames[currentMonth] + ' ' + currentYear"></span>
                    <button @click="nextMonth()" type="button" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    </button>
                </div>

                {{-- Tên ngày --}}
                <div class="grid grid-cols-7 mb-1">
                    <template x-for="dn in dayNames" :key="dn">
                        <div class="text-center text-[11px] font-bold text-slate-400 py-1.5" x-text="dn"></div>
                    </template>
                </div>

                {{-- Các ngày --}}
                <div class="grid grid-cols-7 gap-y-0.5">
                    <template x-for="(day, idx) in getDays(currentMonth, currentYear)" :key="'d'+idx">
                        <button type="button"
                            @click="day.ok && pick(day.k)"
                            @mouseenter="selecting === 'end' && day.ok && (hoveredDate = day.k)"
                            :disabled="!day.ok"
                            class="w-9 h-9 text-[13px] rounded-lg flex items-center justify-center mx-auto transition-all duration-75"
                            :class="{
                                'text-slate-300 cursor-default': !day.ok,
                                'text-slate-700 hover:bg-blue-50 hover:text-blue-600 cursor-pointer': day.ok && !isSel(day.k),
                                'bg-blue-600 text-white font-bold shadow-sm': isSel(day.k),
                                'bg-blue-50/80 text-blue-700': inRange(day.k) && day.ok && !isSel(day.k),
                                'ring-2 ring-blue-400 ring-inset': isToday(day.k) && !isSel(day.k)
                            }"
                            x-text="day.d">
                        </button>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100 gap-4">
                    <div class="text-xs text-slate-500 font-medium whitespace-nowrap min-w-0">
                        <template x-if="!tempStart"><span class="text-slate-400 italic">Chọn ngày bắt đầu...</span></template>
                        <template x-if="tempStart && !tempEnd"><span class="text-blue-600 font-semibold">Chọn ngày kết thúc...</span></template>
                        <template x-if="tempStart && tempEnd">
                            <span>
                                <span class="font-bold text-slate-800" x-text="keyToDisplay(tempStart)"></span>
                                <span class="mx-1 text-slate-400">→</span>
                                <span class="font-bold text-slate-800" x-text="keyToDisplay(tempEnd)"></span>
                            </span>
                        </template>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button @click="cancel()" type="button" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-colors whitespace-nowrap">Hủy</button>
                        <button @click="apply()" type="button" :disabled="!tempStart || !tempEnd"
                            class="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all disabled:opacity-40 disabled:cursor-not-allowed whitespace-nowrap">
                            Áp dụng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
