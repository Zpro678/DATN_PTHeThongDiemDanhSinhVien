<x-app-layout variant="admin" page-title="Chi tiết tài khoản">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Breadcrumb & Header Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-white transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                            Trang chủ
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <a href="{{ route('admin.accounts.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2 dark:text-gray-400 dark:hover:text-white transition-colors">Tài khoản</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-500">Chi tiết</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="flex gap-3">
                <a href="{{ route('admin.accounts.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                    <svg class="mr-2 -ml-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Quay lại
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center text-green-800 dark:text-green-300">
                <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center text-red-800 dark:text-red-300">
                <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Profile Card -->
            <div class="xl:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                    <div class="px-6 pb-6 relative">
                        <div class="flex justify-center -mt-16 mb-4">
                            <div class="relative rounded-full border-4 border-white dark:border-gray-800 overflow-hidden h-32 w-32 bg-white dark:bg-gray-800">
                                @if($user->avatar)
                                    <img class="h-full w-full object-cover {{ $user->status === 'blocked' ? 'grayscale' : '' }}" src="{{ asset('storage/' . $user->avatar) }}" alt="">
                                @else
                                    <img class="h-full w-full object-cover {{ $user->status === 'blocked' ? 'grayscale' : '' }}" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff&size=128" alt="">
                                @endif
                                
                                @if($user->status === 'active')
                                    <div class="absolute bottom-1 right-4 h-4 w-4 rounded-full bg-green-500 border-2 border-white dark:border-gray-800"></div>
                                @else
                                    <div class="absolute bottom-1 right-4 h-4 w-4 rounded-full bg-red-500 border-2 border-white dark:border-gray-800"></div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $user->email }}</p>
                            
                            <div class="mt-4 flex flex-wrap justify-center gap-2">
                                @if($user->hasRole('admin'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50">
                                        Admin
                                    </span>
                                @elseif($user->hasRole('teacher'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50">
                                        Giảng viên
                                    </span>
                                @elseif($user->hasRole('student'))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                        Sinh viên
                                    </span>
                                @endif
                                
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border border-green-200 dark:border-green-800/50">
                                        Đang hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800/50">
                                        Bị khóa
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 space-y-4">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Tham gia:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $user->created_at?->format('d/m/Y') ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Truy cập cuối:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $user->updated_at?->diffForHumans() ?? 'N/A' }}</span>
                            </div>
                            @if($user->code)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Mã định danh:</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $user->code }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Thao tác nhanh</h4>
                    <div class="space-y-3">
                        <a href="{{ route('admin.accounts.edit', $user) }}" class="w-full flex items-center justify-center px-4 py-2 border border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Chỉnh sửa thông tin
                        </a>
                        
                        <form action="{{ route('admin.accounts.toggle-status', $user) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            @if($user->status === 'active')
                            <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn khóa tài khoản này?')" class="w-full flex items-center justify-center px-4 py-2 border border-red-600 text-red-600 dark:text-red-400 dark:border-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Khóa tài khoản
                            </button>
                            @else
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-green-600 text-green-600 dark:text-green-400 dark:border-green-400 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                Mở khóa tài khoản
                            </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <!-- Details & Stats -->
            <div class="xl:col-span-2 space-y-6">
                
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex items-center">
                        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Lớp đã tạo</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['owned_classes_count'] }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex items-center">
                        <div class="p-3 rounded-xl bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Lớp tham gia</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['joined_classes_count'] }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex items-center">
                        <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Gói dịch vụ</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white truncate" title="{{ $user->tenant?->package?->name ?? 'Free' }}">
                                {{ $user->tenant?->package?->name ?? 'Free' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Detailed Info -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Thông tin chi tiết</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Họ và tên</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Địa chỉ Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ $user->email }}</dd>
                            </div>
                            @if($user->code)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mã định danh (MSSV/MGV)</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ $user->code }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Xác thực Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">
                                    @if($user->email_verified_at)
                                        <span class="text-green-600 dark:text-green-400">Đã xác thực ({{ $user->email_verified_at->format('d/m/Y') }})</span>
                                    @else
                                        <span class="text-yellow-600 dark:text-yellow-400">Chưa xác thực</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổ chức / Đơn vị (Tenant)</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">
                                    @if($user->tenant)
                                        {{ $user->tenant->name }} (ID: {{ $user->tenant->id }})
                                    @else
                                        <span class="text-gray-400 italic">Không thuộc tổ chức nào</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Recent Activity (Optional / Placeholder) -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hoạt động gần đây</h3>
                    </div>
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm">Chưa có dữ liệu hoạt động chi tiết để hiển thị.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
