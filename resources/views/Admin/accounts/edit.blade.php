<x-app-layout variant="admin" page-title="Chỉnh sửa tài khoản">
    <div class="max-w-4xl mx-auto space-y-6">
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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-500">Chỉnh sửa</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="flex gap-3">
                <a href="{{ route('admin.accounts.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                    Hủy bỏ
                </a>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center text-red-800 dark:text-red-300">
                <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Thông tin tài khoản</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">Đang sửa: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $user->email }}</span></span>
            </div>
            
            <form action="{{ route('admin.accounts.update', $user) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Avatar Upload -->
                    <div class="md:col-span-2 flex flex-col items-center justify-center py-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 text-center">Ảnh đại diện</label>
                        <div class="flex flex-col items-center space-y-4">
                            <div class="shrink-0 h-32 w-32 relative rounded-full overflow-hidden border-4 border-gray-200 dark:border-gray-700 shadow-sm">
                                @if($user->avatar)
                                    <img class="h-full w-full object-cover" src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" id="avatarPreview">
                                @else
                                    <img class="h-full w-full object-cover" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff&size=128" alt="Avatar" id="avatarPreview">
                                @endif
                            </div>
                            <label class="block cursor-pointer">
                                <span class="sr-only">Chọn ảnh đại diện</span>
                                <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-slate-500
                                  file:mr-0 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-medium
                                  file:bg-indigo-50 file:text-indigo-700
                                  hover:file:bg-indigo-100
                                  dark:file:bg-indigo-900/30 dark:file:text-indigo-400
                                  dark:text-gray-400
                                  transition-colors cursor-pointer text-center
                                " onchange="document.getElementById('avatarPreview').src = window.URL.createObjectURL(this.files[0])"/>
                            </label>
                        </div>
                        @error('avatar')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name Input -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Họ và tên <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg transition-colors @error('name') border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 @enderror" placeholder="Nhập họ và tên">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Input -->
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Địa chỉ Email <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg transition-colors @error('email') border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500 @enderror" placeholder="VD: nguyenvan@example.com">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Select -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Trạng thái tài khoản <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select id="status" name="status" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg transition-colors">
                                <option value="active" @selected(old('status', $user->status) == 'active')>Đang hoạt động</option>
                                <option value="blocked" @selected(old('status', $user->status) == 'blocked')>Khóa (Blocked)</option>
                            </select>
                        </div>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @if($user->id === auth()->id())
                            <p class="mt-2 text-xs text-yellow-600 dark:text-yellow-500"><svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>Bạn không thể tự khóa tài khoản của mình.</p>
                        @endif
                    </div>
                    
                    <!-- Role Select -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vai trò (Role) <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select id="role" name="role" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg transition-colors">
                                @php
                                    $currentRole = old('role', $user->roles->first()?->name ?? '');
                                @endphp
                                <option value="" disabled @selected($currentRole == '')>Chọn vai trò</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" @selected($currentRole == $role->name)>
                                        @if($role->name === 'admin')
                                            Admin (Quản trị viên)
                                        @elseif($role->name === 'teacher')
                                            Giảng viên
                                        @elseif($role->name === 'student')
                                            Sinh viên
                                        @else
                                            {{ ucfirst($role->name) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('role')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Mỗi tài khoản chỉ được phép có 1 vai trò duy nhất.</p>
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-5 flex justify-end gap-3">
                    <a href="{{ route('admin.accounts.index') }}" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm">
                        Hủy
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
