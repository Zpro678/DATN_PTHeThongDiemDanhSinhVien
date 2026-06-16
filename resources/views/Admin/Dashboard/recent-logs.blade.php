@php
$logs = [
    [
        'action' => "Đăng nhập",
        'user' => "gv_nguyennhat@school.edu.vn",
        'ip' => "115.79.132.40",
        'time' => "Hôm nay, 12:41",
        'device' => "Mac OS - Chrome v121",
        'status' => "Thành công",
        'ok' => true,
        'icon' => "key",
        'iconColor' => "text-blue-600",
        'color' => "bg-blue-50 dark:bg-blue-950/30"
    ],
    [
        'action' => "Tạo lớp học",
        'user' => "gv_hoangminh@school.edu.vn",
        'ip' => "14.161.45.19",
        'time' => "Hôm nay, 11:24",
        'device' => "Windows 11 - Edge v119",
        'status' => "Thành công",
        'ok' => true,
        'icon' => "plus-square",
        'iconColor' => "text-cyan-600",
        'color' => "bg-cyan-50 dark:bg-cyan-950/30"
    ],
    [
        'action' => "Điểm danh",
        'user' => "0306231108 (SV: Trần Văn Hoàng)",
        'ip' => "27.72.105.101",
        'time' => "Hôm nay, 11:15",
        'device' => "Safari Mobile - iPhone 14",
        'status' => "Khớp GPS động",
        'ok' => true,
        'icon' => "user-check",
        'iconColor' => "text-emerald-600",
        'color' => "bg-emerald-50 dark:bg-emerald-950/30"
    ],
    [
        'action' => "Khóa tài khoản",
        'user' => "sys_admin_lock (Hệ thống tự động)",
        'ip' => "10.0.4.15",
        'time' => "Hôm nay, 09:30",
        'device' => "AWS Lambda Worker #2",
        'status' => "Khoá tạm thời",
        'ok' => false,
        'icon' => "lock",
        'iconColor' => "text-rose-600",
        'color' => "bg-rose-50 dark:bg-rose-950/30"
    ],
    [
        'action' => "Mở khóa tài khoản",
        'user' => "admin_huy@school.edu.vn (Admin)",
        'ip' => "115.79.132.89",
        'time' => "Hôm qua, 17:02",
        'device' => "Ubuntu 22.04 - Firefox",
        'status' => "Thành công",
        'ok' => true,
        'icon' => "unlock",
        'iconColor' => "text-purple-600",
        'color' => "bg-purple-50 dark:bg-purple-950/30"
    ],
    [
        'action' => "Đăng xuất",
        'user' => "gv_dangphi@school.edu.vn",
        'ip' => "171.244.20.141",
        'time' => "Hôm qua, 15:40",
        'device' => "Mac OS - Safari v17",
        'status' => "Đã hủy phiên",
        'ok' => true,
        'icon' => "globe",
        'iconColor' => "text-slate-500",
        'color' => "bg-slate-100 dark:bg-slate-800"
    ]
];
@endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm overflow-hidden flex flex-col justify-between h-full">
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800/85">
            <div class="flex items-start sm:items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/30 flex items-center justify-center text-blue-600 shrink-0">
                    <x-sams.icon name="shield-check" class="w-4.5 h-4.5" />
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Nhật ký kiểm toán & Nhật ký hệ thống
                    </h2>
                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-400 mt-0.5">
                        Ghi chép lịch sử phiên bảo mật đăng nhập, cấp quyền và thao tác điểm danh quan trọng.
                    </p>
                </div>
            </div>
            
            <button type="button" onclick="alert('Xuất nhật ký hệ thống log file dạng CSV...')" class="px-3 py-1.5 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg text-[10px] font-extrabold text-slate-500 uppercase tracking-widest cursor-pointer shrink-0">
                Xuất Logs 30 Ngày
            </button>
        </div>

        <!-- Audit Log list -->
        <div class="space-y-3.5">
            @foreach($logs as $log)
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 p-3.5 rounded-xl border border-slate-100/70 dark:border-slate-800 hover:bg-slate-50/40 dark:hover:bg-slate-800/40 transition-all text-xs">
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ $log['color'] }}">
                            <x-sams.icon name="{{ $log['icon'] }}" class="w-3.5 h-3.5 {{ $log['iconColor'] }}" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-slate-800 dark:text-slate-100">{{ $log['action'] }}</span>
                                <span class="text-[10px] text-slate-400 font-bold">&#8226;</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold">{{ $log['time'] }}</span>
                            </div>
                            
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mt-1">
                                Người thực hiện: <span class="text-slate-800 dark:text-slate-200 break-all">{{ $log['user'] }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between md:text-right gap-6 pt-2 md:pt-0 border-t md:border-t-0 border-slate-50 dark:border-slate-800">
                        <div class="text-left md:text-right">
                            <span class="text-[10px] text-slate-500 font-bold block">{{ $log['ip'] }}</span>
                            <span class="text-[9px] text-slate-400 font-bold block mt-0.5">{{ $log['device'] }}</span>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold {{ $log['ok'] ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400' }}">
                            {{ $log['status'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400 font-bold">
        <span>Tổng cộng: 1,482 logs trong phiên học máy</span>
        <button type="button" class="text-blue-600 hover:text-blue-700 hover:underline cursor-pointer">
            Truy cập trung tâm nhật ký &rarr;
        </button>
    </div>
</div>
