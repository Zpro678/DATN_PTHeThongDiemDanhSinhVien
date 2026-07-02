@php
    $buildGradient = function (array $items): string {
        $total = array_sum(array_column($items, 'value'));
        if ($total == 0) return '#e2e8f0 0% 100%';
        $start = 0;
        $segments = [];

        foreach ($items as $item) {
            if ($item['value'] == 0) continue;
            $end = $start + (($item['value'] / $total) * 100);
            $segments[] = sprintf('%s %0.2f%% %0.2f%%', $item['color'], $start, $end);
            $start = $end;
        }

        return implode(', ', $segments);
    };

    $attendanceData = [
        ['name' => 'Có mặt', 'value' => $distribution['present'], 'color' => '#10b981'], // emerald-500
        ['name' => 'Đi trễ', 'value' => $distribution['late'], 'color' => '#f59e0b'], // amber-500
        ['name' => 'Vắng mặt', 'value' => $distribution['absent'], 'color' => '#f43f5e'], // rose-500
        ['name' => 'Có phép', 'value' => $distribution['excused'], 'color' => '#3b82f6'], // blue-500
    ];
    
    $attendanceTotal = array_sum(array_column($attendanceData, 'value'));
@endphp

<div class="h-full">
    <div class="admin-card admin-card-hover overflow-hidden rounded-2xl border p-6 h-full flex flex-col justify-center">
        <div class="relative z-10">
            <h2 class="text-base font-extrabold tracking-tight text-slate-900">
                Phân bố trạng thái điểm danh
            </h2>
            <p class="mt-0.5 text-xs font-semibold text-slate-400">
                Tỷ lệ các trạng thái điểm danh của học viên trên toàn hệ thống.
            </p>
        </div>

        <div class="relative z-10 mt-6 flex flex-col items-center justify-center gap-12 sm:flex-row">
            <div class="relative flex h-48 w-48 shrink-0 items-center justify-center">
                <div class="absolute inset-0 rounded-full" style="background: conic-gradient({{ $buildGradient($attendanceData) }});"></div>
                <div class="absolute inset-[18px] rounded-full bg-white shadow-inner"></div>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-2xl font-black leading-none text-slate-900">{{ number_format($attendanceTotal) }}</span>
                    <span class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Lượt</span>
                </div>
            </div>

            <div class="w-full max-w-sm space-y-3">
                @foreach ($attendanceData as $item)
                    @php
                        $percentage = $attendanceTotal > 0 ? number_format(($item['value'] / $attendanceTotal) * 100, 1) : 0;
                    @endphp
                    <div class="flex items-center justify-between gap-4 text-xs">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                            <span class="max-w-[180px] truncate font-bold text-slate-700">{{ $item['name'] }}</span>
                        </div>
                        <div class="text-right">
                            <span class="block font-extrabold text-slate-800">{{ number_format($item['value']) }} lượt</span>
                            <span class="mt-0.5 block text-[10px] font-bold leading-none text-slate-400">{{ $percentage }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
