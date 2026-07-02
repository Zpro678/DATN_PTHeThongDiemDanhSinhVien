<div class="w-full space-y-8 px-6 py-6 pb-24 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="flex justify-end">
        <button type="button" wire:click="exportExcel" class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-300 active:scale-95">
            <x-user.icon name="download" :size="18" class="text-emerald-600" />
            <span wire:loading.remove wire:target="exportExcel">Xuất báo cáo</span>
            <span wire:loading wire:target="exportExcel">Đang xuất...</span>
        </button>
    </div>

    <!-- Alerts (if any) -->
    @if ($isDemo)
        <div class="rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50 to-amber-100/50 px-5 py-3 text-[13px] font-medium text-amber-800 shadow-sm">
            Đang hiển thị dữ liệu mẫu từ giao diện `develop_v1` để test khi tài khoản chưa có lớp.
        </div>
    @endif

    @if ($totals['warning_count'] > 0)
        <div>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between rounded-3xl border border-rose-200 bg-gradient-to-r from-rose-50 to-white p-6 sm:p-8 relative overflow-hidden shadow-sm">
                <!-- Background Icon -->
                <div class="absolute -right-10 -top-10 text-rose-500/5 pointer-events-none rotate-12">
                    <x-user.icon name="alert-triangle" :size="180" />
                </div>
                
                <div class="relative z-10 flex items-start gap-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white text-rose-600 shadow-md ring-1 ring-rose-100">
                        <x-user.icon name="alert-triangle" :size="28" stroke-width="2.5" />
                    </div>
                    <div class="pt-1">
                        <h3 class="text-lg sm:text-xl font-bold tracking-tight text-rose-900">
                            Cảnh báo nguy hiểm: {{ $totals['warning_count'] }} môn học có nguy cơ cấm thi
                        </h3>
                        <p class="mt-1.5 max-w-3xl text-[14px] font-medium leading-relaxed text-rose-800/80">
                            Bạn đã vượt quá ngưỡng an toàn (vắng &gt; 20%). Vui lòng kiểm tra lại và nộp đơn minh chứng vắng mặt ngay lập tức nếu bạn có lý do chính đáng để tránh bị cấm thi.
                        </p>
                    </div>
                </div>

                <a href="{{ route('student.leave-requests.create', ['ma_user' => auth()->id()]) }}" class="relative z-10 mt-2 lg:mt-0 inline-flex w-full lg:w-auto items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 px-6 py-3.5 text-[14px] font-bold text-white shadow-lg shadow-rose-500/30 transition-all hover:-translate-y-0.5 hover:shadow-rose-500/40 active:scale-95 shrink-0 whitespace-nowrap">
                    <x-user.icon name="file-plus" :size="18" />
                    Nộp đơn minh chứng
                </a>
            </div>
        </div>
    @endif

    <!-- 4 Premium Stats Cards -->
    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        @php
            $statCards = [
                [
                    'label' => 'Tỷ lệ chuyên cần', 'value' => $totals['percent'].'%', 'hint' => 'Trung bình tất cả môn', 'icon' => 'activity',
                    'iconBg' => 'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-blue-500/30', 
                    'blobBg' => 'bg-blue-50'
                ],
                [
                    'label' => 'Số buổi có mặt', 'value' => $totals['present'] + $totals['late'] + $totals['excused'], 'hint' => 'Bao gồm muộn/có phép', 'icon' => 'check-circle',
                    'iconBg' => 'bg-gradient-to-br from-emerald-400 to-teal-500 shadow-emerald-500/30',
                    'blobBg' => 'bg-emerald-50'
                ],
                [
                    'label' => 'Số buổi vắng', 'value' => $totals['absent'], 'hint' => 'Vắng không phép/chưa hợp lệ', 'icon' => 'x-circle',
                    'iconBg' => 'bg-gradient-to-br from-rose-400 to-red-500 shadow-rose-500/30',
                    'blobBg' => 'bg-rose-50'
                ],
                [
                    'label' => 'Số buổi đi muộn', 'value' => $totals['late'], 'hint' => 'Đi muộn theo tổng kết buổi', 'icon' => 'clock',
                    'iconBg' => 'bg-gradient-to-br from-amber-400 to-orange-500 shadow-amber-500/30',
                    'blobBg' => 'bg-amber-50'
                ],
            ];
        @endphp
        @foreach ($statCards as $card)
            <article class="relative overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[12px] font-bold uppercase tracking-wider text-slate-500">{{ $card['label'] }}</span>
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl text-white shadow-lg {{ $card['iconBg'] }}">
                                <x-user.icon :name="$card['icon']" :size="20" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <strong class="text-4xl font-black text-slate-800 tracking-tight">{{ $card['value'] }}</strong>
                        </div>
                    </div>
                    <p class="mt-4 flex items-center gap-1.5 text-[13px] font-medium text-slate-500">
                        <x-user.icon name="info" :size="14" class="text-slate-400" />
                        {{ $card['hint'] }}
                    </p>
                </div>
            </article>
        @endforeach
    </section>

    <!-- Quỹ vắng an toàn (Safe Absence Budget Grid) -->
    @php
        $ranked = collect($subjects)
            ->filter(fn ($s) => ($s['planned_sessions'] ?? $s['total_sessions'] ?? 0) > 0 || ($s['total'] ?? 0) > 0)
            ->sortBy('percent')
            ->values();
    @endphp
    @if ($ranked->isNotEmpty())
        <section>
            <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between px-2">
                <div>
                    <h2 class="text-2xl font-black text-slate-800">Quỹ vắng an toàn theo môn</h2>
                    <p class="mt-1 text-[14px] font-medium text-slate-500">Theo dõi số buổi bạn được phép vắng để không bị cấm thi (ngưỡng &gt; 20%).</p>
                </div>
                <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                    <x-user.icon name="shield-alert" :size="14" />
                    Giới hạn an toàn
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach ($ranked as $subject)
                    @php
                        $percent = $subject['percent'] ?? 100;
                        $budgetLabel = $subject['absence_budget_label'] ?? 'Chưa có dữ liệu';
                        $budgetState = $subject['absence_budget_state'] ?? 'safe';

                        $isDanger = $budgetState === 'danger';
                        $isWarning = $budgetState === 'warning';
                        
                        if ($isDanger) {
                            $cardBorder = 'border-rose-200 hover:border-rose-300';
                            $bgGradient = 'bg-gradient-to-b from-white to-rose-50/30';
                            $titleColor = 'text-rose-900';
                            $badgeClass = 'bg-rose-100 text-rose-700 border-rose-200';
                            $barBg = 'bg-rose-100';
                            $barFill = 'bg-gradient-to-r from-rose-500 to-red-600 shadow-rose-500/50';
                            $icon = 'alert-octagon';
                            $iconColor = 'text-rose-500';
                        } elseif ($isWarning) {
                            $cardBorder = 'border-amber-200 hover:border-amber-300';
                            $bgGradient = 'bg-gradient-to-b from-white to-amber-50/30';
                            $titleColor = 'text-amber-900';
                            $badgeClass = 'bg-amber-100 text-amber-700 border-amber-200';
                            $barBg = 'bg-amber-100';
                            $barFill = 'bg-gradient-to-r from-amber-400 to-orange-500 shadow-amber-500/50';
                            $icon = 'alert-triangle';
                            $iconColor = 'text-amber-500';
                        } else {
                            $cardBorder = 'border-slate-200 hover:border-blue-300';
                            $bgGradient = 'bg-white';
                            $titleColor = 'text-slate-800';
                            $badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                            $barBg = 'bg-slate-100';
                            $barFill = 'bg-gradient-to-r from-blue-500 to-indigo-500 shadow-blue-500/50';
                            $icon = 'shield-check';
                            $iconColor = 'text-blue-500';
                        }
                    @endphp
                    
                    <a href="{{ route('student.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $subject['class_id'], 'from' => 'attendance-stats']) }}"
                       wire:navigate
                       class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border {{ $cardBorder }} {{ $bgGradient }} p-6 shadow-sm transition-all duration-300 hover:shadow-lg">
                       
                        <div class="mb-5 flex items-start justify-between gap-4">
                            <h3 class="{{ $titleColor }} text-[16px] font-bold leading-tight line-clamp-2">
                                {{ $subject['name'] }}
                            </h3>
                            <div class="shrink-0 flex h-9 w-9 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-900/5 group-hover:scale-110 transition-transform duration-300">
                                <x-user.icon :name="$icon" :size="16" class="{{ $iconColor }}" />
                            </div>
                        </div>

                        <div>
                            <div class="mb-3 flex items-end justify-between">
                                <span class="text-3xl font-black {{ $titleColor }}">{{ $percent }}%</span>
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-wider {{ $badgeClass }}">
                                    {{ $budgetLabel }}
                                </span>
                            </div>
                            
                            <div class="h-2.5 w-full overflow-hidden rounded-full {{ $barBg }} shadow-inner">
                                <div class="h-full rounded-full {{ $barFill }} transition-all duration-1000 shadow-[0_0_10px_rgba(0,0,0,0.2)]" style="width: {{ min(100, $percent) }}%"></div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Empty state --}}
    @if (empty($subjects))
        <section class="rounded-3xl border border-dashed border-slate-300 bg-slate-50/50 p-12 text-center mt-6">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200">
                <x-user.icon name="bar-chart-2" :size="28" class="text-slate-400" />
            </div>
            <h3 class="text-[18px] font-bold text-slate-800">Chưa có dữ liệu chuyên cần</h3>
            <p class="mt-2 text-[14px] text-slate-500 max-w-md mx-auto">Khi bạn tham gia lớp và giảng viên bắt đầu điểm danh, thống kê chi tiết của bạn sẽ xuất hiện tại đây.</p>
        </section>
    @endif

</div>
