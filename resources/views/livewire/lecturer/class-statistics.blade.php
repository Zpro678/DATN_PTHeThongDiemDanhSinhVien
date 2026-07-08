<div class="flex-1 overflow-y-auto space-y-6 px-6 py-6 pb-24 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">
    {{-- Summary cards --}}
    @php
        $cards = [
            ['label' => 'Tổng học viên',    'value' => $totalStudents,                             'sub' => 'đang hoạt động',                    'icon' => 'users',         'color' => 'text-tertiary',  'bg' => 'bg-tertiary/10'],
            ['label' => 'Buổi đã chốt',     'value' => "{$closedCount}/{$totalSessions}",          'sub' => 'buổi đã chốt sổ',                   'icon' => 'calendar-check','color' => 'text-secondary', 'bg' => 'bg-secondary/10'],
            ['label' => 'Tiến độ buổi học', 'value' => "{$studiedSessions}/{$plannedSessions}",      'sub' => 'buổi đã học / kế hoạch',            'icon' => 'book-open',     'color' => 'text-primary',   'bg' => 'bg-primary/10'],
            ['label' => 'CC trung bình',    'value' => "{$avgAttendance}%",                        'sub' => 'chuyên cần toàn lớp',               'icon' => 'bar-chart-2',   'color' => ($avgAttendance < 80 ? 'text-error' : ($avgAttendance < 85 ? 'text-amber-500' : 'text-tertiary')), 'bg' => ($avgAttendance < 80 ? 'bg-error/10' : ($avgAttendance < 85 ? 'bg-amber-500/10' : 'bg-tertiary/10'))],
            ['label' => 'Cần chú ý',        'value' => $bannedCount + $warningCount,               'sub' => "{$bannedCount} cấm thi · {$warningCount} cảnh báo", 'icon' => 'alert-triangle','color' => ($bannedCount > 0 ? 'text-error' : ($warningCount > 0 ? 'text-amber-500' : 'text-tertiary')), 'bg' => ($bannedCount > 0 ? 'bg-error/10' : ($warningCount > 0 ? 'bg-amber-500/10' : 'bg-tertiary/10'))],
            ['label' => 'Phép vắng / SV',   'value' => $allowedAbsent,                            'sub' => 'buổi được phép vắng (20%)',         'icon' => 'shield',        'color' => 'text-primary',   'bg' => 'bg-primary/10'],
        ];
    @endphp
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
        @foreach ($cards as $card)
            <div class="flex flex-col gap-2 rounded-2xl bg-white p-5 border border-slate-100 shadow-sm">
                <div class="{{ $card['bg'] }} {{ $card['color'] }} flex h-12 w-12 items-center justify-center rounded-xl">
                    <x-user.icon :name="$card['icon']" :size="22" />
                </div>
                <div class="mt-1">
                    <p class="text-sm font-semibold text-on-surface-variant">{{ $card['label'] }}</p>
                    <p class="mt-1 text-2xl font-black text-on-surface">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs text-on-surface-variant/80">{{ $card['sub'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Main content --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Học viên cần chú ý --}}
        <div class="flex flex-col">
            <h4 class="mb-4 flex items-center gap-2 text-base font-bold text-on-surface">
                <x-user.icon name="alert-triangle" class="text-error" :size="20" />
                Học viên cần chú ý
                @if ($bannedCount + $warningCount > 0)
                    <span class="ml-auto rounded-full bg-error/10 px-2.5 py-0.5 text-xs font-bold text-error">{{ $bannedCount + $warningCount }}</span>
                @endif
            </h4>
            <div class="flex flex-1 flex-col rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                @if ($alertStudents->isEmpty())
                    <div class="flex flex-1 flex-col items-center justify-center gap-2 py-8 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-tertiary/10 text-tertiary">
                            <x-user.icon name="check-circle" :size="24" />
                        </div>
                        <p class="text-base font-semibold text-on-surface">Tất cả ổn định</p>
                        <p class="text-sm text-on-surface-variant">Không có học viên nào cần chú ý.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($alertStudents as $row)
                            @php
                                $s       = $row['stats'];
                                $m       = $row['member'];
                                $banned  = $s['is_banned'];
                                $pct     = $s['attendance_percent'];
                                $present = $s['present_sessions'];
                                $absent  = $s['absent_sessions'];
                                $planned = $s['planned_sessions'];
                            @endphp
                            <a href="{{ route('lecturer.students.show', $m->id) }}" wire:navigate
                                class="flex items-center gap-3 rounded-xl border p-3 {{ $banned ? 'border-error/20 bg-error/5' : 'border-amber-500/20 bg-amber-500/5' }}">
                                @if($m->user && $m->user->avatar)
                                    <img src="{{ $m->user->avatar_url }}" alt="{{ $m->full_name }}" class="h-9 w-9 shrink-0 rounded-full object-cover">
                                @else
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $banned ? 'bg-error/15 text-error' : 'bg-amber-500/15 text-amber-600' }} text-sm font-bold">
                                        {{ mb_strtoupper(mb_substr($m->full_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-on-surface">{{ $m->full_name }}</p>
                                    <p class="text-[11px] text-on-surface-variant">{{ $m->student_code }} · CC: <span class="{{ $banned ? 'text-error font-bold' : 'text-amber-600 font-semibold' }}">{{ $pct }}%</span></p>
                                </div>
                                <div class="shrink-0 text-right">
                                    @if ($banned)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-error/10 px-2 py-0.5 text-[10px] font-bold text-error">
                                            <x-user.icon name="alert-triangle" :size="10" /> Cấm thi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-600">
                                            <x-user.icon name="alert-circle" :size="10" /> Cảnh báo
                                        </span>
                                    @endif
                                    <p class="mt-0.5 text-[10px] text-on-surface-variant">Vắng {{ $absent }}/{{ $planned }} buổi</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('lecturer.students.index') }}" wire:navigate
                        class="mt-3 flex items-center justify-center gap-1.5 rounded-xl border border-outline-variant/20 py-2 text-xs font-semibold text-on-surface-variant">
                        Xem tất cả học viên <x-user.icon name="arrow-right" :size="13" />
                    </a>
                @endif
            </div>
        </div>

        {{-- Biểu đồ chuyên cần theo buổi --}}
        <div class="flex flex-col lg:col-span-2">
            <h4 class="mb-4 flex items-center gap-2 text-base font-bold text-on-surface">
                <x-user.icon name="trending-up" class="text-tertiary" :size="20" />
                Tỉ lệ có mặt theo buổi
                <span class="ml-auto text-xs font-normal text-on-surface-variant">{{ $closedCount }} buổi đã chốt</span>
            </h4>
            <div class="flex flex-1 flex-col justify-center rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                @if (empty($sessionChart))
                    <div class="flex flex-1 flex-col items-center justify-center gap-2 py-10 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <x-user.icon name="bar-chart-2" :size="24" />
                        </div>
                        <p class="text-base font-semibold text-on-surface">Chưa có buổi nào chốt sổ</p>
                        <p class="text-sm text-on-surface-variant">Sau khi chốt buổi điểm danh, biểu đồ sẽ hiển thị tại đây.</p>
                    </div>
                @else
                    {{-- Vùng biểu đồ: trục % bên trái + các cột --}}
                    <div class="flex gap-3">
                        {{-- Trục Y --}}
                        <div class="flex h-56 w-8 shrink-0 flex-col justify-between py-1 text-right text-[10px] text-on-surface-variant/60">
                            <span>100%</span>
                            <span>75%</span>
                            <span>50%</span>
                            <span>25%</span>
                            <span>0%</span>
                        </div>

                        {{-- Khu vực cột với đường lưới ngang --}}
                        <div class="relative h-56 flex-1">
                            {{-- Gridlines --}}
                            <div class="pointer-events-none absolute inset-0 flex flex-col justify-between">
                                @for ($i = 0; $i < 5; $i++)
                                    <div class="border-t border-dashed border-outline-variant/15"></div>
                                @endfor
                            </div>

                            {{-- Cột --}}
                            <div class="absolute inset-0 flex items-end justify-around gap-3 px-2">
                                @foreach ($sessionChart as $session)
                                    @php
                                        $rate  = $session['attendance_rate'];
                                        $color = $rate < 70 ? 'bg-error' : ($rate < 85 ? 'bg-amber-500' : 'bg-tertiary');
                                        $barH  = max($rate, 3);
                                    @endphp
                                    <div class="group relative flex h-full max-w-[64px] flex-1 flex-col items-center justify-end">
                                        {{-- Tooltip --}}
                                        <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden w-max -translate-x-1/2 rounded-lg bg-on-surface px-2.5 py-1.5 text-center text-[11px] font-bold text-white shadow-lg group-hover:block">
                                            {{ $session['name'] }}<br>
                                            <span class="font-normal">{{ $session['present'] }}/{{ $session['total'] }} HV có mặt</span>
                                            <div class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-on-surface"></div>
                                        </div>
                                        {{-- % trên đầu cột --}}
                                        <span class="mb-1 text-[11px] font-bold {{ $rate < 70 ? 'text-error' : ($rate < 85 ? 'text-amber-500' : 'text-tertiary') }}">{{ $rate }}%</span>
                                        {{-- Cột --}}
                                        <div class="{{ $color }} w-full rounded-t-md opacity-85 transition-all group-hover:opacity-100"
                                            style="height: {{ $barH }}%"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Nhãn buổi --}}
                    <div class="ml-11 mt-2 flex justify-around gap-3 px-2">
                        @foreach ($sessionChart as $session)
                            <span class="max-w-[64px] flex-1 truncate text-center text-[11px] font-medium text-on-surface-variant">{{ $session['date'] }}</span>
                        @endforeach
                    </div>

                    {{-- Legend --}}
                    <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-outline-variant/10 pt-3 text-[11px] text-on-surface-variant">
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-tertiary"></span>≥ 85%</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>70–84%</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-error"></span>&lt; 70%</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Danh sách đầy đủ học viên --}}
    <div>
        <h4 class="mb-4 flex items-center gap-2 text-base font-bold text-on-surface">
            Danh sách học viên ({{ $totalStudents }})
            <span class="ml-auto text-xs font-normal text-on-surface-variant">Sắp xếp theo tên A-Z</span>
        </h4>
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            @if ($allStudents->isEmpty())
                <div class="py-12 text-center text-base text-on-surface-variant">Chưa có học viên nào trong lớp.</div>
            @else
                <div class="overflow-x-auto {{ $allStudents->count() > 30 ? 'max-h-[800px] overflow-y-auto relative' : '' }}">
                    <table class="w-full text-base">
                        <thead>
                            <tr class="border-b border-outline-variant/10 bg-surface-container-low/50 text-left text-sm font-bold uppercase tracking-wider text-on-surface {{ $allStudents->count() > 30 ? 'sticky top-0 z-10' : '' }}">
                                <th class="px-4 py-3 text-center w-16">STT</th>
                                <th class="px-4 py-3">Học viên</th>
                                <th class="px-4 py-3 text-center">CC (%)</th>
                                <th class="px-4 py-3 text-center">Có mặt</th>
                                <th class="px-4 py-3 text-center">Muộn</th>
                                <th class="px-4 py-3 text-center">Vắng KP</th>
                                <th class="px-4 py-3 text-center">Vắng CP</th>
                                <th class="px-4 py-3 text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/8">
                            @foreach ($allStudents as $row)
                                @php
                                    $s      = $row['stats'];
                                    $m      = $row['member'];
                                    $pct    = $s ? $s['attendance_percent'] : 100;
                                    $banned = $s && $s['is_banned'];
                                    $warn   = $s && $s['is_warning'] && !$banned;
                                    $barW   = max(min($pct, 100), 0);
                                    $barColor = $pct < 80 ? 'bg-error' : ($pct < 85 ? 'bg-amber-500' : 'bg-tertiary');
                                @endphp
                                <tr @class([
                                    'bg-error/5' => $banned,
                                    'bg-amber-50/40' => $warn,
                                ])>
                                    <td class="px-4 py-3 text-center text-sm font-bold text-on-surface-variant">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('lecturer.students.show', $m->id) }}" wire:navigate
                                            class="flex items-center gap-3">
                                            @if($m->user && $m->user->avatar)
                                                <img src="{{ $m->user->avatar_url }}" alt="{{ $m->full_name }}" class="h-10 w-10 shrink-0 rounded-full object-cover">
                                            @else
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $banned ? 'bg-error/15 text-error' : ($warn ? 'bg-amber-500/15 text-amber-600' : 'bg-primary/10 text-primary') }} text-sm font-bold">
                                                    {{ mb_strtoupper(mb_substr($m->full_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="truncate text-base font-semibold text-on-surface">{{ $m->full_name }}</p>
                                                <p class="text-sm text-on-surface-variant">{{ $m->student_code }}</p>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="font-bold {{ $pct < 80 ? 'text-error' : ($pct < 85 ? 'text-amber-500' : 'text-tertiary') }}">{{ $pct }}%</span>
                                            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-surface-container-high">
                                                <div class="{{ $barColor }} h-full rounded-full transition-all" style="width:{{ $barW }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-on-surface">{{ $s ? $s['present_sessions'] : '—' }}<span class="text-on-surface-variant/60">/{{ $s ? $s['planned_sessions'] : '—' }}</span></td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($s && $s['late_count'] > 0)
                                            <span class="font-medium text-secondary">{{ $s['late_count'] }}x</span>
                                        @else
                                            <span class="text-on-surface-variant/40">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($s && $s['absent_sessions'] > 0)
                                            <span class="font-medium text-error">{{ $s['absent_sessions'] }}</span>
                                        @else
                                            <span class="text-on-surface-variant/40">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($s && $s['excused_sessions'] > 0)
                                            <span class="font-medium text-primary">{{ $s['excused_sessions'] }}</span>
                                        @else
                                            <span class="text-on-surface-variant/40">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($banned)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-error/10 px-3 py-1.5 text-xs font-bold text-error">
                                                <x-user.icon name="alert-triangle" :size="14" /> Cấm thi
                                            </span>
                                        @elseif ($warn)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-3 py-1.5 text-xs font-bold text-amber-600">
                                                <x-user.icon name="alert-circle" :size="14" /> Cảnh báo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-tertiary/10 px-3 py-1.5 text-xs font-bold text-tertiary">
                                                <x-user.icon name="check-circle" :size="14" /> Ổn định
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
