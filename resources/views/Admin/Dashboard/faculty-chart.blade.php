<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    
    <!-- CARD 1: Phân bố sinh viên theo khoa -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div>
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                Phân bố sinh viên theo khoa
            </h2>
            <p class="text-xs font-semibold text-slate-400 dark:text-slate-400 mt-0.5">
                Phần trăm sinh viên chính quy đăng ký môn học và hoạt động trong học kỳ này.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 mt-6">
            <div class="w-48 h-48 shrink-0 relative flex items-center justify-center">
                <!-- Inner Absolute Center Text for Donut styling representation -->
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                    <span class="text-3xl font-black text-slate-900 dark:text-white leading-none">
                        10k+
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                        Sinh viên
                    </span>
                </div>

                <canvas id="studentPieChart"></canvas>
            </div>

            <!-- Color-Coded Indicators list -->
            <div class="space-y-2.5 w-full">
                @php
                    $studentData = [
                        ['name' => 'Công nghệ thông tin', 'value' => 4500, 'color' => '#2563eb'],
                        ['name' => 'Cơ khí', 'value' => 1800, 'color' => '#f59e0b'],
                        ['name' => 'Điện - Điện tử', 'value' => 2300, 'color' => '#10b981'],
                        ['name' => 'Kinh tế - Quản trị', 'value' => 1000, 'color' => '#8b5cf6'],
                        ['name' => 'Khoa Ngoại ngữ', 'value' => 400, 'color' => '#f43f5e'],
                    ];
                    $totalStudents = array_sum(array_column($studentData, 'value'));
                @endphp

                @foreach($studentData as $item)
                    @php
                        $percentage = number_format(($item['value'] / $totalStudents) * 100, 1);
                    @endphp
                    <div class="flex items-center justify-between text-xs py-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item['color'] }}"></span>
                            <span class="font-bold text-slate-650 dark:text-slate-300 truncate max-w-[140px]">
                                {{ $item['name'] }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="font-extrabold text-slate-800 dark:text-slate-100 block">
                                {{ number_format($item['value']) }} SV
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 block leading-none mt-0.5">
                                {{ $percentage }}%
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CARD 2: Phân bố lớp học theo khoa -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div>
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                Phân bố lớp học theo khoa
            </h2>
            <p class="text-xs font-semibold text-slate-400 dark:text-slate-400 mt-0.5">
                Sự phân chia các mã lớp chuyên ngành và lớp đại cương trong kỳ giảng dạy.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 mt-6">
            <div class="w-48 h-48 shrink-0 relative flex items-center justify-center">
                <!-- Inner Absolute Center Text -->
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                    <span class="text-3xl font-black text-slate-900 dark:text-white leading-none">
                        300
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                        Lớp học
                    </span>
                </div>

                <canvas id="classPieChart"></canvas>
            </div>

            <!-- Color-Coded Indicators list -->
            <div class="space-y-2.5 w-full">
                @php
                    $classData = [
                        ['name' => 'Công nghệ thông tin', 'value' => 120, 'color' => '#2563eb'],
                        ['name' => 'Cơ khí', 'value' => 65, 'color' => '#f59e0b'],
                        ['name' => 'Điện - Điện tử', 'value' => 80, 'color' => '#10b981'],
                        ['name' => 'Kinh tế - Quản trị', 'value' => 25, 'color' => '#8b5cf6'],
                        ['name' => 'Khoa Ngoại ngữ', 'value' => 10, 'color' => '#f43f5e'],
                    ];
                    $totalClasses = array_sum(array_column($classData, 'value'));
                @endphp

                @foreach($classData as $item)
                    @php
                        $percentage = number_format(($item['value'] / $totalClasses) * 100, 1);
                    @endphp
                    <div class="flex items-center justify-between text-xs py-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item['color'] }}"></span>
                            <span class="font-bold text-slate-650 dark:text-slate-300 truncate max-w-[140px]">
                                {{ $item['name'] }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="font-extrabold text-slate-800 dark:text-slate-100 block">
                                {{ $item['value'] }} lớp
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 block leading-none mt-0.5">
                                {{ $percentage }}%
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const createDoughnutChart = (canvasId, dataLabels, dataValues, backgroundColors) => {
            const ctx = document.getElementById(canvasId).getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: dataLabels,
                    datasets: [{
                        data: dataValues,
                        backgroundColor: backgroundColors,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%', // Tỉ lệ lỗ ở giữa biểu đồ (donut)
                    plugins: {
                        legend: {
                            display: false // Ẩn legend mặc định
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#fff',
                            bodyColor: '#94a3b8',
                            borderColor: '#1e293b',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return ' Dữ liệu: ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        };

        const labels = ['Công nghệ thông tin', 'Cơ khí', 'Điện - Điện tử', 'Kinh tế - Quản trị', 'Khoa Ngoại ngữ'];
        const colors = ['#2563eb', '#f59e0b', '#10b981', '#8b5cf6', '#f43f5e'];
        
        createDoughnutChart('studentPieChart', labels, [4500, 1800, 2300, 1000, 400], colors);
        createDoughnutChart('classPieChart', labels, [120, 65, 80, 25, 10], colors);
    });
</script>
