<div class="flex min-h-[calc(100vh-8rem)] flex-col space-y-5">
    <div class="admin-card overflow-hidden rounded-3xl border p-5 lg:p-6 flex-none">
        <div class="relative z-10 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Accounts</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Quản lý Tài khoản</h1>
                <p class="mt-1 text-sm text-slate-500">Danh sách tất cả người dùng trong hệ thống (Admin, Giảng viên, Học viên).</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.create', ['ma_user' => request()->route('ma_user') ?? Auth::id()]) }}" class="admin-soft-button inline-flex items-center rounded-xl border border-transparent bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-500/20">
                    <x-user.icon name="plus" :size="18" class="mr-2" />
                    Thêm tài khoản
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="flex-none rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex-none rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="admin-card flex-none overflow-visible rounded-2xl border p-4 lg:p-5 relative z-20">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="relative col-span-1 md:col-span-2">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-user.icon name="search" :size="18" class="text-gray-400" />
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="relative z-10 block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-sm leading-5 text-slate-900 placeholder-slate-400 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Tìm kiếm theo tên, email, MSSV..."
                >
            </div>

            <div>
                @php
                $roleOptions = [
                    ['value' => 'admin', 'label' => 'Admin', 'sub_label' => 'Quản trị viên hệ thống'],
                    ['value' => 'user', 'label' => 'Người dùng', 'sub_label' => 'Giảng viên & Học viên'],
                ];
                @endphp
                <x-custom-select wire:model.live="role" :options="$roleOptions" placeholder="Tất cả vai trò" />
            </div>

            <div>
                @php
                $statusOptions = [
                    ['value' => 'active', 'label' => 'Đang hoạt động', 'sub_label' => 'Tài khoản bình thường'],
                    ['value' => 'blocked', 'label' => 'Đã khóa', 'sub_label' => 'Tài khoản bị vô hiệu hóa'],
                ];
                @endphp
                <x-custom-select wire:model.live="status" :options="$statusOptions" placeholder="" />
            </div>
        </div>
    </div>

    <div class="admin-card flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border relative">
        <!-- Livewire Loading Overlay -->
        <div wire:loading class="absolute inset-0 z-10 bg-white/50 backdrop-blur-sm"></div>

        <div class="flex-1 overflow-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-50/90">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tài khoản</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Vai trò</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Trạng thái</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Lớp tạo</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Truy cập gần nhất</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white/70">
                    @forelse($users as $user)
                        @php
                            $initial = function_exists('mb_substr')
                                ? mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1, 'UTF-8'), 'UTF-8')
                                : strtoupper(substr($user->name ?? 'U', 0, 1));
                        @endphp

                        <tr class="transition-colors hover:bg-blue-50/40 {{ $user->status === 'blocked' ? 'opacity-75' : '' }}">
                            <td class="px-6 py-4 max-w-[200px] sm:max-w-[300px] lg:max-w-md">
                                <div class="flex items-center">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-black text-blue-700 overflow-hidden {{ $user->status === 'blocked' ? 'grayscale' : '' }}">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                                        @else
                                            {{ $initial }}
                                        @endif
                                    </div>
                                    <div class="ml-4 min-w-0 flex-1">
                                        <div class="truncate text-sm font-medium text-gray-900" title="{{ $user->name }}">{{ $user->name }}</div>
                                        <div class="truncate text-sm text-gray-500" title="{{ $user->email }}">
                                            {{ $user->email }}
                                            @if($user->member_id)
                                                <span class="text-gray-400">• {{ $user->member_id }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($user->isAdmin())
                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                                        <span class="-ml-0.5 mr-1.5 h-2 w-2 rounded-full bg-purple-400"></span>
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                        <span class="-ml-0.5 mr-1.5 h-2 w-2 rounded-full bg-blue-400"></span>
                                        Người dùng
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        <span class="-ml-0.5 mr-1.5 h-2 w-2 rounded-full bg-green-400"></span>
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                        <span class="-ml-0.5 mr-1.5 h-2 w-2 rounded-full bg-red-400"></span>
                                        Đã khóa
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">{{ $user->owned_classes_count ?? 0 }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $user->updated_at?->diffForHumans() ?? 'Chưa rõ' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('admin.users.show', $user) }}" class="mr-3 text-gray-500 transition-colors hover:text-gray-900" title="Xem chi tiết">
                                    <x-user.icon name="eye" :size="20" class="inline" />
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="mr-3 text-indigo-600 transition-colors hover:text-indigo-900" title="Chỉnh sửa">
                                    <x-user.icon name="edit" :size="20" class="inline" />
                                </a>
                                <button type="button" wire:click="toggleStatus({{ $user->id }})" class="{{ $user->status === 'active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }} transition-colors" title="{{ $user->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                    <x-user.icon name="{{ $user->status === 'active' ? 'lock' : 'shield-check' }}" :size="20" class="inline" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="whitespace-nowrap px-6 py-10 text-center text-sm text-gray-500">
                                Không tìm thấy tài khoản nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex-none border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            {{ $users->links('vendor.livewire.tailwind') }}
        </div>
    </div>
</div>
