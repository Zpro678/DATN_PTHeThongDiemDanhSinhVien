@php
    $activeSessions = [
        [
            'className' => 'Hệ thống thông tin quản lý (MIS-302)',
            'room' => 'Tòa A - Phòng 402',
            'instructor' => 'ThS. Đỗ Thị Lan',
            'checkedIn' => 48,
            'total' => 50,
            'startedAt' => '15 phút trước',
            'percentage' => 96,
            'pin' => '437-915',
        ],
        [
            'className' => 'Cấu trúc dữ liệu và giải thuật (DSA-102)',
            'room' => 'Khu B - Phòng Tự thực hành',
            'instructor' => 'TS. Nguyễn Thanh Hải',
            'checkedIn' => 72,
            'total' => 80,
            'startedAt' => '8 phút trước',
            'percentage' => 90,
            'pin' => '882-104',
        ],
        [
            'className' => 'Ngữ pháp tiếng Anh chuyên ngành (ENG-229)',
            'room' => 'Cơ sở 2 - Phòng C112',
            'instructor' => 'Cô Katherine Phạm',
            'checkedIn' => 22,
            'total' => 45,
            'startedAt' => '3 phút trước',
            'percentage' => 48.8,
            'pin' => '159-402',
        ],
    ];
@endphp

<div class="admin-card admin-card-hover flex h-full min-h-[430px] flex-col justify-between overflow-hidden rounded-2xl border p-6">
    <div class="relative z-10">
        <div class="mb-6 flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
            <div class="flex items-start gap-2">
                <span class="relative mt-1 flex h-3.5 w-3.5 shrink-0">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-3.5 w-3.5 rounded-full bg-emerald-500"></span>
                </span>
                <div>
                    <h2 class="text-base font-extrabold tracking-tight text-slate-900">
                        Giám sát điểm danh trực tuyến
                    </h2>
                    <p class="mt-0.5 text-xs font-semibold text-slate-400">
                        Nhật ký thực địa lớp học đang kích hoạt mã điểm danh QR/vị trí.
                    </p>
                </div>
            </div>

            <span class="shrink-0 rounded-md bg-slate-100 px-2 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-600">
                3 PHIÊN LIVE
            </span>
        </div>

        <div class="space-y-4">
            @foreach ($activeSessions as $session)
                <div class="space-y-3 rounded-xl border border-slate-100 bg-white/75 p-4 transition-all hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/40 hover:shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="line-clamp-1 text-xs font-extrabold text-slate-800">
                                {{ $session['className'] }}
                            </h3>
                            <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                {{ $session['room'] }} &middot; GV: <span class="text-slate-600">{{ $session['instructor'] }}</span>
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <span class="rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-1 font-mono text-xs font-black text-blue-600">
                                PIN: {{ $session['pin'] }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-1 font-bold text-slate-400">
                                <x-user.icon name="users" :size="14" class="text-slate-400" />
                                Đã điểm danh: <strong class="text-slate-700">{{ $session['checkedIn'] }}/{{ $session['total'] }}</strong>
                            </span>
                            <span class="font-black text-emerald-600">
                                {{ $session['percentage'] }}%
                            </span>
                        </div>

                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-emerald-500 transition-all duration-500" style="width: {{ $session['percentage'] }}%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 text-[10px] font-bold text-slate-400">
                        <span class="flex items-center gap-1">
                            <x-user.icon name="clock" :size="12" />
                            Mở từ: {{ $session['startedAt'] }}
                        </span>

                        <span class="inline-flex items-center gap-1 font-extrabold text-emerald-500">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            KẾT NỐI ONLINE
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="relative z-10 mt-5 border-t border-slate-100 pt-4 text-center">
        <button type="button" class="mx-auto inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline">
            <span>Xem bản đồ vị trí GPS & QR động</span>
            <x-user.icon name="arrow-right" :size="14" />
        </button>
    </div>
</div>
