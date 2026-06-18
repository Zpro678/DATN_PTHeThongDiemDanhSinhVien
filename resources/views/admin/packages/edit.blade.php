@php
    $isFree = (float) $package->price <= 0;
@endphp

<x-admin-layout title="Chỉnh sửa gói dịch vụ">
    <div class="mx-auto max-w-[1000px] space-y-6" x-data="{ isUnlimitedClasses: {{ $package->max_classes >= 999 ? 'true' : 'false' }}, isUnlimitedStudents: true, priceType: '{{ $isFree ? 'free' : 'fixed' }}', hasApi: true, hasGps: true, hasReports: true, hasImport: {{ $package->can_export_excel ? 'true' : 'false' }} }">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-700 transition-colors hover:text-blue-600">
                        <x-user.icon name="layout-dashboard" :size="16" class="mr-2" />
                        Trang chủ
                    </a>
                </li>
                <li class="flex items-center">
                    <x-user.icon name="chevron-right" :size="18" class="text-slate-400" />
                    <a href="{{ route('admin.packages.index') }}" class="ml-1 text-sm font-medium text-slate-700 transition-colors hover:text-blue-600 md:ml-2">Gói dịch vụ</a>
                </li>
                <li class="flex items-center" aria-current="page">
                    <x-user.icon name="chevron-right" :size="18" class="text-slate-400" />
                    <span class="ml-1 text-sm font-medium text-slate-500 md:ml-2">Chỉnh sửa</span>
                </li>
            </ol>
        </nav>

        <div class="admin-card flex flex-col items-start justify-between gap-4 overflow-hidden rounded-3xl border p-6 md:flex-row md:items-center lg:p-7">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-amber-500">Packages</p>
                <h1 class="mt-1 text-[28px] font-black text-slate-900">Chỉnh sửa gói dịch vụ</h1>
                <p class="text-sm text-slate-500">Cập nhật thông số và các quyền lợi cho {{ $package->name }}.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.packages.show', $package) }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                    Hủy bỏ
                </a>
                <button type="button" class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-amber-500/30 transition-all hover:-translate-y-0.5 hover:from-amber-600 hover:to-orange-600">
                    <x-user.icon name="check-circle" :size="16" />
                    Lưu thay đổi
                </button>
            </div>
        </div>

        <form action="#" method="POST" class="space-y-6">
            <div class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/50 px-6 py-5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                        <x-user.icon name="alert-circle" :size="20" />
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Thông tin chung</h2>
                </div>
                <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Tên gói dịch vụ <span class="text-red-500">*</span></label>
                        <input type="text" value="{{ $package->name }}" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Mô tả ngắn</label>
                        <input type="text" value="{{ $isFree ? 'Sử dụng cho giảng viên cá nhân' : 'Giải pháp toàn diện không giới hạn dành cho các tổ chức' }}" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Mô tả chi tiết</label>
                        <textarea rows="4" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-3 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">{{ $isFree ? 'Gói cơ bản dành cho cá nhân bắt đầu quản lý lớp học và điểm danh.' : 'Giải pháp toàn diện không giới hạn dành cho các tổ chức, trung tâm đào tạo và trường học có quy mô lớn.' }}</textarea>
                        <p class="mt-2 text-xs text-slate-500">Mô tả này sẽ được hiển thị ở trang giới thiệu chi tiết gói và báo giá cho khách hàng.</p>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Hình thức tính giá</label>
                        <select x-model="priceType" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="fixed">Cố định (VND)</option>
                            <option value="contact">Thỏa thuận (Liên hệ)</option>
                            <option value="free">Miễn phí</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Thời hạn sử dụng</label>
                        <select class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="1">1 Tháng</option>
                            <option value="3">3 Tháng</option>
                            <option value="6">6 Tháng</option>
                            <option value="12" selected>1 Năm</option>
                            <option value="unlimited">Vĩnh viễn (Trọn đời)</option>
                        </select>
                    </div>
                    <div x-show="priceType === 'fixed'" class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Mức giá (VND)</label>
                        <input type="number" value="{{ (int) $package->price }}" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/50 px-6 py-5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                        <x-user.icon name="settings" :size="20" />
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Giới hạn tài nguyên</h2>
                </div>
                <div class="grid grid-cols-1 gap-8 p-6 md:grid-cols-2">
                    <div class="admin-form-panel relative rounded-xl border p-5">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <label class="text-sm font-bold text-slate-800">Số lượng lớp học tối đa</label>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" x-model="isUnlimitedClasses" class="peer sr-only">
                                <div class="peer h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-amber-500 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                <span class="ml-3 text-xs font-semibold text-slate-600" x-text="isUnlimitedClasses ? 'Không giới hạn' : 'Có giới hạn'"></span>
                            </label>
                        </div>
                        <div x-show="!isUnlimitedClasses" class="mt-3">
                            <input type="number" value="{{ $package->max_classes }}" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="admin-form-panel relative rounded-xl border p-5">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <label class="text-sm font-bold text-slate-800">Xuất báo cáo Excel</label>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" x-model="hasImport" class="peer sr-only">
                                <div class="peer h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-amber-500 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                <span class="ml-3 text-xs font-semibold text-slate-600" x-text="hasImport ? 'Đã bật' : 'Đã tắt'"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card admin-card-hover mb-10 overflow-hidden rounded-2xl border">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/50 px-6 py-5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <x-user.icon name="check-circle" :size="20" />
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Bật / Tắt tính năng</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ([
                        ['model' => 'hasGps', 'title' => 'Xác thực vị trí GPS', 'desc' => 'Cho phép giới hạn bán kính điểm danh của sinh viên quanh vị trí lớp học.'],
                        ['model' => 'hasImport', 'title' => 'Import sinh viên từ Excel/CSV', 'desc' => 'Hỗ trợ upload file danh sách lớp thay vì nhập tay thủ công từng sinh viên.'],
                        ['model' => 'hasReports', 'title' => 'Báo cáo Thống kê Nâng cao', 'desc' => 'Biểu đồ chuyên cần trực quan, xuất báo cáo PDF cuối kỳ, cảnh báo tự động.'],
                        ['model' => 'hasApi', 'title' => 'Tích hợp API (SSO, LMS)', 'desc' => 'Tính năng cao cấp cho phép hệ thống gọi API đồng bộ dữ liệu với trường học.'],
                    ] as $feature)
                        <div class="flex items-center justify-between p-6 transition-colors hover:bg-amber-50/50">
                            <div>
                                <p class="text-base font-bold text-slate-800">{{ $feature['title'] }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $feature['desc'] }}</p>
                            </div>
                            <label class="relative ml-4 inline-flex shrink-0 cursor-pointer items-center">
                                <input type="checkbox" x-model="{{ $feature['model'] }}" class="peer sr-only">
                                <div class="peer h-7 w-14 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end pb-12 pt-4">
                <button type="button" class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-3 text-base font-bold text-white shadow-md shadow-amber-500/40 transition-all hover:-translate-y-0.5 hover:from-amber-600 hover:to-orange-600">
                    <x-user.icon name="check-circle" :size="20" />
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
