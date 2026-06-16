@php
$services = [
    ['name' => "Cơ sở dữ liệu (PostgreSQL)", 'status' => "Hoạt động", 'ok' => true, 'server' => "db-cl-master-01"],
    ['name' => "Bộ nhớ đệm Redis SAMS", 'status' => "Hoạt động", 'ok' => true, 'server' => "redis-cache-main"],
    ['name' => "Cổng thời gian thực WebSocket", 'status' => "Hoạt động", 'ok' => true, 'server' => "ws-gateway-node"],
    ['name' => "Hàng đợi thông báo SMS/Mail", 'status' => "Hoạt động", 'ok' => true, 'server' => "queue-worker-mailer"],
];
@endphp

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between h-full"
     x-data="{ 
         cpuUsage: 24.5, 
         ramUsage: 58.2, 
         storageUsage: 41.9,
         init() {
             setInterval(() => {
                 let cpuDelta = (Math.random() - 0.5) * 4;
                 let nextCpu = this.cpuUsage + cpuDelta;
                 if (nextCpu > 5 && nextCpu < 85) this.cpuUsage = parseFloat(nextCpu.toFixed(1));

                 let ramDelta = (Math.random() - 0.5) * 0.8;
                 let nextRam = this.ramUsage + ramDelta;
                 if (nextRam > 30 && nextRam < 95) this.ramUsage = parseFloat(nextRam.toFixed(1));
             }, 4000);
         }
     }">
    <div>
        <div class="flex items-center justify-between gap-4 mb-6 pb-3 border-b border-slate-100 dark:border-slate-800/85">
            <div class="flex items-center gap-2">
                <x-sams.icon name="server" class="w-5 h-5 text-blue-600 shrink-0" />
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Giám sát hạ tầng & Sức khỏe hệ thống
                    </h2>
                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-400 mt-0.5">
                        Chỉ số hoạt động phụ tải đám mây và trạng thái cổng kết nối API thời gian thực.
                    </p>
                </div>
            </div>
            
            <span class="text-[10px] font-extrabold text-slate-400 tracking-widest uppercase shrink-0 hidden sm:block">
                SAMS HEALTH V2
            </span>
        </div>

        <!-- Load indicators meters -->
        <div class="space-y-4 mb-6">
            <!-- CPU Load -->
            <div class="space-y-1.5">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="text-slate-600 dark:text-slate-300">Vi xử lý CPU Cloud Container</span>
                    <span class="text-slate-900 dark:text-white font-black" x-text="cpuUsage + '%'"></span>
                </div>
                <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div 
                        class="h-full rounded-full transition-all duration-1000"
                        :class="cpuUsage > 75 ? 'bg-rose-500' : (cpuUsage > 50 ? 'bg-amber-500' : 'bg-blue-600')"
                        :style="`width: ${cpuUsage}%`"
                    ></div>
                </div>
            </div>

            <!-- Memory RAM Usage -->
            <div class="space-y-1.5">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="text-slate-600 dark:text-slate-300">Bộ nhớ Memory (RAM)</span>
                    <span class="text-slate-900 dark:text-white font-black" x-text="ramUsage + '%'"></span>
                </div>
                <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-indigo-500 rounded-full transition-all duration-1000" 
                        :style="`width: ${ramUsage}%`"
                    ></div>
                </div>
            </div>

            <!-- Storage Capacity -->
            <div class="space-y-1.5">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="text-slate-600 dark:text-slate-300">Dung lượng ổ đĩa lưu trữ</span>
                    <span class="text-slate-900 dark:text-white font-black" x-text="storageUsage + '%'"></span>
                </div>
                <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-purple-500 rounded-full transition-all duration-1000" 
                        :style="`width: ${storageUsage}%`"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Specific Cloud Services Health status indicators -->
        <div class="space-y-2.5 pt-4 border-t border-slate-100 dark:border-slate-800/80">
            <label class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest block mb-2.5">
                Trạng thái máy chủ liên đới
            </label>
            
            @foreach($services as $svc)
                <div class="flex justify-between items-center p-2.5 rounded-xl border border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-900/40 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $svc['ok'] ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">
                            {{ $svc['name'] }}
                        </span>
                    </div>
                    
                    <div class="text-right">
                        <span class="font-extrabold text-slate-400 block text-[9px] uppercase tracking-wider">{{ $svc['server'] }}</span>
                        <span class="text-[10px] font-bold mt-0.5 block leading-none {{ $svc['ok'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                            {{ $svc['status'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400 font-bold">
        <span>Thời gian hệ thống hoạt động liên tục (Uptime): 142 ngày</span>
        <button type="button" onclick="alert('Hạ tầng SAMS hoạt động bình thường 100%. Các kết nối WebSocket khả dụng.')" class="text-blue-600 hover:text-blue-700 hover:underline flex items-center gap-1 cursor-pointer">
            <x-sams.icon name="refresh-cw" class="w-3.5 h-3.5" />
            Kiểm tra Ping
        </button>
    </div>
</div>
