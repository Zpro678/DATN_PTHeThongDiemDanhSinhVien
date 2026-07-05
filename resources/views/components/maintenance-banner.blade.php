@php
    $isMaintenance = \App\Models\Setting::get('maintenance_mode', false);
    $start = \App\Models\Setting::get('maintenance_start');
    $end = \App\Models\Setting::get('maintenance_end');
    
    // Kiểm tra xem thời gian hiện tại đã vượt qua thời gian kết thúc chưa
    $isPastEnd = $end ? \Carbon\Carbon::parse($end)->isPast() : false;
    
    // Kiểm tra có phải Super Admin không
    $isSuperAdmin = auth()->check() && auth()->user()->role === \App\Models\User::ROLE_SUPER_ADMIN;
@endphp

@if($isMaintenance && $start && $end && !$isPastEnd && !$isSuperAdmin)
    @php
        $startDate = \Carbon\Carbon::parse($start)->format('H:i d/m/Y');
        $endDate = \Carbon\Carbon::parse($end)->format('H:i d/m/Y');
        $cacheKey = md5($start . $end);
    @endphp
    <div x-data="{ showMaintenance: !sessionStorage.getItem('maintenance_closed_{{ $cacheKey }}') }" 
         x-show="showMaintenance" 
         x-transition
         class="bg-rose-600 text-white shadow-lg relative z-50">
        <div class="max-w-[1200px] mx-auto px-4 py-3 flex items-start sm:items-center justify-between gap-4">
            <div class="flex gap-3 items-start sm:items-center">
                <div class="bg-rose-500 rounded-full p-1.5 shrink-0">
                    <x-user.icon name="alert-triangle" :size="20" class="text-white" />
                </div>
                <div class="text-sm">
                    <strong>Thông báo bảo trì:</strong> Hệ thống dự kiến sẽ tạm ngưng hoạt động từ <strong>{{ $startDate }}</strong> đến <strong>{{ $endDate }}</strong>. Vui lòng lưu lại công việc trước thời điểm này.
                </div>
            </div>
            <button @click="showMaintenance = false; sessionStorage.setItem('maintenance_closed_{{ $cacheKey }}', 'true')" class="shrink-0 p-1 rounded-lg hover:bg-rose-500 transition-colors" title="Đóng thông báo">
                <x-user.icon name="x" :size="20" />
            </button>
        </div>
    </div>
@endif
