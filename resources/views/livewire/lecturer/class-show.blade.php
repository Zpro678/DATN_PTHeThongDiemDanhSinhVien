<div x-data="{ showImportModal: false, showShareModal: false }" class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center justify-between w-full sm:w-auto gap-4">
            <h1 class="flex items-center gap-3 text-2xl font-bold uppercase text-slate-800 min-w-0">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <x-user.icon name="book-open" :size="20" />
                </span>
                <span class="truncate">{{ $class->name }}</span>
            </h1>
            <a href="{{ route('managed-classes') }}" class="inline-flex sm:hidden shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                <x-user.icon name="arrow-left" :size="16" />
                Trở về
            </a>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center">
            <button
                type="button"
                class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900"
                wire:click="openImport"
            >
                <x-user.icon name="upload" :size="16" />
                <span>Import</span>
            </button>
            <a
                href="{{ route('lecturer.students.index', ['class_id' => $class->id, 'action' => 'export']) }}"
                wire:navigate
                class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900"
            >
                <x-user.icon name="download" :size="16" />
                <span>Xuất Excel</span>
            </a>
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
            <a href="{{ route('managed-classes') }}" class="hidden sm:inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                <x-user.icon name="arrow-left" :size="16" />
                Trở về
            </a>
        </div>
    </div>

    {{-- Thông tin lớp & Hành động nhanh --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row">
        {{-- Thông tin lớp (Match image 1) --}}
        <div class="relative flex-1 rounded-[20px] bg-blue-50/50 border border-blue-100 p-6 shadow-sm sm:p-8">
            <div class="absolute right-6 top-6 flex items-center justify-center">
                @if($class->status === 'active')
                    <span class="relative flex h-3 w-3" title="Trạng thái: Đang hoạt động">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#3E7B62] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-[#3E7B62]"></span>
                    </span>
                @elseif($class->status === 'archived')
                    <span class="h-3 w-3 rounded-full bg-amber-500" title="Trạng thái: Lưu trữ"></span>
                @else
                    <span class="h-3 w-3 rounded-full bg-slate-400" title="Trạng thái: Đã kết thúc"></span>
                @endif
            </div>
            
            <div class="grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-4">
                <div class="min-w-0">
                    <span class="text-sm text-slate-500">Sinh viên</span>
                    <p class="mt-1 text-2xl sm:text-[28px] font-bold text-slate-800 leading-none">{{ $studentsCount }}</p>
                </div>
                <div class="min-w-0">
                    <span class="text-sm text-slate-500">Điểm danh</span>
                    <p class="mt-1 text-2xl sm:text-[28px] font-bold text-slate-800 leading-none">{{ $sessionsCompleted }} <span class="text-base font-medium text-slate-500">buổi</span></p>
                </div>
                <div class="min-w-0">
                    <span class="text-sm text-slate-500">Tiến độ</span>
                    <p class="mt-1 text-2xl sm:text-[28px] font-bold text-slate-800 leading-none">{{ $sessionsCount > 0 ? round(($sessionsCompleted / $sessionsCount) * 100) : 0 }}%</p>
                </div>
                <div class="min-w-0">
                    <span class="text-sm text-slate-500">Tổng số buổi</span>
                    <p class="mt-1 text-2xl sm:text-[28px] font-bold text-slate-800 leading-none">{{ $class->total_sessions }} <span class="text-base font-medium text-slate-500">buổi</span></p>
                </div>

                @if($class->subject_code)
                <div class="min-w-0">
                    <span class="text-sm text-slate-500">Mã học phần</span>
                    <p class="mt-1 text-base font-bold text-slate-800 truncate" title="{{ $class->subject_code }}">{{ $class->subject_code }}</p>
                </div>
                @else
                <div class="min-w-0 hidden sm:block"></div>
                @endif



                <div class="col-span-2 sm:col-span-1 flex items-end sm:justify-end">
                    <a href="{{ route('lecturer.classes.attendance', $class->id) }}" wire:navigate class="inline-flex w-full sm:w-auto whitespace-nowrap items-center justify-center gap-2 rounded-lg border border-slate-300 bg-slate-200 px-4 py-2 text-sm font-bold text-slate-800 transition-colors hover:bg-slate-300 hover:text-slate-900 shadow-sm">
                        <x-user.icon name="clock" :size="16" />
                        Lịch sử điểm danh
                    </a>
                </div>
            </div>
        </div>

        {{-- Quick Actions (Match image 1 colors) --}}
        <div class="grid grid-cols-2 gap-4 lg:w-[320px] lg:shrink-0 text-center">
            <button type="button" wire:click="checkBeforeAttendance('qr')" class="group flex h-[100px] flex-col items-center justify-center gap-2 rounded-[20px] bg-blue-600 p-4 transition-colors hover:bg-blue-700">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white transition-colors group-hover:bg-white/30">
                    <x-user.icon name="qr-code" :size="20" />
                </div>
                <span class="text-[13px] font-bold text-white">QR</span>
            </button>
            <button type="button" wire:click="checkBeforeAttendance('manual')" class="group flex h-[100px] flex-col items-center justify-center gap-2 rounded-[20px] bg-emerald-600 p-4 transition-colors hover:bg-emerald-700">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white transition-colors group-hover:bg-white/30">
                    <x-user.icon name="check-square" :size="20" />
                </div>
                <span class="text-[13px] font-bold text-white">Thủ công</span>
            </button>
            <a href="{{ route('lecturer.leave-requests.index', ['class_id' => $class->id]) }}" wire:navigate class="group relative flex h-[100px] flex-col items-center justify-center gap-2 rounded-[20px] bg-rose-600 p-4 transition-colors hover:bg-rose-700">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white transition-colors group-hover:bg-white/30">
                    <x-user.icon name="file-text" :size="20" />
                </div>
                <span class="text-[13px] font-bold text-white">Đơn nghỉ</span>
                @if($pendingLeaveRequests > 0)
                    <span class="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-white text-[10px] font-bold text-rose-600">{{ $pendingLeaveRequests }}</span>
                @endif
            </a>
            <a href="{{ route('lecturer.class.statistics', ['class_id' => $class->id]) }}" wire:navigate class="group flex h-[100px] flex-col items-center justify-center gap-2 rounded-[20px] bg-slate-700 p-4 transition-colors hover:bg-slate-800" style="background-color: #334155;">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white transition-colors group-hover:bg-white/30">
                    <x-user.icon name="bar-chart-2" :size="20" />
                </div>
                <span class="text-[13px] font-bold text-white">Thống kê</span>
            </a>
        </div>
    </div>

    {{-- Danh sách học viên --}}
    <div class="mt-8 mb-4 flex items-center justify-between px-1">
        <h2 class="text-lg font-bold text-slate-800">Danh sách học viên ({{ $studentsCount }})</h2>
        <a href="{{ route('lecturer.students.index', ['class_id' => $class->id, 'status' => 'pending']) }}" wire:navigate class="relative inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-600 transition-colors hover:bg-blue-100">
            <x-user.icon name="user-check" :size="16" />
            Duyệt học viên
            @if($pendingMembersCount > 0)
                <span class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white">
                    {{ $pendingMembersCount }}
                </span>
            @endif
        </a>
    </div>
    <div class="mb-8 rounded-[20px] border border-slate-200 bg-white overflow-hidden">
        {{-- Table --}}
        @if($students->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center bg-slate-50/50">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-user.icon name="users" :size="28" />
                </div>
                <h3 class="text-base font-bold text-slate-700">Chưa học viên</h3>
                <p class="mt-1 text-sm text-slate-500">Lớp học này hiện chưa có sinh viên nào.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[750px] table-fixed text-left text-sm whitespace-nowrap">
                    <thead class="border-b border-slate-200 bg-slate-50 text-sm uppercase text-black">
                        <tr>
                            <th scope="col" class="w-[15%] px-6 py-4 font-bold text-center">MSSV</th>
                            <th scope="col" class="w-[25%] pl-6 pr-6 py-4 font-bold">Họ & Tên</th>
                            <th scope="col" class="w-[25%] pl-6 pr-6 py-4 font-bold">Email</th>
                            <th scope="col" class="w-[20%] px-6 py-4 font-bold text-center">Liên kết</th>
                            <th scope="col" class="w-[15%] px-6 py-4 font-bold text-center">Chuyên cần</th>
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
                            <tr class="transition-colors hover:bg-slate-50/50 {{ $rowBg }}">
                                <td class="px-6 py-4 font-medium text-slate-700 text-center">{{ $student->student_code }}</td>
                                <td class="pl-6 pr-6 py-4 text-left">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-[13px] font-bold text-blue-600 uppercase">
                                            {{ mb_substr(collect(explode(' ', $student->full_name))->last(), 0, 1) }}
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-bold text-slate-800">{{ $student->full_name }}</span>
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
                                </td>
                                <td class="pl-6 pr-6 py-4 text-slate-500">{{ $student->email ?? ($student->user ? $student->user->email : '—') }}</td>
                                <td class="px-6 py-4 text-center">
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
                                <td class="px-6 py-4 text-center">
                                    <span @class([
                                        'inline-flex rounded-md px-2 py-1 text-xs font-bold',
                                        'bg-red-100 text-red-700'    => $isBanned,
                                        'bg-amber-100 text-amber-700' => $isWarning,
                                        'bg-green-50 text-green-600'  => !$isBanned && !$isWarning,
                                    ])>{{ $rate }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($students->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 p-4">
                    {{ $students->links() }}
                </div>
            @endif
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
                                <button type="button" wire:click="downloadBasicTemplate" class="font-bold text-blue-600 hover:underline">Mẫu cơ bản</button> hoặc 
                                <button type="button" wire:click="downloadFullTemplate" class="font-bold text-blue-600 hover:underline">Mẫu đầy đủ</button>.
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
                    
                    <div class="mt-4 flex items-center gap-2">
                        <input type="checkbox" id="syncAttendanceShow" wire:model="syncAttendance" @if($isImportingStatus) disabled @endif class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                        <label for="syncAttendanceShow" class="text-[14px] text-slate-700 font-medium">Tự động thêm vào các buổi điểm danh đã có</label>
                    </div>

                    {{-- Progress Bar --}}
                    @if($isImportingStatus)
                        <div class="mt-4 p-4 bg-blue-50 rounded-2xl flex flex-col gap-2 shadow-inner" wire:poll.500ms="checkImportProgress">
                            <div class="flex justify-between text-sm font-semibold text-blue-700">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Đang ghi nhận học viên...
                                </span>
                                <span>{{ $importProcessedRows }}/{{ $importTotalRows }}</span>
                            </div>
                            <div class="w-full bg-blue-100 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $importTotalRows > 0 ? ($importProcessedRows / $importTotalRows) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @endif
 
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
                        <button type="button" wire:click="closeImport" class="rounded-full border border-slate-300 bg-white px-6 py-2.5 text-[14px] font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="flex items-center gap-2 rounded-full bg-blue-600 px-8 py-2.5 text-[14px] font-semibold text-white transition-colors hover:bg-blue-700">
                            <span wire:loading.remove wire:target="processImport">Import</span>
                            <span wire:loading wire:target="processImport">Đang xử lý...</span>
                        </button>
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
                            <button type="button" @click="showPopup = false; setTimeout(() => $wire.openImportFromPopup(), 200)" class="rounded-full bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700">
                                Tới trang import
                            </button>
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
                    {{-- Mã tham gia --}}
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
                                title="Sao chép mã lớp"
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
</div>