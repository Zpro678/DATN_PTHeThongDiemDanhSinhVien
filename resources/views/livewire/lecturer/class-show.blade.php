<div x-data="{ showImportModal: false, showShareModal: false, showBan: false, banConfirm: { open: false, id: null, name: '' } }" wire:poll.2s class="w-full space-y-6 px-6 py-6 pb-24 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- Header --}}
    <div class="mb-6 flex justify-end">
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center w-full sm:w-auto">
            <button
                type="button"
                class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900"
                wire:click="openImport"
            >
                <x-user.icon name="upload" :size="16" />
                <span>Import</span>
            </button>
            <x-user.export-button
                label="Xuất Excel"
                :can="$canExportExcel"
                action="exportExcel"
                class="w-full sm:w-auto" />
            <button
                type="button"
                class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900"
                x-on:click="showShareModal = true"
            >
                <x-user.icon name="send" :size="16" />
                <span>Chia sẻ</span>
            </button>
            <a
                href="{{ route('lecturer.classes.settings', $class) }}"
                wire:navigate
                class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900"
            >
                <x-user.icon name="settings" :size="18" />
                Cài đặt lớp
            </a>
        </div>
    </div>

@php
    $colorOptions = [
        'bg-[#475569]', 'bg-[#1D4ED8]', 'bg-[#0F766E]', 'bg-[#4338CA]',
        'bg-[#047857]', 'bg-[#0369A1]', 'bg-[#6D28D9]', 'bg-[#B45309]',
    ];
    $colorIndex = hexdec(substr(md5((string) $class->id), 0, 8));
    $themeColor = $colorOptions[$colorIndex % count($colorOptions)];
    $progressPct = $sessionsCount > 0 ? round(($sessionsCompleted / $sessionsCount) * 100) : 0;

    $statusMeta = match ($class->status) {
        'active'   => ['label' => 'Đang hoạt động', 'dot' => 'bg-emerald-300', 'ping' => true],
        'archived' => ['label' => 'Lưu trữ',        'dot' => 'bg-amber-300',   'ping' => false],
        default    => ['label' => 'Đã kết thúc',    'dot' => 'bg-slate-300',   'ping' => false],
    };
@endphp

    {{-- Thông tin lớp & Hành động nhanh --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row">
        {{-- Thông tin lớp --}}
        <div class="relative flex-1 overflow-hidden rounded-3xl {{ $themeColor }} p-6 shadow-lg shadow-slate-900/10 sm:p-8">
            {{-- Hoạ tiết nền mềm --}}
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white/15 via-transparent to-black/25"></div>
            <div class="pointer-events-none absolute -right-16 -top-24 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-12 h-56 w-56 rounded-full bg-black/10 blur-2xl"></div>

            <div class="relative">
                {{-- Tiêu đề --}}
                <div class="mb-7 flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-inner ring-1 ring-white/25 backdrop-blur-sm">
                            <x-user.icon name="book-open" :size="26" />
                        </div>
                        <div class="min-w-0">
                            <h1 class="truncate text-2xl font-bold text-white sm:text-3xl">{{ $class->name }}</h1>
                            <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-1 text-sm font-semibold text-white shadow-sm backdrop-blur-sm">
                                <x-user.icon name="hash" :size="13" /> {{ $class->class_code ?? $class->join_key }}
                            </span>
                        </div>
                    </div>

                    {{-- Trạng thái --}}
                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold text-white shadow-sm ring-1 ring-white/20 backdrop-blur-sm"
                        title="Trạng thái lớp">
                        <span class="relative flex h-2 w-2">
                            @if($statusMeta['ping'])
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $statusMeta['dot'] }} opacity-75"></span>
                            @endif
                            <span class="relative inline-flex h-2 w-2 rounded-full {{ $statusMeta['dot'] }}"></span>
                        </span>
                        {{ $statusMeta['label'] }}
                    </span>
                </div>

                {{-- Các ô chỉ số --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-2xl bg-white/10 p-4 shadow-sm ring-1 ring-white/10 backdrop-blur-sm transition-colors hover:bg-white/15">
                        <div class="flex items-center gap-2 text-white/80">
                            <x-user.icon name="users" :size="15" />
                            <span class="text-xs font-medium sm:text-sm">Sĩ số</span>
                        </div>
                        <p class="mt-2 text-2xl font-black text-white sm:text-3xl">{{ $studentsCount }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 shadow-sm ring-1 ring-white/10 backdrop-blur-sm transition-colors hover:bg-white/15">
                        <div class="flex items-center gap-2 text-white/80">
                            <x-user.icon name="calendar" :size="15" />
                            <span class="text-xs font-medium sm:text-sm">Dự kiến</span>
                        </div>
                        <p class="mt-2 text-2xl font-black text-white sm:text-3xl">{{ $class->total_sessions }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 shadow-sm ring-1 ring-white/10 backdrop-blur-sm transition-colors hover:bg-white/15">
                        <div class="flex items-center gap-2 text-white/80">
                            <x-user.icon name="clipboard-check" :size="15" />
                            <span class="text-xs font-medium sm:text-sm">Đã ĐĐ</span>
                        </div>
                        <p class="mt-2 text-2xl font-black text-white sm:text-3xl">{{ $sessionsCompleted }}</p>
                    </div>
                </div>

                {{-- Thanh tiến độ --}}
                <div class="mt-5">
                    <div class="mb-2 flex items-center justify-between text-xs font-semibold text-white/85">
                        <span class="inline-flex items-center gap-1.5">
                            <x-user.icon name="trending-up" :size="14" /> Tiến độ điểm danh
                        </span>
                        <span>{{ $progressPct }}%</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-black/15">
                        <div class="h-full rounded-full bg-white/85 shadow-sm transition-all duration-500"
                            style="width: {{ min($progressPct, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-2 grid-rows-2 gap-3 text-center lg:w-[320px] lg:shrink-0 lg:self-stretch">
            <button type="button" wire:click="checkBeforeAttendance('qr')" class="group flex h-full min-h-[108px] flex-col items-center justify-center gap-2.5 rounded-3xl border border-blue-200 bg-gradient-to-b from-blue-50 to-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md hover:shadow-blue-500/10">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:bg-blue-200">
                    <x-user.icon name="qr-code" :size="20" />
                </div>
                <span class="text-[13px] font-bold text-blue-700">QR</span>
            </button>
            <button type="button" wire:click="checkBeforeAttendance('manual')" class="group flex h-full min-h-[108px] flex-col items-center justify-center gap-2.5 rounded-3xl border border-emerald-200 bg-gradient-to-b from-emerald-50 to-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md hover:shadow-emerald-500/10">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:bg-emerald-200">
                    <x-user.icon name="check-square" :size="20" />
                </div>
                <span class="text-[13px] font-bold text-emerald-700">Thủ công</span>
            </button>
            <a href="{{ route('lecturer.classes.attendance', $class->id) }}" wire:navigate class="group flex h-full min-h-[108px] flex-col items-center justify-center gap-2.5 rounded-3xl border border-rose-200 bg-gradient-to-b from-rose-50 to-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-rose-300 hover:shadow-md hover:shadow-rose-500/10">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:bg-rose-200">
                    <x-user.icon name="history" :size="20" />
                </div>
                <span class="text-[13px] font-bold leading-tight text-rose-700">Lịch sử ĐD</span>
            </a>
            <a href="{{ route('lecturer.class.statistics', ['class_id' => $class->id]) }}" wire:navigate class="group flex h-full min-h-[108px] flex-col items-center justify-center gap-2.5 rounded-3xl border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md hover:shadow-slate-500/10">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:bg-slate-200">
                    <x-user.icon name="bar-chart-2" :size="20" />
                </div>
                <span class="text-[13px] font-bold text-slate-700">Thống kê</span>
            </a>
        </div>
    </div>

    {{-- Progress Bar Chạy Ngầm --}}
    @if($isImportingStatus)
        <div class="mx-1 mt-6 p-4 bg-blue-50/80 rounded-[20px] border border-blue-100 flex flex-col gap-2 shadow-sm" wire:poll.500ms="checkImportProgress">
            <div class="flex justify-between text-[13px] font-bold text-blue-700">
                <span class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Tạo lớp thành công. Danh sách sinh viên đang được import
                </span>
                <span>{{ $importProcessedRows }}/{{ $importTotalRows }}</span>
            </div>
            <div class="w-full bg-blue-100 rounded-full h-2 mt-1">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $importTotalRows > 0 ? ($importProcessedRows / $importTotalRows) * 100 : 0 }}%"></div>
            </div>
        </div>
    @endif

    {{-- Danh sách học viên --}}
    <div class="mt-8 mb-4 flex items-center justify-between px-1">
        <h2 class="text-lg font-bold text-slate-800">Danh sách học viên ({{ $studentsCount }})</h2>
        <div class="flex items-center gap-4">
            {{-- Công tắc chung: ẩn/hiện nút "Cấm thi"/"Gửi cảnh báo" ở cột Hành động (mặc định ẩn, chỉ hiển thị phía GV, không lưu). --}}
            <div class="flex items-center gap-2" title="Hiển thị sinh viên cấm thi">
                <span class="text-sm font-medium text-slate-500">Hiển thị sinh viên cấm thi</span>
                <button type="button" @click="showBan = !showBan"
                    :class="showBan ? 'bg-primary' : 'bg-outline-variant/50'"
                    class="relative h-6 w-12 shrink-0 rounded-full transition-colors"
                    :aria-pressed="showBan">
                    <span :class="showBan ? 'right-1' : 'left-1'" class="absolute top-1 h-4 w-4 rounded-full bg-white shadow transition-all"></span>
                </button>
            </div>
            <a href="{{ route('lecturer.classes.pending-members', $class->id) }}" wire:navigate class="relative inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-600 transition-colors hover:bg-blue-100">
                <x-user.icon name="user-check" :size="16" />
                Duyệt học viên
                @if($pendingMembersCount > 0)
                    <span class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white">
                        {{ $pendingMembersCount }}
                    </span>
                @endif
            </a>
        </div>
    </div>
    {{-- Thanh tìm kiếm + bộ lọc trạng thái chuyên cần --}}
    <div class="mb-4 flex flex-col gap-3 px-1 sm:flex-row sm:items-center sm:gap-4">
        {{-- Ô tìm kiếm --}}
        <div class="relative flex-1">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                <x-user.icon name="search" :size="18" />
            </span>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Tìm học viên..."
                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-9 text-sm text-slate-700 placeholder-slate-400 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
            >
            @if($search !== '')
                <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-2 flex items-center rounded-full p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600" title="Xóa tìm kiếm">
                    <x-user.icon name="x" :size="16" />
                </button>
            @endif
        </div>

        {{-- Cụm nút lọc trạng thái --}}
        <div class="flex shrink-0 items-center gap-1 overflow-x-auto rounded-xl bg-slate-100 p-1 hide-scrollbar">
            @php
                $filterTabs = [
                    ['key' => '',        'label' => 'Tất cả',            'count' => null,          'active' => 'bg-white text-slate-900 shadow-sm'],
                    ['key' => 'warning', 'label' => 'Sắp vượt ngưỡng',  'count' => $warningTotal, 'active' => 'bg-white text-amber-700 shadow-sm'],
                    ['key' => 'banned',  'label' => 'Nguy cơ cấm thi',   'count' => $bannedTotal,  'active' => 'bg-white text-red-700 shadow-sm'],
                ];
            @endphp
            @foreach($filterTabs as $tab)
                @php $isActive = $activeFilter === $tab['key']; @endphp
                <button
                    type="button"
                    wire:click="$set('filter', '{{ $tab['key'] }}')"
                    @class([
                        'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-bold transition-colors shrink-0',
                        $tab['active'] => $isActive,
                        'text-slate-500 hover:text-slate-700' => ! $isActive,
                    ])
                >
                    {{ $tab['label'] }}
                    @if(!is_null($tab['count']))
                        <span @class([
                            'rounded-full px-1.5 text-xs',
                            'bg-amber-100 text-amber-700' => $tab['key'] === 'warning',
                            'bg-red-100 text-red-700'     => $tab['key'] === 'banned',
                        ])>{{ $tab['count'] }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <div class="mb-8 rounded-[20px] border border-slate-200 bg-white overflow-hidden">
        {{-- Table --}}
        @if($students->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center bg-slate-50/50">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-user.icon name="users" :size="28" />
                </div>
                @if(!empty($activeFilter) || $search !== '')
                    <h3 class="text-base font-bold text-slate-700">Không có sinh viên phù hợp</h3>
                    <p class="mt-1 text-sm text-slate-500">Không tìm thấy sinh viên khớp bộ lọc. <button type="button" wire:click="clearFilter" class="font-bold text-blue-600 hover:underline">Xóa lọc &amp; tìm kiếm</button></p>
                @else
                    <h3 class="text-base font-bold text-slate-700">Chưa học viên</h3>
                    <p class="mt-1 text-sm text-slate-500">Lớp học này hiện chưa có sinh viên nào.</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto {{ $students->count() > 30 ? 'max-h-[800px] overflow-y-auto relative' : '' }}">
                <table class="w-full min-w-[760px] table-fixed text-left text-sm whitespace-nowrap">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr class="{{ $students->count() > 30 ? 'sticky top-0 z-10 bg-slate-50' : '' }}">
                            <th scope="col" class="w-[6%] px-4 py-4 text-center">STT</th>
                            <th scope="col" class="w-[32%] pl-6 pr-4 py-4">Họ & Tên</th>
                            <th scope="col" class="w-[26%] pl-6 pr-4 py-4">Email</th>
                            <th scope="col" class="w-[13%] px-4 py-4 text-center">Liên kết</th>
                            <th scope="col" class="w-[10%] px-4 py-4 text-center">Chuyên cần</th>
                            <th scope="col" class="w-[13%] px-4 py-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $student)
                            @php
                                // $statsMap từ LectureManageStudentService — tính theo TIẾT, chỉ buổi đã chốt.
                                $stats     = $statsMap[$student->id] ?? [];
                                $rate      = (int) ($stats['attendance_percent'] ?? 100);
                                $isBanned  = (bool) ($stats['is_banned']  ?? false);
                                $isWarning = (bool) ($stats['is_warning'] ?? false);

                                // Màu row: đỏ nhạt = cấm thi, vàng nhạt = cảnh báo.
                                $rowBg = $isBanned ? 'bg-red-50/40' : ($isWarning ? 'bg-amber-50/40' : '');
                            @endphp
                            <tr wire:key="stu-{{ $student->id }}" class="transition-colors hover:bg-slate-50/50" :class="showBan ? '{{ $rowBg }}' : ''">
                                <td class="px-4 py-4 font-medium text-slate-500 text-center">{{ $loop->iteration }}</td>
                                <td class="pl-6 pr-4 py-4 text-left">
                                    <div class="flex items-center gap-3">
                                        @if($student->user && $student->user->avatar)
                                            <img src="{{ $student->user->avatar_url }}" alt="{{ $student->displayName }}" class="h-8 w-8 shrink-0 rounded-full object-cover">
                                        @else
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-[13px] font-bold text-blue-600 uppercase">
                                                {{ mb_substr(collect(explode(' ', trim((string)$student->displayName)))->last() ?: 'S', 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-bold text-slate-800">{{ $student->displayName }}</span>
                                            <div x-show="showBan" x-cloak>
                                                @if ($isBanned)
                                                    <span class="inline-flex w-fit items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-red-700">
                                                        <x-user.icon name="alert-triangle" :size="10" />
                                                        Nguy cơ cấm thi
                                                    </span>
                                                @elseif ($isWarning)
                                                    <span class="inline-flex w-fit items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">
                                                        <x-user.icon name="alert-triangle" :size="10" />
                                                        Cảnh báo chuyên cần
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="pl-6 pr-4 py-4 text-slate-500 truncate">{{ $student->email ?? ($student->user ? $student->user->email : '—') }}</td>
                                <td class="px-4 py-4 text-center">
                                    @if($student->user_id)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span>
                                            Đã liên kết
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Chưa liên kết
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span @class([
                                        'inline-flex rounded-md px-2 py-1 text-xs font-bold',
                                        'bg-red-100 text-red-700'    => $isBanned,
                                        'bg-amber-100 text-amber-700' => $isWarning,
                                        'bg-green-50 text-green-600'  => !$isBanned && !$isWarning,
                                    ])>{{ $rate }}%</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($isBanned || $isWarning)
                                        {{-- Ẩn/hiện theo công tắc chung "Hiển thị sinh viên cấm thi" ở đầu bảng (mặc định ẩn). --}}
                                        <span x-show="!showBan" class="text-slate-300">—</span>
                                        <span x-show="showBan" x-cloak>
                                                @if($isBanned)
                                                    @if($student->user_id)
                                                        <button
                                                            type="button"
                                                            @click="banConfirm = { open: true, id: {{ $student->id }}, name: @js($student->full_name) }"
                                                            wire:loading.attr="disabled"
                                                            wire:target="sendExamBan"
                                                            class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white transition-colors hover:bg-red-700 disabled:opacity-60"
                                                        >
                                                            <x-user.icon name="alert-triangle" :size="14" />
                                                            Cấm thi
                                                        </button>
                                                    @else
                                                        <span class="text-xs font-medium text-slate-400" title="Chưa liên kết tài khoản">Chưa liên kết</span>
                                                    @endif
                                                @elseif($isWarning)
                                                    @if($student->user_id)
                                                        <button
                                                            type="button"
                                                            wire:click="sendAttendanceWarning({{ $student->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="sendAttendanceWarning"
                                                            class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 transition-colors hover:bg-amber-100 disabled:opacity-60"
                                                        >
                                                            <x-user.icon name="bell" :size="14" />
                                                            Gửi cảnh báo
                                                        </button>
                                                    @else
                                                        <span class="text-xs font-medium text-slate-400" title="Chưa liên kết tài khoản">Chưa liên kết</span>
                                                    @endif
                                                @endif
                                        </span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>


    {{-- Import Modal --}}
    @if ($isImporting)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm transition-all">
                <form wire:submit="processImport" class="w-full max-w-[560px] rounded-[24px] bg-white p-6 shadow-2xl">
                
                {{-- Header --}}
                <div class="mb-6 flex items-start justify-between">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                            <x-user.icon name="upload" :size="24" />
                        </div>
                        <div>
                            <h3 class="text-[20px] font-bold text-slate-800">Import danh sách học viên</h3>
                            <p class="mt-1 text-[14px] text-slate-500">
                                Tải lên tệp Excel hoặc CSV chứa danh sách học viên. Bạn có thể tải: 
                                <button type="button" wire:click="downloadBasicTemplate" class="font-bold text-blue-600 hover:underline">File mẫu Excel</button>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeImport" class="mt-1 shrink-0 rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-user.icon name="x" :size="20" />
                    </button>
                </div>

                {{-- Form Content --}}
                <div class="space-y-4">
                    {{-- File Dropzone --}}
                    <label class="group relative flex @if($isImportingStatus) cursor-not-allowed opacity-60 @else cursor-pointer @endif flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-slate-200 bg-[#F9FAFB] py-8 transition-colors hover:border-blue-400 hover:bg-blue-50/50">
                        <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" @if($isImportingStatus) disabled @endif class="peer absolute inset-0 h-full w-full cursor-pointer opacity-0">
                        
                        <div class="flex flex-col items-center justify-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border-[2.5px] border-slate-500 text-slate-500 transition-colors group-hover:border-blue-600 group-hover:text-blue-600">
                                <div wire:loading.remove wire:target="importFile">
                                    <x-user.icon name="upload" :size="20" />
                                </div>
                                <div wire:loading wire:target="importFile">
                                    <x-user.icon name="loader" class="animate-spin" :size="20" />
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <span class="text-[15px] font-bold text-slate-700 transition-colors group-hover:text-blue-600" wire:loading.remove wire:target="importFile">
                                    @if($importFile)
                                        {{ $importFile->getClientOriginalName() }}
                                    @else
                                        Nhấn để chọn file
                                    @endif
                                </span>
                                <span class="text-[15px] font-bold text-blue-600" wire:loading wire:target="importFile">
                                    Đang tải file...
                                </span>
                                <p class="mt-1 text-[13px] text-slate-400" wire:loading.remove wire:target="importFile">.xlsx, .xls, .csv</p>
                            </div>
                        </div>
                    </label>
                    @error('importFile')<span class="mt-1 block text-center text-sm text-red-500">{{ $message }}</span>@enderror
                    
                    {{-- Error Summary --}}
                    @if(!empty($importErrors))
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <h4 class="mb-2 text-sm font-bold text-red-800">Đã nhập {{ $importSuccess }} học viên. Có {{ count($importErrors) }} lỗi:</h4>
                            <ul class="list-disc space-y-1 pl-5 text-[13px] text-red-700 max-h-32 overflow-y-auto">
                                @foreach($importErrors as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
 
                {{-- Footer Buttons --}}
                <div class="mt-6 flex justify-end gap-3">
                    @if(!$isImportingStatus)
                        @if($importSuccess > 0)
                            <button type="button" wire:click="closeImport" class="rounded-full bg-blue-600 px-8 py-2.5 text-[14px] font-semibold text-white transition-colors hover:bg-blue-700">
                                Đóng
                            </button>
                        @else
                            <button type="button" wire:click="closeImport" class="rounded-full border border-slate-300 bg-white px-6 py-2.5 text-[14px] font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                                Hủy bỏ
                            </button>
                            <button type="submit" class="flex items-center gap-2 rounded-full bg-blue-600 px-8 py-2.5 text-[14px] font-semibold text-white transition-colors hover:bg-blue-700">
                                <span wire:loading.remove wire:target="processImport">Import</span>
                                <span wire:loading wire:target="processImport">Đang xử lý...</span>
                            </button>
                        @endif
                    @else
                        <button type="button" disabled class="flex items-center gap-2 rounded-full bg-slate-100 px-8 py-2.5 text-[14px] font-semibold text-slate-400 cursor-not-allowed">
                            Đang xử lý...
                        </button>
                    @endif
                </div>
            </form>
            </div>
        </template>
    @endif

    {{-- No Students Popup --}}
    <template x-teleport="body">
        <div x-data="{ showPopup: @entangle('showNoStudentsPopup') }">
            <div x-show="showPopup" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                 
                <div x-show="showPopup" @click.away="showPopup = false"
                     x-transition:enter="transition ease-out duration-300 delay-75"
                     x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     class="relative w-full max-w-sm overflow-hidden rounded-[24px] bg-white shadow-2xl">
                    
                    <button type="button" @click="showPopup = false" class="absolute right-4 top-4 rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-user.icon name="x" :size="20" />
                    </button>
                    
                    <div class="p-6 text-center">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-orange-50 text-orange-500">
                            <x-user.icon name="alert-circle" :size="24" />
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-slate-800">Lớp chưa có sinh viên</h3>
                        <p class="mb-6 text-[14px] text-slate-500">Vui lòng import danh sách lớp trước khi tiến hành điểm danh.</p>
                        
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('lecturer.classes.settings', $class) }}" wire:navigate class="rounded-full bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 text-center">
                                Cài đặt lớp
                            </a>
                             <button type="button" @click="showPopup = false" class="rounded-full px-6 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50 border border-transparent">
                                Hủy bỏ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Share Modal --}}
    <template x-teleport="body">
        <div x-show="showShareModal" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm transition-all"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
             
            <div x-show="showShareModal" @click.away="showShareModal = false"
                 x-transition:enter="transition ease-out duration-300 delay-75"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 class="relative w-full max-w-md overflow-hidden rounded-[24px] bg-white shadow-2xl">
                
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-bold text-slate-800">Chia sẻ lớp học</h3>
                    <button type="button" @click="showShareModal = false" class="rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-user.icon name="x" :size="20" />
                    </button>
                </div>
                
                {{-- Content --}}
                <div class="p-6 space-y-6">
                    {{-- Mã QR tham gia: học viên quét để vào lớp; chưa đăng nhập sẽ được yêu cầu đăng nhập trước. --}}
                    @if($shareQr)
                        <div class="flex flex-col items-center gap-3">
                            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm [&>svg]:h-44 [&>svg]:w-44">
                                {!! $shareQr !!}
                            </div>
                            <p class="max-w-[260px] text-center text-xs text-slate-500">
                                Học viên quét mã QR bằng camera để tham gia lớp. Nếu chưa đăng nhập, hệ thống sẽ yêu cầu đăng nhập trước.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="h-px flex-1 bg-slate-100"></div>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Hoặc dùng mã / đường dẫn</span>
                            <div class="h-px flex-1 bg-slate-100"></div>
                        </div>
                    @endif

                    {{-- Mã lớp --}}
                    @if(($class->class_code ?? '') && $class->class_code !== $class->join_key)
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Mã lớp</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 rounded-xl bg-slate-50 px-4 py-3 font-mono text-lg font-bold tracking-widest text-slate-800 text-center border border-slate-200">
                                {{ $class->class_code }}
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Mã tham gia lớp --}}
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Mã tham gia lớp</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 rounded-xl bg-slate-50 px-4 py-3 font-mono text-lg font-bold tracking-widest text-blue-600 text-center border border-slate-200">
                                {{ $class->join_key }}
                            </div>
                            <button 
                                type="button" 
                                x-data="{ copiedCode: false }"
                                @click="navigator.clipboard.writeText('{{ $class->join_key }}'); copiedCode = true; setTimeout(() => copiedCode = false, 2000)"
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition-colors hover:bg-blue-50 hover:text-blue-600"
                                :class="copiedCode ? '!bg-green-500 !text-white' : ''"
                                title="Sao chép mã tham gia lớp"
                            >
                                <template x-if="!copiedCode"><x-user.icon name="copy" :size="20" /></template>
                                <template x-if="copiedCode"><x-user.icon name="check" :size="20" /></template>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Link chia sẻ --}}
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Đường dẫn tham gia</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 overflow-hidden rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600 border border-slate-200 whitespace-nowrap text-ellipsis">
                                {{ url('/student/join-class?code=' . $class->join_key) }}
                            </div>
                            <button 
                                type="button" 
                                x-data="{ copiedLink: false }"
                                @click="navigator.clipboard.writeText('{{ url('/student/join-class?code=' . $class->join_key) }}'); copiedLink = true; setTimeout(() => copiedLink = false, 2000)"
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition-colors hover:bg-blue-50 hover:text-blue-600"
                                :class="copiedLink ? '!bg-green-500 !text-white' : ''"
                                title="Sao chép đường dẫn"
                            >
                                <template x-if="!copiedLink"><x-user.icon name="link" :size="20" /></template>
                                <template x-if="copiedLink"><x-user.icon name="check" :size="20" /></template>
                            </button>
                        </div>
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="bg-slate-50 px-6 py-4 flex justify-end">
                    <button type="button" @click="showShareModal = false" class="rounded-full border border-slate-200 bg-white px-6 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal xác nhận CẤM THI --}}
    <template x-teleport="body">
        <div
            x-show="banConfirm.open"
            x-cloak
            @keydown.escape.window="banConfirm.open = false"
            class="fixed inset-0 z-[120] flex items-center justify-center p-4"
        >
            <div
                x-show="banConfirm.open"
                x-transition.opacity
                @click="banConfirm.open = false"
                class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
            ></div>

            <div
                x-show="banConfirm.open"
                x-transition.scale.origin.center
                class="relative w-full max-w-[420px] overflow-hidden rounded-[24px] bg-white shadow-2xl"
            >
                <div class="p-6 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-600">
                        <x-user.icon name="alert-triangle" :size="28" />
                    </div>
                    <h3 class="mt-4 text-[19px] font-bold text-slate-800">Xác nhận cấm thi</h3>
                    <p class="mt-2 text-[14px] leading-relaxed text-slate-500">
                        Gửi thông báo <span class="font-bold text-red-600">CẤM THI</span> cho sinh viên
                        <span class="font-bold text-slate-700" x-text="banConfirm.name"></span>?
                    </p>
                </div>
                <div class="flex gap-3 bg-slate-50 px-6 py-4">
                    <button
                        type="button"
                        @click="banConfirm.open = false"
                        class="flex-1 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100"
                    >
                        Huỷ
                    </button>
                    <button
                        type="button"
                        @click="$wire.sendExamBan(banConfirm.id); banConfirm.open = false"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-full bg-red-600 px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-red-700"
                    >
                        <x-user.icon name="alert-triangle" :size="16" />
                        Cấm thi
                    </button>
                </div>
            </div>
        </div>
    </template>

    <livewire:lecturer.attendance.quick-attendance-modal />
</div>
