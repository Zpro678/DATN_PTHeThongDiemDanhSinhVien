<div class="mx-auto max-w-lg p-4 py-12 relative">
    <!-- Main Card -->
    <div class="group relative rounded-[2.5rem] bg-white shadow-2xl shadow-slate-200/50 ring-1 ring-slate-100 transition-all duration-500 hover:shadow-blue-900/5">
        
        <!-- Header Gradient Area -->
        <div class="relative overflow-hidden rounded-t-[2.5rem] bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 px-6 py-6 sm:px-8">
            <!-- Glass effect overlay pattern -->
            <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
            
            <div class="relative z-10 flex items-center gap-4 sm:gap-5">
                <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.25rem] bg-white/20 backdrop-blur-xl shadow-2xl ring-1 ring-white/50">
                    <div class="absolute inset-0 rounded-[1.25rem] bg-white/20 animate-ping opacity-20"></div>
                    <x-user.icon name="scan" :size="28" class="text-white" />
                </div>
                
                <div class="text-left">
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white drop-shadow-sm">Xác nhận điểm danh</h1>
                    <p class="mt-0.5 text-xs sm:text-sm font-medium text-blue-100">Vui lòng xác nhận để được ghi nhận có mặt.</p>
                </div>
            </div>
        </div>

        <!-- Body Area -->
        <div class="relative bg-white px-6 sm:px-8 pb-10 pt-8 rounded-b-[2.5rem]">
            @if(session('success'))
                <div class="mb-8 rounded-3xl border border-emerald-100 bg-emerald-50 p-6 text-center shadow-inner">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 mb-4 shadow-sm">
                        <x-user.icon name="check-circle-2" :size="32" />
                    </div>
                    <h3 class="text-xl font-black text-emerald-700">{{ session('success') }}</h3>
                </div>
            @endif

            @if($statusMessage && !$isSuccess && !session('success'))
                <div class="mb-8 rounded-3xl border border-rose-100 bg-rose-50 p-6 text-center shadow-inner">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 mb-4 shadow-sm">
                        <x-user.icon name="alert-triangle" :size="32" />
                    </div>
                    <h3 class="text-lg font-bold text-rose-700">{{ $statusMessage }}</h3>
                </div>
            @endif

            @if($session)
                <div class="relative mb-8 overflow-hidden rounded-3xl border border-slate-100 bg-gradient-to-b from-slate-50 to-white p-6 shadow-sm ring-1 ring-slate-900/5">
                    <div class="absolute right-0 top-0 -mr-8 -mt-8 h-32 w-32 rounded-full bg-blue-50 blur-3xl"></div>
                    
                    <div class="relative z-10">
                        <div class="mb-5 flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-100/50 text-blue-600 ring-1 ring-blue-100">
                                <x-user.icon name="book-open" :size="24" />
                            </div>
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Môn học</p>
                                <p class="mt-1 text-lg font-black text-slate-900 leading-tight">{{ $session->courseClass->name }}</p>
                                <p class="mt-1 inline-flex items-center rounded-lg bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700">{{ $session->courseClass->code }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500 ring-1 ring-amber-100">
                                    <x-user.icon name="layers" :size="20" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Phiên</p>
                                    <p class="mt-0.5 truncate text-sm font-bold text-slate-800" title="{{ $session->name }}">{{ $session->name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500 ring-1 ring-emerald-100">
                                    <x-user.icon name="calendar" :size="20" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Ngày</p>
                                    <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $session->date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($isGuestForm)
                    <form wire:submit.prevent="submitGuestForm" class="mt-8 space-y-5">
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm font-medium text-blue-800 flex gap-3 items-center">
                            <x-user.icon name="info" :size="20" class="shrink-0" />
                            Bạn chưa đăng nhập. Vui lòng nhập thông tin để điểm danh.
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-black text-slate-700">Mã số sinh viên <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <x-user.icon name="credit-card" :size="20" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                                <input type="text" wire:model="studentCode" class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 font-bold text-slate-900 transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" placeholder="Nhập MSSV của bạn" required>
                            </div>
                            @error('studentCode') <span class="mt-1 block text-sm font-bold text-rose-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-black text-slate-700">Họ và tên <span class="text-slate-400 font-semibold">(Tùy chọn)</span></label>
                            <div class="relative">
                                <x-user.icon name="user" :size="20" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                                <input type="text" wire:model="fullName" class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 font-bold text-slate-900 transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" placeholder="Nhập họ tên của bạn">
                            </div>
                        </div>
                        <button type="submit" class="group relative inline-flex w-full items-center justify-center overflow-hidden rounded-[1.5rem] bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-4 text-lg font-black text-white shadow-xl shadow-blue-600/20 transition-all hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-600/40 active:scale-[0.98]">
                            <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 group-hover:translate-x-full"></div>
                            <span class="relative z-10">Tiếp tục điểm danh</span>
                        </button>
                    </form>
                @elseif(!$isSuccess && $record && ($statusMessage == '' || $isGpsError))
                    <div class="mt-8 text-center" x-data="{
                        isCheckingIn: false,
                        hasGpsRequirement: @js((bool)$session?->gps_radius),
                        performCheckIn() {
                            if (this.isCheckingIn) return;
                            this.isCheckingIn = true;
                            if (this.hasGpsRequirement) {
                                if (window.isSecureContext === false) {
                                    alert('LỖI BẢO MẬT: Trình duyệt chặn quyền GPS vì trang web chưa có chứng chỉ bảo mật (HTTPS) hoặc không chạy trên localhost. Vui lòng test trên localhost hoặc deploy web có HTTPS.');
                                    this.isCheckingIn = false;
                                    return;
                                }

                                if (navigator.geolocation) {
                                    navigator.geolocation.getCurrentPosition(
                                        (position) => {
                                            $wire.checkIn(position.coords.latitude, position.coords.longitude).then(() => {
                                                this.isCheckingIn = false;
                                            });
                                        },
                                        (error) => {
                                            console.warn('Cannot get location', error);
                                            let msg = 'Không thể lấy vị trí. Vui lòng bật vị trí (GPS) và cấp quyền cho trình duyệt.';
                                            if (error.code === 1) msg = 'Bạn đã từ chối cấp quyền vị trí cho trình duyệt.';
                                            if (error.code === 2) msg = 'Không thể xác định được vị trí hiện tại của thiết bị.';
                                            if (error.code === 3) msg = 'Quá thời gian lấy vị trí (Timeout).';
                                            alert(msg);
                                            this.isCheckingIn = false;
                                        },
                                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                                    );
                                } else {
                                    alert('Trình duyệt của bạn không hỗ trợ định vị.');
                                    this.isCheckingIn = false;
                                }
                            } else {
                                $wire.checkIn(null, null).then(() => {
                                    this.isCheckingIn = false;
                                });
                            }
                        }
                    }" x-init="if (@js($isAutoCheckIn)) { performCheckIn(); }">
                        <button 
                            @click="performCheckIn"
                            x-bind:disabled="isCheckingIn"
                            class="group relative inline-flex w-full items-center justify-center overflow-hidden rounded-[1.5rem] bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-5 text-lg font-black text-white shadow-xl shadow-blue-600/20 transition-all hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-600/40 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-70"
                        >
                            <!-- Shimmer effect -->
                            <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 group-hover:translate-x-full"></div>
                            
                            <span x-show="!isCheckingIn" class="relative z-10 flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md transition-transform duration-300 group-hover:rotate-12 group-hover:scale-110">
                                    <x-user.icon name="check-circle" :size="24" class="text-white" />
                                </span>
                                Ghi nhận có mặt
                            </span>
                            <span x-show="isCheckingIn" class="relative z-10 flex items-center gap-3" x-cloak>
                                <svg class="h-6 w-6 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span class="animate-pulse">Đang xác thực...</span>
                            </span>
                        </button>
                        
                        <p x-show="isCheckingIn" class="mt-4 flex items-center justify-center gap-2 text-sm font-bold text-blue-600 animate-pulse">
                            <x-user.icon name="loader" :size="16" class="animate-spin" />
                            Hệ thống đang tự động điểm danh, vui lòng đợi...
                        </p>
                    </div>
                @endif
                
                @if($isSuccess)
                    <div class="mt-8">
                        @auth
                            <a href="{{ route('student.attendance.history', ['ma_user' => auth()->id()]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-6 py-4 font-black text-white shadow-xl shadow-slate-900/20 transition hover:bg-slate-800 hover:-translate-y-0.5">
                                Xem lịch sử điểm danh
                                <x-user.icon name="arrow-right" :size="18" />
                            </a>
                        @else
                            <a href="/" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-6 py-4 font-black text-white shadow-xl shadow-slate-900/20 transition hover:bg-slate-800 hover:-translate-y-0.5">
                                Về trang chủ
                                <x-user.icon name="arrow-right" :size="18" />
                            </a>
                        @endauth
                    </div>
                @endif
            @else
                <div class="mt-6 text-center">
                    @auth
                        <a href="{{ route('dashboard', ['ma_user' => auth()->id()]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-6 py-4 font-black text-white shadow-xl shadow-slate-900/20 transition hover:bg-slate-800 hover:-translate-y-0.5">
                            Về trang chủ
                            <x-user.icon name="arrow-right" :size="18" />
                        </a>
                    @else
                        <a href="/" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-6 py-4 font-black text-white shadow-xl shadow-slate-900/20 transition hover:bg-slate-800 hover:-translate-y-0.5">
                            Về trang chủ
                            <x-user.icon name="arrow-right" :size="18" />
                        </a>
                    @endauth
                </div>
            @endif
        </div>
    </div>
</div>
