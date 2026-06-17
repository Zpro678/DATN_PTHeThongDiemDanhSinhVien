@php
    // ================================================================
    // 🧪 DỮ LIỆU TĨNH — chỉ dùng để kiểm tra giao diện
    // Sẽ được thay bằng dữ liệu thật từ controller sau
    // ================================================================
    $staticClasses = [
        [
            'id'            => 1,
            'code'          => 'CS402',
            'subject_code'  => 'CS402',
            'name'          => 'Thuật toán Nâng cao',
            'description'   => 'Lý thuyết & Thực hành Thuật toán Nâng Cao',
            'semester'      => 'HK2 2024-2025',
            'status'        => 'active',
            'attended'      => 14,
            'total'         => 15,
            'rate'          => 96.8,
            'is_warning'    => false,
            'teacher'       => 'TS. Nguyễn Mạnh Hùng',
            'credits'       => 3,
        ],
        [
            'id'            => 2,
            'code'          => 'DB101',
            'subject_code'  => 'DB101',
            'name'          => 'Thiết kế & Quản trị SQL',
            'description'   => 'Cơ sở dữ liệu Quan Hệ & Tối Ưu Hóa',
            'semester'      => 'HK2 2024-2025',
            'status'        => 'active',
            'attended'      => 15,
            'total'         => 16,
            'rate'          => 92.5,
            'is_warning'    => false,
            'teacher'       => 'ThS. Trần Thị Mai',
            'credits'       => 4,
        ],
        [
            'id'            => 3,
            'code'          => 'PY201',
            'subject_code'  => 'PY201',
            'name'          => 'Phát triển Web Python',
            'description'   => 'Lập trình Fullstack với Django & FastAPI',
            'semester'      => 'HK2 2024-2025',
            'status'        => 'active',
            'attended'      => 13,
            'total'         => 15,
            'rate'          => 94.0,
            'is_warning'    => false,
            'teacher'       => 'TS. Lê Đức Anh',
            'credits'       => 3,
        ],
        [
            'id'            => 4,
            'code'          => 'PH102',
            'subject_code'  => 'PH102',
            'name'          => 'Vật lý đại cương 2',
            'description'   => 'Vật lý Ứng dụng Điện học & Từ học',
            'semester'      => 'HK2 2024-2025',
            'status'        => 'active',
            'attended'      => 10,
            'total'         => 14,
            'rate'          => 72.0,
            'is_warning'    => true,
            'teacher'       => 'PGS.TS Phạm Văn B',
            'credits'       => 3,
        ],
        [
            'id'            => 5,
            'code'          => 'NET301',
            'subject_code'  => 'NET301',
            'name'          => 'Lý thuyết Mạng Máy Tính',
            'description'   => 'Mạng căn bản & Giao thức Định tuyến',
            'semester'      => 'HK2 2024-2025',
            'status'        => 'active',
            'attended'      => 13,
            'total'         => 14,
            'rate'          => 95.0,
            'is_warning'    => false,
            'teacher'       => 'ThS. Nguyễn Văn C',
            'credits'       => 3,
        ],
        [
            'id'            => 6,
            'code'          => 'ENG101',
            'subject_code'  => 'ENG101',
            'name'          => 'Tiếng Anh Chuyên Ngành',
            'description'   => 'Tiếng Anh Giao Tiếp & Tài Liệu Kỹ Thuật',
            'semester'      => 'HK1 2024-2025',
            'status'        => 'archived',
            'attended'      => 12,
            'total'         => 12,
            'rate'          => 100.0,
            'is_warning'    => false,
            'teacher'       => 'Cô Trần Thu D',
            'credits'       => 2,
        ],
        [
            'id'            => 7,
            'code'          => 'QA101',
            'subject_code'  => 'QA101',
            'name'          => 'Kiểm thử Phần mềm',
            'description'   => 'Đảm bảo Chất lượng & Kiểm thử Tự động',
            'semester'      => 'HK1 2024-2025',
            'status'        => 'archived',
            'attended'      => 14,
            'total'         => 15,
            'rate'          => 93.3,
            'is_warning'    => false,
            'teacher'       => 'TS. Ngô Bảo E',
            'credits'       => 3,
        ],
    ];

    // Dùng dữ liệu từ controller nếu có, không thì dùng dữ liệu tĩnh
    $displayClasses = isset($classes) && count($classes) > 0 ? $classes : $staticClasses;
    $displayTotal   = isset($totalClasses) ? $totalClasses : count($staticClasses);
    $displayWarning = isset($warningCount)  ? $warningCount  : count(array_filter($staticClasses, fn($c) => $c['is_warning']));
    $displaySearch  = isset($search)        ? $search        : '';
    $displayFilter  = isset($statusFilter)  ? $statusFilter  : '';
@endphp

<x-app-layout variant="student" pageTitle="Lớp học của tôi">
    <div class="space-y-6 p-6 lg:p-8 animate-in fade-in duration-300">

        {{-- ===== TIÊU ĐỀ TRANG ===== --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2 dark:text-white">
                    <x-sams.icon name="book-open" class="w-6 h-6 text-blue-600 shrink-0" />
                    Lớp học của tôi
                </h1>
                <p class="text-xs text-slate-500 font-semibold mt-1 dark:text-slate-400">
                    Quản lý danh sách học phần, theo dõi chuyên cần và lưu vết học tập của bạn
                </p>
            </div>

            {{-- Thẻ tổng quan --}}
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-blue-50 border border-blue-100 rounded-2xl px-4 py-2 text-center dark:bg-blue-950/30 dark:border-blue-900/30">
                    <span class="block text-xl font-black text-blue-700 leading-none dark:text-blue-300">{{ $displayTotal }}</span>
                    <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider">Lớp học</span>
                </div>
                @if ($displayWarning > 0)
                    <div class="bg-rose-50 border border-rose-100 rounded-2xl px-4 py-2 text-center dark:bg-rose-950/30 dark:border-rose-900/30">
                        <span class="block text-xl font-black text-rose-600 leading-none dark:text-rose-400">{{ $displayWarning }}</span>
                        <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider">Cảnh báo</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== THANH TÌM KIẾM & LỌC ===== --}}
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm
                    flex flex-col md:flex-row md:items-center justify-between gap-4
                    dark:bg-slate-900 dark:border-slate-800">

            {{-- Ô tìm kiếm --}}
            <form method="GET" action="{{ route('student.classes.list') }}"
                  class="flex items-center gap-2.5 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2
                         text-xs font-semibold text-slate-700 w-full md:max-w-md
                         focus-within:border-blue-400 focus-within:ring-1 focus-within:ring-blue-200 transition-all
                         dark:bg-slate-800 dark:border-slate-700 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-900/30">
                <x-sams.icon name="search" class="w-[18px] h-[18px] text-slate-400 shrink-0" />
                <input
                    id="search-student-classes"
                    name="search"
                    type="text"
                    value="{{ $displaySearch }}"
                    placeholder="Mã môn học, tên môn học, giảng viên..."
                    class="w-full bg-transparent border-none p-0 outline-none focus:outline-none focus:ring-0 text-slate-800 font-semibold h-8
                           dark:text-white placeholder:text-slate-400"
                    autocomplete="off"
                >
            </form>

            {{-- Nút lọc trạng thái --}}
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('student.classes.list') }}"
                   class="px-4 py-2.5 border rounded-xl text-xs font-bold cursor-pointer transition-all
                          {{ $displayFilter === ''
                              ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                              : 'bg-white text-slate-500 border-slate-200 hover:text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700' }}">
                    Tất cả
                </a>
                <a href="{{ route('student.classes.list', ['status' => 'active']) }}"
                   class="px-4 py-2.5 border rounded-xl text-xs font-bold cursor-pointer transition-all
                          {{ $displayFilter === 'active'
                              ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                              : 'bg-white text-slate-500 border-slate-200 hover:text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700' }}">
                    Đang học
                </a>
                <a href="{{ route('student.classes.list', ['status' => 'archived']) }}"
                   class="px-4 py-2.5 border rounded-xl text-xs font-bold cursor-pointer transition-all
                          {{ $displayFilter === 'archived'
                              ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                              : 'bg-white text-slate-500 border-slate-200 hover:text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700' }}">
                    Đã hoàn thành
                </a>
            </div>
        </div>



        {{-- ===== GRID LỚP HỌC ===== --}}
        @if (count($displayClasses) === 0)
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 shadow-sm py-16
                        flex flex-col items-center justify-center text-center gap-4
                        dark:bg-slate-900 dark:border-slate-700">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center dark:bg-slate-800">
                    <x-sams.icon name="book-open" class="w-8 h-8 text-slate-300" />
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-700 dark:text-white">Không tìm thấy lớp học nào</p>
                    <p class="text-xs text-slate-400 font-semibold mt-1">
                        Bạn chưa được thêm vào lớp học nào hoặc bộ lọc không có kết quả.
                    </p>
                </div>
                <a href="{{ route('student.classes.list') }}"
                   class="px-4 py-2 bg-blue-50 text-blue-700 rounded-xl text-xs font-bold hover:bg-blue-100 transition-all">
                    Xoá bộ lọc
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach ($displayClasses as $item)
                    @php
                        // Hỗ trợ cả array (mock) và object (Eloquent)
                        $id         = is_array($item) ? $item['id']          : $item->id;
                        $code       = is_array($item) ? ($item['subject_code'] ?? $item['code']) : ($item->subject_code ?? $item->code);
                        $name       = is_array($item) ? $item['name']         : $item->name;
                        $desc       = is_array($item) ? ($item['description'] ?? '') : ($item->description ?? '');
                        $semester   = is_array($item) ? ($item['semester'] ?? '—') : ($item->semester ?? '—');
                        $status     = is_array($item) ? $item['status']        : $item->status;
                        $attended   = is_array($item) ? ($item['attended'] ?? $item['attended_sessions'] ?? 0) : ($item->attended_sessions ?? 0);
                        $total      = is_array($item) ? ($item['total'] ?? $item['total_sessions_disp'] ?? 0) : ($item->total_sessions_disp ?? 0);
                        $rate       = is_array($item) ? ($item['rate'] ?? $item['attendance_rate'] ?? 100) : ($item->attendance_rate ?? 100);
                        $isWarning  = is_array($item) ? ($item['is_warning'] ?? false) : ($item->is_warning ?? false);
                        $isArchived = $status === 'archived';
                    @endphp

                    @php
                        $teacherName = is_array($item) ? ($item['teacher'] ?? 'GV. SAMS') : ($item->user->name ?? 'GV. SAMS');
                        $credits = is_array($item) ? ($item['credits'] ?? 3) : ($item->credits ?? 3);
                    @endphp
                    <div class="bg-white rounded-3xl p-5 border shadow-sm transition-all flex flex-col justify-between min-h-[250px]
                                {{ $isWarning ? 'border-rose-220 bg-rose-50/5 dark:bg-rose-950/20 dark:border-rose-900/50' : 'border-slate-200 hover:border-blue-200 dark:bg-slate-900 dark:border-slate-800 dark:hover:border-blue-800' }}">

                        <div class="flex-1">
                            {{-- Header: Code & Status --}}
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs px-2.5 py-1 bg-slate-100 text-slate-700 font-semibold rounded-lg dark:bg-slate-800 dark:text-slate-300">{{ $code }}</span>
                                @if ($isWarning)
                                    <span class="text-[10px] px-2.5 py-1 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg font-bold uppercase dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-400">Cảnh báo</span>
                                @elseif ($isArchived)
                                    <span class="text-[10px] px-2.5 py-1 bg-slate-50 text-slate-500 border border-slate-200 rounded-lg font-bold uppercase dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400">Hoàn thành</span>
                                @else
                                    <span class="text-[10px] px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg font-bold uppercase dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400">An toàn</span>
                                @endif
                            </div>

                            {{-- Title & Desc --}}
                            <h3 class="text-base font-bold text-slate-800 leading-snug dark:text-white" title="{{ $code }}: {{ $name }}">
                                {{ $code }}: {{ $name }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-1 mb-4 dark:text-slate-400" title="{{ $desc ?: 'Học kỳ: ' . $semester }}">
                                {{ $desc ?: 'Học kỳ: ' . $semester }}
                            </p>

                            <hr class="border-slate-100 dark:border-slate-800 mb-4" />

                            {{-- Stats Grid --}}
                            <div class="grid grid-cols-2 gap-y-4 gap-x-2 text-xs mb-5">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1 dark:text-slate-400">Giảng viên</p>
                                    <p class="font-bold text-slate-800 truncate dark:text-slate-200" title="{{ $teacherName }}">{{ $teacherName }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1 dark:text-slate-400">Số tín chỉ</p>
                                    <p class="font-bold text-slate-800 font-mono dark:text-slate-200">{{ $credits }} <span class="font-sans text-[11px] text-slate-500 dark:text-slate-400">Tín chỉ</span></p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1 dark:text-slate-400">Số buổi đã học</p>
                                    <p class="font-bold text-slate-800 font-mono dark:text-slate-200">{{ $attended }} / {{ $total }} <span class="font-sans text-[11px] text-slate-500 dark:text-slate-400">buổi</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1 dark:text-slate-400">Tỷ lệ chuyên cần</p>
                                    <p class="font-bold text-blue-600 font-mono dark:text-blue-400">{{ $rate }}%</p>
                                </div>
                            </div>
                        </div>

                        {{-- Progress Bar & Action --}}
                        <div class="mt-auto">
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mb-4 dark:bg-slate-800">
                                <div class="h-full rounded-full transition-all duration-700 {{ $isWarning ? 'bg-rose-500' : 'bg-blue-600' }}" style="width: {{ min($rate, 100) }}%;"></div>
                            </div>

                            <a href="{{ route('student.classes.detail', $id) }}"
                               class="w-full py-2.5 bg-blue-50/50 hover:bg-blue-100/80 text-blue-700 font-bold text-[13px] rounded-xl cursor-pointer transition-all flex items-center justify-center gap-1.5 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                                <span>Xem chi tiết lớp học</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-app-layout>
