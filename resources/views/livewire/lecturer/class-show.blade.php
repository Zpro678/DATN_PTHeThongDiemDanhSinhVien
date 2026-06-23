<div x-data="{ showImportModal: false }" class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface uppercase">{{ $class->name }}</h1>
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
            <a href="{{ route('managed-classes', ['ma_user' => auth()->id()]) }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-on-primary hover:bg-primary/90 transition-colors">
                <x-user.icon name="arrow-left" :size="16" />
                Trở về
            </a>
        </div>
    </div>

    {{-- Thống kê tổng quan --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        {{-- Học viên --}}
        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-shadow hover:shadow-md">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                <x-user.icon name="users" :size="20" />
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Học viên</p>
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
                <x-user.icon name="bar-chart" :size="20" />
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
                        @if(!$isEditingCode)
                        <button
                            type="button"
                            class="rounded-full p-2 text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors"
                            title="Đổi mã lớp"
                            wire:click="toggleEditCode"
                        >
                            <x-user.icon name="edit" :size="18" />
                        </button>
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
                        @endif
                    </div>
                </div>
                
                @if($isEditingCode)
                    <div class="mt-4">
                        <div class="relative">
                            <input type="text" wire:model="newClassCode" class="w-full rounded-xl border border-outline-variant py-2 pl-3 pr-10 text-center font-mono text-xl font-black uppercase tracking-wide text-on-surface focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Nhập mã lớp mới">
                            <button type="button" wire:click="generateRandomCode" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors" title="Tạo mã ngẫu nhiên">
                                <x-user.icon name="refresh-cw" :size="18" />
                            </button>
                        </div>
                        @error('newClassCode') <span class="mt-1 block text-center text-xs text-error">{{ $message }}</span> @enderror
                        <div class="mt-3 flex justify-center gap-2">
                            <button type="button" wire:click="toggleEditCode" class="rounded-lg bg-surface-container px-4 py-1.5 text-sm font-medium text-on-surface hover:bg-surface-container-high transition-colors">Hủy</button>
                            <button type="button" wire:click="updateClassCode" class="rounded-lg bg-primary px-4 py-1.5 text-sm font-medium text-on-primary hover:bg-primary/90 transition-colors">Lưu</button>
                        </div>
                    </div>
                @else
                    <div class="mt-4 text-center">
                        <div :class="copied ? 'bg-green-100 text-green-600' : 'bg-primary/10 text-primary'" class="rounded-2xl px-2 py-4 font-mono text-xl sm:text-2xl font-black tracking-wide whitespace-nowrap overflow-hidden text-ellipsis transition-colors" title="{{ $class->code }}">
                            <span x-show="!copied">{{ $class->code }}</span>
                            <span x-show="copied" x-cloak>Đã sao chép</span>
                        </div>
                    </div>
                    <p class="mt-3 text-center text-xs text-on-surface-variant">Gửi mã này cho học viên để tham gia lớp học.</p>
                @endif
            </div>


            {{-- Thông tin lớp --}}
            <div class="flex-1 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20">
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
            {{-- Quản lý học viên --}}
            <div class="flex flex-col gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 sm:flex-row sm:items-center sm:justify-between">
                 <div class="flex items-center gap-4">
                     <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-secondary/10 text-secondary">
                         <x-user.icon name="users" :size="24" />
                     </div>
                     <div>
                         <h3 class="font-bold text-on-surface">Danh sách học viên</h3>
                         <p class="text-sm text-on-surface-variant">{{ $studentsCount }} học viên trong lớp. Quản lý và thêm học viên.</p>
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
            <div class="flex flex-1 flex-col rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-bold text-on-surface flex items-center gap-2">
                        <x-user.icon name="clock" :size="18" class="text-primary" />
                        Buổi điểm danh gần đây ({{ $class->sessions->count() }})
                    </h3>
                    <a href="{{ route('lecturer.classes.attendance', $class->id) }}" class="text-sm font-bold text-primary transition-colors hover:text-primary/80">
                        Xem tất cả
                    </a>
                </div>

                @if($recentSessions->isEmpty())
                    <div class="flex flex-1 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-outline-variant/20 bg-surface-container-lowest py-12 text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <x-user.icon name="history" :size="32" />
                        </div>
                        <h3 class="text-lg font-bold text-on-surface">Chưa có dữ liệu</h3>
                        <p class="mt-1 text-sm text-on-surface-variant">Lớp học này chưa có buổi điểm danh nào.</p>
                    </div>
                @else
                <div class="space-y-4">
                    @foreach($recentSessions as $session)
                    <div class="group flex flex-col justify-between gap-4 rounded-2xl border border-outline-variant/20 bg-white p-4 transition-all hover:border-primary/30 hover:shadow-md sm:flex-row sm:items-center">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                                <x-user.icon name="{{ $session->qr_token ? 'qr-code' : 'check-square' }}" :size="20" />
                            </div>
                            <div>
                                <h4 class="flex items-center gap-3 font-bold text-on-surface line-clamp-1">
                                    <span>{{ $session->name }}</span>
                                    <span class="text-[11px] font-medium text-on-surface-variant/70">{{ $session->created_at->format('H:i') }}</span>
                                </h4>
                                <div class="mt-1 flex items-center gap-2 text-xs text-on-surface-variant">
                                    <span class="flex items-center gap-1"><x-user.icon name="calendar" :size="12" /> {{ $session->date->format('d/m/Y') }}</span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1"><x-user.icon name="clock" :size="12" /> {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-4 sm:justify-end">
                            <div class="flex gap-3 text-[13px] sm:flex-col sm:items-end sm:gap-1">
                                <div class="flex gap-2">
                                    <span class="font-semibold text-success" title="Có mặt">{{ $session->present_count }} có mặt</span>
                                    <span class="text-on-surface-variant/50">|</span>
                                    <span class="font-semibold text-error" title="Vắng">{{ $session->absent_count }} vắng</span>
                                </div>
                                @if($session->late_count > 0 || $session->excused_count > 0)
                                <div class="flex gap-2 text-on-surface-variant">
                                    @if($session->late_count > 0)<span title="Trễ">{{ $session->late_count }} trễ</span>@endif
                                    @if($session->late_count > 0 && $session->excused_count > 0)<span>•</span>@endif
                                    @if($session->excused_count > 0)<span title="Có phép">{{ $session->excused_count }} phép</span>@endif
                                </div>
                                @endif
                            </div>
                            <a href="{{ $session->qr_token ? route('lecturer.attendance.qr.session', $session) : route('lecturer.attendance.manual.session', $session) }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-surface-container text-on-surface-variant transition-colors hover:bg-primary hover:text-white" title="Chi tiết">
                                <x-user.icon name="chevron-right" :size="16" />
                            </a>
                        </div>
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
        <button type="button" wire:click="checkBeforeAttendance('qr')" class="w-full group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-primary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                <x-user.icon name="qr-code" :size="24" />
            </div>
            <span class="text-sm font-bold text-on-surface text-center">Điểm danh QR</span>
        </button>

        {{-- Điểm danh Thủ công --}}
        <button type="button" wire:click="checkBeforeAttendance('manual')" class="w-full group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-tertiary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-tertiary/10 text-tertiary transition-colors group-hover:bg-tertiary group-hover:text-white">
                <x-user.icon name="check-square" :size="24" />
            </div>
            <span class="text-sm font-bold text-on-surface text-center">Điểm danh thủ công</span>
        </button>

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
                            <div class="mt-1 text-[14px] text-slate-600">
                                <p>Tải lên tệp Excel hoặc CSV chứa danh sách học viên.</p>
                                <div class="mt-2 flex flex-col gap-1">
                                    <span class="font-medium">Tải file mẫu:</span>
                                    <div class="flex flex-wrap gap-4">
                                        <button type="button" wire:click="downloadFullTemplate" class="inline-flex items-center gap-1 font-bold text-blue-700 hover:underline">
                                            <x-user.icon name="download" :size="14" /> Mẫu đầy đủ
                                        </button>
                                        <button type="button" wire:click="downloadBasicTemplate" class="inline-flex items-center gap-1 font-bold text-blue-700 hover:underline">
                                            <x-user.icon name="download" :size="14" /> Mẫu cơ bản
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" wire:click="closeImport" class="mt-1 shrink-0 rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-user.icon name="x" :size="20" />
                    </button>
                </div>

                {{-- Form Content --}}
                <div class="space-y-4">
                    
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
                        <input type="checkbox" id="syncAttendanceShow" wire:model="syncAttendance" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                        <label for="syncAttendanceShow" class="text-[15px] text-slate-700 font-medium">Tự động thêm vào các buổi điểm danh đã có</label>
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
    {{-- No Students Popup --}}
    <template x-teleport="body">
        <div x-data="{ showPopup: @entangle('showNoStudentsPopup') }">
            <div x-show="showPopup" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-md"
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
                     class="relative w-full max-w-md overflow-hidden rounded-[24px] bg-white shadow-2xl">
                    
                    <button type="button" @click="showPopup = false" class="absolute right-4 top-4 rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-user.icon name="x" :size="20" />
                    </button>
                    
                    <div class="p-6 text-center">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-orange-500">
                            <x-user.icon name="alert-circle" :size="24" />
                        </div>
                        <h3 class="mb-1.5 text-lg font-bold text-slate-900">Lớp chưa có học viên</h3>
                        <p class="mb-5 text-[14px] text-slate-600">Vui lòng import danh sách lớp trước khi tiến hành điểm danh.</p>
                        
                        <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
                            <button type="button" @click="showPopup = false" class="rounded-full px-6 py-2.5 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-100">
                                Hủy bỏ
                            </button>
                            <button type="button" @click="showPopup = false; setTimeout(() => $wire.openImportFromPopup(), 200)" class="rounded-full bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-500/30 transition-all hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/40">
                                Tới trang import
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
