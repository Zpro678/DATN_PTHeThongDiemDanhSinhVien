<x-app-layout variant="admin" page-title="Cấu hình hệ thống">
    <div class="max-w-[1200px] mx-auto" x-data="{ activeTab: 'attendance' }">
        
        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Cấu hình hệ thống</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Quản lý các thiết lập lõi, quy tắc nghiệp vụ và cổng kết nối của SAMS.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-colors">
                    Hủy thay đổi
                </button>
                <button type="button" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm shadow-blue-500/30 transition-all flex items-center gap-2">
                    <x-sams.icon name="save" class="w-4 h-4" />
                    Lưu cấu hình
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Sidebar Navigation -->
            <div class="w-full lg:w-64 shrink-0">
                <nav class="flex flex-row lg:flex-col gap-1 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0 sams-scrollbar">
                    
                    <button @click="activeTab = 'attendance'" :class="{ 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400': activeTab === 'attendance', 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800/50': activeTab !== 'attendance' }" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-colors whitespace-nowrap lg:whitespace-normal text-left">
                        <x-sams.icon name="calendar-check" class="w-5 h-5 opacity-70" />
                        Điểm danh & Chuyên cần
                    </button>

                    <button @click="activeTab = 'payment'" :class="{ 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400': activeTab === 'payment', 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800/50': activeTab !== 'payment' }" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-colors whitespace-nowrap lg:whitespace-normal text-left">
                        <x-sams.icon name="credit-card" class="w-5 h-5 opacity-70" />
                        Thanh toán PayOS
                    </button>

                    <button @click="activeTab = 'email'" :class="{ 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400': activeTab === 'email', 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800/50': activeTab !== 'email' }" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-colors whitespace-nowrap lg:whitespace-normal text-left">
                        <x-sams.icon name="mail" class="w-5 h-5 opacity-70" />
                        Email & Thông báo
                    </button>

                    <button @click="activeTab = 'general'" :class="{ 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400': activeTab === 'general', 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800/50': activeTab !== 'general' }" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-colors whitespace-nowrap lg:whitespace-normal text-left">
                        <x-sams.icon name="settings" class="w-5 h-5 opacity-70" />
                        Giao diện & Tải lên
                    </button>

                    <button @click="activeTab = 'maintenance'" :class="{ 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400': activeTab === 'maintenance', 'text-slate-600 hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-400': activeTab !== 'maintenance' }" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-colors whitespace-nowrap lg:whitespace-normal text-left mt-0 lg:mt-4">
                        <x-sams.icon name="alert-triangle" class="w-5 h-5 opacity-70" />
                        Bảo trì hệ thống
                    </button>

                </nav>
            </div>

            <!-- Content Area -->
            <div class="flex-1 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden min-h-[500px]">
                
                <!-- Tab 1: Attendance -->
                <div x-cloak x-show="activeTab === 'attendance'" class="p-6 md:p-8 space-y-8" x-transition.opacity>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Quy tắc Điểm danh</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Thiết lập các thông số mặc định khi giảng viên tạo lớp học hoặc phiên điểm danh.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Ngưỡng cảnh báo vắng (%)</label>
                                <div class="relative">
                                    <input type="number" value="20" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <span class="absolute right-4 top-3 text-slate-400 text-sm font-medium">%</span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Sinh viên nghỉ quá tỷ lệ này sẽ bị gắn cờ đỏ cảnh báo.</p>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Thời gian đi muộn mặc định (phút)</label>
                                <div class="relative">
                                    <input type="number" value="15" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <span class="absolute right-4 top-3 text-slate-400 text-sm font-medium">phút</span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Sau bao lâu từ khi mở điểm danh thì bị tính là đi muộn.</p>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Bán kính GPS mặc định (mét)</label>
                                <div class="relative">
                                    <input type="number" value="50" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <span class="absolute right-4 top-3 text-slate-400 text-sm font-medium">m</span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Khoảng cách tối đa sinh viên được phép cách vị trí giảng viên.</p>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Chặn Check-in 2 máy cùng lúc</label>
                                <div class="mt-2 flex items-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" value="" class="sr-only peer" checked>
                                      <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                                      <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">Kích hoạt chống gian lận IP/Device</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Payment -->
                <div x-cloak x-show="activeTab === 'payment'" class="p-6 md:p-8 space-y-8" x-transition.opacity>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Tích hợp cổng PayOS</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Cấu hình kết nối để xử lý thanh toán tự động qua mã QR ngân hàng.</p>
                        
                        <div class="p-4 mb-6 rounded-xl bg-amber-50 border border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20 flex gap-3 text-amber-800 dark:text-amber-400">
                            <x-sams.icon name="alert-circle" class="w-5 h-5 shrink-0 mt-0.5" />
                            <div class="text-sm">
                                <p class="font-bold">Lưu ý bảo mật</p>
                                <p class="mt-1 opacity-90">Không chia sẻ các thông tin API Key này với bất kỳ ai. Các thông tin này có thể lấy tại Dashboard của <a href="#" class="underline font-semibold hover:text-amber-600">PayOS.vn</a>.</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Môi trường (Environment)</label>
                                <select class="w-full md:w-1/2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="sandbox">Sandbox (Thử nghiệm)</option>
                                    <option value="production" selected>Production (Thực tế)</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Client ID</label>
                                <input type="text" value="xxxx-xxxx-xxxx-xxxx" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors font-mono text-sm">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">API Key</label>
                                <div class="relative">
                                    <input type="password" value="********************************" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors font-mono text-sm pr-10">
                                    <button class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                        <x-sams.icon name="eye" class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Checksum Key</label>
                                <div class="relative">
                                    <input type="password" value="********************************" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors font-mono text-sm pr-10">
                                    <button class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                        <x-sams.icon name="eye" class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Email -->
                <div x-cloak x-show="activeTab === 'email'" class="p-6 md:p-8 space-y-8" x-transition.opacity>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Cấu hình Mail Server (SMTP)</h2>
                            <button class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">Gửi mail test</button>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Thiết lập kết nối để hệ thống gửi các thông báo tự động tới người dùng.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Mail Driver</label>
                                <select class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="smtp" selected>SMTP</option>
                                    <option value="mailgun">Mailgun</option>
                                    <option value="ses">Amazon SES</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Mail Host</label>
                                <input type="text" value="smtp.gmail.com" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Mail Port</label>
                                <input type="text" value="465" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Mã hóa (Encryption)</label>
                                <select class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="tls">TLS</option>
                                    <option value="ssl" selected>SSL</option>
                                </select>
                            </div>
                            
                            <div class="space-y-2 md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Tên người gửi (From Name)</label>
                                <input type="text" value="SAMS System Notification" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: General -->
                <div x-cloak x-show="activeTab === 'general'" class="p-6 md:p-8 space-y-8" x-transition.opacity>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Giao diện & Tải lên</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Thay đổi thông tin nhận diện hệ thống và các giới hạn dung lượng.</p>
                        
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Tên hệ thống</label>
                                <input type="text" value="SAMS - Smart Attendance Management" class="w-full md:w-2/3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Logo hệ thống</label>
                                <div class="flex items-center gap-6 mt-2">
                                    <div class="w-20 h-20 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg">
                                        <x-sams.icon name="graduation-cap" class="w-10 h-10" />
                                    </div>
                                    <div class="space-y-2">
                                        <button class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold shadow-sm hover:bg-slate-50 transition-colors">
                                            Thay đổi Logo
                                        </button>
                                        <p class="text-[11px] text-slate-500">Khuyên dùng định dạng PNG, SVG hoặc JPG. Kích thước tối đa 2MB.</p>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-slate-100 dark:border-slate-800">

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Giới hạn dung lượng Import Excel (MB)</label>
                                <input type="number" value="10" class="w-full md:w-1/3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Dung lượng tối đa cho phép tải lên khi Import danh sách sinh viên.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Maintenance -->
                <div x-cloak x-show="activeTab === 'maintenance'" class="p-6 md:p-8 space-y-8" x-transition.opacity>
                    <div>
                        <h2 class="text-lg font-bold text-rose-600 dark:text-rose-500 mb-1">Khu vực nguy hiểm</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Các thiết lập ảnh hưởng trực tiếp đến trạng thái hoạt động của toàn bộ ứng dụng.</p>
                        
                        <div class="p-6 rounded-2xl border border-rose-200 bg-rose-50 dark:bg-rose-950/20 dark:border-rose-900/50 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                            <div>
                                <h3 class="text-base font-bold text-rose-900 dark:text-rose-400">Chế độ bảo trì hệ thống</h3>
                                <p class="text-sm text-rose-700 dark:text-rose-300/70 mt-1">Khi bật chế độ này, tất cả người dùng (trừ Super Admin) sẽ không thể đăng nhập hoặc điểm danh. Thường dùng khi cập nhật phiên bản mới.</p>
                            </div>
                            
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" value="" class="sr-only peer">
                                <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-300 dark:peer-focus:ring-rose-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-slate-600 peer-checked:bg-rose-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
