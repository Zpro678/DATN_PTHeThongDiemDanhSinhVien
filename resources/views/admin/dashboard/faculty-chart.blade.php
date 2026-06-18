@php
    $buildGradient = function (array $items): string {
        $total = array_sum(array_column($items, 'value'));
        $start = 0;
        $segments = [];

        foreach ($items as $item) {
            $end = $start + (($item['value'] / $total) * 100);
            $segments[] = sprintf('%s %0.2f%% %0.2f%%', $item['color'], $start, $end);
            $start = $end;
        }

        return implode(', ', $segments);
    };

    $studentData = [
        ['name' => 'Công nghệ thông tin', 'value' => 4500, 'color' => '#2563eb'],
        ['name' => 'Cơ khí', 'value' => 1800, 'color' => '#f59e0b'],
        ['name' => 'Điện - Điện tử', 'value' => 2300, 'color' => '#10b981'],
        ['name' => 'Kinh tế - Quản trị', 'value' => 1000, 'color' => '#8b5cf6'],
        ['name' => 'Khoa Ngoại ngữ', 'value' => 400, 'color' => '#f43f5e'],
    ];
    $classData = [
        ['name' => 'Công nghệ thông tin', 'value' => 120, 'color' => '#2563eb'],
        ['name' => 'Cơ khí', 'value' => 65, 'color' => '#f59e0b'],
        ['name' => 'Điện - Điện tử', 'value' => 80, 'color' => '#10b981'],
        ['name' => 'Kinh tế - Quản trị', 'value' => 25, 'color' => '#8b5cf6'],
        ['name' => 'Khoa Ngoại ngữ', 'value' => 10, 'color' => '#f43f5e'],
    ];
    $studentTotal = array_sum(array_column($studentData, 'value'));
    $classTotal = array_sum(array_column($classData, 'value'));
@endphp

<div class="admin-grid-equal grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="admin-card admin-card-hover overflow-hidden rounded-2xl border p-6">
        <div class="relative z-10">
            <h2 class="text-base font-extrabold tracking-tight text-slate-900">
                Phân bố sinh viên theo khoa
            </h2>
            <p class="mt-0.5 text-xs font-semibold text-slate-400">
                Phần trăm sinh viên chính quy đăng ký môn học và hoạt động trong học kỳ này.
            </p>
        </div>

        <div class="relative z-10 mt-6 flex flex-col items-center justify-between gap-6 sm:flex-row">
            <div class="relative flex h-48 w-48 shrink-0 items-center justify-center">
                <div class="absolute inset-0 rounded-full" style="background: conic-gradient({{ $buildGradient($studentData) }});"></div>
                <div class="absolute inset-[18px] rounded-full bg-white shadow-inner"></div>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-3xl font-black leading-none text-slate-900">10k+</span>
                    <span class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Sinh viên</span>
                </div>
            </div>

            <div class="w-full space-y-2.5">
                @foreach ($studentData as $item)
                    @php
                        $percentage = number_format(($item['value'] / $studentTotal) * 100, 1);
                    @endphp
                    <div class="flex items-center justify-between gap-4 text-xs">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                            <span class="max-w-[180px] truncate font-bold text-slate-700">{{ $item['name'] }}</span>
                        </div>
                        <div class="text-right">
                            <span class="block font-extrabold text-slate-800">{{ number_format($item['value']) }} SV</span>
                            <span class="mt-0.5 block text-[10px] font-bold leading-none text-slate-400">{{ $percentage }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="admin-card admin-card-hover overflow-hidden rounded-2xl border p-6">
        <div class="relative z-10">
            <h2 class="text-base font-extrabold tracking-tight text-slate-900">
                Phân bố lớp học theo khoa
            </h2>
            <p class="mt-0.5 text-xs font-semibold text-slate-400">
                Sự phân chia các mã lớp chuyên ngành và lớp đại cương trong kỳ giảng dạy.
            </p>
        </div>

        <div class="relative z-10 mt-6 flex flex-col items-center justify-between gap-6 sm:flex-row">
            <div class="relative flex h-48 w-48 shrink-0 items-center justify-center">
                <div class="absolute inset-0 rounded-full" style="background: conic-gradient({{ $buildGradient($classData) }});"></div>
                <div class="absolute inset-[18px] rounded-full bg-white shadow-inner"></div>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-3xl font-black leading-none text-slate-900">300</span>
                    <span class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Lớp học</span>
                </div>
            </div>

            <div class="w-full space-y-2.5">
                @foreach ($classData as $item)
                    @php
                        $percentage = number_format(($item['value'] / $classTotal) * 100, 1);
                    @endphp
                    <div class="flex items-center justify-between gap-4 text-xs">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                            <span class="max-w-[180px] truncate font-bold text-slate-700">{{ $item['name'] }}</span>
                        </div>
                        <div class="text-right">
                            <span class="block font-extrabold text-slate-800">{{ $item['value'] }} lớp</span>
                            <span class="mt-0.5 block text-[10px] font-bold leading-none text-slate-400">{{ $percentage }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
