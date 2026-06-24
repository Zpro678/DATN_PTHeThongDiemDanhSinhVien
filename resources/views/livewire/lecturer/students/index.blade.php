<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-end">
        <div>
            <h1 class="flex items-center gap-3 text-2xl font-extrabold uppercase tracking-tight text-slate-900">
                <x-user.icon name="users" class="text-primary" />
                Học viên
            </h1>
            <p class="mt-2 text-sm text-slate-500">Quản lý danh sách học viên trong các lớp bạn đang phụ trách.</p>
        </div>
        <div class="flex items-center gap-3">
            @if ($canExportExcel)
                <button type="button" wire:click="openExport" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-50">
                    <x-user.icon name="download" :size="18" />
                    Xuất Excel
                </button>
            @else
                <a href="{{ route('upgrade') }}" title="Nâng cấp lên gói Pro để xuất báo cáo Excel"
                    class="inline-flex items-center justify-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-5 py-2.5 text-sm font-bold text-amber-700 transition-colors hover:bg-amber-100">
                    <x-user.icon name="download" :size="18" />
                    Xuất Excel
                    <span class="rounded-full bg-amber-200 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800">Pro</span>
                </a>
            @endif
            <a href="{{ route('lecturer.leave-requests.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-50">
                <x-user.icon name="file-text" :size="18" />
                Đơn xin nghỉ
            </a>
            @if ($showBackButton)
                <a href="javascript:history.back()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0a46d1] px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-blue-800 shadow-sm">
                    <x-user.icon name="arrow-left" :size="18" />
                    Trở về
                </a>
            @endif
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => 'Tổng học viên', 'value' => $attendanceOverview['total_students'], 'color' => 'text-primary'],
            ['label' => 'Có mặt', 'value' => $attendanceOverview['present_lessons'], 'color' => 'text-emerald-600'],
            ['label' => 'Muộn', 'value' => $attendanceOverview['late_lessons'], 'color' => 'text-amber-600'],
            ['label' => 'Vắng', 'value' => $attendanceOverview['absent_lessons'], 'color' => 'text-red-600'],
            ['label' => 'Chuyên cần tổng', 'value' => $attendanceOverview['attendance_percent'].'%', 'color' => 'text-primary'],
        ] as $overviewItem)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $overviewItem['label'] }}</p>
                <p class="mt-2 text-3xl font-extrabold {{ $overviewItem['color'] }}">{{ $overviewItem['value'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:grid-cols-[1fr_260px_auto]">
        <label class="relative">
            <x-user.icon name="search" :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Tìm theo tên, mã học viên hoặc email..." class="w-full rounded-xl border-slate-200 py-2.5 pl-11 pr-4 text-sm focus:border-primary focus:ring-primary/20">
        </label>
        <select wire:model.live="classFilter" class="rounded-xl border-slate-200 text-sm font-semibold text-slate-700 focus:border-primary focus:ring-primary/20">
            <option value="all">Tất cả lớp học</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}">{{ $class->code }} - {{ $class->name }}</option>
            @endforeach
        </select>
        <div class="flex rounded-xl bg-slate-100 p-1">
            <button type="button" wire:click="setStatusFilter('active')" @class(['rounded-lg px-4 py-2 text-xs font-bold transition-colors', 'bg-white text-primary shadow-sm' => $statusFilter === 'active', 'text-slate-500' => $statusFilter !== 'active'])>Đang học</button>
            <button type="button" wire:click="setStatusFilter('archived')" @class(['rounded-lg px-4 py-2 text-xs font-bold transition-colors', 'bg-white text-primary shadow-sm' => $statusFilter === 'archived', 'text-slate-500' => $statusFilter !== 'archived'])>Lưu trữ</button>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Học viên</th>
                        <th class="px-4 py-4">Lớp học</th>
                        <th class="px-4 py-4 text-center">Có mặt</th>
                        <th class="px-4 py-4 text-center">Muộn</th>
                        <th class="px-4 py-4 text-center">Vắng</th>
                        <th class="px-4 py-4 text-center">Chuyên cần</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($members as $member)
                        @php
                            $stats = $attendanceStats[$member->id] ?? [
                                'present_lessons' => 0,
                                'late_lessons' => 0,
                                'absent_lessons' => 0,
                                'attendance_percent' => 0,
                            ];
                            $rate = (float) $stats['attendance_percent'];
                        @endphp
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="px-6 py-4">
                                <a href="{{ route('lecturer.students.show', $member) }}" class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-bold text-primary">{{ mb_strtoupper(mb_substr($member->full_name, 0, 1)) }}</span>
                                    <span>
                                        <span class="block text-sm font-bold text-slate-900">{{ $member->full_name }}</span>
                                        <span class="block text-xs text-slate-500">{{ $member->student_code }} · {{ $member->user?->email ?? 'Chưa liên kết tài khoản' }}</span>
                                    </span>
                                </a>
                            </td>
                            <td class="px-4 py-4">
                                <span class="block text-sm font-semibold text-slate-700">{{ $member->courseClass->name }}</span>
                                <span class="text-xs text-slate-500">{{ $member->courseClass->code }}</span>
                            </td>
                            <td class="px-4 py-4 text-center text-sm font-bold text-emerald-600">{{ $stats['present_lessons'] }}</td>
                            <td class="px-4 py-4 text-center text-sm font-bold text-amber-600">{{ $stats['late_lessons'] }}</td>
                            <td class="px-4 py-4 text-center text-sm font-bold text-red-600">{{ $stats['absent_lessons'] }}</td>
                            <td class="px-4 py-4 text-center">
                                <span @class(['inline-flex rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $rate >= 80, 'bg-amber-50 text-amber-700' => $rate >= 60 && $rate < 80, 'bg-red-50 text-red-700' => $rate < 60])>{{ $rate }}%</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('lecturer.students.show', $member) }}" class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-primary/10 hover:text-primary" title="Xem chi tiết"><x-user.icon name="eye" :size="18" /></a>
                                    @if ($statusFilter === 'active')
                                        <button type="button" wire:click="openEdit({{ $member->id }})" class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-800" title="Sửa"><x-user.icon name="edit" :size="18" /></button>
                                        <button type="button" wire:click="confirmArchive({{ $member->id }})" class="rounded-lg p-2 text-red-500 transition-colors hover:bg-red-50" title="Lưu trữ"><x-user.icon name="x" :size="18" /></button>
                                    @else
                                        <button type="button" wire:click="restoreMember({{ $member->id }})" class="rounded-lg px-3 py-2 text-xs font-bold text-primary transition-colors hover:bg-primary/10">Khôi phục</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-14 text-center text-sm text-slate-500">Không tìm thấy học viên phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($members->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $members->links() }}</div>
        @endif
    </section>

    @if ($editingMemberId)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                <form wire:submit="saveMember" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Sửa thông tin học viên</h3>
                        <button type="button" wire:click="closeEdit" class="rounded-full p-2 text-slate-400 hover:bg-slate-100"><x-user.icon name="x" :size="18" /></button>
                    </div>
                    <div class="space-y-4">
                        <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Họ và tên</span><input wire:model="editingName" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20">@error('editingName')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                        <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Mã học viên</span><input wire:model="editingStudentCode" class="w-full rounded-xl border-slate-200 uppercase focus:border-primary focus:ring-primary/20">@error('editingStudentCode')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                        <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Trạng thái</span><select wire:model="editingStatus" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20"><option value="active">Đang học</option><option value="dropped">Đã thôi học</option></select></label>
                    </div>
                    <div class="mt-6 flex justify-end gap-3"><button type="button" wire:click="closeEdit" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">Hủy</button><button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white">Lưu thay đổi</button></div>
                </form>
            </div>
        </template>
    @endif

    @if ($isAdding)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                <form wire:submit="addMember" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Thêm thành viên mới</h3>
                        <button type="button" wire:click="closeAdd" class="rounded-full p-2 text-slate-400 hover:bg-slate-100"><x-user.icon name="x" :size="18" /></button>
                    </div>
                    <div class="space-y-4">
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-slate-700">Lớp học</span>
                            <select wire:model="newClassId" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20">
                                <option value="">-- Chọn lớp học --</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->code }} - {{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('newClassId')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Họ và tên</span><input wire:model="newName" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20" placeholder="Nguyễn Văn A">@error('newName')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                        <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Mã học viên</span><input wire:model="newStudentCode" class="w-full rounded-xl border-slate-200 uppercase focus:border-primary focus:ring-primary/20" placeholder="SV001">@error('newStudentCode')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    </div>
                    <div class="mt-6 flex justify-end gap-3"><button type="button" wire:click="closeAdd" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">Hủy</button><button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white">Thêm học viên</button></div>
                </form>
            </div>
        </template>
    @endif

    @if ($isImporting)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm transition-all">
                <form wire:submit="processImport" class="w-full max-w-[560px] rounded-[24px] bg-white p-6 shadow-2xl">
                
                {{-- Header --}}
                <div class="mb-6 flex items-start justify-between">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                            <x-user.icon name="upload" :size="24" />
                        </div>
                        <div>
                            <h3 class="text-[20px] font-bold text-slate-900">Import danh sách học viên</h3>
                            <p class="mt-1 text-[14px] text-slate-600">
                                Tải lên tệp Excel hoặc CSV chứa danh sách học viên. 
                                <button type="button" wire:click="downloadTemplate" class="font-bold text-blue-700 hover:underline">Tải mẫu file.</button>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeImport" class="mt-1 shrink-0 rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-user.icon name="x" :size="20" />
                    </button>
                </div>

                {{-- Form Content --}}
                <div class="space-y-4">
                    
                    {{-- Class Selection --}}
                    <div>
                        <select wire:model="importClassId" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 text-[15px] font-medium text-slate-700 focus:border-blue-500 focus:ring-blue-500/20">
                            <option value="">-- Chọn lớp học để import --</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->code }} - {{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('importClassId')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                    </div>

                    {{-- File Dropzone --}}
                    <label class="group relative flex cursor-pointer flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-slate-200 bg-slate-50/50 py-8 transition-colors hover:border-blue-400 hover:bg-blue-50/50">
                        <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" class="peer absolute inset-0 h-full w-full cursor-pointer opacity-0">
                        
                        <div class="flex flex-col items-center justify-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border-[2.5px] border-slate-700 text-slate-700 transition-colors group-hover:border-blue-600 group-hover:text-blue-600">
                                <div wire:loading.remove wire:target="importFile">
                                    <x-user.icon name="upload" :size="20" />
                                </div>
                                <div wire:loading wire:target="importFile">
                                    <x-user.icon name="loader" class="animate-spin" :size="20" />
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <span class="text-[16px] font-bold text-slate-800 transition-colors group-hover:text-blue-700" wire:loading.remove wire:target="importFile">
                                    @if($importFile)
                                        {{ $importFile->getClientOriginalName() }}
                                    @else
                                        Nhấn để chọn file
                                    @endif
                                </span>
                                <span class="text-[16px] font-bold text-blue-700" wire:loading wire:target="importFile">
                                    Đang tải file...
                                </span>
                                <p class="mt-1 text-[14px] text-slate-500" wire:loading.remove wire:target="importFile">.xlsx, .xls, .csv</p>
                            </div>
                        </div>
                    </label>
                    @error('importFile')<span class="mt-1 block text-center text-sm text-red-500">{{ $message }}</span>@enderror
                    
                    <div class="mt-4 flex items-center gap-2">
                        <input type="checkbox" id="syncAttendanceIndex" wire:model="syncAttendance" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                        <label for="syncAttendanceIndex" class="text-[15px] text-slate-700 font-medium">Tự động thêm vào các buổi điểm danh đã có</label>
                    </div>

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
                    <button type="button" wire:click="closeImport" class="rounded-full border border-slate-300 bg-white px-8 py-2.5 text-[15px] font-bold text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="flex items-center gap-2 rounded-full bg-[#0a46d1] px-10 py-2.5 text-[15px] font-bold text-white transition-colors hover:bg-blue-800">
                        <span wire:loading.remove wire:target="processImport">Import</span>
                        <span wire:loading wire:target="processImport">Đang xử lý...</span>
                    </button>
                </div>
            </form>
            </div>
        </template>
    @endif

    @if ($archivingMemberId)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-4 flex items-center gap-3 text-red-600">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                            <x-user.icon name="alert-triangle" :size="20" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Xác nhận lưu trữ</h3>
                    </div>
                    <p class="text-sm text-slate-600">Bạn có chắc chắn muốn chuyển học viên này vào danh sách lưu trữ? Bạn có thể khôi phục lại sau nếu cần.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="closeArchiveConfirm" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 transition-colors">Hủy</button>
                        <button type="button" wire:click="archiveMember" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition-colors">Lưu trữ</button>
                    </div>
                </div>
            </div>
        </template>
    @endif

    @if ($isExporting)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm transition-all">
                <div class="w-full max-w-[500px] rounded-[24px] bg-white p-6 shadow-2xl">
                
                {{-- Header --}}
                <div class="mb-6 flex items-start justify-between">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <x-user.icon name="download" :size="24" />
                        </div>
                        <div>
                            <h3 class="text-[20px] font-bold text-slate-900">Xuất báo cáo Excel</h3>
                            <p class="mt-1 text-[14px] text-slate-600">Tùy chọn cấu hình báo cáo điểm danh</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeExport" class="mt-1 shrink-0 rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-user.icon name="x" :size="20" />
                    </button>
                </div>

                {{-- Form Content --}}
                <div class="space-y-4">
                    {{-- Class Selection --}}
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Lớp học cần xuất</label>
                        <select wire:model="exportClassId" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 text-[15px] font-medium text-slate-700 focus:border-primary focus:ring-primary/20">
                            <option value="all">Tất cả lớp học (Chia nhiều Sheet)</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->code }} - {{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Formula Selection --}}
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Công thức tính Chuyên cần (%)</label>
                        
                        <div class="mb-3 flex items-center gap-2">
                            <input type="checkbox" id="customFormulaCheck" wire:model.live="isCustomFormula" class="rounded border-slate-300 text-primary focus:ring-primary">
                            <label for="customFormulaCheck" class="text-[14px] font-semibold text-slate-700 cursor-pointer">Nhập công thức tùy chỉnh</label>
                        </div>

                        @if (! $isCustomFormula)
                        <div class="mb-3">
                            <select wire:model="selectedTemplate" class="w-full rounded-xl border-slate-200 bg-white py-2 px-3 text-[14px] font-medium text-slate-700 focus:border-primary focus:ring-primary/20">
                                <option value="(c + m) / t * 100">Mặc định: (c + m) / t * 100</option>
                                <option value="v / t * 100">Tính tỷ lệ vắng: v / t * 100</option>
                                <option value="(c + m + v) / t * 100">Điểm danh đầy đủ: (c + m + v) / t * 100</option>
                                <option value="(c + m - floor(m / 3)) / t * 100">Phạt đi muộn (3 lần muộn = 1 lần vắng): (c + m - floor(m / 3)) / t * 100</option>
                            </select>
                        </div>
                        @else
                        <div class="mt-4 border-t border-slate-100 pt-4">
                            <input type="text" wire:model="exportFormula" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 px-4 text-[15px] font-medium text-slate-700 focus:border-primary focus:ring-primary/20" placeholder="VD: (c + m) / t * 100">
                            <div class="mt-2 text-xs text-slate-500 space-y-1">
                                <p>Bạn có thể tự nhập công thức với các biến sau:</p>
                                <ul class="list-disc pl-4 grid grid-cols-2 gap-x-2">
                                    <li><code>c</code>: Số buổi có mặt</li>
                                    <li><code>m</code>: Số buổi đi muộn</li>
                                    <li><code>v</code>: Số buổi vắng không phép</li>
                                    <li><code>p</code>: Số buổi vắng có phép</li>
                                    <li><code>t</code>: Tổng số buổi đã học</li>
                                </ul>
                                <p class="text-[11px] mt-1 text-slate-400">Ví dụ: <code>(c + m) / t * 100</code>, hoặc <code>(c + m - floor(m / 3)) / t * 100</code> (3 muộn = 1 vắng)</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Footer Buttons --}}
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" wire:click="closeExport" class="rounded-full border border-slate-300 bg-white px-8 py-2.5 text-[15px] font-bold text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                        Hủy bỏ
                    </button>
                    <button type="button" wire:click="exportExcel" class="flex items-center gap-2 rounded-full bg-emerald-600 px-10 py-2.5 text-[15px] font-bold text-white transition-colors hover:bg-emerald-700">
                        <span wire:loading.remove wire:target="exportExcel">Xuất báo cáo</span>
                        <span wire:loading wire:target="exportExcel">Đang xử lý...</span>
                    </button>
                </div>
                </div>
            </div>
        </template>
    @endif
</div>
