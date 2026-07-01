<div class="w-full px-6 py-6 pb-24 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <x-slot name="title">
        Thông tin: {{ $class->name }}
    </x-slot>

    @php
        $validSessions = $attendanceDetail['attended'] ?? (($attendanceDetail['present'] ?? 0) + ($attendanceDetail['late'] ?? 0) + ($attendanceDetail['excused'] ?? 0));
        $percent = $attendanceDetail['percent'] ?? 100;
        $isWarning = $attendanceDetail['warning'] ?? false;
        $plannedSessions = $attendanceDetail['planned_sessions'] ?? 0;
        $allowedAbsentSessions = $attendanceDetail['allowed_absent_sessions'] ?? 0;
        $safeAbsenceSessions = $attendanceDetail['safe_absence_sessions'] ?? 0;
        $exceededAbsentSessions = $attendanceDetail['exceeded_absent_sessions'] ?? 0;
        $absenceBudgetState = $attendanceDetail['absence_budget_state'] ?? 'safe';
        $absenceBudgetLabel = $attendanceDetail['absence_budget_label'] ?? 'Chưa có dữ liệu';
        $absenceBudgetValue = $absenceBudgetState === 'danger' ? 'Vượt '.$exceededAbsentSessions.' buổi' : $safeAbsenceSessions.' buổi';
        $backRoute = $fromAttendanceStats
            ? route('student.attendance.stats', ['ma_user' => auth()->id()])
            : route('joined-classes', ['ma_user' => auth()->id()]);
        $backLabel = $fromAttendanceStats ? 'Trở về thống kê' : 'Trở về lớp học';
        
        $colorOptions = [
            'bg-[#475569]', 'bg-[#1D4ED8]', 'bg-[#0F766E]', 'bg-[#4338CA]',
            'bg-[#047857]', 'bg-[#0369A1]', 'bg-[#6D28D9]', 'bg-[#B45309]',
        ];
        $textColorOptions = [
            'text-[#475569]', 'text-[#1D4ED8]', 'text-[#0F766E]', 'text-[#4338CA]',
            'text-[#047857]', 'text-[#0369A1]', 'text-[#6D28D9]', 'text-[#B45309]',
        ];
        $themeBgClass = $colorOptions[$class->id % count($colorOptions)];
        $themeTextClass = $textColorOptions[$class->id % count($textColorOptions)];
        
        $bgIcons = ['laptop', 'book', 'code', 'book-open', 'graduation-cap', 'layout-dashboard'];
        $themeIcon = $bgIcons[$class->id % count($bgIcons)];
    @endphp

    {{-- Header Banner like Google Classroom --}}
    <div class="mt-6 mb-8 relative overflow-hidden rounded-xl {{ $themeBgClass }} shadow-sm">
        
        {{-- Background Icon --}}
        <div class="absolute right-0 top-1/2 z-0 opacity-15 pointer-events-none transform -translate-y-1/2 translate-x-1/4 sm:translate-x-0 sm:right-10">
            <x-user.icon :name="$themeIcon" :size="160" class="text-white transform -rotate-12" stroke-width="1.5" />
        </div>
        
        <div class="flex flex-col h-full min-h-[160px] sm:min-h-[180px] px-6 py-6 sm:px-8 lg:px-10 relative z-10">
            <div class="flex justify-between items-start gap-4">
                <div class="pr-2">
                    <h1 class="text-3xl sm:text-[2.5rem] leading-tight font-medium tracking-tight text-white drop-shadow-sm">{{ $class->name }}</h1>
                    <p class="mt-2 text-[15px] text-white/90 drop-shadow-sm">
                        Mã lớp: <span class="font-bold text-white">{{ $class->join_key }}</span>
                        @if($class->subject_code)
                            <span class="mx-2 opacity-60">•</span>
                            Mã học phần: <span class="font-bold text-white">{{ $class->subject_code }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6 lg:flex-row items-stretch">
        {{-- Sidebar: Thông tin lớp --}}
        <div class="flex flex-col lg:w-1/3 xl:w-1/4">
            <div class="flex flex-col h-full rounded-2xl bg-white p-5 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 {{ $themeTextClass }}">
                        <x-user.icon name="info" :size="20" />
                    </div>
                    <h3 class="text-[1.05rem] font-bold text-slate-800">Thông tin lớp</h3>
                </div>

                <div class="flex flex-col gap-2.5 text-[13px] sm:text-sm">
                    <!-- Giảng viên -->
                    <div class="flex items-center justify-between rounded-xl bg-[#f8faff] px-4 py-3 transition hover:bg-[#f0f4ff]">
                        <div class="flex items-center gap-3">
                            <x-user.icon name="user" :size="16" class="text-slate-600" />
                            <span class="font-medium text-slate-500">Giảng viên</span>
                        </div>
                        <span class="font-bold text-slate-800 text-right leading-tight max-w-[55%]">{{ $class->owner->name ?? 'Chưa cập nhật' }}</span>
                    </div>

                    <!-- Mã học phần -->
                    <div class="flex items-center justify-between rounded-xl bg-[#f8faff] px-4 py-3 transition hover:bg-[#f0f4ff]">
                        <div class="flex items-center gap-3">
                            <x-user.icon name="qr-code" :size="16" class="text-slate-600" />
                            <span class="font-medium text-slate-500">Mã học phần</span>
                        </div>
                        <span class="font-bold text-slate-800 text-right">{{ $class->subject_code ?? 'N/A' }}</span>
                    </div>

                    <!-- Tổng số buổi -->
                    <div class="flex items-center justify-between rounded-xl bg-[#f8faff] px-4 py-3 transition hover:bg-[#f0f4ff]">
                        <div class="flex items-center gap-3">
                            <x-user.icon name="calendar" :size="16" class="text-slate-600" />
                            <span class="font-medium text-slate-500">Tổng số buổi</span>
                        </div>
                        <span class="font-bold text-slate-800 text-right">{{ $class->total_sessions }} buổi</span>
                    </div>

                    <!-- Vắng tối đa -->
                    <div class="flex items-center justify-between rounded-xl bg-[#f8faff] px-4 py-3 transition hover:bg-[#f0f4ff]">
                        <div class="flex items-center gap-3">
                            <x-user.icon name="shield-alert" :size="16" class="text-slate-600" />
                            <span class="font-medium text-slate-500">Vắng tối đa</span>
                        </div>
                        <span class="font-bold text-slate-800 text-right">{{ $allowedAbsentSessions }} buổi</span>
                    </div>
                </div>

                <div class="mt-auto border-t border-slate-100/80 pt-5">
                    <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-widest">
                        <span class="text-slate-500">TRẠNG THÁI</span>
                        <div class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                            <x-user.icon name="check-circle" :size="14" />
                            <span>ĐANG DIỄN RA</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Main Content: Chuyên cần --}}
        <div class="flex flex-1 flex-col gap-6">
            
            {{-- 6 ô thống kê --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                @php
                    $budgetColor = 'emerald';
                    $budgetBg = 'bg-[#f0fdf4]';
                    $budgetBorder = 'border-emerald-100';
                    $budgetExtra = 'shadow-md shadow-emerald-500/20 border-emerald-200/80 ring-1 ring-emerald-500/20';
                    $budgetTag = 'An toàn';
                    
                    if ($absenceBudgetState === 'warning') {
                        $budgetColor = 'amber';
                        $budgetBg = 'bg-[#fffbeb]';
                        $budgetBorder = 'border-amber-100';
                        $budgetExtra = 'shadow-md shadow-amber-500/20 border-amber-200/80 ring-1 ring-amber-500/20';
                        $budgetTag = 'Cảnh báo';
                    } elseif ($absenceBudgetState === 'danger') {
                        $budgetColor = 'rose';
                        $budgetBg = 'bg-[#fff1f2]';
                        $budgetBorder = 'border-rose-100';
                        $budgetExtra = 'shadow-md shadow-rose-500/20 border-rose-200/80 ring-1 ring-rose-500/20';
                        $budgetTag = 'Nguy hiểm';
                    }

                    $tiles = [
                        ['label' => 'CHUYÊN CẦN', 'value' => $percent.'%', 'icon' => 'trending-up', 'color' => 'blue', 'border' => 'border-blue-100', 'bg' => 'bg-[#f4f7fe]', 'extra' => 'shadow-sm'],
                        ['label' => 'BUỔI HỢP LỆ', 'value' => $validSessions, 'icon' => 'check-circle', 'color' => 'emerald', 'border' => 'border-emerald-100', 'bg' => 'bg-[#f0fdf4]', 'extra' => 'shadow-sm'],
                        ['label' => 'TỔNG BUỔI', 'value' => $attendanceDetail['total'] ?? 0, 'icon' => 'calendar-check', 'color' => 'slate', 'border' => 'border-slate-100', 'bg' => 'bg-[#f8fafc]', 'extra' => 'shadow-sm'],
                        ['label' => 'BUỔI ĐI MUỘN', 'value' => $attendanceDetail['late'] ?? 0, 'icon' => 'clock', 'color' => 'amber', 'border' => 'border-amber-100', 'bg' => 'bg-[#fffbeb]', 'extra' => 'shadow-sm'],
                        ['label' => 'BUỔI VẮNG', 'value' => $attendanceDetail['absent'] ?? 0, 'icon' => 'x-circle', 'color' => 'rose', 'border' => 'border-rose-100', 'bg' => 'bg-[#fff1f2]', 'extra' => 'shadow-sm'],
                        ['label' => 'QUỸ VẮNG', 'value' => $absenceBudgetValue, 'icon' => $absenceBudgetState === 'safe' ? 'shield-check' : 'alert-triangle', 'color' => $budgetColor, 'border' => $budgetBorder, 'bg' => $budgetBg, 'extra' => $budgetExtra, 'status_text' => $budgetTag],
                    ];
                @endphp
                @foreach($tiles as $tile)
                    @php 
                        $c = $tile['color']; 
                        $textClass = [
                            'blue' => 'text-blue-600', 'emerald' => 'text-emerald-600', 'slate' => 'text-slate-600', 
                            'amber' => 'text-amber-600', 'rose' => 'text-rose-600', 'indigo' => 'text-indigo-600'
                        ][$c];
                        $bgIconClass = [
                            'blue' => 'bg-blue-100 text-blue-600', 'emerald' => 'bg-emerald-100 text-emerald-600', 
                            'slate' => 'bg-slate-100 text-slate-600', 'amber' => 'bg-amber-100 text-amber-600', 
                            'rose' => 'bg-rose-100 text-rose-600', 'indigo' => 'bg-indigo-100 text-indigo-600'
                        ][$c];
                    @endphp
                    <div class="relative flex items-center gap-3.5 rounded-2xl border {{ $tile['border'] }} {{ $tile['bg'] }} p-3.5 sm:p-4 {{ $tile['extra'] }}">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $bgIconClass }}">
                            <x-user.icon name="{{ $tile['icon'] }}" :size="18" />
                        </div>
                        <div>
                            <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-0.5">{{ $tile['label'] }}</p>
                            <strong class="text-xl sm:text-2xl font-black leading-none {{ $textClass }}">
                                {{ $tile['value'] }}
                            </strong>
                        </div>
                        @if(isset($tile['status_text']))
                            <div class="absolute top-3.5 right-4">
                                <span class="text-[9px] font-bold uppercase tracking-widest {{ $textClass }} opacity-90">{{ $tile['status_text'] }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Thanh tiến độ --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 sm:p-6 shadow-sm">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Tiến độ chuyên cần</h3>
                        <p class="mt-0.5 text-[13px] text-slate-500">Duy trì trên mức tối thiểu để đủ điều kiện dự thi.</p>
                    </div>
                    <div class="text-right hidden sm:block">
                        <div class="text-[2rem] leading-none font-black {{ $themeTextClass }}">{{ $percent }}%</div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-1.5">CURRENT STATUS</div>
                    </div>
                </div>
                
                <div class="mb-2">
                    <div class="relative h-3.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full {{ $themeBgClass }} transition-all duration-700" style="width: {{ min(100, $percent) }}%"></div>
                        <div class="absolute top-0 bottom-0 w-[3px] bg-red-500" style="left: 80%"></div>
                    </div>
                    <div class="relative mt-2 flex justify-between text-[11px] sm:text-xs font-semibold text-slate-500">
                        <span>0%</span>
                        <span class="absolute whitespace-nowrap" style="left: 80%; transform: translateX(-50%); color: #ef4444;">80% (Yêu cầu)</span>
                        <span>100%</span>
                    </div>
                </div>
                
                <div class="mt-6 flex items-start gap-3.5 rounded-xl bg-slate-50 px-5 py-4 text-[13px] text-slate-600 border border-slate-100">
                    <div class="{{ $themeTextClass }} shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.9 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>
                    </div>
                    <p class="leading-relaxed">
                        Dữ liệu được tính từ các buổi đã chốt của lớp này (mỗi buổi tính 1 đơn vị). Bạn cần duy trì tỷ lệ tham gia từ <strong class="text-slate-800">80% trở lên</strong> để đảm bảo kiến thức và đủ điều kiện tham dự kỳ thi cuối kỳ.
                    </p>
                </div>
            </div>


        </div>
    </div>
</div>
