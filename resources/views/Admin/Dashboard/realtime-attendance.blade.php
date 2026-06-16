@php
$activeSessions = [
    [
        'className' => "Hệ thống thông tin quản lý (MIS-302)",
        'room' => "Tòa A - Phòng 402",
        'instructor' => "ThS. Đỗ Thị Lan",
        'checkedIn' => 48,
        'total' => 50,
        'startedAt' => "15 phút trước",
        'online' => true,
        'percentage' => 96,
        'pin' => "437-915"
    ],
    [
        'className' => "Cấu trúc dữ liệu và giải thuật (DSA-102)",
        'room' => "Khu B - Phòng Tự thực hành",
        'instructor' => "TS. Nguyễn Thanh Hải",
        'checkedIn' => 72,
        'total' => 80,
        'startedAt' => "8 phút trước",
        'online' => true,
        'percentage' => 90,
        'pin' => "882-104"
    ],
    [
        'className' => "Ngữ pháp tiếng Anh chuyên ngành (ENG-229)",
        'room' => "Cơ sở 2 - Phòng C112",
        'instructor' => "Cô Katherine Phạm",
        'checkedIn' => 22,
        'total' => 45,
        'startedAt' => "3 phút trước",
        'online' => true,
        'percentage' => 48.8,
        'pin' => "159-402"
    ]
];
@endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between h-full">
    <div>
        <div class="flex items-start sm:items-center justify-between gap-4 mb-6 pb-3 border-b border-slate-100 dark:border-slate-800/85">
            <div class="flex items-start sm:items-center gap-2">
                <span class="relative flex h-3.5 w-3.5 shrink-0 mt-1 sm:mt-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                </span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Giám sát điểm danh trực tuyến
                    </h2>
                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-400 mt-0.5">
                        Nhật ký thực địa lớp học đang kích hoạt mã điểm danh QR/vị trí.
                    </p>
                </div>
            </div>

            <span class="text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-1 rounded-md tracking-wider uppercase shrink-0">
                3 PHIÊN LIVE
            </span>
        </div>

        <div class="space-y-4">
            @foreach($activeSessions as $session)
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-900/40 hover:border-slate-200 dark:hover:border-slate-700 transition-all space-y-3">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-800 dark:text-slate-200 line-clamp-1">
                                {{ $session['className'] }}
                            </h3>
                            <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-wide">
                                {{ $session['room'] }} &middot; GV: <span class="text-slate-600 dark:text-slate-300">{{ $session['instructor'] }}</span>
                            </p>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="font-mono text-xs font-black bg-blue-50 dark:bg-blue-900/30 text-blue-600 px-2.5 py-1 rounded-lg border border-blue-100 dark:border-blue-800/50">
                                PIN: {{ $session['pin'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Progress and status indicators -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400 dark:text-slate-500 font-bold flex items-center gap-1">
                                <x-sams.icon name="users" class="w-3.5 h-3.5 text-slate-400" />
                                Đã điểm danh: <strong class="text-slate-700 dark:text-slate-300">{{ $session['checkedIn'] }}/{{ $session['total'] }}</strong>
                            </span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400">
                                {{ $session['percentage'] }}%
                            </span>
                        </div>

                        <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-emerald-500 rounded-full transition-all duration-500" style="width: {{ $session['percentage'] }}%"></div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-[10px] text-slate-400 font-bold pt-1">
                        <span class="flex items-center gap-1">
                            <x-sams.icon name="clock" class="w-3 h-3" />
                            Mở từ: {{ $session['startedAt'] }}
                        </span>

                        <span class="inline-flex items-center gap-1 text-emerald-500 font-extrabold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            KẾT NỐI ONLINE
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 text-center">
        <button type="button" class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline flex items-center justify-center gap-1.5 mx-auto cursor-pointer">
            <span>Xem bản đồ vị trí GPS & QR động</span>
            <x-sams.icon name="arrow-right" class="w-3.5 h-3.5" />
        </button>
    </div>
</div>
