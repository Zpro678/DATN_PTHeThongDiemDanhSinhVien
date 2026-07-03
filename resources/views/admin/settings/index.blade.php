<x-admin-layout title="Cấu hình hệ thống">
    <div class="mx-auto max-w-[1200px]" x-data="{ activeTab: 'payment' }">
        <div class="mb-6 p-5 lg:p-6">
            <div class="relative z-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Settings</p>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Cấu hình hệ thống</h1>
                <p class="mt-1 text-sm text-slate-500">Quản lý các thiết lập lõi, quy tắc nghiệp vụ và cổng kết nối của SAMS.</p>
            </div>
            </div>
        </div>

        <div class="flex flex-col gap-8 lg:flex-row">
            <div class="w-full shrink-0 lg:w-64">
                <nav class="admin-card flex flex-row gap-1 overflow-x-auto rounded-2xl border p-2 lg:flex-col lg:overflow-visible">


                    <button type="button" @click="activeTab = 'payment'" :class="{ 'bg-blue-50 text-blue-700': activeTab === 'payment', 'text-slate-600 hover:bg-slate-50': activeTab !== 'payment' }" class="flex items-center gap-3 whitespace-nowrap rounded-xl px-4 py-3 text-left text-sm font-bold transition-colors lg:whitespace-normal">
                        <x-user.icon name="credit-card" :size="20" class="opacity-70" />
                        Thanh toán PayOS
                    </button>

                    <button type="button" @click="activeTab = 'email'" :class="{ 'bg-blue-50 text-blue-700': activeTab === 'email', 'text-slate-600 hover:bg-slate-50': activeTab !== 'email' }" class="flex items-center gap-3 whitespace-nowrap rounded-xl px-4 py-3 text-left text-sm font-bold transition-colors lg:whitespace-normal">
                        <x-user.icon name="mail" :size="20" class="opacity-70" />
                        Email & Thông báo
                    </button>

                    <button type="button" @click="activeTab = 'general'" :class="{ 'bg-blue-50 text-blue-700': activeTab === 'general', 'text-slate-600 hover:bg-slate-50': activeTab !== 'general' }" class="flex items-center gap-3 whitespace-nowrap rounded-xl px-4 py-3 text-left text-sm font-bold transition-colors lg:whitespace-normal">
                        <x-user.icon name="settings" :size="20" class="opacity-70" />
                        Giao diện & Tải lên
                    </button>

                    <button type="button" @click="activeTab = 'maintenance'" :class="{ 'bg-rose-50 text-rose-700': activeTab === 'maintenance', 'text-slate-600 hover:bg-rose-50 hover:text-rose-600': activeTab !== 'maintenance' }" class="mt-0 flex items-center gap-3 whitespace-nowrap rounded-xl px-4 py-3 text-left text-sm font-bold transition-colors lg:mt-4 lg:whitespace-normal">
                        <x-user.icon name="alert-triangle" :size="20" class="opacity-70" />
                        Bảo trì hệ thống
                    </button>
                </nav>
            </div>

            <div class="admin-card min-h-[500px] flex-1 overflow-visible rounded-3xl border">
                <div x-cloak x-show="activeTab === 'payment'" class="space-y-8 p-6 md:p-8" x-transition.opacity>
                    <div>
                        <h2 class="mb-1 text-lg font-bold text-slate-900">Tích hợp cổng PayOS</h2>
                        <p class="mb-6 text-sm text-slate-500">Cấu hình kết nối để xử lý thanh toán tự động qua mã QR ngân hàng.</p>

                        <div class="mb-6 flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
                            <x-user.icon name="alert-circle" :size="20" class="mt-0.5 shrink-0" />
                            <div class="text-sm">
                                <p class="font-bold">Lưu ý bảo mật</p>
                                <p class="mt-1 opacity-90">Không chia sẻ các thông tin API Key này với bất kỳ ai. Các thông tin này có thể lấy tại Dashboard của PayOS.vn.</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Môi trường (Environment)</label>
                                @php
                                $envOptions = [
                                    ['value' => 'sandbox', 'label' => 'Sandbox (Thử nghiệm)', 'sub_label' => 'Dùng để test giao dịch'],
                                    ['value' => 'production', 'label' => 'Production (Thực tế)', 'sub_label' => 'Giao dịch thật'],
                                ];
                                @endphp
                                <x-custom-select :options="$envOptions" placeholder="Chọn môi trường" value="production" />
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Client ID</label>
                                <input type="text" value="xxxx-xxxx-xxxx-xxxx" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-sm text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">API Key</label>
                                <div class="relative">
                                    <input type="password" value="********************************" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 pr-10 font-mono text-sm text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                    <button type="button" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                                        <x-user.icon name="eye" :size="20" />
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Checksum Key</label>
                                <div class="relative">
                                    <input type="password" value="********************************" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 pr-10 font-mono text-sm text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                    <button type="button" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                                        <x-user.icon name="eye" :size="20" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
                            <button type="button" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">
                                Hủy
                            </button>
                            <button type="button" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                                Lưu cấu hình PayOS
                            </button>
                        </div>
                    </div>
                </div>

                <div x-cloak x-show="activeTab === 'email'" x-transition.opacity>
                    @livewire('admin.settings.email-settings')
                </div>

                <div x-cloak x-show="activeTab === 'general'" x-transition.opacity>
                    @livewire('admin.settings.general-settings')
                </div>

                <div x-cloak x-show="activeTab === 'maintenance'" class="space-y-8 p-6 md:p-8" x-transition.opacity>
                    <div>
                        <h2 class="mb-1 text-lg font-bold text-rose-600">Khu vực nguy hiểm</h2>
                        <p class="mb-6 text-sm text-slate-500">Các thiết lập ảnh hưởng trực tiếp đến trạng thái hoạt động của toàn bộ ứng dụng.</p>

                        <div class="flex flex-col items-start justify-between gap-6 rounded-2xl border border-rose-200 bg-rose-50 p-6 md:flex-row md:items-center">
                            <div>
                                <h3 class="text-base font-bold text-rose-900">Chế độ bảo trì hệ thống</h3>
                                <p class="mt-1 text-sm text-rose-700">Khi bật chế độ này, tất cả người dùng (trừ Super Admin) sẽ không thể đăng nhập hoặc điểm danh. Thường dùng khi cập nhật phiên bản mới.</p>
                            </div>

                            <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                <input type="checkbox" value="" class="peer sr-only">
                                <div class="peer h-7 w-14 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-rose-600 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Thời gian dự kiến Bắt đầu</label>
                                <input type="datetime-local" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-rose-500 focus:ring-2 focus:ring-rose-500">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">Thời gian dự kiến Kết thúc</label>
                                <input type="datetime-local" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 transition-colors focus:border-rose-500 focus:ring-2 focus:ring-rose-500">
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
                            <button type="button" class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm shadow-rose-500/25 transition-all hover:-translate-y-0.5 hover:bg-rose-700">
                                Thông báo lên hệ thống
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
