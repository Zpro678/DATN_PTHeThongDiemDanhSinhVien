<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('classes.index') }}" class="rounded-lg border border-slate-200 p-2 text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-800">
                    <x-sams.icon name="arrow-left" class="h-5 w-5" />
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            {{ $class->subject_code ?: 'Môn học' }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $class->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-950/30 dark:text-green-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $class->status === 'active' ? 'Hoạt động' : 'Lưu trữ' }}
                        </span>
                    </div>
                    <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $class->name }}</h1>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button @click="$dispatch('open-edit-modal')" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-350 dark:hover:bg-slate-800">
                    <x-sams.icon name="edit" class="h-4 w-4" />
                    Sửa lớp học
                </button>
                
                <form action="{{ route('classes.archive', $class) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn lưu trữ lớp học này?')" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-350 dark:hover:bg-slate-800">
                        <x-sams.icon name="archive" class="h-4 w-4" />
                        Lưu trữ
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <!-- Banner Details Card -->
    <div class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-6 md:grid-cols-4">
            <!-- Mã tham gia -->
            <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Mã tham gia lớp</span>
                <div class="mt-2 flex items-center justify-between">
                    <span class="font-mono text-2xl font-bold text-slate-800 dark:text-slate-200 select-all">{{ $class->code }}</span>
                    <div class="flex gap-1">
                        <button onclick="navigator.clipboard.writeText('{{ $class->code }}'); alert('Đã sao chép mã lớp!')" class="rounded p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-600 dark:hover:bg-slate-800" title="Sao chép">
                            <x-sams.icon name="copy" class="h-4 w-4" />
                        </button>
                        <form action="{{ route('classes.regenerate-code', $class) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-600 dark:hover:bg-slate-800" title="Tạo mã mới">
                                <x-sams.icon name="refresh-cw" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Học kỳ -->
            <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Học kỳ</span>
                <span class="mt-2 block text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $class->semester ?: '--' }}</span>
            </div>

            <!-- Tổng sinh viên -->
            <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Sĩ số lớp</span>
                <span class="mt-2 block text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $class->members->count() }} học viên</span>
            </div>

            <!-- Tổng số buổi học -->
            <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Số buổi học</span>
                <span class="mt-2 block text-2xl font-bold text-slate-800 dark:text-slate-200">{{ $class->sessions->count() }} / {{ $class->total_sessions }} buổi</span>
            </div>
        </div>

        @if($class->description)
            <div class="mt-6 border-t border-slate-100 pt-4 dark:border-slate-800">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Mô tả lớp học</span>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-350">{{ $class->description }}</p>
            </div>
        @endif
    </div>

    <!-- Alert success/error -->
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800/30 dark:bg-green-950/20 dark:text-green-400" role="alert">
            <x-sams.icon name="check-circle" class="h-5 w-5 text-green-500" />
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabs component with AlpineJS -->
    <div x-data="{ activeTab: localStorage.getItem('class-active-tab-{{ $class->id }}') || 'students' }" x-init="$watch('activeTab', value => localStorage.setItem('class-active-tab-{{ $class->id }}', value))" class="space-y-6">
        <div class="border-b border-slate-200 dark:border-slate-800">
            <nav class="-mb-px flex gap-6" aria-label="Tabs">
                <button @click="activeTab = 'students'" :class="activeTab === 'students' ? 'border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-450'" class="flex items-center gap-2 border-b-2 py-4 px-1 text-sm font-semibold transition-all">
                    <x-sams.icon name="users" class="h-5 w-5" />
                    Sinh viên
                    <span :class="activeTab === 'students' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" class="ml-1 rounded-full py-0.5 px-2.5 text-xs font-semibold">
                        {{ $class->members->count() }}
                    </span>
                </button>

                <button @click="activeTab = 'sessions'" :class="activeTab === 'sessions' ? 'border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-450'" class="flex items-center gap-2 border-b-2 py-4 px-1 text-sm font-semibold transition-all">
                    <x-sams.icon name="calendar" class="h-5 w-5" />
                    Buổi điểm danh
                    <span :class="activeTab === 'sessions' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" class="ml-1 rounded-full py-0.5 px-2.5 text-xs font-semibold">
                        {{ $class->sessions->count() }}
                    </span>
                </button>

                <button @click="activeTab = 'config'" :class="activeTab === 'config' ? 'border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-400' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-450'" class="flex items-center gap-2 border-b-2 py-4 px-1 text-sm font-semibold transition-all">
                    <x-sams.icon name="settings" class="h-5 w-5" />
                    Cấu hình lớp
                </button>
            </nav>
        </div>

        <!-- Tab 1: Students List -->
        <div x-cloak x-show="activeTab === 'students'" class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Danh sách sinh viên trong lớp</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Xem, tìm kiếm, sửa thông tin sinh viên hoặc nhập danh sách hàng loạt.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('classes.import.form', $class) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-350 dark:hover:bg-slate-800">
                        <x-sams.icon name="file-spreadsheet" class="h-4 w-4" />
                        Nhập từ Excel/CSV
                    </a>
                    <a href="{{ route('classes.members.create', $class) }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        <x-sams.icon name="plus" class="h-4 w-4" />
                        Thêm sinh viên
                    </a>
                </div>
            </div>

            <!-- Import validation error notification -->
            @if(session('import_errors'))
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-900/30 dark:bg-red-950/20 dark:text-red-400">
                    <div class="flex items-center gap-2">
                        <x-sams.icon name="alert-triangle" class="h-5 w-5 text-red-500" />
                        <h4 class="text-sm font-bold">Phát hiện lỗi khi import:</h4>
                    </div>
                    <ul class="mt-2 list-inside list-disc text-xs space-y-1">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Students Table -->
            @if($class->members->count() > 0)
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-400">
                                <th class="py-3.5 px-6">MSSV</th>
                                <th class="py-3.5 px-6">Họ và tên</th>
                                <th class="py-3.5 px-6">Trạng thái</th>
                                <th class="py-3.5 px-6 text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-300">
                            @foreach($class->members as $member)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-950">
                                    <td class="py-4 px-6 font-mono font-semibold">{{ $member->student_code }}</td>
                                    <td class="py-4 px-6 font-bold text-slate-900 dark:text-white">{{ $member->full_name }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $member->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-950/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-950/30 dark:text-red-400' }}">
                                            {{ $member->status === 'active' ? 'Hoạt động' : 'Nghỉ học' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right flex items-center justify-end gap-2">
                                        <a href="{{ route('classes.members.edit', [$class, $member]) }}" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white" title="Sửa">
                                            <x-sams.icon name="edit-2" class="h-4 w-4" />
                                        </a>
                                        <form action="{{ route('classes.members.destroy', [$class, $member]) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa sinh viên khỏi lớp học này?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/20" title="Xóa">
                                                <x-sams.icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 p-12 text-center dark:border-slate-800">
                    <x-sams.icon name="user-plus" class="mx-auto h-8 w-8 text-slate-400" />
                    <h3 class="mt-4 text-base font-bold text-slate-900 dark:text-white">Danh sách trống</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lớp học này chưa có sinh viên nào đăng ký.</p>
                    <div class="mt-6 flex items-center justify-center gap-2">
                        <a href="{{ route('classes.import.form', $class) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-350 dark:hover:bg-slate-800">
                            Import danh sách
                        </a>
                        <a href="{{ route('classes.members.create', $class) }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                            Thêm thủ công
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Tab 2: Sessions list (Placeholder for TV3) -->
        <div x-cloak x-show="activeTab === 'sessions'" class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Danh sách các buổi học & điểm danh</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Xem và quản lý các phiên điểm danh theo từng buổi.</p>
                </div>
                <div>
                    <!-- TV3 handle this feature -->
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white opacity-50 cursor-not-allowed">
                        <x-sams.icon name="plus" class="h-4 w-4" />
                        Tạo buổi điểm danh
                    </button>
                </div>
            </div>

            @if($class->sessions->count() > 0)
                <!-- List sessions -->
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-400">
                                <th class="py-3.5 px-6">Buổi học</th>
                                <th class="py-3.5 px-6">Ngày học</th>
                                <th class="py-3.5 px-6">Hình thức</th>
                                <th class="py-3.5 px-6">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm text-slate-700 dark:text-slate-300">
                            @foreach($class->sessions as $session)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-950">
                                    <td class="py-4 px-6 font-bold text-slate-900 dark:text-white">{{ $session->name }}</td>
                                    <td class="py-4 px-6">{{ $session->date }}</td>
                                    <td class="py-4 px-6">{{ $session->type }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $session->status === 'closed' ? 'bg-slate-100 text-slate-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $session->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 p-12 text-center dark:border-slate-800">
                    <x-sams.icon name="calendar" class="mx-auto h-8 w-8 text-slate-400" />
                    <h3 class="mt-4 text-base font-bold text-slate-900 dark:text-white">Chưa có buổi học nào</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Các buổi học và phiên điểm danh sẽ được thiết lập bởi TV3.</p>
                </div>
            @endif
        </div>

        <!-- Tab 3: Config -->
        <div x-cloak x-show="activeTab === 'config'" class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Cấu hình & Tùy chọn chi tiết</h2>
                <div class="space-y-6 divide-y divide-slate-100 dark:divide-slate-800">
                    <div class="grid gap-4 md:grid-cols-3 py-4">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Tên môn / lớp</span>
                        <span class="md:col-span-2 text-slate-600 dark:text-slate-400">{{ $class->name }}</span>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3 py-4">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Mã môn học</span>
                        <span class="md:col-span-2 text-slate-600 dark:text-slate-400">{{ $class->subject_code ?: 'Chưa thiết lập' }}</span>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3 py-4">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Tổng số buổi học</span>
                        <span class="md:col-span-2 text-slate-600 dark:text-slate-400">{{ $class->total_sessions }} buổi ({{ $class->lessons_per_session }} tiết/buổi)</span>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3 py-4">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Bắt buộc duyệt học viên</span>
                        <span class="md:col-span-2 text-slate-600 dark:text-slate-400">
                            {{ $class->require_approval ? 'Có, duyệt thủ công khi sinh viên nhập mã lớp' : 'Không, tự động thêm vào lớp' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }" 
         @open-edit-modal.window="open = true"
         @keydown.escape.window="open = false"
         x-show="open" 
         class="relative z-50" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true"
         x-cloak>
         
         <!-- Background backdrop -->
         <div x-show="open" 
              x-transition:enter="ease-out duration-300" 
              x-transition:enter-start="opacity-0" 
              x-transition:enter-end="opacity-100" 
              x-transition:leave="ease-in duration-200" 
              x-transition:leave-start="opacity-100" 
              x-transition:leave-end="opacity-0" 
              class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

         <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <!-- Modal panel -->
                <div x-show="open" @click.away="open = false"
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    
                    <form action="{{ route('classes.update', $class) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-950/50 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white" id="modal-title">Cập nhật thông tin lớp học</h3>
                            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                                <span class="sr-only">Đóng</span>
                                <x-sams.icon name="x" class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="px-6 py-6 space-y-5">
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <!-- Tên lớp -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Tên lớp / Môn học <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $class->name) }}" required
                                        class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white placeholder-slate-400">
                                    @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <!-- Mã môn học -->
                                <div>
                                    <label for="subject_code" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Mã môn học</label>
                                    <input type="text" name="subject_code" id="subject_code" value="{{ old('subject_code', $class->subject_code) }}"
                                        class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                    @error('subject_code') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <!-- Học kỳ -->
                                <div>
                                    <label for="semester" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Học kỳ</label>
                                    <input type="text" name="semester" id="semester" value="{{ old('semester', $class->semester) }}" placeholder="VD: Học kỳ 1 2024-2025"
                                        class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                    @error('semester') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <!-- Tổng số buổi -->
                                <div>
                                    <label for="total_sessions" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Tổng số buổi <span class="text-red-500">*</span></label>
                                    <input type="number" name="total_sessions" id="total_sessions" value="{{ old('total_sessions', $class->total_sessions) }}" required min="1" max="100"
                                        class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                    @error('total_sessions') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <!-- Số tiết mỗi buổi -->
                                <div>
                                    <label for="lessons_per_session" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Số tiết mỗi buổi <span class="text-red-500">*</span></label>
                                    <input type="number" name="lessons_per_session" id="lessons_per_session" value="{{ old('lessons_per_session', $class->lessons_per_session) }}" required min="1" max="10"
                                        class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                    @error('lessons_per_session') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <!-- Mô tả -->
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Mô tả thêm</label>
                                    <textarea name="description" id="description" rows="3"
                                        class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('description', $class->description) }}</textarea>
                                    @error('description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <!-- Yêu cầu duyệt -->
                                <div class="md:col-span-2 mt-2">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" name="require_approval" value="1" {{ old('require_approval', $class->require_approval) ? 'checked' : '' }}
                                            class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:checked:bg-blue-500">
                                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Yêu cầu duyệt khi sinh viên tham gia bằng mã lớp</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 dark:border-slate-800 dark:bg-slate-950/50">
                            <button type="button" @click="open = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                Hủy bỏ
                            </button>
                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
         </div>
    </div>
</x-app-layout>
