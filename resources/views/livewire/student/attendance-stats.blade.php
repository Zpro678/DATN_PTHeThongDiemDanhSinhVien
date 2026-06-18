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
                <a href="{{ route('student.attendance.history') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-surface-container-lowest border border-outline-variant/20 px-5 py-2.5 text-[13px] font-bold text-on-surface transition hover:bg-surface-container-low hover:shadow-sm shrink-0">
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

                        <a href="{{ route('student.leave-requests.create') }}" class="relative z-10 inline-flex items-center justify-center gap-2 rounded-xl bg-white border border-rose-200 px-5 py-2.5 text-[13px] font-bold text-rose-600 shadow-sm transition hover:bg-rose-50 hover:text-rose-700 active:scale-95 shrink-0 whitespace-nowrap">
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
                ['label' => 'Số buổi vắng', 'value' => $totals['absent'], 'hint' => 'Vắng không phép', 'bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-100'],
                ['label' => 'Số lần đi muộn', 'value' => $totals['late'], 'hint' => 'Đi trễ quá giờ quy định', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100'],
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

        <!-- Subjects List/Grid -->
        <section class="overflow-hidden rounded-[2.5rem] border border-outline-variant/10 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-outline-variant/10 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-[22px] font-black text-on-surface">Biến động chuyên cần theo môn học</h2>
                </div>
                <button type="button" wire:click="toggleView" class="inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/5 px-4 py-1.5 text-[11px] font-bold uppercase tracking-widest text-primary transition hover:bg-primary/10 shrink-0">
                    <x-user.icon :name="$showList ? 'blocks' : 'list'" :size="14" />
                    {{ $showList ? 'Xem dạng thẻ' : 'Xem dạng danh sách' }}
                </button>
            </div>

            <div class="p-4 sm:p-5 bg-surface-container-lowest/30">
                @if (! $showList)
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
                        @foreach ($subjects as $subject)
                            @php
                                $isWarning = $subject['warning'];
                                // Luôn dùng màu xanh cho header
                                $headerTheme = ['bg' => 'bg-[#124ca6]'];
                                
                                // Màu sắc cho các thành phần khác (badge, progress bar)
                                $activeTheme = $isWarning ? 
                                    ['bg' => 'bg-[#c5221f]', 'text' => 'text-[#c5221f]', 'light' => 'bg-[#fce8e6]'] : 
                                    ['bg' => 'bg-[#124ca6]', 'text' => 'text-[#124ca6]', 'light' => 'bg-[#e8f0fe]'];
                            @endphp

                            <article class="group relative flex flex-col overflow-hidden rounded-[1.25rem] border border-outline-variant/20 bg-white shadow-sm transition-all duration-300 hover:shadow-md">
                                <!-- Header -->
                                <div class="{{ $headerTheme['bg'] }} p-4 pb-6 text-white relative">
                                    <div class="flex items-start justify-between">
                                        <div class="pr-6">
                                            <h4 class="text-[18px] font-bold leading-tight line-clamp-1">
                                                <a href="#" class="hover:underline">{{ $subject['name'] }}</a>
                                            </h4>
                                            <div class="mt-1 text-[13px] text-white/90">
                                                {{ $subject['teacher'] }} · {{ $subject['semester'] }}
                                            </div>
                                        </div>
                                        <button class="absolute top-4 right-2 text-white hover:bg-white/10 rounded-full p-1.5 transition">
                                            <x-user.icon name="more-vertical" :size="20" />
                                        </button>
                                    </div>
                                </div>

                                <div class="flex flex-1 flex-col p-4 relative">
                                    <!-- Tags & Class Code -->
                                    <div class="mb-6 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="{{ $activeTheme['light'] }} {{ $activeTheme['text'] }} rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">
                                                {{ $isWarning ? 'CẢNH BÁO' : 'ĐANG HỌC' }}
                                            </span>
                                            <span class="bg-surface-container-low rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant flex items-center gap-1.5">
                                                <x-user.icon name="graduation-cap" :size="12" />
                                                HỌC VIÊN
                                            </span>
                                        </div>
                                        <span class="text-[12px] font-medium text-on-surface-variant">Mã lớp: <span class="font-bold text-on-surface">{{ $subject['code'] }}</span></span>
                                    </div>

                                    <!-- Stats & Progress -->
                                    <div class="flex flex-wrap-reverse items-end justify-between gap-y-4 gap-x-4 mt-3">
                                        <div class="flex flex-wrap gap-5 sm:gap-8">
                                            <div class="flex flex-col gap-2">
                                                <p class="text-[11px] font-bold uppercase text-on-surface-variant/70 whitespace-nowrap leading-none">Có mặt</p>
                                                <p class="text-[20px] font-black text-on-surface-variant leading-none">{{ $subject['present'] }}<span class="text-[14px] font-bold text-on-surface-variant/60">/{{ max(1, $subject['total']) }}</span></p>
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <p class="text-[11px] font-bold uppercase text-on-surface-variant/70 whitespace-nowrap leading-none">Vắng</p>
                                                <p class="text-[20px] font-black text-[#d93025] leading-none">{{ $subject['absent'] }}</p>
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <p class="text-[11px] font-bold uppercase text-on-surface-variant/70 whitespace-nowrap leading-none">Đi trễ</p>
                                                <p class="text-[20px] font-black text-[#f29900] leading-none">{{ $subject['late'] }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex-1 min-w-[140px] flex flex-col justify-start">
                                            <div class="flex items-start justify-between mb-3">
                                                <span class="text-[11px] font-bold uppercase text-on-surface-variant/70 leading-none">Chuyên cần</span>
                                                <span class="text-[16px] font-black {{ $activeTheme['text'] }} leading-none">{{ $subject['percent'] }}%</span>
                                            </div>
                                            <div class="h-2 w-full overflow-hidden rounded-full {{ $activeTheme['light'] }}">
                                                <div class="{{ $activeTheme['bg'] }} h-full rounded-full transition-all duration-1000" style="width: {{ min(100, $subject['percent']) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-8 border-t border-outline-variant/10 pt-4 flex items-center justify-between">
                                        <button class="flex items-center gap-2 rounded-full bg-[#5f6368] px-4 py-2 text-[13px] font-medium text-white transition hover:bg-[#5f6368]/90">
                                            <x-user.icon name="blocks" :size="16" />
                                            Chi tiết
                                        </button>
                                        <div class="flex items-center gap-4 text-on-surface-variant/70">
                                            <button class="hover:text-on-surface transition">
                                                <x-user.icon name="log-in" :size="20" />
                                            </button>
                                            <button class="hover:text-on-surface transition">
                                                <x-user.icon name="clock" :size="20" />
                                            </button>
                                            <button class="hover:text-on-surface transition">
                                                <x-user.icon name="file-text" :size="20" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($subjects as $subject)
                            <div @class([
                                'flex flex-col gap-3 rounded-2xl border p-4 transition sm:flex-row sm:items-center sm:justify-between',
                                'border-rose-200/60 bg-gradient-to-r from-rose-50/50 to-white' => $subject['warning'],
                                'border-outline-variant/10 bg-white' => ! $subject['warning'],
                            ])>
                                <div>
                                    <span class="inline-flex rounded-md bg-surface-container-low px-2 py-0.5 text-[10px] font-black uppercase text-on-surface-variant">{{ $subject['code'] }}</span>
                                    <h3 class="mt-1 text-[15px] font-black text-on-surface">{{ $subject['name'] }}</h3>
                                    <p class="mt-0.5 text-[11px] font-bold text-on-surface-variant">{{ $subject['teacher'] }} · {{ $subject['class_code'] }}</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <p class="text-[11px] font-bold text-on-surface-variant">{{ $subject['present'] + $subject['late'] + $subject['excused'] }}/{{ max(1, $subject['total']) }} buổi hợp lệ</p>
                                    </div>
                                    <span @class([
                                        'text-2xl font-black w-16 text-right',
                                        'text-rose-600' => $subject['warning'],
                                        'text-primary' => ! $subject['warning'],
                                    ])>{{ $subject['percent'] }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
