<x-admin-layout title="Gói dịch vụ">
    <div class="mx-auto max-w-[1400px]">
        <div class="admin-card mb-6 overflow-hidden rounded-3xl border p-5 lg:p-6">
            <div class="relative z-10 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Packages</p>
                <h1 class="mb-1 text-[28px] font-bold text-slate-900">Quản lý gói dịch vụ</h1>
                <p class="text-sm text-slate-500">Xem và cấu hình các gói dịch vụ Free/Pro trên hệ thống.</p>
            </div>
            <div class="flex items-center gap-5">
                <button type="button" class="admin-soft-button flex items-center gap-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-500/20">
                    <x-user.icon name="search" :size="16" class="text-white" />
                    Tìm kiếm
                </button>
            </div>
            </div>
        </div>

        <div class="admin-grid-equal grid grid-cols-1 items-stretch gap-6 md:grid-cols-3">
            <a href="{{ route('admin.packages.create') }}" class="admin-card-hover group flex h-full min-h-[360px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-white/80 shadow-sm transition-all duration-500 ease-out hover:border-blue-400 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-100">
                <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-600 bg-gradient-to-br from-blue-600 to-cyan-500 text-white shadow-md shadow-blue-500/25 transition-all duration-500 ease-out group-hover:scale-105 group-hover:rotate-3 group-hover:shadow-blue-500/40">
                    <x-user.icon name="plus" :size="28" />
                </div>
                <h3 class="text-base font-bold text-slate-700 transition-colors duration-500 ease-out group-hover:text-blue-700">Thêm gói dịch vụ mới</h3>
                <p class="mt-1.5 text-sm text-slate-500 transition-colors duration-500 ease-out group-hover:text-slate-600">Tạo cấu hình gói mới</p>
            </a>

            @forelse ($packages as $index => $package)
                @php
                    $isFree = (float) $package->price <= 0;
                    $accent = $isFree ? 'from-blue-500 via-indigo-500 to-violet-500' : 'from-amber-400 via-yellow-500 to-orange-500';
                    $badgeClass = $isFree
                        ? 'bg-blue-50 text-blue-600 border-blue-100'
                        : 'bg-amber-50 text-amber-600 border-amber-100';
                    $priceLabel = $isFree ? '0đ' : number_format($package->price, 0, ',', '.') . 'đ';
                    $priceClass = $isFree ? 'text-emerald-600' : 'text-blue-600';
                @endphp

                <div class="admin-card admin-card-hover group flex h-full min-h-[360px] flex-col overflow-hidden rounded-2xl border">
                    <div class="h-1 bg-gradient-to-r {{ $accent }}"></div>

                    <div class="relative z-10 px-6 pb-4 pt-6">
                        <div class="mb-3 flex items-start justify-between">
                            <h2 class="truncate pr-2 text-lg font-bold leading-tight text-slate-900">{{ $package->name }}</h2>
                            <button type="button" class="mt-0.5 shrink-0 rounded-lg p-1.5 text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600">
                                <x-user.icon name="more-vertical" :size="20" />
                            </button>
                        </div>
                        <div class="mb-3 flex items-center gap-2.5">
                            <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $badgeClass }}">
                                {{ $package->code }}
                            </span>
                            <span class="inline-flex items-center rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-600">
                                <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Đang hoạt động
                            </span>
                        </div>
                        <p class="text-sm font-medium text-slate-500">{{ $isFree ? 'Sử dụng cho giảng viên cá nhân' : 'Không giới hạn cho tổ chức/trường học' }}</p>
                    </div>

                    <div class="relative z-10 mx-6 mb-3 mt-auto rounded-xl border border-slate-100 bg-white/75 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 text-center">
                                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Lớp học</p>
                                <p class="text-2xl font-extrabold leading-none tracking-tight text-slate-900">{{ $package->max_classes }}</p>
                            </div>

                            <div class="h-10 w-px bg-slate-200/80"></div>

                            <div class="flex-1 text-center">
                                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Xuất file</p>
                                <p class="text-2xl font-extrabold leading-none tracking-tight text-slate-900">{{ $package->can_export_excel ? 'Có' : 'Không' }}</p>
                            </div>

                            <div class="h-10 w-px bg-slate-200/80"></div>

                            <div class="flex-1 text-center">
                                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Giá</p>
                                <div class="flex flex-col items-center">
                                    <p class="text-2xl font-extrabold leading-none tracking-tight {{ $priceClass }}">{{ $priceLabel }}</p>
                                    <p class="mt-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">{{ $isFree ? 'Vĩnh viễn' : 'Theo gói' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 border-t border-slate-200/60 pt-3 text-center">
                            <p class="flex items-center justify-center gap-1.5 text-xs font-medium text-slate-500">
                                <x-user.icon name="clock" :size="14" class="text-slate-400" />
                                {{ $isFree ? 'Giới hạn tính năng' : 'Mở khóa tính năng nâng cao' }}
                            </p>
                        </div>
                    </div>

                    <div class="relative z-10 flex gap-3 px-6 pb-6 pt-2">
                        <a href="{{ route('admin.packages.show', $package) }}" class="admin-soft-button flex w-[45%] items-center justify-center rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 transition-all hover:bg-slate-50">
                            Xem chi tiết
                        </a>
                        <a href="{{ route('admin.packages.edit', $package) }}" class="admin-soft-button flex w-[55%] items-center justify-center gap-2 rounded-xl bg-gradient-to-r {{ $isFree ? 'from-blue-600 to-cyan-600 shadow-blue-500/20' : 'from-amber-500 to-orange-500 shadow-amber-500/20' }} py-2.5 text-sm font-semibold text-white shadow-sm transition-all">
                            <x-user.icon name="edit" :size="16" />
                            Sửa gói
                        </a>
                    </div>
                </div>
            @empty
                <div class="admin-card flex min-h-[320px] items-center justify-center rounded-2xl border border-dashed border-slate-300 p-10 text-center text-sm font-medium text-slate-500 md:col-span-2">
                    Chưa có gói dịch vụ nào.
                </div>
            @endforelse
        </div>
    </div>
</x-admin-layout>
