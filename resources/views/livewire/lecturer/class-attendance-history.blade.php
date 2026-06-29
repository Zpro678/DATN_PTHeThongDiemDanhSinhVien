<div x-data="{ 
    viewMode: @js($initialGroupKey ? 'session' : 'matrix'), // 'matrix', 'session'
    showModal: false,
    selectedGroupKey: @js($initialGroupKey),
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
}" class="mx-auto max-w-[1300px] space-y-8 p-4 pb-24 sm:p-8 font-sans text-slate-800">

    <section class="flex flex-col justify-between gap-6 md:flex-row md:items-end px-2">
        <div class="space-y-1">
            <p class="text-sm font-semibold tracking-widest text-indigo-500 uppercase">Quản lý điểm danh</p>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                {{ $courseClass->join_key }} 
                <span class="text-slate-300 font-light">|</span> 
                <span class="text-2xl text-slate-700">{{ $courseClass->name }}</span>
            </h1>
            <p class="text-sm text-slate-500 font-medium mt-2">Tổng cộng <span class="font-bold text-slate-700">{{ count($sessions) }}</span> phiên học</p>
        </div>
        
        <div class="flex items-center gap-3">
            <template x-if="viewMode !== 'matrix'">
                <button @click="viewMode = 'matrix'; scrollToTop()" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 transition-all hover:bg-slate-50 hover:text-indigo-600 hover:ring-indigo-200">
                    <x-user.icon name="arrow-left" :size="18" class="transition-transform group-hover:-translate-x-1" />
                    Trở về
                </button>
            </template>
            <template x-if="viewMode === 'matrix'">
                <a href="{{ route('lecturer.classes.show', $courseClass->id) }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 transition-all hover:bg-slate-50">
                    <x-user.icon name="arrow-left" :size="18" class="transition-transform group-hover:-translate-x-1" />
                    Quay lại
                </a>
            </template>
        </div>
    </section>

    @if(session('status'))
        <div class="rounded-2xl bg-emerald-50/80 px-6 py-4 text-sm font-medium text-emerald-800 border border-emerald-100 shadow-sm backdrop-blur-sm flex items-center gap-3">
            <x-user.icon name="check-circle" :size="20" class="text-emerald-500" />
            {{ session('status') }}
        </div>
    @endif

    <section x-show="viewMode === 'matrix'" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="rounded-3xl border border-slate-100 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr>
                            <th scope="col" class="sticky left-0 z-20 w-[160px] min-w-[160px] max-w-[160px] sm:w-[280px] sm:min-w-[280px] sm:max-w-none bg-white px-3 sm:px-8 py-4 sm:py-6 shadow-[8px_0_24px_-12px_rgba(0,0,0,0.1)] border-b border-slate-100">
                                <span class="text-[13px] font-black uppercase tracking-widest text-slate-700">Sinh viên</span>
                            </th>
                            @foreach($groupedSessionsInfo as $groupKey => $info)
                                <th scope="col" 
                                    @click="selectedGroupKey = '{{ $groupKey }}'; viewMode = 'session'; scrollToTop()"
                                    class="min-w-[140px] px-4 py-4 text-center cursor-pointer transition-all hover:bg-indigo-50/50 group border-b border-slate-100">
                                    <div class="flex flex-col items-center justify-center gap-1">
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-500 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">{{ $info['name'] }}</span>
                                        <span class="text-[14px] font-black text-slate-800 group-hover:text-indigo-900">{{ $info['date'] }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($members as $member)
                            <tr class="transition-colors hover:bg-slate-50 group/row">
                                <td class="sticky left-0 z-10 w-[160px] min-w-[160px] max-w-[160px] sm:w-[280px] sm:min-w-[280px] sm:max-w-none bg-white px-3 sm:px-6 py-3 sm:py-4 shadow-[8px_0_24px_-12px_rgba(0,0,0,0.1)] group-hover/row:bg-slate-50 transition-colors">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            @php
                                                $mInfo = $membersData[$member->id];
                                            @endphp
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl font-bold shadow-sm border {{ $mInfo['avatar_bg'] }} {{ $mInfo['avatar_text'] }} {{ $mInfo['avatar_border'] }}">
                                                {{ mb_substr($member->full_name, 0, 1) }}
                                            </div>
                                            <div class="flex min-w-0 flex-col">
                                                <span class="truncate font-black text-slate-900" title="{{ $member->full_name }}">{{ $member->full_name }}</span>
                                                <span class="text-xs font-medium text-slate-400">{{ $member->student_code }}</span>
                                            </div>
                                        </div>
                                        @php
                                            // % chuyên cần đã tính chuẩn trong component (vắng −1, muộn −0.5).
                                            $perc = (int) ($mInfo['attendance_percent'] ?? 100);
                                            if ($perc >= 85) {
                                                $badgeClass = 'bg-teal-50 text-teal-700 border-teal-100 shadow-[0_0_10px_rgba(15,118,110,0.1)]';
                                            } elseif ($perc >= 80) {
                                                $badgeClass = 'bg-amber-50 text-amber-600 border-amber-100 shadow-[0_0_10px_rgba(245,158,11,0.1)]';
                                            } else {
                                                $badgeClass = 'bg-rose-50 text-rose-600 border-rose-100 shadow-[0_0_10px_rgba(244,63,94,0.1)]';
                                            }
                                        @endphp
                                        <span class="inline-flex shrink-0 items-center justify-center rounded-xl px-2 py-1 text-[11px] font-black border {{ $badgeClass }}" title="Tỷ lệ chuyên cần">
                                            {{ $perc }}%
                                        </span>
                                    </div>
                                </td>
                                
                                @foreach($groupedSessionsInfo as $groupKey => $info)
                                    @php
                                        $cell = $matrix[$member->id][$groupKey];
                                        $status = $cell['status'];
                                    @endphp
                                    <td class="px-4 py-3 text-center align-middle">
                                        <div class="inline-flex justify-center" 
                                             @click="selectedStudentId = {{ $member->id }}; selectedGroupKey = '{{ $groupKey }}'; showModal = true;">
                                            @if($status === 'present')
                                                <div class="flex cursor-pointer items-center justify-center text-emerald-500 transition-transform hover:scale-125">
                                                    <x-user.icon name="check" :size="20" stroke-width="3" />
                                                </div>
                                            @elseif($status === 'absent')
                                                <div class="flex cursor-pointer items-center justify-center text-rose-500 transition-transform hover:scale-125">
                                                    <x-user.icon name="x" :size="20" stroke-width="3" />
                                                </div>
                                            @elseif($status === 'late')
                                                <div class="flex cursor-pointer items-center justify-center text-amber-500 transition-transform hover:scale-125">
                                                    <x-user.icon name="clock" :size="20" stroke-width="2.5" />
                                                </div>
                                            @elseif($status === 'excused')
                                                <div class="flex cursor-pointer items-center justify-center text-blue-500 transition-transform hover:scale-125">
                                                    <x-user.icon name="check-circle" :size="20" stroke-width="2.5" />
                                                </div>
                                            @elseif($status === 'partial')
                                                <div class="flex cursor-pointer items-center justify-center text-orange-500 transition-transform hover:scale-125" title="Vắng giữa giờ">
                                                    <x-user.icon name="alert-triangle" :size="20" stroke-width="2.5" />
                                                </div>
                                            @elseif($status === 'early_leave')
                                                <div class="flex cursor-pointer items-center justify-center text-rose-500 transition-transform hover:scale-125" title="Về sớm">
                                                    <x-user.icon name="log-out" :size="20" stroke-width="2.5" />
                                                </div>
                                            @else
                                                <div class="flex cursor-pointer items-center justify-center text-slate-300 transition-transform hover:scale-125">
                                                    <x-user.icon name="minus" :size="20" stroke-width="2.5" />
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($groupedSessionsInfo) + 1 }}" class="px-8 py-20 text-center">
                                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-50 text-slate-300">
                                        <x-user.icon name="users" :size="32" />
                                    </div>
                                    <p class="text-sm text-slate-500">Chưa có dữ liệu sinh viên hoặc điểm danh.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($members->hasPages())
                <div class="border-t border-slate-100 px-8 py-6">
                    {{ $members->links() }}
                </div>
            @endif
        </div>
    </section>

    <section x-cloak x-show="viewMode === 'session'" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="rounded-3xl bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
            <div class="px-8 py-8 md:px-10 border-b border-slate-50">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-600 uppercase tracking-widest"
                                  x-show="groupedSessionsInfo[selectedGroupKey]"
                                  x-text="(groupedSessionsInfo[selectedGroupKey]?.columns?.length ?? 1) + ' phiên'"></span>
                            <span class="flex items-center gap-1.5 text-sm font-medium text-slate-500" x-show="groupedSessionsInfo[selectedGroupKey]?.timeStr">
                                <x-user.icon name="clock" :size="16" class="text-slate-400" />
                                <span x-text="groupedSessionsInfo[selectedGroupKey]?.timeStr"></span>
                            </span>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900">
                            <span class="capitalize" x-text="groupedSessionsInfo[selectedGroupKey]?.name"></span> ngày <span class="text-indigo-600" x-text="groupedSessionsInfo[selectedGroupKey]?.date"></span>
                        </h2>
                    </div>
                    
                    <div class="flex rounded-2xl bg-slate-100 p-1.5 shadow-inner w-full md:w-auto">
                        <button @click="sessionFilter = 'merged'" 
                                :class="{'bg-white text-indigo-600 shadow-sm': sessionFilter === 'merged', 'text-slate-500 hover:text-slate-700': sessionFilter !== 'merged'}" 
                                class="flex-1 md:flex-none rounded-xl px-6 py-2.5 text-sm font-bold transition-all duration-300">
                            Kết quả chốt
                        </button>
                        <button @click="sessionFilter = 'detailed'" 
                                :class="{'bg-white text-indigo-600 shadow-sm': sessionFilter === 'detailed', 'text-slate-500 hover:text-slate-700': sessionFilter !== 'detailed'}" 
                                class="flex-1 md:flex-none rounded-xl px-6 py-2.5 text-sm font-bold transition-all duration-300">
                            Chi tiết từng phiên
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-transparent border-b border-slate-100">
                        <tr>
                            <th scope="col" class="px-8 py-6 w-16 text-center text-[13px] font-black uppercase tracking-widest text-slate-700">STT</th>
                            <th scope="col" class="py-6 text-[13px] font-black uppercase tracking-widest text-slate-700 transition-all duration-300" :class="sessionFilter === 'merged' ? 'pl-20 pr-8' : 'px-8'">Sinh viên</th>
                            
                            <template x-if="sessionFilter === 'detailed' && groupedSessionsInfo[selectedGroupKey]">
                                <template x-for="col in groupedSessionsInfo[selectedGroupKey].columns" :key="col.iteration">
                                    <th scope="col" class="px-4 py-6 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-[13px] font-black uppercase tracking-widest text-slate-700" x-text="'Lần ' + col.iteration"></span>
                                            <span x-show="col.time" class="mt-1 text-[10px] font-medium text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full" x-text="col.time"></span>
                                        </div>
                                    </th>
                                </template>
                            </template>

                            <th scope="col" class="px-8 py-6 text-center text-[13px] font-black uppercase tracking-widest text-slate-700" x-show="sessionFilter === 'merged'">Buổi tính</th>
                            <th scope="col" class="px-8 py-6 text-center text-[13px] font-black uppercase tracking-widest text-slate-700">Trạng thái chốt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="(student, index) in drawerStudents" :key="student.id">
                            <tr class="group hover:bg-slate-50/60 transition-colors">
                                <td class="px-8 py-5 text-center font-semibold text-slate-400" x-text="index + 1"></td>
                                <td class="py-5 align-middle transition-all duration-300" :class="sessionFilter === 'merged' ? 'pl-20 pr-8' : 'px-8'">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-bold transition-colors border group-hover:opacity-80" 
                                             :class="`${student.avatar_bg} ${student.avatar_text} ${student.avatar_border}`" 
                                             x-text="student.full_name.charAt(0)"></div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900" x-text="student.full_name"></span>
                                            <span class="text-xs font-medium text-slate-400" x-text="student.student_code"></span>
                                        </div>
                                    </div>
                                </td>
                                
                                <template x-if="sessionFilter === 'detailed' && groupedSessionsInfo[selectedGroupKey]">
                                    <template x-for="col in groupedSessionsInfo[selectedGroupKey].columns" :key="col.iteration">
                                        <td class="px-4 py-5 align-middle text-center cursor-pointer" @click="selectedStudentId = student.id; viewMode = 'timeline'; scrollToTop()">
                                            <template x-if="getDetail(student, col.iteration)">
                                                <span class="inline-flex min-w-[96px] items-center justify-center rounded-xl px-3 py-2 text-[13px] font-bold transition-all hover:scale-105" 
                                                    :class="{
                                                        'bg-emerald-50 text-emerald-600': getDetail(student, col.iteration).status === 'present' || getDetail(student, col.iteration).status === 'excused',
                                                        'bg-rose-50 text-rose-600': getDetail(student, col.iteration).status === 'absent',
                                                        'bg-amber-50 text-amber-600': getDetail(student, col.iteration).status === 'late',
                                                        'bg-slate-50 text-slate-500': getDetail(student, col.iteration).status === 'pending'
                                                    }" x-text="getDetail(student, col.iteration).statusText"></span>
                                            </template>
                                            <template x-if="!getDetail(student, col.iteration)">
                                                <span class="text-slate-300 font-light">-</span>
                                            </template>
                                        </td>
                                    </template>
                                </template>

                                <td class="px-8 py-5 text-center align-middle" x-show="sessionFilter === 'merged'">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-sm font-black text-slate-700 border border-slate-200 shadow-sm" x-text="student.cell.attendedSessions"></span>
                                </td>

                                <td class="px-8 py-5 text-center align-middle">
                                    <span x-show="student.cell.status === 'present'" class="inline-flex min-w-[110px] items-center justify-center rounded-xl px-4 py-2 text-[13px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-sm">Có mặt</span>
                                    <span x-show="student.cell.status === 'absent'" class="inline-flex min-w-[110px] items-center justify-center rounded-xl px-4 py-2 text-[13px] font-bold bg-rose-50 text-rose-600 border border-rose-100 shadow-sm" x-text="student.cell.text || 'Vắng mặt'"></span>
                                    <span x-show="student.cell.status === 'late'" class="inline-flex min-w-[110px] items-center justify-center rounded-xl px-4 py-2 text-[13px] font-bold bg-amber-50 text-amber-600 border border-amber-100 shadow-sm" x-text="student.cell.text || 'Đi trễ'"></span>
                                    <span x-show="student.cell.status === 'partial'" class="inline-flex min-w-[110px] items-center justify-center rounded-xl px-4 py-2 text-[13px] font-bold bg-orange-50 text-orange-600 border border-orange-100 shadow-sm">Vắng giữa giờ</span>
                                    <span x-show="student.cell.status === 'early_leave'" class="inline-flex min-w-[110px] items-center justify-center rounded-xl px-4 py-2 text-[13px] font-bold bg-rose-50 text-rose-600 border border-rose-100 shadow-sm">Về sớm</span>
                                    <span x-show="student.cell.status === 'excused'" class="inline-flex min-w-[110px] items-center justify-center rounded-xl px-4 py-2 text-[13px] font-bold bg-blue-50 text-blue-600 border border-blue-100 shadow-sm">Có phép</span>
                                    <span x-show="student.cell.status === 'pending'" class="inline-flex min-w-[110px] items-center justify-center rounded-xl px-4 py-2 text-[13px] font-bold bg-slate-50 text-slate-500 border border-slate-200 shadow-sm">Chưa ĐD</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            @if($members->hasPages())
                <div class="border-t border-slate-100 px-8 py-6">
                    {{ $members->links() }}
                </div>
            @endif
        </div>
    </section>

    <!-- POPUP MODAL THÔNG TIN ĐIỂM DANH -->
    <div x-cloak x-show="showModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showModal" @click.away="showModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    
                    <template x-if="modalData">
                        <div>
                            <!-- Header -->
                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="text-base font-black text-slate-800" id="modal-title">Chi tiết điểm danh</h3>
                                <button type="button" @click="showModal = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition-colors">
                                    <x-user.icon name="x" :size="20" stroke-width="2.5" />
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-6 space-y-4">
                                <!-- Student Info -->
                                <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50/50 border border-slate-100">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-lg font-black border"
                                         :class="`${modalData.student.avatar_bg} ${modalData.student.avatar_text} ${modalData.student.avatar_border}`">
                                        <span x-text="modalData.student.full_name.charAt(0)"></span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900" x-text="modalData.student.full_name"></p>
                                        <p class="text-sm font-medium text-slate-500" x-text="modalData.student.student_code"></p>
                                    </div>
                                </div>

                                <!-- Details Grid -->
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-3 text-center">
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400 mb-1 whitespace-nowrap">Ngày học</p>
                                        <p class="text-[15px] font-bold text-slate-700" x-text="modalData.sessionInfo.date"></p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-3 text-center">
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400 mb-1 whitespace-nowrap">Buổi ghi nhận</p>
                                        <p class="text-[15px] font-bold text-slate-700" x-text="modalData.cell.attendedSessions + ' buổi'"></p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-3 text-center">
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400 mb-1 whitespace-nowrap">Đã điểm danh</p>
                                        <p class="text-[15px] font-bold text-indigo-600" x-text="modalData.student.total_attended_sessions + '/' + modalData.student.total_course_sessions + ' buổi'"></p>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="rounded-2xl border border-slate-100 p-4 flex items-center justify-between"
                                     :class="{
                                         'bg-emerald-50/50 border-emerald-100/50': modalData.cell.status === 'present',
                                         'bg-rose-50/50 border-rose-100/50': modalData.cell.status === 'absent',
                                         'bg-amber-50/50 border-amber-100/50': modalData.cell.status === 'late',
                                         'bg-slate-50/50 border-slate-100/50': modalData.cell.status === 'pending'
                                     }">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Trạng thái chốt</p>
                                    <span class="inline-flex px-3 py-1 rounded-xl text-sm font-black"
                                          :class="{
                                              'bg-emerald-100 text-emerald-700': modalData.cell.status === 'present',
                                              'bg-rose-100 text-rose-700': modalData.cell.status === 'absent',
                                              'bg-amber-100 text-amber-700': modalData.cell.status === 'late',
                                              'bg-slate-200 text-slate-700': modalData.cell.status === 'pending'
                                          }" x-text="modalData.cell.text"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>