<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Danh sách lớp học</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Quản lý và theo dõi các lớp học điểm danh của bạn.</p>
            </div>
            <div>
                <a href="{{ route('classes.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <x-sams.icon name="plus" class="h-4 w-4" />
                    Thêm lớp học
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Filters Section -->
    <div class="mb-8 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="GET" action="{{ route('classes.index') }}" class="flex flex-col gap-4 md:flex-row md:items-center">
            <div class="relative flex-1">
                <x-sams.icon name="search" class="absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên môn, mã môn, hoặc mã lớp..." class="w-full rounded-lg border border-slate-200 bg-slate-50/50 py-2.5 pl-11 pr-4 text-sm outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100 dark:focus:bg-slate-900" />
            </div>
            
            <div class="w-full md:w-48">
                <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-200 bg-slate-50/50 py-2.5 px-3 text-sm outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100 dark:focus:bg-slate-900">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Đã lưu trữ</option>
                </select>
            </div>

            @if(request('search') || request('status'))
                <div>
                    <a href="{{ route('classes.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-800">
                        Đặt lại
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800/30 dark:bg-green-950/20 dark:text-green-400" role="alert">
            <x-sams.icon name="check-circle" class="h-5 w-5 text-green-500" />
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Classes Grid -->
    @if($classes->count() > 0)
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($classes as $class)
                <div class="group flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-6 transition-all hover:border-blue-500 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
                    <div>
                        <div class="flex items-start justify-between">
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ $class->subject_code ?: 'Môn học' }}
                            </span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $class->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-950/30 dark:text-green-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400' }}">
                                {{ $class->status === 'active' ? 'Hoạt động' : 'Lưu trữ' }}
                            </span>
                        </div>

                        <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white group-hover:text-blue-600">
                            <a href="{{ route('classes.show', $class) }}">{{ $class->name }}</a>
                        </h3>
                        
                        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400 line-clamp-2">
                            {{ $class->description ?: 'Không có mô tả.' }}
                        </p>

                        <!-- Class Info Stats -->
                        <div class="mt-6 grid grid-cols-2 gap-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                            <div>
                                <span class="block text-xs font-medium text-slate-400 dark:text-slate-500">Mã tham gia</span>
                                <div class="mt-0.5 flex items-center gap-1.5 font-mono text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <span>{{ $class->code }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-slate-400 dark:text-slate-500">Sinh viên</span>
                                <span class="mt-0.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $class->members->count() }} học viên
                                </span>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-slate-400 dark:text-slate-500">Học kỳ</span>
                                <span class="mt-0.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $class->semester ?: '--' }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-slate-400 dark:text-slate-500">Tổng số buổi</span>
                                <span class="mt-0.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $class->total_sessions ?: 0 }} buổi
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <a href="{{ route('classes.show', $class) }}" class="flex-1 rounded-lg bg-slate-50 py-2 text-center text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                            Chi tiết
                        </a>
                        <a href="{{ route('classes.edit', $class) }}" class="rounded-lg border border-slate-200 p-2 text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-800" title="Chỉnh sửa">
                            <x-sams.icon name="edit" class="h-4 w-4" />
                        </a>
                        
                        <form action="{{ route('classes.archive', $class) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn lưu trữ lớp học này?')" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-lg border border-slate-200 p-2 text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-800" title="Lưu trữ">
                                <x-sams.icon name="archive" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $classes->links() }}
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 p-12 text-center dark:border-slate-800">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                <x-sams.icon name="book" class="h-6 w-6 text-slate-400" />
            </div>
            <h3 class="mt-4 text-base font-bold text-slate-900 dark:text-white">Không tìm thấy lớp học nào</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                @if(request('search') || request('status'))
                    Không có lớp học nào khớp với bộ lọc tìm kiếm của bạn.
                @else
                    Bạn chưa tạo lớp học nào. Hãy bắt đầu bằng cách tạo lớp đầu tiên.
                @endif
            </p>
            <div class="mt-6">
                @if(request('search') || request('status'))
                    <a href="{{ route('classes.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        Đặt lại tìm kiếm
                    </a>
                @else
                    <a href="{{ route('classes.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        <x-sams.icon name="plus" class="h-4 w-4" />
                        Thêm lớp học
                    </a>
                @endif
            </div>
        </div>
    @endif
</x-app-layout>
