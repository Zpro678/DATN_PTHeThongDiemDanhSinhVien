<div>
    <div class="mb-6">
        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Quản lý Hệ thống</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">Bảo trì & Phục hồi</h1>
        <p class="mt-1 text-sm font-medium text-slate-500">Quản lý sao lưu dữ liệu và trạng thái hoạt động của hệ thống.</p>
    </div>

    <div class="space-y-8">
        <!-- Phân khu 1: Sao lưu hệ thống -->
        <section class="admin-card rounded-2xl border bg-white p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5 mb-5">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900">1. Sao lưu Hệ thống</h2>
                    </div>
                    <p class="mt-2 text-sm text-slate-500 ml-13">Tiến hành sao lưu Cơ sở dữ liệu hiện tại để đảm bảo an toàn trước khi bảo trì.</p>
                </div>
                <button wire:click="createBackup" wire:loading.attr="disabled" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700 whitespace-nowrap">
                    <span wire:loading.remove wire:target="createBackup">Tiến hành Sao lưu</span>
                    <span wire:loading wire:target="createBackup">Đang xử lý...</span>
                </button>
            </div>
        </section>

        <!-- Phân khu 2: Phục hồi hệ thống -->
        <section class="admin-card rounded-2xl border bg-white p-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-5 mb-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">2. Phục hồi Dữ liệu</h2>
                    <p class="text-sm text-slate-500">Danh sách các bản sao lưu đã tạo. Chỉ Super Admin mới có quyền phục hồi.</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tên File</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Thời gian tạo</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kích thước</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($backups as $backup)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $backup['name'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">{{ $backup['created_at']->format('d/m/Y H:i:s') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium">
                                    <button wire:click="openRestoreModal('{{ $backup['name'] }}')" class="text-emerald-600 hover:text-emerald-900 mr-3 font-bold">Phục hồi</button>
                                    <button wire:click="deleteBackup('{{ $backup['name'] }}')" class="text-rose-600 hover:text-rose-900 font-bold" wire:confirm="Bạn có chắc chắn muốn xóa bản sao lưu này?">Xóa</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Chưa có bản sao lưu nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Phân khu 3: Bảo trì hệ thống -->
        <section class="admin-card rounded-2xl border bg-white p-6 border-rose-200 bg-rose-50/30">
            <div class="flex items-center gap-3 border-b border-rose-100 pb-5 mb-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-rose-900">3. Trạng thái Hệ thống (Bảo trì)</h2>
                    <p class="text-sm text-rose-700">Yêu cầu phải có bản sao lưu trong vòng 12h qua trước khi bật chế độ bảo trì.</p>
                </div>
            </div>

            <form wire:submit.prevent="save">
                <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-center bg-white p-5 rounded-xl border border-rose-100 shadow-sm mb-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Chế độ bảo trì</h3>
                        <p class="mt-1 text-sm text-slate-500">Khi bật, khách và user thường sẽ bị đăng xuất và thấy trang bảo trì.</p>
                    </div>

                    <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                        <input type="checkbox" wire:model="maintenance_mode" class="peer sr-only">
                        <div class="peer h-7 w-14 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-rose-600 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Thời gian dự kiến Bắt đầu</label>
                        <input type="datetime-local" wire:model="start_time" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        @error('start_time') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">Thời gian dự kiến Kết thúc</label>
                        <input type="datetime-local" wire:model="end_time" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        @error('end_time') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-rose-500/25 transition-all hover:-translate-y-0.5 hover:bg-rose-700 disabled:opacity-50" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Cập nhật & Lưu</span>
                        <span wire:loading wire:target="save">Đang xử lý...</span>
                    </button>
                </div>
            </form>
        </section>
    </div>

    <!-- Modal Phục Hồi -->
    @if($showRestoreModal)
    @teleport('body')
    <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl p-6" @click.away="$wire.set('showRestoreModal', false)">
            <div class="mb-5 flex items-center gap-4 text-emerald-600">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900">Xác nhận Phục hồi</h3>
                    <p class="text-sm font-medium text-slate-500">Bản sao lưu: <span class="text-slate-900 font-bold">{{ $selectedBackup }}</span></p>
                </div>
            </div>
            
            <div class="mb-6 rounded-xl bg-rose-50 p-4 border border-rose-100 text-sm text-rose-700">
                <strong>Cảnh báo:</strong> Việc phục hồi sẽ ghi đè toàn bộ Cơ sở dữ liệu hiện tại. Dữ liệu mới sinh ra sau thời điểm sao lưu sẽ bị mất vĩnh viễn.
            </div>

            <form wire:submit.prevent="restoreBackup">
                <div class="space-y-2 mb-6">
                    <label class="block text-sm font-bold text-slate-700">Mật khẩu Super Admin</label>
                    <input type="password" wire:model="super_admin_password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500" placeholder="Nhập mật khẩu để xác nhận" required>
                    @error('super_admin_password') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="$set('showRestoreModal', false)" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">Hủy</button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-emerald-500/25 transition-all hover:bg-emerald-700" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="restoreBackup">Tiến hành Phục hồi</span>
                        <span wire:loading wire:target="restoreBackup">Đang xử lý...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endteleport
    @endif
</div>
