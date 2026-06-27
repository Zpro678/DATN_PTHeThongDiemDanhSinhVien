<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="w-full max-w-none space-y-6">
        <!-- Header & Alerts -->
        <section class="overflow-hidden rounded-[2.5rem] border border-outline-variant/10 bg-white shadow-sm flex flex-col">
            <div class="flex flex-col justify-between gap-4 p-4 sm:p-5 md:flex-row md:items-center">
                <div>
                    <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-primary">
                        <x-user.icon name="bar-chart" :size="14" />
                        Học bạ chuyên cần
                    </div>
                    <h1 class="text-2xl font-extrabold uppercase tracking-tight text-slate-900">Thống kê chuyên cần</h1>
                </div>
                <a href="{{ route('student.attendance.history', ['ma_user' => auth()->id()]) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-surface-container-lowest border border-outline-variant/20 px-5 py-2.5 text-[13px] font-bold text-on-surface transition hover:bg-surface-container-low hover:shadow-sm shrink-0">
                    <x-user.icon name="history" :size="16" />
                    Lịch sử điểm danh
                </a>
            </div>

            @if ($isDemo)
                <div class="mx-4 mb-4 sm:mx-5 sm:mb-5 rounded-2xl border border-amber-200/60 bg-gradient-to-br from-amber-50 to-amber-100/50 px-5 py-3 text-[13px] font-medium text-amber-800 shadow-sm">
                    Đang hiển thị dữ liệu mẫu từ giao diện `develop_v1` để test khi tài khoản chưa có lớp.
                </div>
            @endif

            @if ($totals['warning_count'] > 0)
                <div class="mx-4 mb-4 sm:mx-5 sm:mb-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between rounded-2xl border-l-[6px] border-l-rose-500 border-y border-r border-rose-100 bg-gradient-to-r from-rose-50/80 to-white p-4 relative overflow-hidden shadow-sm">
                        <!-- Icon nền mờ -->
                        <div class="absolute -right-4 -top-6 text-rose-500/5 pointer-events-none rotate-12">
                            <x-user.icon name="alert-triangle" :size="120" />
                        </div>
                        
                        <div class="relative z-10 flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600 ring-4 ring-rose-50">
                                <x-user.icon name="alert-triangle" :size="20" stroke-width="2.5" />
                            </div>
                            <div class="pt-1">
                                <h3 class="text-[15px] font-black uppercase tracking-tight text-rose-900">
                                    Cảnh báo: Có {{ $totals['warning_count'] }} môn học nguy cơ cấm thi
                                </h3>
                                <p class="mt-1 max-w-2xl text-[13px] font-medium leading-relaxed text-rose-800/80">
                                    Bạn cần duy trì chuyên cần từ 80% trở lên. Hãy kiểm tra các môn dưới mức an toàn và nộp đơn minh chứng nếu vắng có lý do chính đáng.
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('student.leave-requests.create', ['ma_user' => auth()->id()]) }}" class="relative z-10 inline-flex items-center justify-center gap-2 rounded-xl bg-white border border-rose-200 px-5 py-2.5 text-[13px] font-bold text-rose-600 shadow-sm transition hover:bg-rose-50 hover:text-rose-700 active:scale-95 shrink-0 whitespace-nowrap">
                            Nộp đơn minh chứng
                        </a>
                    </div>
                </div>
            @endif
        </section>

        <!-- 4 Stats Cards -->
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Tỷ lệ chuyên cần', 'value' => $totals['percent'].'%', 'hint' => 'Trung bình tất cả môn', 'bg' => 'bg-primary/5', 'text' => 'text-primary', 'border' => 'border-primary/10'],
                ['label' => 'Số buổi có mặt', 'value' => $totals['present'] + $totals['late'] + $totals['excused'], 'hint' => 'Bao gồm muộn/có phép', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100'],
                ['label' => 'Số buổi vắng', 'value' => $totals['absent'], 'hint' => 'Vắng không phép/chưa hợp lệ', 'bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-100'],
                ['label' => 'Số buổi đi muộn', 'value' => $totals['late'], 'hint' => 'Đi trễ quá giờ quy định', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100'],
            ] as $card)
                <article class="flex flex-col justify-between overflow-hidden rounded-[2.5rem] border {{ $card['border'] }} bg-white shadow-sm transition hover:shadow-md">
                    <div class="p-5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">{{ $card['label'] }}</span>
                        <div class="mt-2 flex items-center justify-between">
                            <strong class="text-3xl font-black text-on-surface">{{ $card['value'] }}</strong>
                            <span class="flex h-10 w-10 items-center justify-center rounded-full {{ $card['bg'] }} {{ $card['text'] }}">
                                <x-user.icon name="bar-chart" :size="18" />
                            </span>
                        </div>
                    </div>
                    <div class="bg-surface-container-lowest px-5 py-2.5 border-t border-outline-variant/10">
                        <p class="text-[11px] font-bold text-on-surface-variant">{{ $card['hint'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <!-- Quỹ vắng an toàn -->
        @php
            // Xếp môn theo % tăng dần: môn rủi ro cao nhất lên đầu.
            $ranked = collect($subjects)
                ->filter(fn ($s) => ($s['planned_sessions'] ?? $s['total_sessions'] ?? 0) > 0 || ($s['total'] ?? 0) > 0)
                ->sortBy('percent')
                ->values();
        @endphp
        @if ($ranked->isNotEmpty())
            <section class="overflow-hidden rounded-[2.5rem] border border-outline-variant/10 bg-white shadow-sm">
                <div class="flex flex-col gap-1 border-b border-outline-variant/10 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-[22px] font-black text-on-surface">Quỹ vắng an toàn theo môn</h2>
                        <p class="mt-1 text-[13px] text-on-surface-variant">Số buổi bạn còn được phép vắng trước khi rớt mốc đủ điều kiện dự thi.</p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-surface-container-low px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">
                        <x-user.icon name="info" :size="13" />
                        Ngưỡng cấm thi: vắng &gt; 20% số buổi
                    </span>
                </div>

                <div class="flex flex-col divide-y divide-outline-variant/10 p-2 sm:p-3">
                    @foreach ($ranked as $subject)
                        @php
                            $percent = $subject['percent'] ?? 100;
                            $budgetLabel = $subject['absence_budget_label'] ?? 'Chưa có dữ liệu';
                            $budgetState = $subject['absence_budget_state'] ?? 'safe';

                            // Màu theo lớp (đồng bộ với bảng màu của thẻ môn học); lớp cảnh báo dùng tông đỏ.
                            $palette = [
                                ['text' => 'text-[#174ea6]', 'light' => 'bg-[#e8f0fe]', 'bar' => 'bg-[#174ea6]'],
                                ['text' => 'text-[#0f6e56]', 'light' => 'bg-[#defcf4]', 'bar' => 'bg-[#0f6e56]'],
                                ['text' => 'text-[#4c3fab]', 'light' => 'bg-[#efedfd]', 'bar' => 'bg-[#4c3fab]'],
                                ['text' => 'text-[#b45309]', 'light' => 'bg-[#fff1e0]', 'bar' => 'bg-[#b45309]'],
                                ['text' => 'text-[#9d174d]', 'light' => 'bg-[#fce7f1]', 'bar' => 'bg-[#9d174d]'],
                                ['text' => 'text-[#3730a3]', 'light' => 'bg-[#e7e9fd]', 'bar' => 'bg-[#3730a3]'],
                            ];
                            $warningTheme = ['text' => 'text-[#c5221f]', 'light' => 'bg-[#fce8e6]', 'bar' => 'bg-[#c5221f]'];
                            $zone = $subject['warning'] ? $warningTheme : $palette[((int) $subject['class_id']) % count($palette)];

                            if ($budgetState === 'danger') {
                                $budgetColor = 'text-[#a52714]';
                            } elseif ($budgetState === 'warning') {
                                $budgetColor = 'text-[#b45309]';
                            } else {
                                $budgetColor = $zone['text'];
                            }
                        @endphp
                        <a href="{{ route('student.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $subject['class_id'], 'from' => 'attendance-stats']) }}"
                           class="group flex items-center gap-3 rounded-2xl px-3 py-3.5 transition-colors hover:bg-surface-container-lowest sm:gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="mb-1.5 flex items-center justify-between gap-3">
                                    <span class="truncate text-[14px] font-bold text-on-surface">{{ $subject['name'] }}</span>
                                    <span class="shrink-0 text-[14px] font-black {{ $zone['text'] }}">{{ $percent }}%</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full {{ $zone['light'] }}">
                                    <div class="{{ $zone['bar'] }} h-full rounded-full transition-all duration-1000" style="width: {{ min(100, $percent) }}%"></div>
                                </div>
                                <span class="mt-2 block text-[12px] font-bold {{ $budgetColor }} sm:hidden">{{ $budgetLabel }}</span>
                            </div>
                            <span class="hidden w-[120px] shrink-0 text-right text-[12px] font-bold {{ $budgetColor }} sm:block">{{ $budgetLabel }}</span>
                            <x-user.icon name="chevron-right" :size="16" class="shrink-0 text-on-surface-variant/40 transition-colors group-hover:text-on-surface-variant" />
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Empty state khi chưa có dữ liệu chuyên cần --}}
        @if (empty($subjects))
            <section class="rounded-[2.5rem] border border-outline-variant/10 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <x-user.icon name="bar-chart" :size="24" />
                </div>
                <h3 class="text-base font-bold text-on-surface">Chưa có dữ liệu chuyên cần</h3>
                <p class="mt-2 text-sm text-on-surface-variant">Khi bạn tham gia lớp và có phiên điểm danh đã chốt, thống kê sẽ hiển thị tại đây.</p>
            </section>
        @endif

    </div>
</div>
