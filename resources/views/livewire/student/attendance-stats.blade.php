<div class="min-h-full bg-slate-50/60 px-4 py-6 pb-24 sm:px-6 xl:px-8">
    <div class="w-full max-w-none space-y-6">
        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-black uppercase tracking-[0.2em] text-blue-700">
                        <x-user.icon name="bar-chart" :size="16" />
                        Học bạ chuyên cần
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-950">Thống kê chuyên cần & phân tích học bạ</h1>
                    <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-slate-500">Báo cáo hiệu suất chuyên cần cá nhân, xu hướng học tập và cảnh báo rủi ro theo từng học phần.</p>
                </div>
                <a href="{{ route('student.attendance.history') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                    <x-user.icon name="history" :size="18" />
                    Lịch sử điểm danh
                </a>
            </div>
        </section>

        @if ($isDemo)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-800">
                Đang hiển thị dữ liệu mẫu từ giao diện `develop_v1` để test khi tài khoản chưa có lớp.
            </div>
        @endif

        @if ($totals['warning_count'] > 0)
            <section class="flex flex-col gap-4 rounded-[2rem] border border-rose-200 bg-rose-50 p-5 text-rose-950 shadow-sm lg:flex-row lg:items-center lg:justify-between">
                <div class="flex gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">
                        <x-user.icon name="alert-triangle" :size="24" />
                    </span>
                    <div>
                        <h2 class="text-base font-black uppercase tracking-tight">Cảnh báo: {{ $totals['warning_count'] }} môn học đang có nguy cơ cấm thi</h2>
                        <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 text-rose-800/80">Sinh viên cần duy trì chuyên cần từ 80% trở lên. Hãy kiểm tra các môn có màu đỏ và nộp đơn minh chứng nếu có lý do chính đáng.</p>
                    </div>
                </div>
                <a href="{{ route('lecturer.leave-requests.index') }}" class="inline-flex justify-center rounded-2xl bg-rose-600 px-5 py-3 text-sm font-black text-white transition hover:bg-rose-700">Kiểm tra đơn nghỉ</a>
            </section>
        @endif

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Tỷ lệ chuyên cần', 'value' => $totals['percent'].'%', 'hint' => 'Trung bình tất cả môn học', 'class' => 'text-blue-600 bg-blue-50'],
                ['label' => 'Số buổi có mặt', 'value' => $totals['present'] + $totals['late'] + $totals['excused'], 'hint' => 'Bao gồm muộn và có phép', 'class' => 'text-emerald-600 bg-emerald-50'],
                ['label' => 'Số buổi vắng', 'value' => $totals['absent'], 'hint' => 'Vắng không phép', 'class' => 'text-rose-600 bg-rose-50'],
                ['label' => 'Số lần đi muộn', 'value' => $totals['late'], 'hint' => 'Đi trễ quá giờ quy định', 'class' => 'text-amber-600 bg-amber-50'],
            ] as $card)
                <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-500">{{ $card['label'] }}</span>
                    <div class="mt-4 flex items-end justify-between gap-3">
                        <strong class="text-3xl font-black leading-none {{ str($card['class'])->before(' ') }}">{{ $card['value'] }}</strong>
                        <span class="{{ $card['class'] }} rounded-2xl px-3 py-2">
                            <x-user.icon name="bar-chart" :size="20" />
                        </span>
                    </div>
                    <p class="mt-3 border-t border-slate-100 pt-3 text-xs font-semibold text-slate-500">{{ $card['hint'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm lg:p-6">
            <div class="mb-5 flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-black uppercase tracking-wide text-slate-950">Biến động chuyên cần theo môn học</h2>
                    <p class="mt-1 text-xs font-semibold text-slate-500">Thống kê chi tiết số buổi và tỷ lệ chuyên cần tích lũy của từng học phần.</p>
                </div>
                <button type="button" wire:click="toggleView" class="rounded-2xl bg-blue-50 px-4 py-2 text-sm font-black text-blue-700 transition hover:bg-blue-100">
                    {{ $showList ? 'Xem dạng thẻ' : 'Xem dạng danh sách' }}
                </button>
            </div>

            @if (! $showList)
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($subjects as $subject)
                        <article @class([
                            'group overflow-hidden rounded-[2rem] border bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg',
                            'border-rose-200' => $subject['warning'],
                            'border-slate-200' => ! $subject['warning'],
                        ])>
                            <div @class([
                                'p-5 text-white',
                                'bg-gradient-to-br from-rose-500 to-rose-700' => $subject['warning'],
                                'bg-gradient-to-br from-blue-600 to-indigo-700' => ! $subject['warning'],
                            ])>
                                <span class="rounded-xl bg-white/15 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider">{{ $subject['code'] }}</span>
                                <h3 class="mt-8 min-h-[48px] text-lg font-black leading-tight">{{ $subject['name'] }}</h3>
                            </div>
                            <div class="space-y-4 p-5">
                                <p class="text-xs font-bold text-slate-500">{{ $subject['teacher'] }} · {{ $subject['semester'] }}</p>
                                <div class="flex items-end justify-between">
                                    <div>
                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Tỷ lệ</span>
                                        <p @class([
                                            'mt-1 text-3xl font-black',
                                            'text-rose-600' => $subject['warning'],
                                            'text-blue-600' => ! $subject['warning'],
                                        ])>{{ $subject['percent'] }}%</p>
                                    </div>
                                    <span @class([
                                        'rounded-full px-3 py-1 text-xs font-black uppercase',
                                        'bg-rose-50 text-rose-700' => $subject['warning'],
                                        'bg-emerald-50 text-emerald-700' => ! $subject['warning'],
                                    ])>{{ $subject['warning'] ? 'Cảnh báo' : 'Ổn định' }}</span>
                                </div>
                                <div>
                                    <div class="mb-2 flex justify-between text-[11px] font-black uppercase text-slate-400">
                                        <span>Chuyên cần</span>
                                        <span>{{ $subject['present'] + $subject['late'] + $subject['excused'] }}/{{ max(1, $subject['total']) }}</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div @class([
                                            'h-full rounded-full',
                                            'bg-rose-500' => $subject['warning'],
                                            'bg-blue-600' => ! $subject['warning'],
                                        ]) style="width: {{ min(100, $subject['percent']) }}%"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center text-xs font-black">
                                    <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-700">Có mặt<br>{{ $subject['present'] }}</div>
                                    <div class="rounded-2xl bg-amber-50 p-3 text-amber-700">Muộn<br>{{ $subject['late'] }}</div>
                                    <div class="rounded-2xl bg-rose-50 p-3 text-rose-700">Vắng<br>{{ $subject['absent'] }}</div>
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
                            'border-rose-200 bg-rose-50/50' => $subject['warning'],
                            'border-slate-200 bg-slate-50/50' => ! $subject['warning'],
                        ])>
                            <div>
                                <span class="rounded-lg bg-blue-50 px-2 py-0.5 text-[10px] font-black uppercase text-blue-700">{{ $subject['code'] }}</span>
                                <h3 class="mt-2 text-sm font-black text-slate-950">{{ $subject['name'] }}</h3>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $subject['teacher'] }} · {{ $subject['class_code'] }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span @class([
                                    'text-2xl font-black',
                                    'text-rose-600' => $subject['warning'],
                                    'text-blue-600' => ! $subject['warning'],
                                ])>{{ $subject['percent'] }}%</span>
                                <span class="text-xs font-bold text-slate-500">{{ $subject['present'] + $subject['late'] + $subject['excused'] }}/{{ max(1, $subject['total']) }} buổi hợp lệ</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
