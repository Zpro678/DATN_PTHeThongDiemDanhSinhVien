<div>
    <div class="mx-auto max-w-[1000px] space-y-6">

        <div class="admin-card flex flex-col items-start justify-between gap-4 overflow-hidden rounded-3xl border p-6 md:flex-row md:items-center lg:p-7">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-blue-500">Packages</p>
                <h1 class="mt-1 text-[28px] font-black text-slate-900">Thêm gói dịch vụ mới</h1>
                <p class="text-sm text-slate-500">Thiết lập thông số và các quyền lợi cho một gói dịch vụ hoàn toàn mới.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.packages.index') }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                    Hủy bỏ
                </a>
                <button type="button" wire:click="save" class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-500/30 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                    <x-user.icon name="plus" :size="16" />
                    Tạo gói dịch vụ
                </button>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="admin-card admin-card-hover overflow-visible rounded-2xl border relative z-20">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/50 px-6 py-5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                        <x-user.icon name="alert-circle" :size="20" />
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Thông tin chung</h2>
                </div>
                <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Tên gói dịch vụ <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="VD: Gói Khởi Nghiệp (Startup)" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Mô tả chi tiết</label>
                        <textarea rows="4" wire:model="description" placeholder="Nhập mô tả chi tiết các lợi ích..." class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-3 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500"></textarea>
                        <p class="mt-2 text-xs text-slate-500">Mô tả này sẽ được hiển thị ở trang giới thiệu chi tiết gói và báo giá cho khách hàng.</p>
                        @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Hình thức tính giá</label>
                        @php
                        $priceTypeOptions = [
                            ['value' => 'fixed', 'label' => 'Cố định (VND)', 'sub_label' => 'Thanh toán một mức giá cố định'],
                            ['value' => 'contact', 'label' => 'Thỏa thuận (Liên hệ)', 'sub_label' => 'Khách hàng liên hệ để nhận báo giá'],
                            ['value' => 'free', 'label' => 'Miễn phí', 'sub_label' => 'Không thu phí người dùng'],
                        ];
                        @endphp
                        <x-custom-select wire:model.live="priceType" :options="$priceTypeOptions" placeholder="Chọn hình thức" />
                        @error('priceType') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Thời hạn sử dụng</label>
                        @php
                        $durationOptions = [
                            ['value' => '30', 'label' => '1 Tháng', 'sub_label' => 'Sử dụng trong 30 ngày'],
                            ['value' => '90', 'label' => '3 Tháng', 'sub_label' => 'Sử dụng trong 90 ngày'],
                            ['value' => '180', 'label' => '6 Tháng', 'sub_label' => 'Sử dụng trong 180 ngày'],
                            ['value' => '365', 'label' => '1 Năm', 'sub_label' => 'Sử dụng trong 365 ngày'],
                            ['value' => '0', 'label' => 'Vĩnh viễn', 'sub_label' => 'Sử dụng không giới hạn thời gian'],
                        ];
                        @endphp
                        <x-custom-select wire:model="duration_days" :options="$durationOptions" placeholder="Chọn thời hạn" />
                        @error('duration_days') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    @if($priceType === 'fixed')
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Mức giá (VND)</label>
                        <input type="number" wire:model="price" placeholder="VD: 5000000" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        @error('price') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    @endif
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
                                <input type="checkbox" wire:model.live="isUnlimitedClasses" class="peer sr-only">
                                <div class="peer h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-amber-500 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                <span class="ml-3 text-xs font-semibold text-slate-600">{{ $isUnlimitedClasses ? 'Không giới hạn' : 'Có giới hạn' }}</span>
                            </label>
                        </div>
                        @if(!$isUnlimitedClasses)
                        <div class="mt-3">
                            <input type="number" wire:model="max_classes" placeholder="VD: 5" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            @error('max_classes') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        @endif
                    </div>

                    <div class="admin-form-panel relative rounded-xl border p-5">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <label class="text-sm font-bold text-slate-800">Học viên / Lớp tối đa</label>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" wire:model.live="isUnlimitedStudents" class="peer sr-only">
                                <div class="peer h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-amber-500 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                                <span class="ml-3 text-xs font-semibold text-slate-600">{{ $isUnlimitedStudents ? 'Không giới hạn' : 'Có giới hạn' }}</span>
                            </label>
                        </div>
                        @if(!$isUnlimitedStudents)
                        <div class="mt-3">
                            <input type="number" wire:model="max_students_per_class" placeholder="VD: 50" class="w-full rounded-xl border border-slate-300 bg-transparent px-4 py-2.5 text-slate-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            @error('max_students_per_class') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        @endif
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
                    <div class="flex items-center justify-between p-6 transition-colors hover:bg-blue-50/40">
                        <div>
                            <p class="text-base font-bold text-slate-800">Xác thực vị trí GPS</p>
                            <p class="mt-1 text-sm text-slate-500">Cho phép giới hạn bán kính điểm danh của học viên quanh vị trí lớp học.</p>
                        </div>
                        <label class="relative ml-4 inline-flex shrink-0 cursor-pointer items-center">
                            <input type="checkbox" wire:model="hasGps" class="peer sr-only">
                            <div class="peer h-7 w-14 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between p-6 transition-colors hover:bg-blue-50/40">
                        <div>
                            <p class="text-base font-bold text-slate-800">Import học viên từ Excel/CSV</p>
                            <p class="mt-1 text-sm text-slate-500">Hỗ trợ upload file danh sách lớp thay vì nhập tay thủ công từng học viên.</p>
                        </div>
                        <label class="relative ml-4 inline-flex shrink-0 cursor-pointer items-center">
                            <input type="checkbox" wire:model="hasImport" class="peer sr-only">
                            <div class="peer h-7 w-14 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between p-6 transition-colors hover:bg-blue-50/40">
                        <div>
                            <p class="text-base font-bold text-slate-800">Báo cáo Thống kê Nâng cao</p>
                            <p class="mt-1 text-sm text-slate-500">Biểu đồ chuyên cần trực quan, xuất báo cáo PDF cuối kỳ, cảnh báo tự động.</p>
                        </div>
                        <label class="relative ml-4 inline-flex shrink-0 cursor-pointer items-center">
                            <input type="checkbox" wire:model="hasReports" class="peer sr-only">
                            <div class="peer h-7 w-14 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between p-6 transition-colors hover:bg-blue-50/40">
                        <div>
                            <p class="text-base font-bold text-slate-800">Tích hợp API (SSO, LMS)</p>
                            <p class="mt-1 text-sm text-slate-500">Tính năng cao cấp cho phép hệ thống gọi API đồng bộ dữ liệu với trường học.</p>
                        </div>
                        <label class="relative ml-4 inline-flex shrink-0 cursor-pointer items-center">
                            <input type="checkbox" wire:model="hasApi" class="peer sr-only">
                            <div class="peer h-7 w-14 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-6 after:w-6 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pb-12 pt-4">
                <button type="submit" class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-3 text-base font-bold text-white shadow-md shadow-blue-500/40 transition-all hover:-translate-y-0.5 hover:from-blue-700 hover:to-cyan-700">
                    <x-user.icon name="plus" :size="20" />
                    Khởi tạo cấu hình mới
                </button>
            </div>
        </form>
    </div>
</div>
