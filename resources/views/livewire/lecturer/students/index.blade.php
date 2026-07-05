<div class="w-full space-y-6 px-6 py-6 pb-24 sm:px-10 lg:px-16">


    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
    @endif
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
    @endif

    <section class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ([
            ['label' => 'Tổng học viên', 'value' => $attendanceOverview['total_students'], 'icon' => 'users', 'color' => 'text-primary', 'bg' => 'bg-primary/10', 'iconColor' => 'text-primary', 'border' => 'hover:border-primary/30'],
            ['label' => 'Có mặt', 'value' => $attendanceOverview['present_sessions'], 'icon' => 'check-circle', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'iconColor' => 'text-emerald-600', 'border' => 'hover:border-emerald-300'],
            ['label' => 'Muộn', 'value' => $attendanceOverview['late_sessions'], 'icon' => 'clock', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50', 'iconColor' => 'text-amber-600', 'border' => 'hover:border-amber-300'],
            ['label' => 'Vắng', 'value' => $attendanceOverview['absent_sessions'], 'icon' => 'x-circle', 'color' => 'text-red-600', 'bg' => 'bg-red-50', 'iconColor' => 'text-red-600', 'border' => 'hover:border-red-300'],
            ['label' => 'TB chuyên cần', 'value' => $attendanceOverview['attendance_percent'].'%', 'icon' => 'bar-chart', 'color' => 'text-slate-900', 'bg' => 'bg-primary/10', 'iconColor' => 'text-primary', 'border' => 'hover:border-primary/30'],
        ] as $overviewItem)
            <div @class([
                'group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5',
                $overviewItem['border'],
                'col-span-2 sm:col-span-1 lg:col-span-1' => $loop->last,
            ])>
                <div class="flex items-center gap-3">
                    <div class="{{ $overviewItem['bg'] }} flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110">
                        <x-user.icon :name="$overviewItem['icon']" :size="20" class="{{ $overviewItem['iconColor'] }}" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-bold uppercase tracking-wider text-slate-700">{{ $overviewItem['label'] }}</p>
                        <p class="mt-0.5 text-lg font-extrabold {{ $overviewItem['color'] }}">{{ $overviewItem['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    <div class="grid gap-3 lg:grid-cols-[1fr_260px_auto]">
        <label class="relative">
            <x-user.icon name="search" :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Tìm theo tên, mã học viên hoặc email..." class="w-full rounded-xl border-slate-200 py-2.5 pl-11 pr-4 text-sm focus:border-primary focus:ring-primary/20">
        </label>
        <x-custom-select wire:model.live="classFilter" placeholder="" :options="collect($classes)
            ->map(fn ($class) => ['value' => (string) $class->id, 'label' => ($class->class_code ?? $class->join_key) . ' - ' . $class->name])
            ->values()->all()" />
        <div class="inline-flex w-max ml-auto rounded-xl bg-slate-100 p-1">
            <button type="button" wire:click="setStatusFilter('active')" @class(['rounded-lg px-4 py-2 text-xs font-bold transition-colors', 'bg-white text-primary shadow-sm' => $statusFilter === 'active', 'text-slate-500' => $statusFilter !== 'active'])>Đang học</button>
            <button type="button" wire:click="setStatusFilter('archived')" @class(['rounded-lg px-4 py-2 text-xs font-bold transition-colors', 'bg-white text-primary shadow-sm' => $statusFilter === 'archived', 'text-slate-500' => $statusFilter !== 'archived'])>Lưu trữ</button>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col" style="min-height: 500px;">
        <div class="overflow-x-auto flex-1 bg-white {{ $members->count() > 30 ? 'max-h-[700px] overflow-y-auto relative' : '' }}">
            <table class="w-full min-w-[900px] text-left whitespace-nowrap">
                <thead class="text-sm font-bold uppercase tracking-wider text-slate-500 {{ $members->count() > 30 ? 'bg-slate-100 sticky top-0 z-10 shadow-sm' : 'bg-slate-50' }}">
                    <tr>
                        <th class="px-6 py-4">Học viên</th>
                        <th class="px-4 py-4">Lớp học</th>
                        <th class="px-4 py-4 text-center">Có mặt</th>
                        <th class="px-4 py-4 text-center">Muộn</th>
                        <th class="px-4 py-4 text-center">Vắng</th>
                        <th class="px-4 py-4 text-center">Liên kết</th>
                        <th class="px-4 py-4 text-center">Chuyên cần</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($members as $member)
                        @php
                            $stats = $attendanceStats[$member->id] ?? [
                                'present_sessions' => 0,
                                'late_sessions' => 0,
                                'absent_sessions' => 0,
                                'attendance_percent' => 0,
                                'is_warning' => false,
                                'is_banned' => false,
                            ];
                            $rate = (float) $stats['attendance_percent'];
                            $isBanned = $stats['is_banned'] ?? false;
                            $isWarning = $stats['is_warning'] ?? false;
                        @endphp
                        <tr @class(['transition-colors hover:bg-slate-50/70', 'bg-red-50/30' => $isBanned, 'bg-amber-50/30' => $isWarning && !$isBanned])>
                            <td class="px-6 py-4">
                                <a href="{{ route('lecturer.students.show', $member) }}" class="flex items-center gap-3">
                                    @if($member->user && $member->user->avatar)
                                        <img src="{{ str_starts_with($member->user->avatar, 'http') ? $member->user->avatar : asset('storage/' . $member->user->avatar) }}" alt="{{ $member->displayName }}" class="h-10 w-10 shrink-0 rounded-full object-cover">
                                    @else
                                        <span @class([
                                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-full font-bold',
                                            'bg-red-100 text-red-600'     => $isBanned,
                                            'bg-amber-100 text-amber-600' => $isWarning && !$isBanned,
                                            'bg-primary/10 text-primary'  => !$isBanned && !$isWarning,
                                        ])>{{ mb_strtoupper(mb_substr(trim((string)$member->displayName) ?: 'S', 0, 1)) }}</span>
                                    @endif
                                    <span>
                                        <span class="block text-sm font-bold text-slate-900">{{ $member->displayName }}</span>
                                        <span class="block text-xs text-slate-500">{{ $member->email ?? ($member->user?->email ?? 'Chưa có email') }}</span>
                                    </span>
                                </a>
                            </td>
                            <td class="px-4 py-4">
                                <span class="block text-sm font-semibold text-slate-700">{{ $member->courseClass->name }}</span>
                                <span class="text-xs text-slate-500">{{ $member->courseClass->class_code ?? $member->courseClass->join_key }}</span>
                            </td>
                            <td class="px-4 py-4 text-center text-sm font-bold text-emerald-600">{{ $stats['present_sessions'] }}</td>
                            <td class="px-4 py-4 text-center text-sm font-bold text-amber-600">{{ $stats['late_sessions'] }}</td>
                            <td class="px-4 py-4 text-center text-sm font-bold text-red-600">{{ $stats['absent_sessions'] }}</td>
                            <td class="px-4 py-4 text-center">
                                @if($member->user_id)
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
                                <div class="flex flex-col items-center gap-1">
                                    <span @class(['inline-flex rounded-full px-3 py-1 text-xs font-bold', 'bg-red-50 text-red-700' => $isBanned, 'bg-amber-50 text-amber-700' => $isWarning, 'bg-emerald-50 text-emerald-700' => !$isBanned && !$isWarning])>{{ $rate }}%</span>
                                    @if ($isBanned)
                                        <span class="text-[10px] font-bold uppercase tracking-wide text-red-600">Cấm thi</span>
                                    @elseif ($isWarning)
                                        <span class="text-[10px] font-bold uppercase tracking-wide text-amber-600">Cảnh báo</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($isBanned)
                                        <a href="{{ route('lecturer.students.show', $member) }}" class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-2.5 py-1.5 text-[11px] font-bold text-red-700 transition-colors hover:bg-red-200" title="Nguy cơ cấm thi">
                                            <x-user.icon name="alert-triangle" :size="13" />
                                            Cấm thi
                                        </a>
                                    @elseif ($isWarning)
                                        <a href="{{ route('lecturer.students.show', $member) }}" class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1.5 text-[11px] font-bold text-amber-700 transition-colors hover:bg-amber-200" title="Cảnh báo chuyên cần">
                                            <x-user.icon name="alert-triangle" :size="13" />
                                            Cảnh báo
                                        </a>
                                    @endif
                                    <a href="{{ route('lecturer.students.show', $member) }}" class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-primary/10 hover:text-primary" title="Xem chi tiết"><x-user.icon name="eye" :size="18" /></a>
                                    @if ($statusFilter === 'active')
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
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-slate-700">Email</span>
                            <input type="email" wire:model="editingEmail" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20" placeholder="nva@email.com">
                            @error('editingEmail')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Trạng thái</span><x-custom-select wire:model="editingStatus" placeholder="" :value="$editingStatus" :options="[['value' => 'active', 'label' => 'Đang học'], ['value' => 'dropped', 'label' => 'Đã thôi học']]" /></label>
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
                            <x-custom-select wire:model="newClassId" placeholder="-- Chọn lớp học --" :value="$newClassId" :options="collect($classes)
                                ->map(fn ($class) => ['value' => (string) $class->id, 'label' => $class->join_key . ' - ' . $class->name])
                                ->values()->all()" />
                            @error('newClassId')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Họ và tên</span><input wire:model="newName" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20" placeholder="Nguyễn Văn A">@error('newName')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                        <label class="block space-y-2">
                            <span class="text-sm font-semibold text-slate-700">Email (Không bắt buộc)</span>
                            <input type="email" wire:model="newEmail" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20" placeholder="nva@email.com">
                            @error('newEmail')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
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
                                Tải lên tệp Excel hoặc CSV chứa danh sách học viên. Bạn có thể tải: 
                                <button type="button" wire:click="downloadBasicTemplate" class="font-bold text-blue-700 hover:underline">File mẫu Excel</button>
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
                        <x-custom-select wire:model="importClassId" placeholder="-- Chọn lớp học để import --" :value="$importClassId" :options="collect($classes)
                            ->map(fn ($class) => ['value' => (string) $class->id, 'label' => $class->join_key . ' - ' . $class->name])
                            ->values()->all()" />
                        @error('importClassId')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                    </div>

                    {{-- File Dropzone --}}
                    <label class="group relative flex @if($isImportingStatus) cursor-not-allowed opacity-60 @else cursor-pointer @endif flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-slate-200 bg-slate-50/50 py-8 transition-colors hover:border-blue-400 hover:bg-blue-50/50">
                        <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" @if($isImportingStatus) disabled @endif class="peer absolute inset-0 h-full w-full cursor-pointer opacity-0">
                        
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
                        <button type="button" wire:click="closeImport" class="rounded-full border border-slate-300 bg-white px-8 py-2.5 text-[15px] font-bold text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="flex items-center gap-2 rounded-full bg-[#0a46d1] px-10 py-2.5 text-[15px] font-bold text-white transition-colors hover:bg-blue-800">
                            <span wire:loading.remove wire:target="processImport">Import</span>
                            <span wire:loading wire:target="processImport">Đang xử lý...</span>
                        </button>
                    @else
                        <button type="button" disabled class="flex items-center gap-2 rounded-full bg-slate-100 px-10 py-2.5 text-[15px] font-bold text-slate-400 cursor-not-allowed">
                            Đang xử lý...
                        </button>
                    @endif
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
            <div class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" x-data="{ showHelpModal: false }">

                {{-- Main Modal (luôn căn giữa màn hình) --}}
                <div class="w-[500px] max-w-[calc(100vw-2rem)] rounded-[24px] bg-white p-6 shadow-2xl">
                
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

                {{-- Form Content only (single column now) --}}
                <div class="grid grid-cols-1 gap-6 items-start">
                    {{-- Left Column: Form Content --}}
                    <div class="space-y-4">
                        {{-- Class Selection --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Lớp học cần xuất</label>
                            <div class="mb-3" x-data="{
                                open: false,
                                value: @entangle('exportClassId'),
                                position: 'bottom',
                                options: [
                                    { value: 'all', label: 'Tất cả lớp học (Chia nhiều Sheet)' },
                                    @foreach ($classes as $class)
                                        { value: '{{ $class->id }}', label: '{{ $class->join_key }} - {{ $class->name }}' },
                                    @endforeach
                                ],
                                get selectedOption() { return this.options.find(o => o.value == this.value) ?? this.options[0] },
                                checkPosition() {
                                    this.$nextTick(() => {
                                        let rect = this.$refs.btn.getBoundingClientRect();
                                        let menuRect = this.$refs.menu.getBoundingClientRect();
                                        let spaceBelow = window.innerHeight - rect.bottom;
                                        let spaceAbove = rect.top;
                                        this.position = (spaceBelow < menuRect.height && spaceAbove > spaceBelow) ? 'top' : 'bottom';
                                    });
                                }
                            }" @click.outside="open = false" @keydown.escape.window="open = false">
                                <div class="relative">
                                    <button type="button" @click="open = !open; if(open) checkPosition();" x-ref="btn"
                                        class="flex w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-bold text-slate-700 outline-none transition-all hover:border-primary focus:border-primary focus:ring-2 focus:ring-primary/20"
                                        :class="open ? 'border-primary ring-2 ring-primary/20' : ''">
                                        <span x-text="selectedOption.label"></span>
                                        <x-user.icon name="chevron-down" :size="16" class="shrink-0 text-slate-400 transition-transform duration-200" x-bind:class="open ? 'rotate-180 text-primary' : ''" />
                                    </button>
                                    <div x-show="open" x-cloak x-ref="menu" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                                        class="absolute left-0 right-0 z-50 overflow-hidden max-h-60 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                                        :class="position === 'top' ? 'bottom-full mb-1.5' : 'top-full mt-1.5'">
                                        <template x-for="option in options" :key="option.value">
                                            <button type="button"
                                                @click="value = option.value; open = false"
                                                class="flex w-full items-center justify-between px-4 py-3 text-sm font-bold transition-colors hover:bg-slate-50"
                                                :class="value == option.value ? 'text-primary bg-primary/5' : 'text-slate-700'">
                                                <span x-text="option.label"></span>
                                                <span x-show="value == option.value" class="flex h-4 w-4 items-center justify-center rounded-full bg-primary">
                                                    <x-user.icon name="check" :size="10" class="text-white" />
                                                </span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Formula Selection --}}
                        <div>
                            <label class="mb-2 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                <span>Công thức tính Chuyên cần (%)</span>
                                <button type="button" @click="showHelpModal = !showHelpModal" class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition-colors hover:bg-slate-200" title="Hướng dẫn các ký hiệu">
                                    <x-user.icon name="help-circle" :size="14" />
                                </button>
                            </label>
                            
                            @if (!$isCustomFormula)
                                <!-- Dropdown Chọn tên công thức (Custom Alpine.js) -->
                                <div class="mb-3" x-data="{
                                    open: false,
                                    value: @entangle('selectedTemplate'),
                                    position: 'bottom',
                                    options: [
                                        { value: '(c + m + p) / t * 100', label: 'Mặc định' },
                                        { value: 'v / t * 100', label: 'Tính tỷ lệ vắng' },
                                        { value: '(c + m + v + p) / t * 100', label: 'Điểm danh đầy đủ' },
                                        { value: '(c + m + p - floor(m / 3)) / t * 100', label: 'Phạt đi muộn (3 lần muộn = 1 lần vắng)' },
                                    ],
                                    get label() { return this.options.find(o => o.value == this.value)?.label ?? 'Chọn...' },
                                    checkPosition() {
                                        this.$nextTick(() => {
                                            let rect = this.$refs.btn.getBoundingClientRect();
                                            let menuRect = this.$refs.menu.getBoundingClientRect();
                                            let spaceBelow = window.innerHeight - rect.bottom;
                                            let spaceAbove = rect.top;
                                            this.position = (spaceBelow < menuRect.height && spaceAbove > spaceBelow) ? 'top' : 'bottom';
                                        });
                                    }
                                }" @click.outside="open = false" @keydown.escape.window="open = false">
                                    <div class="relative">
                                        <button type="button" @click="open = !open; if(open) checkPosition();" x-ref="btn"
                                            class="flex w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-bold text-slate-700 outline-none transition-all hover:border-primary focus:border-primary focus:ring-2 focus:ring-primary/20"
                                            :class="open ? 'border-primary ring-2 ring-primary/20' : ''">
                                            <span x-text="label"></span>
                                            <x-user.icon name="chevron-down" :size="16" class="shrink-0 text-slate-400 transition-transform duration-200" x-bind:class="open ? 'rotate-180 text-primary' : ''" />
                                        </button>
                                        <div x-show="open" x-cloak x-ref="menu" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                                            class="absolute left-0 right-0 z-50 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10"
                                            :class="position === 'top' ? 'bottom-full mb-1.5' : 'top-full mt-1.5'">
                                            <template x-for="option in options" :key="option.value">
                                                <button type="button"
                                                    @click="value = option.value; open = false"
                                                    class="flex w-full items-center justify-between px-4 py-3 text-sm font-bold transition-colors hover:bg-slate-50"
                                                    :class="value == option.value ? 'text-primary bg-primary/5' : 'text-slate-700'">
                                                    <span x-text="option.label"></span>
                                                    <span x-show="value == option.value" class="flex h-4 w-4 items-center justify-center rounded-full bg-primary">
                                                        <x-user.icon name="check" :size="10" class="text-white" />
                                                    </span>
                                                </button>
                                            </template>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer Buttons --}}
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="closeExport" class="rounded-full border border-slate-300 bg-white px-8 py-2.5 text-[15px] font-bold text-slate-700 transition-colors hover:bg-slate-50 hover:text-slate-900">
                        Hủy bỏ
                    </button>
                    <button type="button" wire:click="exportExcel" class="flex items-center gap-2 rounded-full bg-emerald-600 px-10 py-2.5 text-[15px] font-bold text-white transition-colors hover:bg-emerald-700">
                        <span wire:loading.remove wire:target="exportExcel">Xuất Excel</span>
                        <span wire:loading wire:target="exportExcel">Đang xuất...</span>
                    </button>
                </div>
                </div>
                {{-- End Main Modal --}}

                {{-- Side Panel: Hướng dẫn (fixed, bên phải modal, không ảnh hưởng vị trí modal) --}}
                <div
                    x-show="showHelpModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-x-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-x-4 scale-95"
                    class="fixed z-[115] w-[290px] max-w-[calc(100vw-2rem)] rounded-[24px] bg-white p-5 shadow-2xl border border-slate-100 left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 xl:left-[calc(50vw+262px)] xl:translate-x-0"
                    style="display: none;"
                >
                    {{-- Panel Header --}}
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                        <p class="font-bold text-slate-800 flex items-center gap-1.5 text-sm">
                            <x-user.icon name="help-circle" class="text-primary" :size="16" />
                            Hướng dẫn các ký hiệu
                        </p>
                        <button type="button" @click="showHelpModal = false" class="text-slate-400 hover:text-slate-600 transition-colors rounded-full p-1 hover:bg-slate-100">
                            <x-user.icon name="x" :size="14" />
                        </button>
                    </div>

                    <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                        {{-- Công thức mẫu --}}
                        <div>
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">Công thức mẫu</p>
                            <ul class="space-y-1.5">
                                <li class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-slate-700">Mặc định</span>
                                    <code class="bg-slate-100 rounded px-1.5 py-0.5 text-[11px] text-primary font-mono">(c + m + p) / t * 100</code>
                                </li>
                                <li class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-slate-700">Tính tỷ lệ vắng</span>
                                    <code class="bg-slate-100 rounded px-1.5 py-0.5 text-[11px] text-primary font-mono">v / t * 100</code>
                                </li>
                                <li class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-slate-700">Điểm danh đầy đủ</span>
                                    <code class="bg-slate-100 rounded px-1.5 py-0.5 text-[11px] text-primary font-mono">(c + m + v + p) / t * 100</code>
                                </li>
                                <li class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-slate-700">Phạt đi muộn</span>
                                    <code class="bg-slate-100 rounded px-1.5 py-0.5 text-[11px] text-primary font-mono">(c + m + p - floor(m/3)) / t * 100</code>
                                </li>
                            </ul>
                        </div>

                        <div class="border-t border-slate-100 pt-3">
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">Ký hiệu biến (công thức)</p>
                            <ul class="space-y-1.5">
                                <li class="flex items-center gap-2">
                                    <code class="bg-emerald-50 text-emerald-700 rounded px-1.5 py-0.5 font-mono text-[11px] min-w-[20px] text-center">c</code>
                                    <span>Số buổi có mặt</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <code class="bg-amber-50 text-amber-700 rounded px-1.5 py-0.5 font-mono text-[11px] min-w-[20px] text-center">m</code>
                                    <span>Số buổi đi muộn</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <code class="bg-red-50 text-red-700 rounded px-1.5 py-0.5 font-mono text-[11px] min-w-[20px] text-center">v</code>
                                    <span>Vắng không phép</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <code class="bg-blue-50 text-blue-700 rounded px-1.5 py-0.5 font-mono text-[11px] min-w-[20px] text-center">p</code>
                                    <span>Nghỉ có phép</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <code class="bg-slate-100 text-slate-700 rounded px-1.5 py-0.5 font-mono text-[11px] min-w-[20px] text-center">t</code>
                                    <span>Tổng số buổi đã học</span>
                                </li>
                            </ul>
                        </div>

                        <div class="border-t border-slate-100 pt-3">
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">Hàm toán học</p>
                            <ul class="space-y-1.5">
                                <li class="flex items-center gap-2">
                                    <code class="bg-slate-100 text-slate-700 rounded px-1.5 py-0.5 font-mono text-[11px]">floor()</code>
                                    <span>Làm tròn xuống</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <code class="bg-slate-100 text-slate-700 rounded px-1.5 py-0.5 font-mono text-[11px]">ceil()</code>
                                    <span>Làm tròn lên</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <code class="bg-slate-100 text-slate-700 rounded px-1.5 py-0.5 font-mono text-[11px]">round()</code>
                                    <span>Làm tròn thường</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                {{-- End Side Panel --}}

            </div>
        </template>
    @endif
</div>
