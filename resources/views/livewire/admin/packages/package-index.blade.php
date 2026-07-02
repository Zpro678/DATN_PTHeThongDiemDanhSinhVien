<div>
    <div class="mx-auto max-w-[1400px]">
        <div class="mb-6 p-5 lg:p-6">
            <div class="relative z-10 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Packages</p>
                <h1 class="mb-1 text-[28px] font-bold text-slate-900">Quản lý gói dịch vụ</h1>
                <p class="text-sm text-slate-500">Xem và cấu hình các gói dịch vụ trên hệ thống.</p>
            </div>
            </div>
        </div>

        <div class="flex flex-nowrap items-stretch gap-6 overflow-x-auto pt-4 pb-6 -mt-4 snap-x snap-mandatory px-2 scroll-smooth">
            <a href="{{ route('admin.packages.create') }}" class="w-[340px] shrink-0 snap-start admin-card-hover group flex flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-white/80 shadow-sm transition-all duration-500 ease-out hover:border-blue-400 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-100">
                <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-600 bg-gradient-to-br from-blue-600 to-cyan-500 text-white shadow-md shadow-blue-500/25 transition-all duration-500 ease-out group-hover:scale-105 group-hover:rotate-3 group-hover:shadow-blue-500/40">
                    <x-user.icon name="plus" :size="28" />
                </div>
                <h3 class="text-base font-bold text-slate-700 transition-colors duration-500 ease-out group-hover:text-blue-700">Thêm gói dịch vụ mới</h3>
                <p class="mt-1.5 text-sm text-slate-500 transition-colors duration-500 ease-out group-hover:text-slate-600">Tạo cấu hình gói mới</p>
            </a>

            @forelse ($packages as $index => $package)
                @php
                    $isFree = (float) $package->price <= 0;
                    $isEnterprise = strtolower($package->plan_tier) === 'enterprise';
                    
                    if ($isEnterprise) {
                        $accent = 'from-purple-500 via-fuchsia-500 to-pink-500';
                        $badgeClass = 'bg-purple-50 text-purple-600 border-purple-100';
                        $priceClass = 'text-purple-600';
                    } elseif ($isFree) {
                        $accent = 'from-blue-500 via-indigo-500 to-violet-500';
                        $badgeClass = 'bg-blue-50 text-blue-600 border-blue-100';
                        $priceClass = 'text-emerald-600';
                    } else {
                        $accent = 'from-amber-400 via-yellow-500 to-orange-500';
                        $badgeClass = 'bg-amber-50 text-amber-600 border-amber-100';
                        $priceClass = 'text-blue-600';
                    }

                    $priceLabel = $isFree ? '0đ' : number_format($package->price, 0, ',', '.') . 'đ';
                    $durationLabel = $package->duration_days > 0 ? '/' . ($package->duration_days >= 365 ? round($package->duration_days / 365) . ' năm' : round($package->duration_days / 30) . ' tháng') : '/Vĩnh viễn';
                @endphp

                <div class="w-[340px] shrink-0 snap-start admin-card admin-card-hover group flex flex-col overflow-hidden rounded-2xl border {{ !$package->is_active ? 'opacity-70 grayscale-[50%]' : '' }}">
                    <div class="h-2.5 w-full bg-gradient-to-r {{ $accent }}"></div>

                    <div class="relative z-10 px-6 pb-4 pt-5">
                        <div class="mb-3 flex items-start justify-between" x-data="{ open: false }">
                            <h2 class="truncate pr-2 text-lg font-bold leading-tight text-slate-900">{{ $package->name }}</h2>
                            <div class="relative">
                                <button type="button" @click="open = !open" @click.outside="open = false" class="mt-0.5 shrink-0 rounded-lg p-1.5 text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600">
                                    <x-user.icon name="more-vertical" :size="20" />
                                </button>
                                
                                <div x-show="open" 
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 top-full z-50 mt-1 w-48 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg outline-none" 
                                    style="display: none;">
                                    
                                    <a href="{{ route('admin.packages.edit', $package) }}" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 hover:text-blue-600">
                                        <x-user.icon name="edit-3" :size="16" />
                                        Sửa thông tin
                                    </a>
                                    
                                    <button type="button" wire:click="toggleStatus({{ $package->id }})" @click="open = false" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 hover:text-amber-600">
                                        @if($package->is_active)
                                            <x-user.icon name="pause-circle" :size="16" />
                                            Tạm dừng
                                        @else
                                            <x-user.icon name="play-circle" :size="16" />
                                            Mở hoạt động
                                        @endif
                                    </button>
                                    
                                    <div class="my-1 h-px bg-slate-100"></div>
                                    
                                    <button type="button" 
                                        wire:click="deletePackage({{ $package->id }})"
                                        wire:confirm="Bạn có chắc chắn muốn xóa gói dịch vụ này? Hành động này sẽ xóa mềm gói dịch vụ."
                                        @click="open = false"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-600 transition-colors hover:bg-rose-50 hover:text-rose-700">
                                        <x-user.icon name="trash-2" :size="16" />
                                        Xóa gói dịch vụ
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 flex items-center gap-2.5 overflow-hidden whitespace-nowrap h-[26px]">
                            <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $badgeClass }}">
                                {{ $package->plan_tier }}
                            </span>
                            @if($package->is_active)
                                <span class="inline-flex items-center rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-600">
                                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Hoạt động
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-500">
                                    Tạm dừng
                                </span>
                            @endif
                        </div>
                        <p class="text-sm font-medium text-slate-500 h-[40px] line-clamp-2" title="{{ $package->description }}">{{ $package->description ?? 'Không có mô tả' }}</p>
                    </div>

                    <div class="px-6 pb-4">
                        <div class="flex items-end gap-1">
                            <p class="text-3xl font-extrabold leading-none tracking-tight {{ $priceClass }}">{{ $priceLabel }}</p>
                            <p class="text-xs font-semibold text-slate-500 mb-1">{{ $durationLabel }}</p>
                        </div>
                    </div>

                    <div class="px-6 flex-1">
                        <ul class="space-y-3">
                            @if(is_array($package->features))
                                @foreach($package->features as $feature)
                                    <li class="flex items-start gap-2.5">
                                        <x-user.icon name="check-circle-2" :size="16" class="mt-0.5 shrink-0 {{ $priceClass }}" />
                                        <span class="text-sm text-slate-600">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <div class="relative z-10 mx-6 mb-3 mt-6 rounded-xl border border-slate-100 bg-slate-50 p-3 grid grid-cols-3 gap-2">
                        <div class="flex flex-col items-center justify-center">
                            <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">SV/Lớp</p>
                            <div class="flex h-5 items-center justify-center">
                                @if($package->max_students_per_class >= 9999)
                                    <span class="text-lg font-bold text-slate-700 leading-none">&infin;</span>
                                @else
                                    <span class="text-sm font-bold text-slate-700 leading-none">{{ $package->max_students_per_class }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-center justify-center border-l border-r border-slate-200/80">
                            <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">Lớp tối đa</p>
                            <div class="flex h-5 items-center justify-center">
                                @if($package->max_classes >= 9999)
                                    <span class="text-lg font-bold text-slate-700 leading-none">&infin;</span>
                                @else
                                    <span class="text-sm font-bold text-slate-700 leading-none">{{ $package->max_classes }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-center justify-center">
                            <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">Bán kính</p>
                            <div class="flex h-5 items-center justify-center">
                                <span class="text-sm font-bold text-slate-700 leading-none">{{ $package->max_gps_radius }}m</span>
                            </div>
                        </div>
                    </div>

                    <div class="relative z-10 flex gap-3 px-6 pb-6 pt-2">
                        <a href="{{ route('admin.packages.show', $package) }}" class="admin-soft-button flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-700 transition-all hover:bg-slate-50">
                            <x-user.icon name="eye" :size="16" />
                            Xem chi tiết
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
</div>
