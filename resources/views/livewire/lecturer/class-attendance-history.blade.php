<div x-data="{ 
    viewMode: 'matrix', // 'matrix', 'session', 'timeline'
    selectedGroupKey: null,
    selectedStudentId: null,
    sessionFilter: 'merged',
    matrix: @js($matrix),
    members: @js($membersData),
    groupedSessionsInfo: @js($groupedSessionsInfo),
    
    get drawerStudents() {
        if (!this.selectedGroupKey || !this.matrix || !this.members) return [];
        return Object.values(this.members).map(m => {
            const cell = this.matrix[m.id]?.[this.selectedGroupKey];
            return {
                ...m,
                cell: cell || { status: 'pending', text: 'Chưa điểm danh', details: [] }
            };
        });
    },
    
    get modalData() {
        if (!this.selectedGroupKey || !this.selectedStudentId) return null;
        const student = this.members[this.selectedStudentId];
        const sessionInfo = this.groupedSessionsInfo[this.selectedGroupKey];
        const cell = this.matrix[this.selectedStudentId]?.[this.selectedGroupKey] || { status: 'pending', text: 'Chưa điểm danh', details: [] };
        return { student, sessionInfo, cell };
    },

    getDetail(student, iteration) {
        return student.cell.details.find(d => d.iteration === iteration);
    },

    scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}" class="mx-auto max-w-[1300px] space-y-6 p-4 pb-24 sm:p-8">

    <!-- HEADER CHUNG -->
    <section class="flex flex-col justify-between gap-4 md:flex-row md:items-center px-1">
        <h1 class="flex items-center gap-3 text-2xl font-extrabold tracking-tight text-slate-900">
            <x-user.icon name="calendar-check" class="text-blue-600" :size="28" />
            {{ $courseClass->code }} - {{ $courseClass->name }} ({{ count($sessions) }} phiên)
        </h1>
        <div class="flex items-center gap-3">
            <template x-if="viewMode !== 'matrix'">
                <button @click="viewMode = 'matrix'; scrollToTop()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-50 px-5 py-2.5 text-[15px] font-bold text-blue-700 transition-colors hover:bg-blue-100 shadow-sm">
                    <x-user.icon name="arrow-left" :size="18" />
                    Trở về Bảng ma trận
                </button>
            </template>
            <template x-if="viewMode === 'matrix'">
                <a href="{{ route('lecturer.classes.show', $courseClass->id) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 px-5 py-2.5 text-[15px] font-medium text-slate-700 transition-colors hover:bg-slate-200">
                    <x-user.icon name="arrow-left" :size="18" />
                    Trở về
                </a>
            </template>
        </div>
    </section>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <!-- MÀN HÌNH 1: BẢNG MA TRẬN -->
    <section x-show="viewMode === 'matrix'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="mt-0">
        <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Bảng tổng sắp điểm danh</h2>
        
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="sticky left-0 z-20 min-w-[200px] border-b border-r border-slate-200 bg-slate-50 px-6 py-4 shadow-[4px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                Sinh viên
                            </th>
                            @php $dayIndex = 1; @endphp
                            @foreach($groupedSessionsInfo as $groupKey => $info)
                                <th scope="col" 
                                    @click="selectedGroupKey = '{{ $groupKey }}'; viewMode = 'session'; scrollToTop()"
                                    class="min-w-[130px] whitespace-nowrap border-b border-r border-slate-200 px-4 py-3 text-center last:border-r-0 cursor-pointer hover:bg-blue-50 transition-colors group">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="text-[11px] font-black tracking-widest text-blue-600 group-hover:text-blue-700">Buổi {{ $dayIndex++ }}</span>
                                        <span class="mt-0.5 text-[13px] text-slate-700 font-bold group-hover:text-blue-900">{{ $info['date'] }}</span>
                                        @if($info['timeStr'])
                                            <span class="mt-0.5 text-[11px] text-slate-500 group-hover:text-blue-700">{{ $info['timeStr'] }}</span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($members as $member)
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="sticky left-0 z-10 border-r border-slate-200 bg-white px-6 py-3 shadow-[4px_0_12px_-4px_rgba(0,0,0,0.05)] group-hover:bg-slate-50">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-600">
                                            {{ mb_substr($member->full_name, 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900">{{ $member->full_name }}</span>
                                            <span class="text-xs text-slate-500">{{ $member->student_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                
                                @foreach($groupedSessionsInfo as $groupKey => $info)
                                    @php
                                        $cell = $matrix[$member->id][$groupKey];
                                        $status = $cell['status'];
                                    @endphp
                                    <td class="border-r border-slate-200 px-4 py-3 text-center align-middle last:border-r-0">
                                        <div class="group/tooltip relative inline-flex justify-center" title="Nhấn để xem chi tiết"
                                             @click="selectedStudentId = {{ $member->id }}; selectedGroupKey = '{{ $groupKey }}'; viewMode = 'timeline'; scrollToTop()">
                                            @if($status === 'present')
                                                <div class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-emerald-100 text-emerald-600 ring-1 ring-inset ring-emerald-200/50 transition-transform hover:scale-110 shadow-sm">
                                                    <x-user.icon name="check" :size="18" stroke-width="3" />
                                                </div>
                                            @elseif($status === 'absent')
                                                <div class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-rose-100 text-rose-600 ring-1 ring-inset ring-rose-200/50 transition-transform hover:scale-110 shadow-sm">
                                                    <x-user.icon name="x" :size="18" stroke-width="3" />
                                                </div>
                                            @elseif($status === 'late')
                                                <div class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-amber-100 text-amber-600 ring-1 ring-inset ring-amber-200/50 transition-transform hover:scale-110 shadow-sm">
                                                    <x-user.icon name="clock" :size="18" stroke-width="3" />
                                                </div>
                                            @else
                                                <div class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-slate-100 text-slate-400 ring-1 ring-inset ring-slate-200 transition-transform hover:scale-110 shadow-sm">
                                                    <x-user.icon name="minus" :size="18" stroke-width="3" />
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($groupedSessionsInfo) + 1 }}" class="px-6 py-12 text-center text-slate-500">
                                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <x-user.icon name="users" :size="32" />
                                    </div>
                                    Lớp học chưa có sinh viên hoặc chưa có dữ liệu điểm danh.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- MÀN HÌNH 2: CHI TIẾT BUỔI HỌC (Thay thế Drawer) -->
    <section x-cloak x-show="viewMode === 'session'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="mt-0">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <!-- Header Màn hình Chi tiết -->
            <div class="px-6 py-6 sm:px-8 bg-slate-50 border-b border-slate-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">
                            Chi tiết điểm danh ngày <span class="text-blue-600" x-text="groupedSessionsInfo[selectedGroupKey]?.date"></span>
                            <span class="text-slate-500 font-medium text-base ml-1" 
                                  x-show="groupedSessionsInfo[selectedGroupKey]"
                                  x-text="groupedSessionsInfo[selectedGroupKey]?.startLesson && groupedSessionsInfo[selectedGroupKey]?.endLesson ? '(Tiết ' + groupedSessionsInfo[selectedGroupKey].startLesson + ' - Tiết ' + groupedSessionsInfo[selectedGroupKey].endLesson + ')' : '(' + groupedSessionsInfo[selectedGroupKey]?.lessonCount + ' tiết)'"></span>
                        </h2>
                        <p class="mt-1.5 text-sm font-medium text-slate-500" x-show="groupedSessionsInfo[selectedGroupKey]?.timeStr">
                            Khung giờ: <span x-text="groupedSessionsInfo[selectedGroupKey]?.timeStr"></span>
                        </p>
                    </div>
                    <div class="flex rounded-lg p-1 bg-slate-200/50 ring-1 ring-inset ring-slate-200 w-full md:w-auto">
                        <button @click="sessionFilter = 'merged'" :class="{'bg-white shadow-sm ring-1 ring-slate-200 text-slate-900': sessionFilter === 'merged', 'text-slate-500 hover:text-slate-700': sessionFilter !== 'merged'}" class="flex-1 md:flex-none rounded-md px-6 py-2.5 text-sm font-semibold transition-all">Kết quả</button>
                        <button @click="sessionFilter = 'detailed'" :class="{'bg-white shadow-sm ring-1 ring-slate-200 text-slate-900': sessionFilter === 'detailed', 'text-slate-500 hover:text-slate-700': sessionFilter !== 'detailed'}" class="flex-1 md:flex-none rounded-md px-6 py-2.5 text-sm font-semibold transition-all">Chi tiết các phiên</button>
                    </div>
                </div>
            </div>

            <!-- Body Màn hình Chi tiết -->
            <div class="p-6 sm:p-8 bg-slate-50/50">
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-sm font-bold uppercase text-slate-600 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16 text-center">STT</th>
                                <th scope="col" class="px-6 py-4">Học viên</th>
                                
                                <!-- Dynamic Columns for Iterations -->
                                <template x-if="sessionFilter === 'detailed' && groupedSessionsInfo[selectedGroupKey]">
                                    <template x-for="col in groupedSessionsInfo[selectedGroupKey].columns" :key="col.iteration">
                                        <th scope="col" class="px-4 py-4 text-center min-w-[120px] whitespace-nowrap">
                                            <span x-text="'Lần ' + col.iteration"></span>
                                            <span x-show="col.time" class="text-xs font-medium text-slate-400 ml-1" x-text="'(' + col.time + ')'"></span>
                                        </th>
                                    </template>
                                </template>

                                <th scope="col" class="px-6 py-4 text-center whitespace-nowrap" x-show="sessionFilter === 'merged'">Số tiết ghi nhận</th>
                                <th scope="col" class="px-6 py-4 text-center">Trạng thái chốt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <template x-for="(student, index) in drawerStudents" :key="student.id">
                                <tr class="transition-colors hover:bg-slate-50">
                                    <td class="px-6 py-3 text-center align-middle font-medium text-slate-500" x-text="index + 1"></td>
                                    <td class="px-6 py-3 align-middle">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900" x-text="student.full_name"></span>
                                            <span class="text-xs text-slate-500" x-text="student.student_code"></span>
                                        </div>
                                    </td>
                                    
                                    <!-- Các cột chi tiết từng phiên -->
                                    <template x-if="sessionFilter === 'detailed' && groupedSessionsInfo[selectedGroupKey]">
                                        <template x-for="col in groupedSessionsInfo[selectedGroupKey].columns" :key="col.iteration">
                                            <td class="px-4 py-3 align-middle text-center border-l border-slate-100">
                                                <template x-if="getDetail(student, col.iteration)">
                                                    <span class="font-bold text-[13px]" 
                                                        :class="{
                                                            'text-emerald-600': getDetail(student, col.iteration).status === 'present' || getDetail(student, col.iteration).status === 'excused',
                                                            'text-rose-600': getDetail(student, col.iteration).status === 'absent',
                                                            'text-amber-600': getDetail(student, col.iteration).status === 'late',
                                                            'text-slate-400': getDetail(student, col.iteration).status === 'pending'
                                                        }" x-text="getDetail(student, col.iteration).statusText"></span>
                                                </template>
                                                <template x-if="!getDetail(student, col.iteration)">
                                                    <span class="text-slate-300">-</span>
                                                </template>
                                            </td>
                                        </template>
                                    </template>

                                    <!-- Cột Số tiết ghi nhận -->
                                    <td class="px-6 py-3 text-center align-middle border-l border-slate-100" x-show="sessionFilter === 'merged'">
                                        <span class="inline-flex items-center justify-center min-w-[28px] h-7 rounded-lg bg-slate-100 px-2 text-sm font-black text-slate-700 shadow-[inset_0_1px_2px_rgba(0,0,0,0.05)]" x-text="student.cell.attendedLessons"></span>
                                    </td>

                                    <!-- Cột Trạng thái chốt -->
                                    <td class="px-6 py-3 text-center align-middle border-l border-slate-100">
                                        <span x-show="student.cell.status === 'present'" class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Có mặt</span>
                                        <span x-show="student.cell.status === 'absent'" class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-600/10">Vắng mặt</span>
                                        <span x-show="student.cell.status === 'late'" class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">Đi trễ</span>
                                        <span x-show="student.cell.status === 'pending'" class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-600 ring-1 ring-inset ring-slate-500/10">Chưa ĐD</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- MÀN HÌNH 3: DÒNG THỜI GIAN (Thay thế Modal) -->
    <section x-cloak x-show="viewMode === 'timeline'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="mt-0 max-w-4xl mx-auto">
        <template x-if="modalData">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-6 sm:px-10 bg-slate-50 border-b border-slate-200">
                    <h2 class="text-xl font-bold leading-6 text-slate-900">
                        Hồ sơ điểm danh chi tiết
                    </h2>
                </div>
                
                <div class="p-6 sm:p-10">
                    <!-- Thông tin sinh viên -->
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 ring-1 ring-inset ring-slate-200 shadow-sm mb-10">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xl font-black text-blue-600" x-text="modalData.student.full_name.charAt(0)"></div>
                        <div class="flex flex-col">
                            <span class="text-lg font-black text-slate-900" x-text="modalData.student.full_name"></span>
                            <span class="text-sm font-medium text-slate-500" x-text="'Mã SV: ' + modalData.student.student_code + ' — Ngày: ' + modalData.sessionInfo.date"></span>
                        </div>
                    </div>

                    <!-- Timeline lớn -->
                    <div class="flow-root px-4 max-w-2xl mx-auto">
                        <ul role="list" class="-mb-8">
                            <template x-for="(detail, index) in modalData.cell.details" :key="detail.iteration">
                                <li>
                                    <div class="relative pb-10">
                                        <span x-show="index !== modalData.cell.details.length - 1" class="absolute left-6 top-6 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-6 items-start">
                                            <div>
                                                <span class="h-12 w-12 rounded-full flex items-center justify-center ring-8 ring-white shadow-sm"
                                                    :class="{
                                                        'bg-emerald-100 text-emerald-600': detail.status === 'present' || detail.status === 'excused',
                                                        'bg-rose-100 text-rose-600': detail.status === 'absent',
                                                        'bg-amber-100 text-amber-600': detail.status === 'late',
                                                        'bg-slate-100 text-slate-400': detail.status === 'pending'
                                                    }">
                                                    <svg x-show="detail.status === 'present' || detail.status === 'excused'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                    <svg x-show="detail.status === 'absent'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    <svg x-show="detail.status === 'late'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                                    <svg x-show="detail.status === 'pending'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-2">
                                                <div>
                                                    <p class="text-[15px] text-slate-500">Điểm danh Lần <span x-text="detail.iteration"></span>: <span class="font-black text-lg text-slate-900 ml-1" x-text="detail.statusText"></span></p>
                                                    <p class="mt-1 text-sm font-medium text-slate-400">Hình thức: <span x-text="detail.type"></span></p>
                                                </div>
                                                <div class="whitespace-nowrap text-right text-sm font-bold text-slate-500 bg-slate-50 px-3 py-1.5 rounded-lg h-fit border border-slate-100">
                                                    <time x-text="detail.time"></time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>
                            <li x-show="modalData.cell.details.length === 0">
                                <div class="relative pb-10 text-base text-slate-500 italic text-center">
                                    Chưa có dữ liệu điểm danh.
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Khung kết luận -->
                    <div class="mt-12 rounded-2xl bg-slate-50 py-5 px-8 border border-slate-200 text-center flex flex-col sm:flex-row items-center justify-between max-w-2xl mx-auto shadow-inner">
                        <span class="text-base font-bold text-slate-600 mb-3 sm:mb-0">TRẠNG THÁI CHỐT CỦA HỆ THỐNG:</span>
                        <span class="text-lg font-black px-5 py-2 rounded-xl shadow-sm bg-white border"
                            :class="{
                                'text-emerald-700 border-emerald-200': modalData.cell.status === 'present',
                                'text-rose-700 border-rose-200': modalData.cell.status === 'absent',
                                'text-amber-700 border-amber-200': modalData.cell.status === 'late',
                                'text-slate-600 border-slate-200': modalData.cell.status === 'pending'
                            }" x-text="modalData.cell.text"></span>
                    </div>
                </div>
            </div>
        </template>
    </section>
</div>
