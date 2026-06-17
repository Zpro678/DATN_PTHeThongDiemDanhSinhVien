<x-app-layout variant="lecturer" page-title="Chi tiết đơn xin nghỉ">
    <div class="w-full font-sans">

        {{-- Page Header with Action Buttons --}}
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
                <div class="min-w-0">
                    <h1 class="text-lg font-extrabold text-slate-900 tracking-tight truncate uppercase">Chi tiết đơn xin nghỉ</h1>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-[12px] font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-500/20 active:scale-[0.98] ">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Duyệt đơn
                </button>
                <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-white text-red-600 text-[12px] font-bold rounded-xl border border-red-200 hover:bg-red-50 transition-all active:scale-[0.98] ">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    Từ chối
                </button>
            </div>
        </div>

        {{-- Top Row: Student Info (left 2/3) + Request Details (right 1/3) --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">

            {{-- Student Info + Reason --}}
            <div class="xl:col-span-2 bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden min-w-0">
                <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-[12px] font-extrabold text-slate-900 uppercase tracking-wider">Thông tin sinh viên</h2>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-4">
                        <img src="https://ui-avatars.com/api/?name=Alex+Thompson&background=dbeafe&color=1e40af&size=128&bold=true" alt="Avatar" class="w-14 h-14 shrink-0 rounded-2xl border-2 border-white shadow-lg">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-extrabold text-slate-900 truncate">Alex Thompson</h3>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 ">
                                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                                    SV20210001
                                </span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 ">
                                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    <span class="truncate">alex.thompson@student.edu.vn</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 ">
                                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    0901 234 567
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Lý do xin nghỉ --}}
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-[11px] font-extrabold text-slate-900 uppercase tracking-wider mb-2">Lý do xin nghỉ</p>
                        <div class="p-3.5 bg-amber-50/50 rounded-xl border border-amber-100/70">
                            <p class="text-[13px] leading-relaxed font-medium text-slate-700 break-words">
                                Bị ốm, sốt cao không thể đi học được. Em đã đi khám bệnh tại phòng khám đa khoa và được bác sĩ chỉ định nghỉ ngơi 2 ngày. Em xin phép thầy/cô cho em nghỉ buổi học ngày 18/06/2026. Em sẽ bổ sung bài tập và nội dung bài học sau khi khỏe lại. Em xin cảm ơn ạ.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Request Details --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden min-w-0">
                <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-[12px] font-extrabold text-slate-900 uppercase tracking-wider">Thông tin đơn</h2>
                </div>
                <div class="p-5 space-y-3.5">
                    <div class="flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-extrabold text-slate-900 uppercase tracking-wider">Lớp học</p>
                            <p class="text-[13px] font-bold text-slate-800 mt-0.5 truncate">CS402: Thuật toán</p>
                        </div>
                    </div>
                    <div class="border-t border-slate-100"></div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-extrabold text-slate-900 uppercase tracking-wider">Ngày xin nghỉ</p>
                            <p class="text-[13px] font-bold text-slate-800 mt-0.5">18/06/2026</p>
                            <p class="text-[11px] font-medium text-slate-500">Ca 1 (07:00 - 09:30)</p>
                        </div>
                    </div>
                    <div class="border-t border-slate-100"></div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-extrabold text-slate-900 uppercase tracking-wider">Thời gian gửi đơn</p>
                            <p class="text-[13px] font-bold text-slate-800 mt-0.5">17/06/2026 08:30</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Row: Evidence (left) + Attendance (right) --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

            {{-- Minh chứng đính kèm --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden min-w-0">
                <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-[12px] font-extrabold text-slate-900 uppercase tracking-wider">Minh chứng đính kèm</h2>
                </div>
                <div class="p-5">
                    {{-- Alpine Component for Gallery --}}
                    <div class="space-y-4" x-data="{ lightboxOpen: false, activeImage: '', activeTitle: '' }">
                        
                        {{-- Image Grid (2 columns for multiple images) --}}
                        <div class="grid grid-cols-2 gap-3">
                            {{-- Image 1 --}}
                            <div class="relative group cursor-pointer rounded-xl overflow-hidden border border-slate-200 shadow-sm aspect-video bg-slate-100" 
                                 @click="activeImage = 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=800&h=500&fit=crop'; activeTitle = 'giay-kham-benh-1.jpg'; lightboxOpen = true">
                                <img src="https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=800&h=500&fit=crop" 
                                     alt="Giấy khám bệnh 1" 
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[1px]">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/25 text-white backdrop-blur-md shadow-sm">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
                                    </span>
                                </div>
                            </div>

                            {{-- Image 2 --}}
                            <div class="relative group cursor-pointer rounded-xl overflow-hidden border border-slate-200 shadow-sm aspect-video bg-slate-100" 
                                 @click="activeImage = 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=800&h=500&fit=crop'; activeTitle = 'don-thuoc.jpg'; lightboxOpen = true">
                                <img src="https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=800&h=500&fit=crop" 
                                     alt="Đơn thuốc" 
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[1px]">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/25 text-white backdrop-blur-md shadow-sm">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- File info list --}}
                        <div class="space-y-2">
                            {{-- File 1 --}}
                            <div class="flex items-center gap-3 px-3.5 py-2 bg-slate-50 rounded-xl border border-slate-100 hover:border-blue-200 transition-colors group">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </span>
                                <div class="min-w-0 flex-1 cursor-pointer" @click="activeImage = 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=800&h=500&fit=crop'; activeTitle = 'giay-kham-benh-1.jpg'; lightboxOpen = true">
                                    <p class="text-[12px] font-bold text-slate-700 truncate group-hover:text-blue-600 transition-colors">giay-kham-benh-1.jpg</p>
                                    <p class="text-[10px] font-medium text-slate-400 truncate">Ảnh JPEG • 1.2 MB</p>
                                </div>
                                <a href="#" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-blue-600 transition-colors" title="Tải xuống">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                </a>
                            </div>

                            {{-- File 2 --}}
                            <div class="flex items-center gap-3 px-3.5 py-2 bg-slate-50 rounded-xl border border-slate-100 hover:border-blue-200 transition-colors group">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </span>
                                <div class="min-w-0 flex-1 cursor-pointer" @click="activeImage = 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=800&h=500&fit=crop'; activeTitle = 'don-thuoc.jpg'; lightboxOpen = true">
                                    <p class="text-[12px] font-bold text-slate-700 truncate group-hover:text-blue-600 transition-colors">don-thuoc.jpg</p>
                                    <p class="text-[10px] font-medium text-slate-400 truncate">Ảnh JPEG • 0.8 MB</p>
                                </div>
                                <a href="#" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-blue-600 transition-colors" title="Tải xuống">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                </a>
                            </div>
                        </div>

                        {{-- Dynamic Lightbox --}}
                        <template x-teleport="body">
                            <div x-show="lightboxOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" @keydown.escape.window="lightboxOpen = false">
                                <div x-show="lightboxOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-950/90 backdrop-blur-md" @click="lightboxOpen = false"></div>
                                <div x-show="lightboxOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="relative z-10 max-w-5xl w-full flex flex-col items-center">
                                    <div class="w-full flex justify-between items-center mb-4">
                                        <p class="text-white font-bold text-[15px]" x-text="activeTitle"></p>
                                        <button @click="lightboxOpen = false" class="flex items-center justify-center w-10 h-10 rounded-full bg-white/10 text-white hover:bg-white/25 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                    <img :src="activeImage" :alt="activeTitle" class="w-full max-h-[80vh] object-contain rounded-xl shadow-2xl">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Chuyên cần gần đây --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden min-w-0 flex flex-col h-full">
                <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 shrink-0">
                    <h2 class="text-[12px] font-extrabold text-slate-900 uppercase tracking-wider">Chuyên cần gần đây</h2>
                </div>
                
                {{-- Khu vực danh sách có thể cuộn --}}
                <div class="p-5 space-y-2 flex-1 overflow-y-auto max-h-[300px] min-h-[150px]">
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-green-50/60 border border-green-100/60">
                        <div class="min-w-0">
                            <p class="text-[12px] font-bold text-slate-700">16/06/2026</p>
                            <p class="text-[10px] font-medium text-slate-400">Ca 1</p>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full  shrink-0">Có mặt</span>
                    </div>
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-green-50/60 border border-green-100/60">
                        <div class="min-w-0">
                            <p class="text-[12px] font-bold text-slate-700">14/06/2026</p>
                            <p class="text-[10px] font-medium text-slate-400">Ca 1</p>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full  shrink-0">Có mặt</span>
                    </div>
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-amber-50/60 border border-amber-100/60">
                        <div class="min-w-0">
                            <p class="text-[12px] font-bold text-slate-700">12/06/2026</p>
                            <p class="text-[10px] font-medium text-slate-400">Ca 1</p>
                        </div>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full  shrink-0">Đi muộn</span>
                    </div>
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-green-50/60 border border-green-100/60">
                        <div class="min-w-0">
                            <p class="text-[12px] font-bold text-slate-700">10/06/2026</p>
                            <p class="text-[10px] font-medium text-slate-400">Ca 1</p>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full  shrink-0">Có mặt</span>
                    </div>
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-red-50/60 border border-red-100/60">
                        <div class="min-w-0">
                            <p class="text-[12px] font-bold text-slate-700">08/06/2026</p>
                            <p class="text-[10px] font-medium text-slate-400">Ca 1</p>
                        </div>
                        <span class="text-[10px] font-bold text-red-700 bg-red-100 px-2 py-0.5 rounded-full  shrink-0">Vắng</span>
                    </div>
                    
                    {{-- Ví dụ thêm dữ liệu để test scroll nếu có nhiều --}}
                    {{-- <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-green-50/60 border border-green-100/60">
                        <div class="min-w-0">
                            <p class="text-[12px] font-bold text-slate-700">06/06/2026</p>
                            <p class="text-[10px] font-medium text-slate-400">Ca 1</p>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full  shrink-0">Có mặt</span>
                    </div> --}}
                </div>

                {{-- Footer cố định --}}
                <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50 shrink-0">
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="font-bold text-slate-500">Tỷ lệ chuyên cần</span>
                        <span class="font-extrabold text-emerald-600">87.5%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Back Button --}}
        <div class="mt-6 mb-2 flex justify-start">
            <a href="/lecturer/students/leave" class="inline-flex items-center gap-2 px-5 py-1.5 bg-white border border-slate-200/80 rounded-xl text-[13px] font-bold text-slate-600 transition-all duration-300 shadow-sm hover:shadow-md hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50/50 hover:-translate-x-1 group">
                <svg class="w-4 h-4 shrink-0 transition-transform duration-300 group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                Quay lại
            </a>
        </div>

    </div>
</x-app-layout>
