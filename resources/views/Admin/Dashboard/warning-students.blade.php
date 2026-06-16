@php
$students = [
    [
        'mssv' => '0306211029',
        'name' => 'Lê Minh Huy',
        'class' => 'CD_CNTT21A',
        'subject' => 'Hệ quản trị CSDL',
        'attendanceRate' => 62.5,
        'level' => 'Nguy cấp (Cấm thi)',
        'levelColor' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border-rose-200 dark:border-rose-800'
    ],
    [
        'mssv' => '0306231108',
        'name' => 'Trần Văn Hoàng',
        'class' => 'CD_CNTT23B',
        'subject' => 'Lập trình Web nâng cao',
        'attendanceRate' => 74.2,
        'level' => 'Cảnh cáo (Gần giới hạn)',
        'levelColor' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border-amber-200 dark:border-amber-800'
    ],
    [
        'mssv' => '0306221544',
        'name' => 'Phạm Thị Thúy',
        'class' => 'CD_DKH22C',
        'subject' => 'Kỹ thuật vi điều khiển',
        'attendanceRate' => 78.0,
        'level' => 'Nhắc nhở nhẹ',
        'levelColor' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400 border-blue-200 dark:border-blue-800'
    ]
];
@endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm overflow-hidden flex flex-col justify-between h-full">
    <div>
        <!-- Card Header section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800/85">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/20 flex items-center justify-center text-rose-500 shrink-0">
                    <x-sams.icon name="shield-alert" class="w-4.5 h-4.5" />
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Sinh viên có nguy cơ cấm thi
                    </h2>
                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-400 mt-0.5">
                        Danh sách những bạn có tỷ lệ đi học thấp hơn 80% (quy định của nhà trường).
                    </p>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button" class="px-3.5 py-2 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 flex items-center gap-1.5 cursor-pointer">
                    <x-sams.icon name="file-down" class="w-3.5 h-3.5" />
                    <span>Xuất Excel</span>
                </button>
            </div>
        </div>

        <!-- Responsive Table Grid -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-extrabold tracking-wider uppercase text-[10px]">
                        <th class="py-3 px-4 whitespace-nowrap">MSSV</th>
                        <th class="py-3 px-4 whitespace-nowrap">Họ và tên</th>
                        <th class="py-3 px-4 whitespace-nowrap">Lớp chính</th>
                        <th class="py-3 px-4 whitespace-nowrap">Môn giảng dạy</th>
                        <th class="py-3 px-4 text-center whitespace-nowrap">Tỷ lệ đi học</th>
                        <th class="py-3 px-4 min-w-[160px] whitespace-nowrap">Đánh giá chung</th>
                        <th class="py-3 px-4 text-right whitespace-nowrap">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    @foreach($students as $item)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-black text-slate-500 dark:text-slate-400">
                                {{ $item['mssv'] }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-800 dark:text-slate-200">
                                {{ $item['name'] }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-500 dark:text-slate-400">
                                {{ $item['class'] }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-600 dark:text-slate-300 truncate max-w-[150px]">
                                {{ $item['subject'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-black text-xs {{ $item['attendanceRate'] < 70 ? 'text-rose-600' : 'text-amber-600' }}">
                                    {{ $item['attendanceRate'] }}%
                                </span>
                                <div class="w-16 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mt-1.5 mx-auto overflow-hidden">
                                    <div 
                                        class="h-full rounded-full {{ $item['attendanceRate'] < 70 ? 'bg-rose-500' : 'bg-amber-500' }}"
                                        style="width: {{ $item['attendanceRate'] }}%"
                                    ></div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg border {{ $item['levelColor'] }}">
                                    {{ $item['level'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button
                                    type="button"
                                    onclick="alert('Chi tiết cảnh báo sinh viên: {{ $item['name'] }}\nThông báo nhắc nhở tự động đã được chuyển qua SMS/Email công tác.')"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 hover:underline cursor-pointer"
                                >
                                    <span>Xem</span>
                                    <x-sams.icon name="arrow-up-right" class="w-3.5 h-3.5" />
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-400 font-bold">
        <span>Hiển thị 3 / 84 trường hợp khẩn cấp</span>
        <button type="button" class="text-blue-600 hover:text-blue-700 hover:underline cursor-pointer">
            Quản lý toàn bộ cảnh báo chuyên cần &rarr;
        </button>
    </div>
</div>
