<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">
                Phân tích Chi tiết Chuyên cần
            </h2>
            <p class="text-xs font-semibold text-slate-400 dark:text-slate-400 mt-0.5">
                Biểu đồ chuyên cần, tỷ lệ nghỉ học và đi muộn trung bình suốt 12 tháng học tập qua.
            </p>
        </div>

        <!-- Legend pills -->
        <div class="flex items-center gap-3.5 text-xs font-extrabold text-slate-500">
            <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 block"></span>
                Chuyên cần
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 block"></span>
                Vắng mặt
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 block"></span>
                Đi muộn
            </span>
        </div>
    </div>

    <div class="w-full h-80 relative">
        <canvas id="attendanceChart"></canvas>
    </div>
</div>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        
        const data = [
            { name: 'T6/25', 'Chuyên cần': 91.2, 'Vắng mặt': 6.1, 'Đi muộn': 2.7 },
            { name: 'T7/25', 'Chuyên cần': 93.4, 'Vắng mặt': 4.8, 'Đi muộn': 1.8 },
            { name: 'T8/25', 'Chuyên cần': 92.1, 'Vắng mặt': 5.2, 'Đi muộn': 2.7 },
            { name: 'T9/25', 'Chuyên cần': 95.8, 'Vắng mặt': 3.1, 'Đi muộn': 1.1 },
            { name: 'T10/25', 'Chuyên cần': 94.2, 'Vắng mặt': 4.0, 'Đi muộn': 1.8 },
            { name: 'T11/25', 'Chuyên cần': 93.9, 'Vắng mặt': 4.2, 'Đi muộn': 1.9 },
            { name: 'T12/25', 'Chuyên cần': 90.5, 'Vắng mặt': 7.3, 'Đi muộn': 2.2 },
            { name: 'T1/26', 'Chuyên cần': 89.2, 'Vắng mặt': 8.5, 'Đi muộn': 2.3 },
            { name: 'T2/26', 'Chuyên cần': 94.6, 'Vắng mặt': 3.9, 'Đi muộn': 1.5 },
            { name: 'T3/26', 'Chuyên cần': 95.1, 'Vắng mặt': 3.4, 'Đi muộn': 1.5 },
            { name: 'T4/26', 'Chuyên cần': 94.0, 'Vắng mặt': 4.2, 'Đi muộn': 1.8 },
            { name: 'T5/26', 'Chuyên cần': 93.2, 'Vắng mặt': 4.8, 'Đi muộn': 2.0 }
        ];

        const labels = data.map(d => d.name);
        const chuyenCan = data.map(d => d['Chuyên cần']);
        const vangMat = data.map(d => d['Vắng mặt']);
        const diMuon = data.map(d => d['Đi muộn']);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Chuyên cần',
                        data: chuyenCan,
                        borderColor: '#2563eb', // blue-600
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#2563eb',
                        pointBorderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'Vắng mặt',
                        data: vangMat,
                        borderColor: '#ef4444', // rose-500
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'Đi muộn',
                        data: diMuon,
                        borderColor: '#f59e0b', // amber-500
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        tension: 0.4,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false // Ẩn legend mặc định vì đã có HTML legend bên trên
                    },
                    tooltip: {
                        backgroundColor: '#0f172a', // slate-900
                        titleColor: '#94a3b8', // slate-400
                        bodyColor: '#f8fafc',
                        borderColor: '#1e293b', // slate-800
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                size: 10,
                                weight: 'bold'
                            }
                        }
                    },
                    y: {
                        min: 0,
                        max: 100,
                        grid: {
                            color: '#f1f5f9', // slate-100
                            drawBorder: false,
                            borderDash: [3, 3]
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                size: 10,
                                weight: 'bold'
                            },
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
