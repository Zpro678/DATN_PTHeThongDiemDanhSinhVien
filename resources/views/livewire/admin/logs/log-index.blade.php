<div>
    <div class="mx-auto max-w-[1200px]">
        <div class="mb-6 p-5 lg:p-6">
            <div class="relative z-10 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Logs</p>
                <h1 class="mb-1 text-[28px] font-bold text-slate-900">Nhật ký hệ thống</h1>
                <p class="text-sm text-slate-500">Theo dõi dòng sự kiện và các hoạt động thay đổi trên hệ thống.</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative">
                    <x-user.icon name="search" :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm hành động, bảng, user..." class="w-64 rounded-xl border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm text-slate-900 shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="relative">
                    <select wire:model.live="dateFilter" class="w-40 appearance-none rounded-xl border border-slate-200 bg-white py-2 pl-4 pr-10 text-sm text-slate-900 shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Tất cả thời gian</option>
                        <option value="1_month">1 tháng gần nhất</option>
                        <option value="3_months">3 tháng gần nhất</option>
                        <option value="6_months">6 tháng gần nhất</option>
                    </select>
                    <x-user.icon name="chevron-down" :size="16" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" />
                </div>
                @if($search)
                    <button wire:click="$set('search', '')" class="text-sm font-semibold text-blue-600 hover:text-blue-700 hover:underline">Xóa lọc</button>
                @endif
            </div>
            </div>
        </div>

        <div class="admin-card overflow-hidden rounded-2xl border p-6 md:p-8" wire:poll.5s>
            <div class="relative z-10 space-y-8 before:absolute before:bottom-2 before:left-[17px] before:top-2 before:w-0.5 before:bg-slate-100">
                @forelse ($logs as $log)
                    @php
                        $action = strtolower($log->action ?? '');
                        $icon = match (true) {
                            str_contains($action, 'create') || str_contains($action, 'add') => 'plus-circle',
                            str_contains($action, 'attendance') || str_contains($action, 'check') => 'calendar-check',
                            str_contains($action, 'update') || str_contains($action, 'setting') => 'settings',
                            str_contains($action, 'delete') || str_contains($action, 'blocked') => 'alert-triangle',
                            default => 'activity',
                        };
                        $tone = match ($icon) {
                            'plus-circle' => 'text-blue-600 bg-blue-50 border-blue-100',
                            'calendar-check' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                            'settings' => 'text-amber-500 bg-amber-50 border-amber-100',
                            'alert-triangle' => 'text-rose-600 bg-rose-50 border-rose-100',
                            default => 'text-indigo-600 bg-indigo-50 border-indigo-100',
                        };
                    @endphp

                    <div class="group relative flex items-start gap-4 md:gap-6">
                        <div class="z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-white bg-slate-50 shadow-sm transition-transform group-hover:scale-105">
                            <x-user.icon name="{{ $icon }}" :size="16" class="{{ explode(' ', $tone)[0] }}" />
                        </div>
                        <div class="flex-1 rounded-2xl border border-slate-100 bg-white/75 p-4 transition-all hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/40 hover:shadow-sm">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <span class="rounded-md border px-2.5 py-1 text-[10px] font-extrabold tracking-wider {{ $tone }}">
                                    {{ strtoupper($log->table_name ?: 'HỆ THỐNG') }}
                                </span>
                                <span class="flex items-center gap-1.5 text-xs font-medium text-slate-400">
                                    <x-user.icon name="calendar" :size="14" />
                                    {{ $log->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                </span>
                            </div>
                            
                            @php
                                $recordName = $log->new_values['name'] ?? $log->old_values['name'] ?? $log->new_values['title'] ?? $log->old_values['title'] ?? null;
                            @endphp
                            <p class="break-words text-sm font-semibold leading-relaxed text-slate-700">
                                <span class="font-bold text-blue-600">{{ $log->user?->name ?? 'Hệ thống' }}</span>
                                {{ $log->action }}
                                @if($log->courseClass)
                                    lớp <span class="font-bold text-slate-900">{{ $log->courseClass->name }}</span>
                                @elseif($recordName)
                                    <span class="font-bold text-slate-900">"{{ $recordName }}"</span>
                                @endif
                                
                                @if($log->row_id)
                                    <span class="text-slate-400 text-xs ml-1" title="{{ $log->row_id }}">
                                        #( {{ \Illuminate\Support\Str::length($log->row_id) > 15 ? \Illuminate\Support\Str::substr($log->row_id, 0, 8) . '...' : $log->row_id }} )
                                    </span>
                                @endif
                            </p>

                            @if($log->ip_address || $log->user_agent)
                                <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px] font-medium text-slate-400">
                                    @if($log->ip_address)
                                        <div class="flex items-center gap-1">
                                            <x-user.icon name="map-pin" :size="12" />
                                            <span>{{ $log->ip_address }}</span>
                                        </div>
                                    @endif
                                    @if($log->user_agent)
                                        <div class="flex items-center gap-1 max-w-[200px] sm:max-w-md truncate" title="{{ $log->user_agent }}">
                                            <x-user.icon name="monitor" :size="12" />
                                            <span class="truncate">{{ $log->user_agent }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($log->new_values || $log->old_values)
                                <div class="mt-4 flex items-center justify-start" x-data>
                                    <button type="button" wire:click="viewLog({{ $log->id }})" class="group/btn flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow">
                                        <div class="flex h-5 w-5 items-center justify-center rounded-md bg-slate-100 text-slate-500 transition-colors group-hover/btn:bg-blue-100 group-hover/btn:text-blue-600">
                                            <x-user.icon name="eye" :size="12" />
                                        </div>
                                        Xem chi tiết
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="relative flex items-start gap-4 md:gap-6">
                        <div class="z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-white bg-slate-50 shadow-sm">
                            <x-user.icon name="activity" :size="16" class="text-slate-400" />
                        </div>
                        <div class="flex-1 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-sm font-medium text-slate-500">
                            @if($search)
                                Không tìm thấy kết quả nào phù hợp với "{{ $search }}".
                            @else
                                Chưa có nhật ký hệ thống.
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>

            @if($hasMore)
                <div x-intersect="$wire.loadMore()" class="relative z-10 space-y-8 animate-pulse mt-8">
                    <!-- Skeleton 1 -->
                    <div class="relative flex items-start gap-4 md:gap-6">
                        <div class="z-10 h-9 w-9 shrink-0 rounded-full bg-slate-200 shadow-sm"></div>
                        <div class="flex-1 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <div class="h-5 w-20 rounded bg-slate-200"></div>
                                <div class="h-4 w-32 rounded bg-slate-200"></div>
                            </div>
                            <div class="h-4 w-3/4 rounded bg-slate-200 mb-2"></div>
                            <div class="h-4 w-1/2 rounded bg-slate-200"></div>
                        </div>
                    </div>
                    <!-- Skeleton 2 -->
                    <div class="relative flex items-start gap-4 md:gap-6">
                        <div class="z-10 h-9 w-9 shrink-0 rounded-full bg-slate-200 shadow-sm"></div>
                        <div class="flex-1 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <div class="h-5 w-24 rounded bg-slate-200"></div>
                                <div class="h-4 w-28 rounded bg-slate-200"></div>
                            </div>
                            <div class="h-4 w-2/3 rounded bg-slate-200 mb-2"></div>
                            <div class="h-4 w-1/3 rounded bg-slate-200"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <!-- Demo Modal for Log Details -->
    <div x-data="{ open: false }" 
         @open-log-modal.window="open = true" 
         @keydown.escape.window="open = false"
         x-cloak>
        
        <template x-teleport="body">
            <div x-show="open" class="relative z-[9999]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div x-show="open" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal -->
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="fixed inset-0 z-10 w-screen overflow-y-auto">
            
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.outside="open = false" class="relative w-full max-w-5xl transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8">
                    
                    <!-- Header -->
                    <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                    <x-user.icon name="activity" :size="20" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">Chi tiết thao tác</h3>
                                    @if($selectedLog)
                                        <p class="text-sm font-medium text-slate-500">ID: #LOG-{{ $selectedLog->id }} • {{ $selectedLog->created_at->format('d/m/Y H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                            <button @click="open = false" class="rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                                <x-user.icon name="x" :size="20" />
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5">
                        @if($selectedLog)
                        <div class="mb-6 grid grid-cols-2 gap-4 rounded-2xl bg-slate-50 p-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Người thực hiện</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">{{ $selectedLog->user?->name ?? 'Hệ thống' }}</p>
                                <p class="text-sm text-slate-500">{{ $selectedLog->user?->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Thiết bị & IP</p>
                                <p class="mt-1 flex items-center gap-1.5 text-base font-semibold text-slate-900">
                                    <x-user.icon name="map-pin" :size="16" class="text-slate-400" />
                                    {{ $selectedLog->ip_address ?? 'Không rõ' }}
                                </p>
                                <p class="text-sm text-slate-500 truncate" title="{{ $selectedLog->user_agent }}">{{ $selectedLog->user_agent ?? 'Không rõ' }}</p>
                            </div>
                        </div>

                        <h4 class="mb-3 mt-8 text-base font-bold text-slate-900">Chi tiết thay đổi dữ liệu (Bảng: {{ $selectedLog->table_name }})</h4>
                        <div class="overflow-hidden rounded-xl border border-slate-200 shadow-sm max-h-[400px] overflow-y-auto scrollbar-custom">
                            <div class="sticky top-0 grid grid-cols-3 divide-x divide-slate-200 bg-slate-100 border-b border-slate-200">
                                <div class="px-4 py-3 text-sm font-extrabold text-slate-500 uppercase tracking-wider">Trường dữ liệu</div>
                                <div class="px-4 py-3 text-sm font-extrabold text-slate-500 uppercase tracking-wider">Giá trị cũ (Old)</div>
                                <div class="px-4 py-3 text-sm font-extrabold text-blue-600 uppercase tracking-wider">Giá trị mới (New)</div>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @php
                                    $oldValues = $selectedLog->old_values ?? [];
                                    $newValues = $selectedLog->new_values ?? [];
                                    $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
                                @endphp
                                
                                @forelse($allKeys as $key)
                                    @php
                                        $oldVal = array_key_exists($key, $oldValues) ? $oldValues[$key] : null;
                                        $newVal = array_key_exists($key, $newValues) ? $newValues[$key] : null;
                                        if (is_array($oldVal) || is_object($oldVal)) $oldVal = json_encode($oldVal, JSON_UNESCAPED_UNICODE);
                                        if (is_array($newVal) || is_object($newVal)) $newVal = json_encode($newVal, JSON_UNESCAPED_UNICODE);
                                    @endphp
                                    <div class="grid grid-cols-3 divide-x divide-slate-100 text-base transition-colors hover:bg-slate-50">
                                        <div class="px-4 py-3 font-semibold text-slate-700 flex items-center">
                                            <span class="rounded bg-slate-100 px-2 py-1 text-sm font-mono text-slate-600 break-all">{{ $key }}</span>
                                        </div>
                                        <div class="px-4 py-3 text-slate-500 flex items-center overflow-x-hidden">
                                            <span class="break-words w-full">{{ $oldVal ?? 'null' }}</span>
                                        </div>
                                        <div class="px-4 py-3 font-bold text-emerald-700 bg-emerald-50/50 flex items-center overflow-x-hidden">
                                            <span class="break-words w-full">{{ $newVal ?? 'null' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-sm text-slate-500">Không có chi tiết các trường thay đổi.</div>
                                @endforelse
                            </div>
                        </div>
                        @else
                            <div class="flex items-center justify-center py-10">
                                <svg class="h-8 w-8 animate-spin text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button @click="open = false" type="button" class="inline-flex w-full justify-center rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-slate-800 sm:w-auto">
                            Đóng cửa sổ
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
