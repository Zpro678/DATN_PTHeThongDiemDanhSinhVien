<div x-data="{ showImportModal: false }" class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-1">
                <a href="{{ route('managed-classes') }}" class="hover:text-primary transition-colors">Lớp tôi quản lý</a>
                <x-user.icon name="chevron-right" :size="14" />
                <span class="text-on-surface font-semibold">{{ $class->name }}</span>
            </div>
            <h1 class="text-2xl font-bold text-on-surface">{{ $class->name }}</h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                Mã lớp: <span class="font-bold text-on-surface">{{ $class->code }}</span>
                @if($class->semester)
                    <span class="mx-1.5 opacity-40">•</span>{{ $class->semester }}
                @endif
                @if($class->subject_code)
                    <span class="mx-1.5 opacity-40">•</span>Mã học phần: {{ $class->subject_code }}
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                x-data="{ shared: false }"
                :class="shared ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-surface-container text-on-surface hover:bg-surface-container-high'"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                x-on:click="navigator.clipboard.writeText('{{ url('/student/join-class?code=' . $class->code) }}'); shared = true; setTimeout(() => shared = false, 3000)"
            >
                <template x-if="!shared">
                    <x-user.icon name="send" :size="16" />
                </template>
                <template x-if="shared">
                    <x-user.icon name="check-circle" :size="16" />
                </template>
                <span x-text="shared ? 'Đã sao chép link' : 'Chia sẻ'"></span>
            </button>
            <a href="{{ route('lecturer.classes.settings', $class->id) }}" class="inline-flex items-center gap-2 rounded-lg bg-surface-container px-4 py-2 text-sm font-medium text-on-surface hover:bg-surface-container-high transition-colors">
                <x-user.icon name="settings" :size="16" />
                Cài đặt
            </a>
            <a href="{{ route('managed-classes') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-on-primary hover:bg-primary/90 transition-colors">
                <x-user.icon name="arrow-left" :size="16" />
                Trở về
            </a>
        </div>
    </div>

    {{-- Thống kê tổng quan --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        {{-- Sinh viên --}}
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-shadow hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                <x-user.icon name="users" :size="20" />
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Sinh viên</p>
                <p class="text-xl font-black text-on-surface leading-tight">{{ $studentsCount }}</p>
            </div>
        </div>

        {{-- Buổi điểm danh --}}
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-shadow hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-tertiary/10 text-tertiary">
                <x-user.icon name="calendar-check" :size="20" />
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Đã điểm danh</p>
                <p class="text-xl font-black text-on-surface leading-tight">{{ $sessionsCompleted }}<span class="text-sm font-bold text-on-surface-variant">/{{ $sessionsCount }}</span></p>
            </div>
        </div>

        {{-- Tỉ lệ tiến độ --}}
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-shadow hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-secondary/10 text-secondary">
                <x-user.icon name="bar-chart-2" :size="20" />
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Tiến độ</p>
                <p class="text-xl font-black text-on-surface leading-tight">
                    {{ $sessionsCount > 0 ? round(($sessionsCompleted / $sessionsCount) * 100) : 0 }}%
                </p>
            </div>
        </div>

        {{-- Đơn chờ duyệt --}}
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-shadow hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error/10 text-error">
                <x-user.icon name="file-text" :size="20" />
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Chờ duyệt</p>
                <p class="text-xl font-black text-on-surface leading-tight">{{ $pendingLeaveRequests }}</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6 lg:flex-row">
        {{-- Sidebar --}}
        <div class="flex flex-col gap-6 lg:w-72 shrink-0">
            {{-- Mã tham gia --}}
            <div x-data="{ copied: false }" class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-on-surface">Mã tham gia lớp</h3>
                    <div class="flex gap-1">
                        <button
                            type="button"
                            class="rounded-full p-2 text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors"
                            title="Sao chép mã"
                            x-on:click="navigator.clipboard.writeText('{{ $class->code }}'); copied = true; setTimeout(() => copied = false, 3000)"
                        >
                            <template x-if="!copied">
                                <x-user.icon name="copy" :size="18" />
                            </template>
                            <template x-if="copied">
                                <x-user.icon name="check-circle" :size="18" class="text-green-600" />
                            </template>
                        </button>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <div :class="copied ? 'bg-green-100 text-green-600' : 'bg-primary/10 text-primary'" class="rounded-2xl px-2 py-4 font-mono text-xl sm:text-2xl font-black tracking-wide whitespace-nowrap overflow-hidden text-ellipsis transition-colors" title="{{ $class->code }}">
                        <span x-show="!copied">{{ $class->code }}</span>
                        <span x-show="copied" x-cloak>Đã sao chép</span>
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-on-surface-variant">Gửi mã này cho sinh viên để tham gia lớp học.</p>
                <a
                    href="{{ route('lecturer.classes.settings', $class->id) }}"
                    class="mt-3 flex items-center justify-center gap-1.5 rounded-xl border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-bold text-orange-700 transition-colors hover:bg-orange-100"
                >
                    <x-user.icon name="refresh-cw" :size="13" />
                    Đổi mã lớp
                </a>
            </div>


            {{-- Thông tin lớp --}}
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20">
                <h3 class="mb-4 font-bold text-on-surface">Thông tin lớp học</h3>
                <div class="flex flex-col gap-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Tên lớp</span>
                        <span class="font-semibold text-on-surface text-right max-w-[60%]">{{ $class->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Trạng thái</span>
                        <span @class([
                            'rounded-full px-2 py-0.5 text-xs font-bold',
                            'bg-tertiary/10 text-tertiary' => $class->status === 'active',
                            'bg-orange-100 text-orange-700' => $class->status === 'archived',
                            'bg-surface-container text-on-surface-variant' => $class->status === 'ended',
                        ])>
                            @if($class->status === 'active') Đang hoạt động
                            @elseif($class->status === 'archived') Lưu trữ
                            @else Đã kết thúc @endif
                        </span>
                    </div>
                    @if($class->semester)
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Học kỳ</span>
                        <span class="font-semibold text-on-surface">{{ $class->semester }}</span>
                    </div>
                    @endif
                    @if($class->subject_code)
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Mã học phần</span>
                        <span class="font-semibold text-on-surface">{{ $class->subject_code }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Tổng số tiết</span>
                        <span class="font-semibold text-on-surface">{{ $class->total_lessons }} tiết</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Duyệt tham gia</span>
                        <span class="font-semibold text-on-surface">{{ $class->require_approval ? 'Có' : 'Không' }}</span>
                    </div>
                    @if($class->description)
                    <div class="pt-2 border-t border-outline-variant/20">
                        <span class="text-on-surface-variant block mb-1">Mô tả</span>
                        <span class="text-on-surface text-xs leading-relaxed">{{ $class->description }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex flex-1 flex-col gap-6">
            {{-- Quản lý sinh viên --}}
            <div class="flex flex-col gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 sm:flex-row sm:items-center sm:justify-between">
                 <div class="flex items-center gap-4">
                     <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-secondary/10 text-secondary">
                         <x-user.icon name="users" :size="24" />
                     </div>
                     <div>
                         <h3 class="font-bold text-on-surface">Danh sách sinh viên</h3>
                         <p class="text-sm text-on-surface-variant">{{ $studentsCount }} sinh viên trong lớp. Quản lý và thêm sinh viên.</p>
                     </div>
                 </div>
                 <div class="flex shrink-0 gap-2">
                     <button type="button" wire:click="openImport" class="inline-flex items-center gap-2 rounded-lg bg-surface-container px-4 py-2 text-sm font-medium text-on-surface transition-colors hover:bg-surface-container-high">
                         <x-user.icon name="upload" :size="16" />
                         Import danh sách
                     </button>
                     <a href="{{ route('lecturer.students.index', ['class_id' => $class->id]) }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-on-primary transition-colors hover:bg-primary/90">
                         <x-user.icon name="users" :size="16" />
                         Xem chi tiết
                     </a>
                 </div>
            </div>

            {{-- Buổi điểm danh gần đây --}}
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-bold text-on-surface flex items-center gap-2">
                        <x-user.icon name="clock" :size="18" class="text-primary" />
                        Buổi điểm danh gần đây
                    </h3>
                    <a href="{{ route('lecturer.attendance.index', ['class_id' => $class->id]) }}" class="text-xs font-bold text-primary hover:underline">
                        Xem tất cả
                    </a>
                </div>

                @if($recentSessions->isEmpty())
                    <div class="py-10 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-surface-container-low">
                            <x-user.icon name="calendar" :size="24" class="text-on-surface-variant" />
                        </div>
                        <p class="text-sm font-medium text-on-surface-variant">Chưa có buổi điểm danh nào.</p>
                        <a href="{{ route('lecturer.attendance.create', ['class_id' => $class->id]) }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:underline">
                            <x-user.icon name="plus" :size="14" />
                            Tạo buổi điểm danh đầu tiên
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-outline-variant/10">
                        @foreach($recentSessions as $session)
                            @php
                                $statusLabel = match($session->status) {
                                    'active'  => 'Đang mở',
                                    'closed'  => 'Đã đóng',
                                    'pending' => 'Chờ mở',
                                    default   => $session->status,
                                };
                                $statusClass = match($session->status) {
                                    'active'  => 'bg-tertiary/10 text-tertiary',
                                    'closed'  => 'bg-surface-container text-on-surface-variant',
                                    'pending' => 'bg-primary/10 text-primary',
                                    default   => 'bg-surface-container text-on-surface-variant',
                                };
                            @endphp
                            <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-surface-container-low">
                                        <x-user.icon name="calendar" :size="16" class="text-on-surface-variant" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-on-surface">{{ $session->name ?? 'Buổi học' }}</p>
                                        <p class="text-xs text-on-surface-variant">
                                            {{ $session->date ? $session->date->format('d/m/Y') : 'Chưa xác định' }}
                                            @if($session->start_time)
                                                · {{ $session->start_time }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        {{-- Điểm danh QR --}}
        <a href="{{ route('lecturer.attendance.qr.create', ['class_id' => $class->id]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-primary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                <x-user.icon name="qr-code" :size="24" />
            </div>
            <span class="text-sm font-bold text-on-surface text-center">Điểm danh QR</span>
        </a>

        {{-- Điểm danh Thủ công --}}
        <a href="{{ route('lecturer.attendance.manual.create', ['class_id' => $class->id]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-tertiary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-tertiary/10 text-tertiary transition-colors group-hover:bg-tertiary group-hover:text-white">
                <x-user.icon name="check-square" :size="24" />
            </div>
            <span class="text-sm font-bold text-on-surface text-center">Điểm danh thủ công</span>
        </a>

        {{-- Đơn xin nghỉ --}}
        <a href="{{ route('lecturer.leave-requests.index', ['class_id' => $class->id]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-error/10 text-error transition-colors group-hover:bg-error group-hover:text-white">
                <x-user.icon name="file-text" :size="24" />
            </div>
            <span class="text-sm font-bold text-on-surface text-center">
                Đơn xin nghỉ
                @if($pendingLeaveRequests > 0)
                    <span class="ml-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-error text-[10px] font-black text-white">{{ $pendingLeaveRequests }}</span>
                @endif
            </span>
        </a>

        {{-- Thống kê --}}
        <a href="{{ route('lecturer.class.statistics', ['class_id' => $class->id]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-secondary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-secondary/10 text-secondary transition-colors group-hover:bg-secondary group-hover:text-white">
                <x-user.icon name="bar-chart-2" :size="24" />
            </div>
            <span class="text-sm font-bold text-on-surface text-center">Thống kê</span>
        </a>
    </div>

    {{-- Import Modal --}}
    @if ($isImporting)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm transition-all">
                <form wire:submit="processImport" class="w-full max-w-[560px] rounded-[24px] bg-white p-8 shadow-2xl">
                
                {{-- Header --}}
                <div class="mb-8 flex items-start justify-between">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                            <x-user.icon name="upload" :size="24" />
                        </div>
                        <div>
                            <h3 class="text-[22px] font-bold text-slate-900">Import danh sách sinh viên</h3>
                            <p class="mt-1.5 text-[15px] text-slate-600">
                                Tải lên tệp Excel hoặc CSV chứa danh sách sinh viên. 
                                <button type="button" wire:click="downloadTemplate" class="font-bold text-blue-700 hover:underline">Tải mẫu file.</button>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeImport" class="mt-1 shrink-0 rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-user.icon name="x" :size="20" />
                    </button>
                </div>

                {{-- Form Content --}}
                <div class="space-y-6">
                    
                    {{-- File Dropzone --}}
                    <label class="group relative flex cursor-pointer flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-slate-200 bg-slate-50/50 py-12 transition-colors hover:border-blue-400 hover:bg-blue-50/50">
                        <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" class="peer absolute inset-0 h-full w-full cursor-pointer opacity-0">
                        
                        <div class="flex flex-col items-center justify-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border-[2.5px] border-slate-700 text-slate-700 transition-colors group-hover:border-blue-600 group-hover:text-blue-600">
                                <div wire:loading wire:target="importFile">
                                    <x-user.icon name="loader" class="animate-spin" :size="20" />
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <span class="text-[17px] font-bold text-slate-800 transition-colors group-hover:text-blue-700" wire:loading.remove wire:target="importFile">
                                    @if($importFile)
                                        {{ $importFile->getClientOriginalName() }}
                                    @else
                                        Nhấn để chọn file
                                    @endif
                                </span>
                                <span class="text-[17px] font-bold text-blue-700" wire:loading wire:target="importFile">
                                    Đang tải file...
                                </span>
                                <p class="mt-1.5 text-[15px] text-slate-500" wire:loading.remove wire:target="importFile">.xlsx, .xls, .csv</p>
                            </div>
                        </div>
                    </label>
                    @error('importFile')<span class="mt-1 block text-center text-sm text-red-500">{{ $message }}</span>@enderror
                    
                    <div class="mt-4 flex items-center gap-2">
                        <input type="checkbox" id="syncAttendanceShow" wire:model="syncAttendance" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                        <label for="syncAttendanceShow" class="text-[15px] text-slate-700 font-medium">Tự động thêm vào các buổi điểm danh đã có</label>
                    </div>

                    {{-- Error Summary --}}
                    @if(!empty($importErrors))
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <h4 class="mb-2 text-sm font-bold text-red-800">Đã nhập {{ $importSuccess }} sinh viên. Có {{ count($importErrors) }} lỗi:</h4>
                            <ul class="list-disc space-y-1 pl-5 text-[13px] text-red-700 max-h-32 overflow-y-auto">
                                @foreach($importErrors as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- Footer Buttons --}}
                <div class="mt-8 flex justify-end gap-4">
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
</div>
