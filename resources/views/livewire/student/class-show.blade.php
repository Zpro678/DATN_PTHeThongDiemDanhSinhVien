<div class="mx-auto max-w-7xl p-4 pb-24 sm:p-6 lg:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <x-slot name="title">
        Thông tin: {{ $class->name }}
    </x-slot>

    @php
        $validLessons = $attendanceDetail['attended'] ?? (($attendanceDetail['present'] ?? 0) + ($attendanceDetail['late'] ?? 0) + ($attendanceDetail['excused'] ?? 0));
        $percent = $attendanceDetail['percent'] ?? 100;
        $isWarning = $attendanceDetail['warning'] ?? false;
        $plannedLessons = $attendanceDetail['planned_lessons'] ?? 0;
        $allowedAbsentLessons = $attendanceDetail['allowed_absent_lessons'] ?? 0;
        $safeAbsenceLessons = $attendanceDetail['safe_absence_lessons'] ?? 0;
        $exceededAbsentLessons = $attendanceDetail['exceeded_absent_lessons'] ?? 0;
        $absenceBudgetState = $attendanceDetail['absence_budget_state'] ?? 'safe';
        $absenceBudgetLabel = $attendanceDetail['absence_budget_label'] ?? 'Chưa có dữ liệu';
        $absenceBudgetValue = $absenceBudgetState === 'danger' ? 'Vượt '.$exceededAbsentLessons.' tiết' : $safeAbsenceLessons.' tiết';
        $backRoute = $fromAttendanceStats
            ? route('student.attendance.stats', ['ma_user' => auth()->id()])
            : route('joined-classes', ['ma_user' => auth()->id()]);
        $backLabel = $fromAttendanceStats ? 'Trở về thống kê' : 'Trở về lớp học';
    @endphp

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-primary">
                <x-user.icon name="school" :size="14" />
                Chi tiết lớp học
            </div>
            <h1 class="text-2xl font-bold text-on-surface">{{ $class->name }}</h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                Mã lớp <span class="font-bold text-on-surface">{{ $class->code }}</span>
                @if($class->semester)
                    <span class="mx-1.5 opacity-40">•</span>{{ $class->semester }}
                @endif
                @if($class->subject_code)
                    <span class="mx-1.5 opacity-40">•</span>Mã học phần <span class="font-bold text-on-surface">{{ $class->subject_code }}</span>
                @endif
            </p>
        </div>
        <a href="{{ $backRoute }}" class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-on-primary transition-colors hover:bg-primary/90">
            <x-user.icon name="arrow-left" :size="16" />
            {{ $backLabel }}
        </a>
    </div>

    <div class="flex flex-col gap-6 lg:flex-row items-stretch">
        {{-- Sidebar: Thông tin lớp --}}
        <div class="flex flex-col gap-6 lg:w-1/3 xl:w-1/4">
            <div class="flex h-full flex-col rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <x-user.icon name="info" :size="20" />
                    </div>
                    <h3 class="font-bold text-on-surface">Thông tin lớp</h3>
                </div>

                <div class="flex flex-col text-sm">
                    <div class="flex items-center justify-between gap-3 py-3">
                        <span class="text-on-surface-variant">Tên lớp</span>
                        <span class="font-semibold text-on-surface text-right max-w-[60%]">{{ $class->name }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-outline-variant/15 py-3">
                        <span class="text-on-surface-variant">Giảng viên</span>
                        <span class="font-semibold text-on-surface text-right">{{ $class->owner->name ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-outline-variant/15 py-3">
                        <span class="text-on-surface-variant">Học kỳ</span>
                        <span class="font-semibold text-on-surface text-right">{{ $class->semester ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-outline-variant/15 py-3">
                        <span class="text-on-surface-variant">Mã học phần</span>
                        <span class="font-semibold text-on-surface text-right">{{ $class->subject_code ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-outline-variant/15 py-3">
                        <span class="text-on-surface-variant">Tổng số tiết</span>
                        <span class="font-semibold text-on-surface text-right">{{ $class->total_lessons }} tiết</span>
                    </div>
                </div>

                {{-- Mã lớp nổi bật --}}
                <div class="mt-auto pt-4">
                    <div class="rounded-2xl bg-primary/5 px-4 py-3 text-center">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-primary">Mã lớp học</p>
                        <p class="mt-1 font-mono text-xl font-black tracking-widest text-primary">{{ $class->code }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content: Chuyên cần --}}
        <div class="flex flex-1 flex-col gap-6">
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Chuyên cần cá nhân</p>
                        <h3 class="mt-1 text-xl font-black text-on-surface">Tỷ lệ tham gia của bạn</h3>
                    </div>
                    <span @class([
                        'inline-flex w-fit items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider',
                        'bg-rose-50 text-rose-600 ring-1 ring-rose-100' => $isWarning,
                        'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100' => ! $isWarning,
                    ])>
                        <x-user.icon name="{{ $isWarning ? 'alert-triangle' : 'shield-check' }}" :size="14" />
                        {{ $isWarning ? 'Cần cải thiện' : 'Đang ổn định' }}
                    </span>
                </div>

                {{-- 6 ô thống kê --}}
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 2xl:grid-cols-6">
                    @php
                        $tiles = [
                            ['label' => 'Tỷ lệ chuyên cần', 'value' => $percent.'%', 'icon' => 'trending-up', 'border' => $isWarning ? 'border-rose-100' : 'border-primary/10', 'bg' => $isWarning ? 'bg-rose-50' : 'bg-primary/5', 'icon_bg' => $isWarning ? 'bg-rose-100 text-rose-600' : 'bg-primary/10 text-primary', 'text' => $isWarning ? 'text-rose-600' : 'text-primary'],
                            ['label' => 'Tiết hợp lệ', 'value' => $validLessons, 'icon' => 'check-circle', 'border' => 'border-emerald-100', 'bg' => 'bg-emerald-50', 'icon_bg' => 'bg-emerald-100 text-emerald-600', 'text' => 'text-emerald-600'],
                            ['label' => 'Tổng tiết đã chốt', 'value' => $attendanceDetail['total'] ?? 0, 'icon' => 'calendar-check', 'border' => 'border-slate-100', 'bg' => 'bg-slate-50', 'icon_bg' => 'bg-slate-100 text-slate-600', 'text' => 'text-slate-700'],
                            ['label' => 'Tiết đi muộn', 'value' => $attendanceDetail['late'] ?? 0, 'icon' => 'clock', 'border' => 'border-amber-100', 'bg' => 'bg-amber-50', 'icon_bg' => 'bg-amber-100 text-amber-600', 'text' => 'text-amber-600'],
                            ['label' => 'Tiết vắng', 'value' => $attendanceDetail['absent'] ?? 0, 'icon' => 'x-circle', 'border' => 'border-rose-100', 'bg' => 'bg-rose-50', 'icon_bg' => 'bg-rose-100 text-rose-600', 'text' => 'text-rose-600'],
                            ['label' => 'Quỹ vắng an toàn', 'value' => $absenceBudgetValue, 'icon' => $absenceBudgetState === 'safe' ? 'shield-check' : 'alert-triangle', 'border' => $absenceBudgetState === 'danger' ? 'border-rose-100' : ($absenceBudgetState === 'warning' ? 'border-amber-100' : 'border-sky-100'), 'bg' => $absenceBudgetState === 'danger' ? 'bg-rose-50' : ($absenceBudgetState === 'warning' ? 'bg-amber-50' : 'bg-sky-50'), 'icon_bg' => $absenceBudgetState === 'danger' ? 'bg-rose-100 text-rose-600' : ($absenceBudgetState === 'warning' ? 'bg-amber-100 text-amber-600' : 'bg-sky-100 text-sky-600'), 'text' => $absenceBudgetState === 'danger' ? 'text-rose-600' : ($absenceBudgetState === 'warning' ? 'text-amber-600' : 'text-sky-600')],
                        ];
                    @endphp
                    @foreach($tiles as $tile)
                        <div class="rounded-2xl border {{ $tile['border'] }} {{ $tile['bg'] }} p-4 transition-shadow hover:shadow-sm">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full {{ $tile['icon_bg'] }}">
                                <x-user.icon name="{{ $tile['icon'] }}" :size="18" />
                            </span>
                            <p class="mt-3 text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">{{ $tile['label'] }}</p>
                            <strong class="mt-1 block text-2xl font-black {{ $tile['text'] }}">{{ $tile['value'] }}</strong>
                        </div>
                    @endforeach
                </div>

                {{-- Thanh tiến độ --}}
                <div class="mt-6 rounded-2xl border border-outline-variant/10 bg-surface-container-low p-5">
                    <div class="mb-2 flex items-center justify-between text-sm font-bold">
                        <span class="text-on-surface-variant">Tiến độ chuyên cần</span>
                        <span @class(['text-rose-600' => $isWarning, 'text-primary' => ! $isWarning])>{{ $percent }}%</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-surface-container-highest">
                        <div @class([
                            'h-full rounded-full transition-all duration-700',
                            'bg-rose-500' => $isWarning,
                            'bg-primary' => ! $isWarning,
                        ]) style="width: {{ min(100, $percent) }}%"></div>
                    </div>
                    <p class="mt-3 text-sm text-on-surface-variant leading-relaxed">
                        Dữ liệu được tính từ các phiên điểm danh đã chốt của lớp này, quy đổi theo số tiết từng buổi. Cần duy trì từ <span class="font-bold text-on-surface">80%</span> trở lên để đủ điều kiện dự thi.
                    </p>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-white/70 px-4 py-3 ring-1 ring-outline-variant/10">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">Tổng tiết kế hoạch</p>
                            <strong class="mt-1 block text-lg font-black text-on-surface">{{ $plannedLessons }} tiết</strong>
                        </div>
                        <div class="rounded-xl bg-white/70 px-4 py-3 ring-1 ring-outline-variant/10">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">Ngưỡng vắng tối đa</p>
                            <strong class="mt-1 block text-lg font-black text-on-surface">{{ $allowedAbsentLessons }} tiết</strong>
                        </div>
                        <div class="rounded-xl bg-white/70 px-4 py-3 ring-1 ring-outline-variant/10">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-on-surface-variant">Trạng thái quỹ</p>
                            <strong @class([
                                'mt-1 block text-lg font-black',
                                'text-rose-600' => $absenceBudgetState === 'danger',
                                'text-amber-600' => $absenceBudgetState === 'warning',
                                'text-sky-600' => $absenceBudgetState === 'safe',
                            ])>{{ $absenceBudgetLabel }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Điểm danh --}}
        <a href="#" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-secondary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-secondary/10 text-secondary transition-colors group-hover:bg-secondary group-hover:text-white">
                <x-user.icon name="log-in" :size="24" />
            </div>
            <span class="font-bold text-on-surface">Tham gia lớp (Nhập mã)</span>
        </button>

        {{-- Lịch sử --}}
        <a href="{{ route('student.attendance.history', ['ma_user' => auth()->id(), 'classFilter' => $class->id]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-primary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                <x-user.icon name="history" :size="24" />
            </div>
            <span class="font-bold text-on-surface">Lịch sử điểm danh</span>
        </a>

        {{-- Đơn xin nghỉ --}}
        <a href="{{ route('student.leave-requests.create', ['ma_user' => auth()->id()]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-[#F59E0B]/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#F59E0B]/10 text-[#F59E0B] transition-colors group-hover:bg-[#F59E0B] group-hover:text-white">
                <x-user.icon name="file-text" :size="24" />
            </div>
            <span class="font-bold text-on-surface">Đơn xin nghỉ</span>
        </a>
    </div>
</div>
