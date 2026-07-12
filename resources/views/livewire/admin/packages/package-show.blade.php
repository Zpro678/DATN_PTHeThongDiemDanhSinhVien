<div>
    @php
        $isFree = (float) $package->price <= 0;
        $isEnterprise = in_array(strtolower($package->plan_tier), ['premium', 'enterprise']);
        
        if ($isEnterprise) {
            $accent = 'from-purple-500 via-fuchsia-500 to-pink-500';
            $badgeClass = 'border-purple-200 bg-purple-100 text-purple-700';
            $priceClass = 'text-purple-600';
            $iconBg = 'border-purple-100 bg-purple-50 text-purple-600';
            $btnClass = 'from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 shadow-purple-500/30';
            $packageLabel = 'PREMIUM PACKAGE';
            $progressClass = 'bg-purple-500';
        } elseif ($isFree) {
            $accent = 'from-blue-500 via-indigo-500 to-violet-500';
            $badgeClass = 'border-blue-200 bg-blue-100 text-blue-700';
            $priceClass = 'text-emerald-600';
            $iconBg = 'border-blue-100 bg-blue-50 text-blue-600';
            $btnClass = 'from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 shadow-blue-500/30';
            $packageLabel = 'FREE PACKAGE';
            $progressClass = 'bg-blue-500';
        } else {
            $accent = 'from-amber-400 via-yellow-500 to-orange-500';
            $badgeClass = 'border-amber-200 bg-amber-100 text-amber-700';
            $priceClass = 'text-blue-600';
            $iconBg = 'border-amber-100 bg-amber-50 text-amber-600';
            $btnClass = 'from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-amber-500/30';
            $packageLabel = 'PRO PACKAGE';
            $progressClass = 'bg-amber-500';
        }

        $priceLabel = $isFree ? '0đ' : number_format($package->price, 0, ',', '.') . 'đ';
        $durationLabel = $package->duration_days > 0 ? ($package->duration_days >= 365 ? round($package->duration_days / 365) . ' năm' : round($package->duration_days / 30) . ' tháng') : 'Vĩnh viễn';
        
        $features = $package->features;
    @endphp

    <div class="mx-auto max-w-[1200px] space-y-6">

        <div class="mb-2 {{ !$package->is_active ? 'opacity-80' : '' }}">
            <div class="h-2 w-full bg-gradient-to-r {{ $accent }}"></div>

            <div class="relative z-10 flex flex-col items-start justify-between gap-6 p-8 md:flex-row md:items-center md:p-10">
                <div>
                    <div class="mb-3 flex items-center gap-3">
                        <span class="inline-flex items-center rounded-lg border {{ $badgeClass }} px-3 py-1.5 text-xs font-bold uppercase tracking-wide shadow-sm">
                            <x-user.icon name="star" :size="14" class="mr-1.5" />
                            {{ $packageLabel }}
                        </span>
                        @if($package->is_active)
                            <span class="inline-flex items-center rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-600">
                                <span class="mr-2 h-2 w-2 rounded-full bg-emerald-500"></span>
                                Đang hoạt động
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-500">
                                Tạm dừng
                            </span>
                        @endif
                    </div>
                    <h1 class="mb-2 text-3xl font-extrabold text-slate-900 md:text-4xl">{{ $package->name }}</h1>
                    <p class="max-w-2xl text-base text-slate-500 md:text-lg">{{ $package->description ?? 'Không có mô tả chi tiết cho gói dịch vụ này.' }}</p>
                </div>

                <div class="flex shrink-0 flex-col items-end">
                    <div class="mb-4 text-right">
                        <p class="mb-1 text-sm font-semibold uppercase tracking-wider text-slate-400">Chi phí</p>
                        <div class="flex flex-col items-end">
                            <p class="text-4xl font-extrabold leading-none {{ $priceClass }}">{{ $priceLabel }}</p>
                            <span class="mt-2 inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                                Thời hạn: {{ $durationLabel }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.packages.index') }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                            Quay lại
                        </a>
                        <a href="{{ route('admin.packages.edit', $package) }}" class="flex items-center gap-2 rounded-xl bg-gradient-to-r {{ $btnClass }} px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5">
                            <x-user.icon name="edit" :size="16" />
                            Chỉnh sửa
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="space-y-6">
                <div class="admin-grid-equal grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-3">
                    <div class="admin-card admin-card-hover rounded-2xl border p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl {{ $iconBg }}">
                            <x-user.icon name="users" :size="24" />
                        </div>
                        <p class="mb-1 text-sm font-semibold uppercase tracking-wider text-slate-500">SV / Lớp</p>
                        <p class="text-3xl font-extrabold text-slate-900">{{ $package->max_students_per_class >= 9999 ? 'Không giới hạn' : $package->max_students_per_class }}</p>
                    </div>
                    
                    <div class="admin-card admin-card-hover rounded-2xl border p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl {{ $iconBg }}">
                            <x-user.icon name="book-open" :size="24" />
                        </div>
                        <p class="mb-1 text-sm font-semibold uppercase tracking-wider text-slate-500">Số lớp học</p>
                        <p class="text-3xl font-extrabold text-slate-900">{{ $package->max_classes >= 9999 ? 'Không giới hạn' : $package->max_classes }}</p>
                    </div>


                    <div class="admin-card admin-card-hover rounded-2xl border p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl {{ $iconBg }}">
                            <x-user.icon name="user-check" :size="24" />
                        </div>
                        <p class="mb-1 text-sm font-semibold uppercase tracking-wider text-slate-500">Người sử dụng</p>
                        <p class="text-3xl font-extrabold text-slate-900">{{ $usersCount }}</p>
                    </div>
                </div>

                <div class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                    <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-5">
                        <h3 class="text-lg font-bold text-slate-900">Tính năng bao gồm</h3>
                    </div>
                    <div class="p-6">
                        <ul class="grid grid-cols-1 gap-x-8 gap-y-4 md:grid-cols-2">
                            @foreach ($features as $feature)
                                <li class="flex items-center py-2">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-emerald-200 bg-emerald-100">
                                        <x-user.icon name="check-circle" :size="14" class="text-emerald-600" />
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-slate-700 md:text-base">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="admin-card overflow-hidden rounded-2xl border">
                    <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-5">
                        <h3 class="text-lg font-bold text-slate-900">Danh sách người đăng ký gần đây</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="border-b border-slate-200 bg-slate-50/50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">Tài khoản</th>
                                    <th class="px-6 py-4">Trạng thái</th>
                                    <th class="px-6 py-4">Ngày đăng ký</th>
                                    <th class="px-6 py-4 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($subscriptions as $sub)
                                    <tr class="transition-colors hover:bg-slate-50/80">
                                        <td class="px-6 py-4 font-bold text-slate-900">{{ $sub->user->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">
                                            @if($sub->status === 'active')
                                                <span class="inline-flex items-center rounded-lg border border-emerald-100 bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-600">Active</span>
                                            @else
                                                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-500">{{ ucfirst($sub->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">{{ $sub->start_date?->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-right">
                                            @if($sub->user)
                                                <a href="{{ route('admin.users.show', $sub->user) }}" class="text-blue-600 hover:underline">Chi tiết</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center font-medium text-slate-500">Chưa có ai đăng ký gói này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($subscriptions->hasPages())
                        <div class="border-t border-slate-100 p-4">
                            {{ $subscriptions->links('vendor.livewire.tailwind') }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
