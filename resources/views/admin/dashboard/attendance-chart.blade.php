@php
    $data = [
        ['name' => 'T6/25', 'Chuyên cần' => 91.2, 'Vắng mặt' => 6.1, 'Đi muộn' => 2.7],
        ['name' => 'T7/25', 'Chuyên cần' => 93.4, 'Vắng mặt' => 4.8, 'Đi muộn' => 1.8],
        ['name' => 'T8/25', 'Chuyên cần' => 92.1, 'Vắng mặt' => 5.2, 'Đi muộn' => 2.7],
        ['name' => 'T9/25', 'Chuyên cần' => 95.8, 'Vắng mặt' => 3.1, 'Đi muộn' => 1.1],
        ['name' => 'T10/25', 'Chuyên cần' => 94.2, 'Vắng mặt' => 4.0, 'Đi muộn' => 1.8],
        ['name' => 'T11/25', 'Chuyên cần' => 93.9, 'Vắng mặt' => 4.2, 'Đi muộn' => 1.9],
        ['name' => 'T12/25', 'Chuyên cần' => 90.5, 'Vắng mặt' => 7.3, 'Đi muộn' => 2.2],
        ['name' => 'T1/26', 'Chuyên cần' => 89.2, 'Vắng mặt' => 8.5, 'Đi muộn' => 2.3],
        ['name' => 'T2/26', 'Chuyên cần' => 94.6, 'Vắng mặt' => 3.9, 'Đi muộn' => 1.5],
        ['name' => 'T3/26', 'Chuyên cần' => 95.1, 'Vắng mặt' => 3.4, 'Đi muộn' => 1.5],
        ['name' => 'T4/26', 'Chuyên cần' => 94.0, 'Vắng mặt' => 4.2, 'Đi muộn' => 1.8],
        ['name' => 'T5/26', 'Chuyên cần' => 93.2, 'Vắng mặt' => 4.8, 'Đi muộn' => 2.0],
    ];

    $series = [
        ['label' => 'Chuyên cần', 'key' => 'Chuyên cần', 'color' => '#2563eb', 'fill' => 'rgba(37, 99, 235, 0.12)', 'width' => 3],
        ['label' => 'Vắng mặt', 'key' => 'Vắng mặt', 'color' => '#ef4444', 'fill' => 'transparent', 'width' => 2],
        ['label' => 'Đi muộn', 'key' => 'Đi muộn', 'color' => '#f59e0b', 'fill' => 'transparent', 'width' => 2],
    ];

    $count = count($data);
    $width = 980;
    $height = 320;
    $paddingX = 44;
    $paddingY = 28;
    $chartWidth = $width - ($paddingX * 2);
    $chartHeight = $height - ($paddingY * 2);

    $buildPoints = function (string $key) use ($data, $paddingX, $paddingY, $chartWidth, $chartHeight, $count): array {
        $points = [];

        foreach ($data as $index => $item) {
            $x = $paddingX + (($count > 1 ? $index / ($count - 1) : 0) * $chartWidth);
            $y = $paddingY + (1 - ($item[$key] / 100)) * $chartHeight;
            $points[] = ['x' => $x, 'y' => $y, 'value' => $item[$key]];
        }

        return $points;
    };

    $buildPath = function (array $points) use ($height, $paddingY): string {
        $commands = [];

        foreach ($points as $index => $point) {
            $commands[] = sprintf('%s%0.1f,%0.1f', $index === 0 ? 'M ' : 'L ', $point['x'], $point['y']);
        }

        return implode(' ', $commands);
    };

    $primaryPoints = $buildPoints('Chuyên cần');
    $primaryPath = $buildPath($primaryPoints);
    $areaPath = $primaryPath . sprintf(' L %0.1f,%0.1f L %0.1f,%0.1f Z', $primaryPoints[array_key_last($primaryPoints)]['x'], $height - $paddingY, $primaryPoints[0]['x'], $height - $paddingY);
@endphp

<div class="admin-card admin-card-hover overflow-hidden rounded-2xl border p-6">
    <div class="relative z-10 mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-extrabold tracking-tight text-slate-900">
                Phân tích Chi tiết Chuyên cần
            </h2>
            <p class="mt-0.5 text-xs font-semibold text-slate-400">
                Biểu đồ chuyên cần, tỷ lệ nghỉ học và đi muộn trung bình suốt 12 tháng học tập qua.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-xs font-extrabold text-slate-500">
            @foreach ($series as $item)
                <span class="flex items-center gap-1.5">
                    <span class="block h-2.5 w-2.5 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                    {{ $item['label'] }}
                </span>
            @endforeach
        </div>
    </div>

    <div class="relative z-10 overflow-hidden rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
        <svg viewBox="0 0 {{ $width }} {{ $height }}" class="h-[320px] w-full">
            @for ($tick = 0; $tick <= 5; $tick++)
                @php
                    $y = $paddingY + (($chartHeight / 5) * $tick);
                    $value = 100 - ($tick * 20);
                @endphp
                <line x1="{{ $paddingX }}" y1="{{ $y }}" x2="{{ $width - $paddingX }}" y2="{{ $y }}" stroke="#e2e8f0" stroke-dasharray="4 6" />
                <text x="10" y="{{ $y + 4 }}" fill="#94a3b8" font-size="10" font-weight="700">{{ $value }}%</text>
            @endfor

            @foreach ($series as $item)
                @php
                    $points = $buildPoints($item['key']);
                    $path = $buildPath($points);
                    $strokeLinecap = 'round';
                    $strokeLinejoin = 'round';
                    $finalX = $points[array_key_last($points)]['x'];
                    $baseline = $height - $paddingY;
                @endphp

                @if ($item['fill'] !== 'transparent')
                    <path d="{{ $path }} L {{ $finalX }},{{ $baseline }} L {{ $points[0]['x'] }},{{ $baseline }} Z" fill="{{ $item['fill'] }}" />
                @endif

                <path d="{{ $path }}" fill="none" stroke="{{ $item['color'] }}" stroke-width="{{ $item['width'] }}" stroke-linecap="{{ $strokeLinecap }}" stroke-linejoin="{{ $strokeLinejoin }}" />

                @foreach ($points as $point)
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="{{ $item['key'] === 'Chuyên cần' ? 4 : 3 }}" fill="#fff" stroke="{{ $item['color'] }}" stroke-width="2" />
                @endforeach
            @endforeach

            @foreach ($data as $index => $item)
                @php
                    $x = $paddingX + (($count > 1 ? $index / ($count - 1) : 0) * $chartWidth);
                @endphp
                <text x="{{ $x }}" y="{{ $height - 4 }}" text-anchor="middle" fill="#94a3b8" font-size="10" font-weight="700">{{ $item['name'] }}</text>
            @endforeach
        </svg>
    </div>
</div>
