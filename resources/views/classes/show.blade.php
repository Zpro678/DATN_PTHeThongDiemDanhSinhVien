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
                <a href="{{ route('classes.edit', $class) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-350 dark:hover:bg-slate-800">
                    <x-sams.icon name="edit" class="h-4 w-4" />
                    Sửa lớp học
                </a>
                
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
</x-app-layout>
