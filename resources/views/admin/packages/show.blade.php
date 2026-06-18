@php
    $isFree = (float) $package->price <= 0;
    $priceLabel = $isFree ? '0đ' : number_format($package->price, 0, ',', '.') . 'đ';
    $packageLabel = $isFree ? 'FREE PACKAGE' : 'PRO PACKAGE';
@endphp

<x-admin-layout title="Chi tiết gói dịch vụ">
    <div class="mx-auto max-w-[1200px] space-y-6">
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
                    <span class="ml-1 text-sm font-medium text-slate-500 md:ml-2">{{ $package->name }}</span>
                </li>
            </ol>
        </nav>

        <div class="admin-card overflow-hidden rounded-3xl border">
            <div class="h-2 w-full bg-gradient-to-r {{ $isFree ? 'from-blue-500 via-indigo-500 to-violet-500' : 'from-amber-400 via-yellow-500 to-orange-500' }}"></div>

            <div class="relative z-10 flex flex-col items-start justify-between gap-6 p-8 md:flex-row md:items-center md:p-10">
                <div>
                    <div class="mb-3 flex items-center gap-3">
                        <span class="inline-flex items-center rounded-lg border {{ $isFree ? 'border-blue-200 bg-blue-100 text-blue-700' : 'border-amber-200 bg-amber-100 text-amber-700' }} px-3 py-1.5 text-xs font-bold uppercase tracking-wide shadow-sm">
                            <x-user.icon name="star" :size="14" class="mr-1.5" />
                            {{ $packageLabel }}
                        </span>
                        <span class="inline-flex items-center rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-600">
                            <span class="mr-2 h-2 w-2 rounded-full bg-emerald-500"></span>
                            Đang hoạt động
                        </span>
                    </div>
                    <h1 class="mb-2 text-3xl font-extrabold text-slate-900 md:text-4xl">{{ $package->name }}</h1>
                    <p class="max-w-2xl text-base text-slate-500 md:text-lg">{{ $isFree ? 'Gói cơ bản dành cho cá nhân bắt đầu quản lý lớp học và điểm danh.' : 'Giải pháp toàn diện không giới hạn dành cho các tổ chức, trung tâm đào tạo và trường học có quy mô lớn.' }}</p>
                </div>

                <div class="flex shrink-0 flex-col items-end">
                    <div class="mb-4 text-right">
                        <p class="mb-1 text-sm font-semibold uppercase tracking-wider text-slate-400">Chi phí</p>
                        <div class="flex flex-col items-end">
                            <p class="text-4xl font-extrabold leading-none {{ $isFree ? 'text-emerald-600' : 'text-blue-600' }}">{{ $priceLabel }}</p>
                            <span class="mt-2 inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                                Thời hạn: {{ $isFree ? 'Vĩnh viễn' : 'Theo gói' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.packages.index') }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                            Quay lại
                        </a>
                        <a href="{{ route('admin.packages.edit', $package) }}" class="flex items-center gap-2 rounded-xl bg-gradient-to-r {{ $isFree ? 'from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 shadow-blue-500/30' : 'from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-amber-500/30' }} px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5">
                            <x-user.icon name="edit" :size="16" />
                            Chỉnh sửa
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="admin-grid-equal grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                    <div class="admin-card admin-card-hover rounded-2xl border p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600">
                            <x-user.icon name="book-open" :size="24" />
                        </div>
                        <p class="mb-1 text-sm font-semibold uppercase tracking-wider text-slate-500">Số lớp học</p>
                        <p class="text-3xl font-extrabold text-slate-900">{{ $package->max_classes }}</p>
                    </div>

                    <div class="admin-card admin-card-hover rounded-2xl border p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-600">
                            <x-user.icon name="download" :size="24" />
                        </div>
                        <p class="mb-1 text-sm font-semibold uppercase tracking-wider text-slate-500">Xuất Excel</p>
                        <p class="text-3xl font-extrabold text-slate-900">{{ $package->can_export_excel ? 'Có' : 'Không' }}</p>
                    </div>

                    <div class="admin-card admin-card-hover rounded-2xl border p-6 sm:col-span-2 md:col-span-1">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-purple-100 bg-purple-50 text-purple-600">
                            <x-user.icon name="database" :size="24" />
                        </div>
                        <p class="mb-1 text-sm font-semibold uppercase tracking-wider text-slate-500">Đăng ký</p>
                        <p class="text-3xl font-extrabold text-slate-900">{{ $package->subscriptions_count ?? 0 }}</p>
                    </div>
                </div>

                <div class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                    <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-5">
                        <h3 class="text-lg font-bold text-slate-900">Tính năng bao gồm</h3>
                    </div>
                    <div class="p-6">
                        <ul class="grid grid-cols-1 gap-x-8 gap-y-4 md:grid-cols-2">
                            @foreach ([
                                'Điểm danh bằng QR Code / Link',
                                'Quản lý chuyên cần & cảnh báo',
                                'Xác thực vị trí GPS',
                                'Nhận diện IP & Thiết bị chống gian lận',
                                'Xuất báo cáo Excel / PDF nâng cao',
                                'Import sinh viên hàng loạt',
                                'Cập nhật dữ liệu Realtime',
                                'Hỗ trợ kỹ thuật 24/7 (Ưu tiên)',
                            ] as $feature)
                                <li class="flex items-start rounded-xl border border-slate-100 bg-white/70 p-3">
                                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-emerald-200 bg-emerald-100">
                                        <x-user.icon name="check-circle" :size="14" class="text-emerald-600" />
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-slate-700 md:text-base">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                    <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-5">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900">Thống kê sử dụng</h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500">Người đăng ký</span>
                            <span class="text-lg font-bold text-slate-900">{{ $package->subscriptions_count ?? 0 }}</span>
                        </div>
                        <div class="mb-6 h-2 w-full rounded-full bg-slate-100">
                            <div class="{{ $isFree ? 'bg-blue-500' : 'bg-amber-500' }} h-2 rounded-full" style="width: {{ min(100, max(12, ($package->subscriptions_count ?? 0) * 12)) }}%"></div>
                        </div>

                        <div class="border-t border-slate-100 pt-6 text-center">
                            <p class="mb-3 text-sm text-slate-500">Gói này đang được dùng trong hệ thống hiện tại.</p>
                            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-blue-600 transition-colors hover:text-blue-800">Xem danh sách khách hàng &rarr;</a>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 text-white shadow-lg transition-transform duration-200 hover:-translate-y-1 hover:shadow-xl">
                    <div class="absolute right-0 top-0 p-4 opacity-10">
                        <x-user.icon name="code" :size="96" />
                    </div>
                    <div class="relative z-10 p-6">
                        <h3 class="mb-2 text-lg font-bold">Tích hợp API</h3>
                        <p class="mb-4 text-sm text-slate-400">Gói nâng cao cho phép cấp phát API Key để tích hợp với hệ thống nội bộ của trường đại học (SSO, LMS).</p>
                        <span class="inline-flex items-center rounded border border-emerald-500/30 bg-emerald-500/20 px-2.5 py-1 text-xs font-bold text-emerald-400">
                            {{ $isFree ? 'Chưa kích hoạt' : 'Đã kích hoạt' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
