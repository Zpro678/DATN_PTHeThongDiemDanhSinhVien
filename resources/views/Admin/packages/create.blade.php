<x-app-layout variant="admin" page-title="Thêm gói dịch vụ mới">
    <div class="max-w-[1000px] mx-auto space-y-8" x-data="{
        isUnlimitedClasses: false,
        isUnlimitedStudents: false,
        priceType: 'fixed',
        hasApi: false,
        hasGps: false,
        hasReports: false,
        hasImport: false
    }">
        
        <!-- Breadcrumbs -->
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        <x-sams.icon name="layout-dashboard" class="w-4 h-4 mr-2" />
                        Trang chủ
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-slate-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('admin.packages.index') }}" class="ml-1 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 md:ml-2 transition-colors">Gói dịch vụ</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-slate-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-slate-500 dark:text-slate-400 md:ml-2">Thêm mới</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-[28px] font-bold text-slate-900 dark:text-white mb-1">Thêm gói dịch vụ mới</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Thiết lập thông số và các quyền lợi cho một gói dịch vụ hoàn toàn mới.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.packages.index') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                    Hủy bỏ
                </a>
                <button class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-blue-800 shadow-sm shadow-blue-500/30 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Tạo gói dịch vụ
                </button>
            </div>
        </div>

        <form action="#" method="POST" class="space-y-6">
            <!-- Section 1: Thông tin cơ bản -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Thông tin chung</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tên gói dịch vụ <span class="text-red-500">*</span></label>
                        <input type="text" placeholder="VD: Gói Khởi Nghiệp (Startup)" class="w-full px-4 py-2.5 bg-transparent border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Mô tả ngắn</label>
                        <input type="text" placeholder="Nhập một câu mô tả ngắn gọn..." class="w-full px-4 py-2.5 bg-transparent border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Mô tả chi tiết</label>
                        <textarea rows="4" placeholder="Nhập mô tả chi tiết các lợi ích..." class="w-full px-4 py-3 bg-transparent border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"></textarea>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Mô tả này sẽ được hiển thị ở trang giới thiệu chi tiết gói và báo giá cho khách hàng.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Hình thức tính giá</label>
                            <select x-model="priceType" class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="fixed">Cố định (VND)</option>
                                <option value="contact">Thỏa thuận (Liên hệ)</option>
                                <option value="free">Miễn phí</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Thời hạn sử dụng</label>
                            <select class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="1">1 Tháng</option>
                                <option value="3">3 Tháng</option>
                                <option value="6">6 Tháng</option>
                                <option value="12" selected>1 Năm</option>
                                <option value="unlimited">Vĩnh viễn (Trọn đời)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div x-show="priceType === 'fixed'" class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Mức giá (VND)</label>
                        <input type="number" placeholder="VD: 5000000" class="w-full px-4 py-2.5 bg-transparent border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                </div>
            </div>

            <!-- Section 2: Giới hạn hệ thống -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Giới hạn tài nguyên</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- Lớp học -->
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-5 rounded-xl border border-slate-100 dark:border-slate-700 relative">
                        <div class="flex justify-between items-center mb-4">
                            <label class="text-sm font-bold text-slate-800 dark:text-slate-200">Số lượng lớp học tối đa</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="isUnlimitedClasses" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                <span class="ml-3 text-xs font-semibold text-slate-600 dark:text-slate-400" x-text="isUnlimitedClasses ? 'Không giới hạn' : 'Có giới hạn'"></span>
                            </label>
                        </div>
                        <div x-show="!isUnlimitedClasses" class="mt-3 transition-all">
                            <input type="number" placeholder="VD: 5" class="w-full px-4 py-2.5 bg-transparent border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>
                    </div>

                    <!-- Sinh viên -->
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-5 rounded-xl border border-slate-100 dark:border-slate-700 relative">
                        <div class="flex justify-between items-center mb-4">
                            <label class="text-sm font-bold text-slate-800 dark:text-slate-200">Sinh viên / Lớp tối đa</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="isUnlimitedStudents" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                <span class="ml-3 text-xs font-semibold text-slate-600 dark:text-slate-400" x-text="isUnlimitedStudents ? 'Không giới hạn' : 'Có giới hạn'"></span>
                            </label>
                        </div>
                        <div x-show="!isUnlimitedStudents" class="mt-3 transition-all">
                            <input type="number" placeholder="VD: 50" class="w-full px-4 py-2.5 bg-transparent border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>
                    </div>

                    <!-- Storage -->
                    <div class="md:col-span-2 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-xl border border-slate-100 dark:border-slate-700 relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <label class="text-sm font-bold text-slate-800 dark:text-slate-200 block mb-1">Dung lượng lưu trữ (Storage)</label>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Giới hạn không gian lưu trữ file đính kèm, minh chứng xin nghỉ phép.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" placeholder="VD: 5" class="w-24 px-4 py-2 bg-transparent border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg text-center font-bold focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <select class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg font-bold focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option>MB</option>
                                <option selected>GB</option>
                                <option>TB</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Tính năng đi kèm -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-10">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Bật / Tắt tính năng</h2>
                </div>
                <div class="p-0 divide-y divide-slate-100 dark:divide-slate-700">
                    
                    <!-- GPS -->
                    <div class="p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <div>
                            <p class="text-base font-bold text-slate-800 dark:text-slate-200">Xác thực vị trí GPS</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Cho phép giới hạn bán kính điểm danh của sinh viên quanh vị trí lớp học.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer ml-4 shrink-0">
                            <input type="checkbox" x-model="hasGps" class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <!-- Import -->
                    <div class="p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <div>
                            <p class="text-base font-bold text-slate-800 dark:text-slate-200">Import sinh viên từ Excel/CSV</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Hỗ trợ upload file danh sách lớp thay vì nhập tay thủ công từng sinh viên.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer ml-4 shrink-0">
                            <input type="checkbox" x-model="hasImport" class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <!-- Reports -->
                    <div class="p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <div>
                            <p class="text-base font-bold text-slate-800 dark:text-slate-200">Báo cáo Thống kê Nâng cao</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Biểu đồ chuyên cần trực quan, xuất báo cáo PDF cuối kỳ, cảnh báo tự động.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer ml-4 shrink-0">
                            <input type="checkbox" x-model="hasReports" class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <!-- API -->
                    <div class="p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors bg-slate-50/50 dark:bg-slate-900/50">
                        <div>
                            <p class="text-base font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                Tích hợp API (SSO, LMS)
                                <span class="bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-500 text-[10px] font-bold px-2 py-0.5 rounded uppercase border border-amber-200 dark:border-amber-500/30">PRO ONLY</span>
                            </p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tính năng cao cấp cho phép hệ thống gọi API đồng bộ dữ liệu với trường học.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer ml-4 shrink-0">
                            <input type="checkbox" x-model="hasApi" class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                </div>
            </div>
            
            <div class="flex justify-end pt-4 pb-12">
                <button type="button" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-base font-bold hover:from-blue-700 hover:to-blue-800 shadow-md shadow-blue-500/40 transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Khởi tạo cấu hình mới
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
