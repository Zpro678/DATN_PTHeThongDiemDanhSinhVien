<x-app-layout variant="lecturer" page-title="Chi tiết lớp học">
    <div class="max-w-[1400px] mx-auto font-sans" x-data="{ activeTab: 'overview' }">
        
        <!-- Header Section -->
        <div class="mb-4 px-1 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="text-[24px] font-black text-slate-900 uppercase tracking-wider">THÔNG TIN LỚP HỌC</h2>
            <!-- Action Buttons -->
            <div class="flex items-center gap-3 w-full sm:w-auto overflow-x-auto pt-4 pr-4 pb-2 sm:pb-0 -mt-4 -mr-4 scrollbar-hide">
                <button onclick="document.getElementById('shareClassModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-white/80 backdrop-blur-sm border border-slate-200 text-slate-700 text-sm font-bold px-4 py-2.5 rounded-[14px] hover:bg-slate-50 hover:text-slate-900 transition-all shadow-sm whitespace-nowrap group">
                    <svg class="w-5 h-5 text-blue-500 shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                    Chia sẻ
                </button>
                <button onclick="document.getElementById('importStudentModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-white/80 backdrop-blur-sm border border-slate-200 text-slate-700 text-sm font-bold px-4 py-2.5 rounded-[14px] hover:bg-slate-50 hover:text-slate-900 transition-all shadow-sm whitespace-nowrap group">
                    <svg class="w-5 h-5 text-blue-500 shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import
                </button>
                <button class="relative inline-flex items-center gap-2 bg-white/80 backdrop-blur-sm border border-slate-200 text-slate-700 text-sm font-bold px-4 py-2.5 rounded-[14px] hover:bg-slate-50 hover:text-slate-900 hover:shadow-md transition-all shadow-sm whitespace-nowrap group overflow-visible">
                    <span class="absolute -top-2.5 -right-2.5 inline-flex items-center gap-0.5 bg-gradient-to-r from-amber-400 to-yellow-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full uppercase tracking-wider shadow-md z-10 animate-pulse">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1l2.928 6.856L20 8.59l-5.072 4.574L16.18 20 10 16.146 3.82 20l1.252-6.836L0 8.59l7.072-.734L10 1z" clip-rule="evenodd" /></svg>
                        PRO
                    </span>
                    <svg class="w-5 h-5 text-slate-500 shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Xuất dữ liệu
                </button>
            </div>
        </div>
        <div class="flex flex-col lg:flex-row gap-6 mb-6">
            <!-- Left Card: Title & Info -->
            <div class="bg-white/60 backdrop-blur-xl rounded-[24px] border border-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 sm:p-7 flex-1 relative overflow-hidden group hover:bg-white/80 transition-all duration-500">
                <!-- Soft background glow -->
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-100/50 rounded-full mix-blend-multiply filter blur-3xl opacity-50 group-hover:opacity-100 transition-opacity duration-700"></div>

                <div class="mb-5 flex flex-col xl:flex-row xl:items-start gap-4 justify-between relative z-10">
                    <h1 class="text-[28px] sm:text-[32px] font-extrabold tracking-tight bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-700 bg-clip-text text-transparent leading-[1.2] break-words line-clamp-2 flex-1" title="CS304 - Thiết kế và Đánh giá thuật toán Ứng dụng Trí tuệ Nhân tạo Nâng cao cho Hệ thống Phân tán Quy mô Lớn">
                        CS304 - Thiết kế và Đánh giá thuật toán Ứng dụng Trí tuệ Nhân tạo Nâng cao cho Hệ thống Phân tán Quy mô Lớn
                    </h1>
                    <span class="inline-flex items-center gap-1.5 bg-white/60 backdrop-blur-md text-blue-700 text-[13px] font-bold px-3.5 py-2 rounded-xl uppercase tracking-wide border border-white shadow-sm shrink-0 mt-1 xl:mt-0 group-hover:bg-white transition-colors duration-300">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Nhóm lớp: CS402-A
                    </span>
                </div>
                
                <div class="flex flex-wrap items-center gap-3 relative z-10">
                    <div class="inline-flex items-center gap-2 bg-white/50 backdrop-blur-sm border border-white/80 text-slate-700 text-[13px] font-semibold px-3.5 py-2 rounded-xl hover:bg-white transition-colors duration-300 shadow-[0_2px_10px_rgb(0,0,0,0.02)] cursor-default">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Khoa học máy tính
                    </div>
                    <div class="inline-flex items-center gap-2 bg-white/50 backdrop-blur-sm border border-white/80 text-slate-700 text-[13px] font-semibold px-3.5 py-2 rounded-xl hover:bg-white transition-colors duration-300 shadow-[0_2px_10px_rgb(0,0,0,0.02)] cursor-default">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Học kỳ 1 (2024/2025)
                    </div>
                    <div class="inline-flex items-center gap-2 bg-white/50 backdrop-blur-sm border border-white/80 text-slate-700 text-[13px] font-semibold px-3.5 py-2 rounded-xl hover:bg-white transition-colors duration-300 shadow-[0_2px_10px_rgb(0,0,0,0.02)] cursor-default">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        TS. Alaric Vance
                    </div>
                </div>
            </div>

            <!-- Right Card: Stats -->
            <div class="relative overflow-hidden rounded-[24px] bg-gradient-to-br from-blue-50/50 to-indigo-50/30 backdrop-blur-xl p-6 w-full lg:w-auto lg:min-w-[340px] flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white group hover:shadow-[0_8px_30px_rgb(59,130,246,0.08)] transition-all duration-500 hover:-translate-y-1">
                <!-- Soft Glow -->
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-blue-200/50 rounded-full mix-blend-multiply filter blur-3xl opacity-60 group-hover:opacity-100 transition-opacity duration-700"></div>
                <div class="absolute -bottom-12 -left-12 w-40 h-40 bg-indigo-200/50 rounded-full mix-blend-multiply filter blur-3xl opacity-60 group-hover:opacity-100 transition-opacity duration-700"></div>

                <div class="flex items-center gap-10 relative z-10">
                    <div class="flex flex-col items-center justify-center gap-1.5">
                        <span class="text-[44px] font-black text-blue-900 leading-none tracking-tight drop-shadow-sm group-hover:scale-105 transition-transform duration-500">60</span>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Sinh viên</span>
                    </div>
                    <div class="w-px h-16 bg-blue-200/50"></div>
                    <div class="flex flex-col items-center justify-center gap-1.5">
                        <div class="flex items-baseline gap-0.5 drop-shadow-sm group-hover:scale-105 transition-transform duration-500">
                            <span class="text-[44px] font-black text-indigo-600 leading-none tracking-tight">92</span>
                            <span class="text-[20px] font-bold text-indigo-600/80 leading-none">%</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Điểm danh TB</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="mb-6 flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <button @click="activeTab = 'overview'" 
                    :class="activeTab === 'overview' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_4px_12px_rgb(79,70,229,0.3)] border-transparent' : 'bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 border-slate-200 shadow-sm'"
                    class="px-5 py-2.5 rounded-xl text-[14px] font-bold whitespace-nowrap transition-all border">
                Tổng quan
            </button>
            <button @click="activeTab = 'students'" 
                    :class="activeTab === 'students' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_4px_12px_rgb(79,70,229,0.3)] border-transparent' : 'bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 border-slate-200 shadow-sm'"
                    class="px-5 py-2.5 rounded-xl text-[14px] font-bold whitespace-nowrap transition-all border">
                Sinh viên
            </button>
            <button @click="activeTab = 'sessions'" 
                    :class="activeTab === 'sessions' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_4px_12px_rgb(79,70,229,0.3)] border-transparent' : 'bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 border-slate-200 shadow-sm'"
                    class="px-5 py-2.5 rounded-xl text-[14px] font-bold whitespace-nowrap transition-all border">
                Phiên điểm danh
            </button>

            <button @click="activeTab = 'settings'" 
                    :class="activeTab === 'settings' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-[0_4px_12px_rgb(79,70,229,0.3)] border-transparent' : 'bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 border-slate-200 shadow-sm'"
                    class="px-5 py-2.5 rounded-xl text-[14px] font-bold whitespace-nowrap transition-all border">
                Cài đặt
            </button>
        </div>



        <!-- Tab Content: Tổng quan -->
        <div x-cloak x-show="activeTab === 'overview'" class="bg-white rounded-[24px] border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            
            <!-- 4 Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="group bg-white rounded-[20px] border border-slate-200 p-6 shadow-sm hover:-translate-y-1.5 hover:shadow-[0_12px_30px_-10px_rgba(0,0,0,0.1)] hover:border-slate-300 transition-all duration-300 ease-out flex flex-col justify-between">
                    <p class="text-[13px] font-extrabold text-slate-500 uppercase tracking-wider mb-2">TỔNG SỐ SINH VIÊN</p>
                    <div class="flex items-end justify-between mt-auto">
                        <span class="text-[30px] font-black text-slate-900 leading-none group-hover:text-blue-600 transition-colors duration-300">60</span>
                        <div class="w-12 h-12 rounded-[14px] bg-blue-50/80 border border-blue-100 flex items-center justify-center group-hover:bg-blue-100 transition-colors duration-300">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="group bg-white rounded-[20px] border border-slate-200 p-6 shadow-sm hover:-translate-y-1.5 hover:shadow-[0_12px_30px_-10px_rgba(0,0,0,0.1)] hover:border-slate-300 transition-all duration-300 ease-out flex flex-col justify-between">
                    <p class="text-[13px] font-extrabold text-slate-500 uppercase tracking-wider mb-2">TỈ LỆ ĐIỂM DANH</p>
                    <div class="flex items-end justify-between mt-auto">
                        <div>
                            <span class="text-[30px] font-black text-slate-900 leading-none group-hover:text-emerald-600 transition-colors duration-300">92%</span>
                        </div>
                        <div class="w-12 h-12 rounded-[14px] bg-emerald-50/80 border border-emerald-100 flex items-center justify-center mb-1 group-hover:bg-emerald-100 transition-colors duration-300">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="group bg-white rounded-[20px] border border-slate-200 p-6 shadow-sm hover:-translate-y-1.5 hover:shadow-[0_12px_30px_-10px_rgba(0,0,0,0.1)] hover:border-slate-300 transition-all duration-300 ease-out flex flex-col justify-between">
                    <p class="text-[13px] font-extrabold text-slate-500 uppercase tracking-wider mb-2">TỔNG SỐ PHIÊN</p>
                    <div class="flex items-end justify-between mt-auto">
                        <span class="text-[30px] font-black text-slate-900 leading-none group-hover:text-slate-700 transition-colors duration-300">24</span>
                        <div class="w-12 h-12 rounded-[14px] bg-slate-50 border border-slate-100 flex items-center justify-center group-hover:bg-slate-100 transition-colors duration-300">
                            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Red Alert -->
                <div class="group bg-red-50/40 rounded-[20px] border border-red-100 p-6 shadow-sm hover:-translate-y-1.5 hover:shadow-[0_12px_30px_-10px_rgba(239,68,68,0.2)] hover:border-red-300 transition-all duration-300 ease-out flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-100 rounded-full opacity-50 blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                    <p class="text-[13px] font-extrabold text-red-600 uppercase tracking-wider mb-2 relative z-10">SINH VIÊN CÓ NGUY CƠ</p>
                    <div class="flex items-end justify-between mt-auto relative z-10">
                        <span class="text-[30px] font-black text-red-600 leading-none">03</span>
                        <div class="w-12 h-12 rounded-[14px] bg-white border border-red-100 flex items-center justify-center shadow-sm group-hover:bg-red-50 transition-colors duration-300">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content below stats -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left: Risk List (occupies 2 columns on lg) -->
                <div class="lg:col-span-2 bg-white rounded-[24px] border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-white">
                        <h2 class="text-[20px] font-extrabold text-slate-900 tracking-tight">Danh sách rủi ro</h2>
                        <span class="w-7 h-7 bg-red-600 text-white text-[12px] font-bold rounded-full flex items-center justify-center shadow-md shadow-red-500/30">3</span>
                    </div>
                    
                    <div class="px-6 py-3 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition-colors cursor-pointer" checked>
                            <span class="text-[13px] font-bold text-slate-600 group-hover:text-slate-900 transition-colors">Chọn tất cả (3)</span>
                        </label>
                    </div>

                    <div class="flex-1 max-h-[320px] overflow-y-auto divide-y divide-slate-100">
                        <!-- Student 1 (High Risk) -->
                        <label class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors group cursor-pointer">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition-colors cursor-pointer" checked>
                                <img src="https://ui-avatars.com/api/?name=Kevin+T.+Miller&background=random" class="w-10 h-10 rounded-full border border-slate-200 object-cover" alt="Avatar">
                                <div>
                                    <h3 class="text-[14px] font-bold text-slate-900 leading-tight group-hover:text-blue-600 transition-colors">Kevin T. Miller</h3>
                                    <p class="text-[12px] font-bold text-red-600 mt-0.5">64% Điểm danh</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="bg-red-100 text-red-700 text-[11px] font-black px-2.5 py-1 rounded-md uppercase tracking-wider">
                                    RỦI RO CAO
                                </span>
                            </div>
                        </label>

                        <!-- Student 2 (Medium Risk) -->
                        <label class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors group cursor-pointer">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition-colors cursor-pointer" checked>
                                <img src="https://ui-avatars.com/api/?name=Sarah+Jenkins&background=random" class="w-10 h-10 rounded-full border border-slate-200 object-cover" alt="Avatar">
                                <div>
                                    <h3 class="text-[14px] font-bold text-slate-900 leading-tight group-hover:text-blue-600 transition-colors">Sarah Jenkins</h3>
                                    <p class="text-[12px] font-bold text-amber-600 mt-0.5">72% Điểm danh</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="bg-amber-100/60 text-amber-700 text-[11px] font-black px-2.5 py-1 rounded-md uppercase tracking-wider">
                                    TRUNG BÌNH
                                </span>
                            </div>
                        </label>

                        <!-- Student 3 (Medium Risk) -->
                        <label class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors group cursor-pointer">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition-colors cursor-pointer">
                                <img src="https://ui-avatars.com/api/?name=Marcus+Zhao&background=random" class="w-10 h-10 rounded-full border border-slate-200 object-cover" alt="Avatar">
                                <div>
                                    <h3 class="text-[14px] font-bold text-slate-900 leading-tight group-hover:text-blue-600 transition-colors">Marcus Zhao</h3>
                                    <p class="text-[12px] font-bold text-amber-600 mt-0.5">75% Điểm danh</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="bg-amber-100/60 text-amber-700 text-[11px] font-black px-2.5 py-1 rounded-md uppercase tracking-wider">
                                    TRUNG BÌNH
                                </span>
                            </div>
                        </label>
                        
                        <!-- Student 4 (Medium Risk) -->
                        <label class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors group cursor-pointer">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition-colors cursor-pointer">
                                <img src="https://ui-avatars.com/api/?name=Emma+Watson&background=random" class="w-10 h-10 rounded-full border border-slate-200 object-cover" alt="Avatar">
                                <div>
                                    <h3 class="text-[14px] font-bold text-slate-900 leading-tight group-hover:text-blue-600 transition-colors">Emma Watson</h3>
                                    <p class="text-[12px] font-bold text-amber-600 mt-0.5">76% Điểm danh</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="bg-amber-100/60 text-amber-700 text-[11px] font-black px-2.5 py-1 rounded-md uppercase tracking-wider">
                                    TRUNG BÌNH
                                </span>
                            </div>
                        </label>
                        
                        <!-- Student 5 (Medium Risk) -->
                        <label class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors group cursor-pointer">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition-colors cursor-pointer">
                                <img src="https://ui-avatars.com/api/?name=Liam+Neeson&background=random" class="w-10 h-10 rounded-full border border-slate-200 object-cover" alt="Avatar">
                                <div>
                                    <h3 class="text-[14px] font-bold text-slate-900 leading-tight group-hover:text-blue-600 transition-colors">Liam Neeson</h3>
                                    <p class="text-[12px] font-bold text-amber-600 mt-0.5">78% Điểm danh</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="bg-amber-100/60 text-amber-700 text-[11px] font-black px-2.5 py-1 rounded-md uppercase tracking-wider">
                                    TRUNG BÌNH
                                </span>
                            </div>
                        </label>
                        
                        <!-- Student 6 (Low Risk) -->
                        <label class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors group cursor-pointer">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition-colors cursor-pointer">
                                <img src="https://ui-avatars.com/api/?name=Olivia+Rodrigo&background=random" class="w-10 h-10 rounded-full border border-slate-200 object-cover" alt="Avatar">
                                <div>
                                    <h3 class="text-[14px] font-bold text-slate-900 leading-tight group-hover:text-blue-600 transition-colors">Olivia Rodrigo</h3>
                                    <p class="text-[12px] font-bold text-amber-600 mt-0.5">79% Điểm danh</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="bg-amber-100/60 text-amber-700 text-[11px] font-black px-2.5 py-1 rounded-md uppercase tracking-wider">
                                    TRUNG BÌNH
                                </span>
                            </div>
                        </label>
                    </div>

                    <div class="p-6 border-t border-slate-100 bg-white mt-auto">
                        <button class="w-full py-3.5 bg-white border-2 border-slate-200 text-slate-800 text-[15px] font-bold rounded-[16px] hover:border-slate-300 hover:bg-slate-50 transition-all shadow-sm">
                            Gửi cảnh báo nhóm
                        </button>
                    </div>
                </div>

                <!-- Right: Warning Stats (occupies 1 column on lg) -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm p-6">
                        <h3 class="text-[12px] font-black text-slate-500 uppercase tracking-widest mb-6">THỐNG KÊ CẢNH BÁO</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <div class="flex justify-between items-end mb-3">
                                    <span class="text-[15px] font-medium text-slate-700">Cảnh báo tự động</span>
                                    <span class="text-[15px] font-bold text-slate-900">Đã gửi 12</span>
                                </div>
                                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-500 rounded-full" style="width: 75%"></div>
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t border-slate-100 mt-2">
                                <button class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 text-[14px] font-bold rounded-xl border border-slate-200 transition-all group shadow-sm">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        Xem DS bị cảnh báo
                                    </div>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- Tab Content: Sinh viên -->
        <div x-cloak x-show="activeTab === 'students'" class="space-y-6">
            <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-[20px] font-extrabold text-slate-900 tracking-tight">Danh sách sinh viên (60)</h2>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative w-full sm:w-64">
                            <input type="text" placeholder="Tìm kiếm sinh viên..." class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-[13px] rounded-lg pl-9 pr-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <button class="flex items-center gap-2 bg-blue-600 text-white text-[13px] font-bold px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 transition-all whitespace-nowrap shadow-sm group">
                            <svg class="w-4 h-4 text-white transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Import
                        </button>
                    </div>
                </div>
                <div class="max-h-[650px] overflow-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-[13px] font-bold text-slate-800 uppercase tracking-wider">
                                <th class="py-4 pl-14 pr-6 w-[30%]">Sinh viên</th>
                                <th class="py-4 px-6 w-[20%]">Mã SV</th>
                                <th class="py-4 px-6 w-[20%]">Điểm danh</th>
                                <th class="py-4 px-6 w-[15%]">Trạng thái</th>
                                <th class="py-4 pr-14 pl-6 w-[15%] text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-[14px]">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 pl-14 pr-6">
                                    <div class="flex items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name=Kevin+T.+Miller&background=random" class="w-9 h-9 rounded-full object-cover">
                                        <span class="font-bold text-slate-900">Kevin T. Miller</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-medium text-slate-600">SV2024001</td>
                                <td class="py-4 px-6 font-bold text-red-600">64%</td>
                                <td class="py-4 px-6">
                                    <span class="bg-green-100 text-green-700 text-[11px] font-bold px-2.5 py-1 rounded-md uppercase">Hoạt động</span>
                                </td>
                                <td class="py-4 pr-14 pl-6 text-right">
                                    <div class="relative flex justify-end" x-data="{ 
                                        open: false, top: 0, left: 0,
                                        toggle() {
                                            if(this.open) { this.open = false; return; }
                                            const rect = this.$refs.btn.getBoundingClientRect();
                                            this.top = rect.bottom + 8;
                                            this.left = rect.right - 176;
                                            this.open = true;
                                        }
                                    }" x-init="$nextTick(() => { document.body.appendChild($refs.menu); })" x-on:scroll.document.capture="open = false" @resize.window="open = false">
                                        <button x-ref="btn" @click="toggle()" @click.away="open = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                        </button>

                                        <!-- Dropdown menu -->
                                        <div x-ref="menu" x-show="open" x-transition.opacity.duration.200ms @click.away="open = false"
                                             class="fixed w-44 bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-100 py-2 z-[9999]" 
                                             :style="`top: ${top}px; left: ${left}px; display: none;`">
                                                <button class="w-full flex items-center gap-3 px-4 py-2.5 text-[14px] text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors font-medium whitespace-nowrap text-left">
                                                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    Xem chi tiết
                                                </button>
                                                <button class="w-full flex items-center gap-3 px-4 py-2.5 text-[14px] text-slate-700 hover:bg-red-50 hover:text-red-600 transition-colors font-medium whitespace-nowrap text-left">
                                                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Xóa khỏi lớp
                                                </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 pl-14 pr-6">
                                    <div class="flex items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name=Sarah+Jenkins&background=random" class="w-9 h-9 rounded-full object-cover">
                                        <span class="font-bold text-slate-900">Sarah Jenkins</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-medium text-slate-600">SV2024002</td>
                                <td class="py-4 px-6 font-bold text-amber-600">72%</td>
                                <td class="py-4 px-6">
                                    <span class="bg-green-100 text-green-700 text-[11px] font-bold px-2.5 py-1 rounded-md uppercase">Hoạt động</span>
                                </td>
                                <td class="py-4 pr-14 pl-6 text-right">
                                    <div class="relative flex justify-end" x-data="{ open: false }">
                                        <button @click="open = !open" @click.away="open = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                        </button>

                                        <!-- Dropdown menu -->
                                        <div x-show="open" x-transition.opacity.duration.200ms
                                             class="absolute right-0 top-full mt-2 w-44 bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-100 py-2 z-10" style="display: none;">
                                            <button class="w-full flex items-center gap-3 px-4 py-2.5 text-[14px] text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors font-medium whitespace-nowrap text-left">
                                                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                Xem chi tiết
                                            </button>
                                            <button class="w-full flex items-center gap-3 px-4 py-2.5 text-[14px] text-slate-700 hover:bg-red-50 hover:text-red-600 transition-colors font-medium whitespace-nowrap text-left">
                                                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Xóa khỏi lớp
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 pl-14 pr-6">
                                    <div class="flex items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name=Marcus+Zhao&background=random" class="w-9 h-9 rounded-full object-cover">
                                        <span class="font-bold text-slate-900">Marcus Zhao</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-medium text-slate-600">SV2024003</td>
                                <td class="py-4 px-6 font-bold text-emerald-600">98%</td>
                                <td class="py-4 px-6">
                                    <span class="bg-green-100 text-green-700 text-[11px] font-bold px-2.5 py-1 rounded-md uppercase">Hoạt động</span>
                                </td>
                                <td class="py-4 pr-14 pl-6 text-right">
                                    <div class="relative flex justify-end" x-data="{ open: false }">
                                        <button @click="open = !open" @click.away="open = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                        </button>

                                        <!-- Dropdown menu -->
                                        <div x-show="open" x-transition.opacity.duration.200ms
                                             class="absolute right-0 top-full mt-2 w-44 bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-100 py-2 z-10" style="display: none;">
                                            <button class="w-full flex items-center gap-3 px-4 py-2.5 text-[14px] text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors font-medium whitespace-nowrap text-left">
                                                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                Xem chi tiết
                                            </button>
                                            <button class="w-full flex items-center gap-3 px-4 py-2.5 text-[14px] text-slate-700 hover:bg-red-50 hover:text-red-600 transition-colors font-medium whitespace-nowrap text-left">
                                                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Xóa khỏi lớp
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content: Phiên điểm danh -->
        <div x-cloak x-show="activeTab === 'sessions'" class="space-y-6">
            <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-[20px] font-extrabold text-slate-900 tracking-tight">Phiên điểm danh</h2>
                        <p class="text-[13px] font-medium text-slate-500 mt-1">Lịch sử 24 phiên điểm danh đã diễn ra</p>
                    </div>
                    <button class="flex items-center gap-2 bg-blue-600 text-white text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-blue-700 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tạo phiên mới
                    </button>
                </div>
                <div class="max-h-[650px] overflow-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-[13px] font-bold text-slate-800 uppercase tracking-wider">
                                <th class="py-4 pl-14 pr-6 w-[35%]">Tên phiên</th>
                                <th class="py-4 px-6 w-[20%]">Ngày tạo</th>
                                <th class="py-4 px-6 w-[15%]">Sĩ số</th>
                                <th class="py-4 px-6 w-[15%]">Trạng thái</th>
                                <th class="py-4 pr-14 pl-6 w-[15%] text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-[14px]">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 pl-14 pr-6 font-bold text-slate-900">Buổi 24 - Ôn tập cuối kỳ</td>
                                <td class="py-4 px-6 text-slate-600 font-medium">Hôm nay, 08:30</td>
                                <td class="py-4 px-6 font-medium"><span class="text-emerald-600 font-bold">57</span>/60</td>
                                <td class="py-4 px-6">
                                    <span class="bg-blue-100 text-blue-700 text-[11px] font-bold px-2.5 py-1 rounded-md uppercase">Đang mở</span>
                                </td>
                                <td class="py-4 pr-14 pl-6 text-right">
                                    <div class="relative flex justify-end" x-data="{ 
                                        open: false, top: 0, left: 0,
                                        toggle() {
                                            if(this.open) { this.open = false; return; }
                                            const rect = this.$refs.btn.getBoundingClientRect();
                                            this.top = rect.bottom + 8;
                                            this.left = rect.right - 176;
                                            this.open = true;
                                        }
                                    }" x-init="$nextTick(() => { document.body.appendChild($refs.menu); })" @scroll.window="open = false" @resize.window="open = false">
                                        <button x-ref="btn" @click="toggle()" @click.away="open = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                        </button>

                                        <!-- Dropdown menu -->
                                        <div x-ref="menu" x-show="open" x-transition.opacity.duration.200ms @click.away="open = false"
                                             class="fixed w-44 bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-100 py-2 z-[9999]" 
                                             :style="`top: ${top}px; left: ${left}px; display: none;`">
                                                <button class="w-full flex items-center gap-3 px-4 py-2.5 text-[14px] text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors font-medium whitespace-nowrap text-left">
                                                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    Xem chi tiết
                                                </button>
                                                <button class="w-full flex items-center gap-3 px-4 py-2.5 text-[14px] text-slate-700 hover:bg-red-50 hover:text-red-600 transition-colors font-medium whitespace-nowrap text-left">
                                                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Xóa phiên
                                                </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 pl-14 pr-6 font-bold text-slate-900">Buổi 23 - Thuật toán đồ thị</td>
                                <td class="py-4 px-6 text-slate-600 font-medium">15/10/2024, 08:30</td>
                                <td class="py-4 px-6 font-medium"><span class="text-emerald-600 font-bold">59</span>/60</td>
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100 text-slate-600 text-[11px] font-bold px-2.5 py-1 rounded-md uppercase">Đã đóng</span>
                                </td>
                                <td class="py-4 pr-14 pl-6 text-right">
                                    <div class="relative flex justify-end" x-data="{ open: false }">
                                        <button @click="open = !open" @click.away="open = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                        </button>

                                        <!-- Dropdown menu -->
                                        <div x-show="open" x-transition.opacity.duration.200ms
                                             class="absolute right-0 top-full mt-2 w-44 bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-100 py-2 z-10" style="display: none;">
                                            <button class="w-full flex items-center gap-3 px-4 py-2.5 text-[14px] text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors font-medium whitespace-nowrap text-left">
                                                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                Xem chi tiết
                                            </button>
                                            <button class="w-full flex items-center gap-3 px-4 py-2.5 text-[14px] text-slate-700 hover:bg-red-50 hover:text-red-600 transition-colors font-medium whitespace-nowrap text-left">
                                                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Xóa phiên
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content: Cài đặt -->
        <div x-cloak x-show="activeTab === 'settings'" class="space-y-6">
            <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-[20px] font-extrabold text-slate-900 tracking-tight">Cài đặt lớp học</h2>
                        <p class="text-[13px] font-medium text-slate-500 mt-1">Quản lý thông tin và cấu hình điểm danh của lớp</p>
                    </div>
                    <button class="flex items-center gap-2 bg-blue-600 text-white text-[14px] font-bold px-5 py-2.5 rounded-xl hover:bg-blue-700 transition-all shadow-sm">
                        Lưu thay đổi
                    </button>
                </div>
                <div class="p-6 md:p-8 space-y-8">
                    <!-- Hàng 1: Mã lớp & Thông tin cơ bản -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Cột trái: Mã lớp học -->
                        <div class="lg:col-span-1">
                            <div class="bg-slate-50/50 rounded-[20px] border border-slate-200 p-6 text-center h-full flex flex-col justify-center">
                                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                </div>
                                <h3 class="text-[14px] font-bold text-slate-500 uppercase tracking-wider mb-2">MÃ THAM GIA LỚP</h3>
                                <div class="flex items-center justify-center gap-3 bg-white border border-slate-200 rounded-xl py-3 px-4 shadow-sm mb-4">
                                    <span class="text-[24px] font-black text-slate-900 tracking-[0.2em]">IT4012</span>
                                    <button class="text-slate-400 hover:text-blue-600 transition-colors" title="Sao chép mã">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    </button>
                                </div>
                                <p class="text-[13px] text-slate-500 leading-relaxed mb-4">Sinh viên sử dụng mã này để xin gia nhập vào lớp. Bạn có thể thay đổi mã hoặc khóa tính năng tham gia bằng mã.</p>
                                <button class="mt-auto w-full flex justify-center items-center gap-2 py-2.5 bg-white border border-slate-200 text-[14px] font-bold text-slate-700 rounded-xl hover:bg-slate-50 hover:text-blue-600 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Tạo mã mới
                                </button>
                            </div>
                        </div>

                        <!-- Cột phải: Thông tin chung -->
                        <div class="lg:col-span-2">
                            <div>
                                <h3 class="text-[16px] font-bold text-slate-900 mb-5 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Thông tin cơ bản
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div class="sm:col-span-2">
                                        <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Tên lớp học <span class="text-red-500">*</span></label>
                                        <input type="text" value="Thiết kế và Đánh giá thuật toán" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Mã môn học <span class="text-red-500">*</span></label>
                                        <input type="text" value="COMP4012" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Học kỳ <span class="text-red-500">*</span></label>
                                        <select class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-medium appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E')] bg-no-repeat bg-[position:right_1rem_center] bg-[length:1.2em_1.2em]">
                                            <option>Học kỳ 1 (2024/2025)</option>
                                            <option>Học kỳ 2 (2023/2024)</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Mô tả thêm</label>
                                        <textarea rows="3" placeholder="Nhập mô tả về lớp học..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all resize-none"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <!-- Hàng 2: Cấu hình điểm danh + Toggles tích hợp -->
                    <div>
                        <h3 class="text-[16px] font-bold text-slate-900 mb-5 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            Cấu hình điểm danh
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            {{-- Hàng 1 --}}
                            <div>
                                <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Tổng số buổi dự kiến</label>
                                <div class="relative">
                                    <input type="number" value="30" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl pl-4 pr-14 py-3 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-medium [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-900 text-[13px] font-bold pointer-events-none">buổi</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Tổng số tiết</label>
                                <div class="relative">
                                    <input type="number" value="3" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl pl-4 pr-14 py-3 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-medium [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-900 text-[13px] font-bold pointer-events-none">tiết</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Yêu cầu duyệt</label>
                                <div
                                    class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 h-[50px]"
                                    x-data="{ requireApproval: true }"
                                >
                                    <span class="text-[13px] text-slate-500">Cần duyệt khi xin vào lớp</span>
                                    <button
                                        type="button"
                                        @click="requireApproval = !requireApproval"
                                        class="relative ml-3 shrink-0 focus:outline-none"
                                        title="Bật/Tắt yêu cầu duyệt"
                                    >
                                        <div class="w-11 h-6 rounded-full transition-colors duration-300" :class="requireApproval ? 'bg-blue-600' : 'bg-slate-200'"></div>
                                        <div class="absolute top-[2px] left-[2px] w-5 h-5 bg-white border border-slate-300 rounded-full shadow-sm transition-transform duration-300" :class="requireApproval ? 'translate-x-5 border-white' : 'translate-x-0'"></div>
                                    </button>
                                </div>
                            </div>

                            {{-- Hàng 2 --}}
                            <div>
                                <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Bán kính quét GPS</label>
                                <div class="relative">
                                    <input type="number" value="100" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl pl-4 pr-14 py-3 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-medium [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-900 text-[13px] font-bold pointer-events-none">mét</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Ngưỡng cảnh báo vắng</label>
                                <div class="relative">
                                    <input type="number" value="20" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl pl-4 pr-14 py-3 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all font-medium [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-900 text-[13px] font-bold pointer-events-none">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Trạng thái lớp</label>
                                <div 
                                    class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 h-[50px]"
                                    x-data="{ isActive: true }"
                                >
                                    <span class="text-[13px] text-slate-500" x-text="isActive ? 'Đang hoạt động' : 'Đã lưu trữ'"></span>
                                    <button
                                        type="button"
                                        @click="isActive = !isActive"
                                        class="relative ml-3 shrink-0 focus:outline-none"
                                        title="Bật/Tắt trạng thái lớp"
                                    >
                                        <div class="w-11 h-6 rounded-full transition-colors duration-300" :class="isActive ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                                        <div class="absolute top-[2px] left-[2px] w-5 h-5 bg-white border border-slate-300 shadow-sm rounded-full transition-transform duration-300" :class="isActive ? 'translate-x-5 border-white' : 'translate-x-0'"></div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Popups -->
    @include('lecture.popup.import_students')
    @include('lecture.popup.share_class')

</x-app-layout>
