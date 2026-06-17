<x-app-layout variant="student" pageTitle="Chi tiết môn học">
    <div class="hidden lg:block w-full h-full p-6 lg:p-8 max-w-7xl mx-auto">
        <div class="space-y-6 animate-in fade-in duration-300">
            <div class="flex items-center justify-between">
                <a href="{{ route('student.stats') }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border-none rounded-xl px-4 py-2 font-bold text-xs cursor-pointer select-none transition-all no-underline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left w-4 h-4 text-slate-500" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg> Trở lại thống kê chuyên cần
                </a>
                <span class="text-[10.5px] font-mono text-slate-400 font-semibold select-none uppercase tracking-wider">Học bạ mã lớp: {{ $classData['code'] }}</span>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
                        <div class="border-b border-slate-105 pb-3">
                            <span class="bg-blue-50 text-blue-700 text-[9px] font-bold px-2 py-0.5 rounded uppercase leading-none font-mono">THÔNG TIN LỚP HỌC</span>
                            <h3 class="text-base font-extrabold text-slate-900 leading-snug mt-2">{{ $classData['code'] }}: {{ $classData['name'] }}</h3>
                            <p class="text-xs text-slate-405 font-medium">Mã học phần đào tạo SAMS chính quy</p>
                        </div>
                        <div class="space-y-3 text-xs font-semibold text-slate-600 leading-relaxed">
                            <div class="flex justify-between border-b border-slate-50 pb-1.5"><span class="text-slate-405 font-medium">Mã lớp môn học:</span><span class="font-mono text-slate-800 font-bold">{{ $classData['class_code'] }}</span></div>
                            <div class="flex justify-between border-b border-slate-50 pb-1.5"><span class="text-slate-405 font-medium">Môn học đào tạo:</span><span class="text-slate-800 font-bold truncate max-w-[150px]" title="{{ $classData['name'] }}">{{ $classData['name'] }}</span></div>
                            <div class="flex justify-between border-b border-slate-50 pb-1.5"><span class="text-slate-405 font-medium">Số lượng tín chỉ:</span><span class="text-slate-800 font-black font-mono">{{ $classData['credits'] }} Tín chỉ (TC)</span></div>
                            <div class="flex justify-between"><span class="text-slate-405 font-medium">Địa điểm giảng đường:</span><span class="bg-blue-50/50 text-blue-700 font-black px-1.5 rounded text-[11px] font-mono">{{ $classData['room'] }}</span></div>
                        </div>
                    </div>
                    
                    <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
                        <div class="flex items-center gap-3 border-b border-slate-105 pb-3">
                            <div class="w-10 h-10 rounded-full bg-slate-105 overflow-hidden border border-slate-200 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user w-5.5 h-5.5 text-slate-405" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div>
                                <span class="bg-slate-100 text-slate-600 text-[8.5px] font-black px-1.5 py-0.5 rounded block uppercase tracking-wider w-fit leading-none">GIẢNG VIÊN</span>
                                <h4 class="text-xs font-bold text-slate-850 mt-1">{{ $classData['teacher'] }}</h4>
                            </div>
                        </div>
                        <div class="space-y-2 text-xs font-semibold text-slate-650 leading-snug">
                            <p class="text-slate-500 font-medium">Trường Cao đẳng Kỹ thuật Cao Thắng. Giảng dạy chính và chịu trách nhiệm hậu kiểm bạ chuyên cần của lớp học.</p>
                            <div class="pt-2 flex items-center gap-1 text-[11px] text-blue-600 hover:underline cursor-pointer">
                                <span>📧 hotro.giangvien@caothang.edu.vn</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
                        <div>
                            <span class="bg-indigo-50 text-indigo-700 text-[9px] font-bold px-2 py-0.5 rounded uppercase leading-none font-mono">LỊCH HỌC HỆ THỐNG</span>
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider block select-none mt-2">Thời khoá biểu và Tiết học</h4>
                        </div>
                        <div class="p-3.5 bg-slate-50 border border-slate-200/50 rounded-2xl text-xs space-y-2.5 font-semibold text-slate-655">
                            <div class="flex justify-between border-b border-b-slate-100 pb-1.5"><span class="text-slate-405 font-medium">Khung thời gian biểu:</span><span class="text-slate-800 font-bold font-mono">{{ $classData['schedule'] }}</span></div>
                            <div class="flex justify-between border-b border-b-slate-100 pb-1.5"><span class="text-slate-405 font-medium">Thời gian mở cổng:</span><span class="text-slate-800 font-mono font-bold">{{ $classData['time_window'] }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-455 font-medium">Học kỳ đào tạo:</span><span class="text-slate-800 font-bold">{{ $classData['semester'] }}</span></div>
                        </div>
                    </div>
                </div>
                
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-4 gap-4 mb-5">
                            <div>
                                <span class="bg-indigo-50 text-indigo-700 text-[9px] font-bold px-2 py-0.5 rounded uppercase leading-none font-mono">TÍCH LUỸ</span>
                                <h3 class="text-base font-black text-slate-900 uppercase mt-1">Tỷ lệ Chuyên cần Môn học</h3>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold uppercase {{ $mockStats['attendance_rate'] >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $mockStats['status_label'] }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                            <div class="md:col-span-4 flex flex-col items-center justify-center">
                                <div class="relative w-32 h-32 flex items-center justify-center select-none">
                                    <svg class="w-full h-full rotate-[-90deg]">
                                        <circle cx="64" cy="64" r="50" fill="transparent" stroke="#f1f5f9" stroke-width="10"></circle>
                                        <circle cx="64" cy="64" r="50" fill="transparent" stroke="{{ $mockStats['attendance_rate'] >= 80 ? '#2563eb' : '#dc2626' }}" stroke-width="10" stroke-dasharray="314.16" stroke-dashoffset="{{ 314.16 - (314.16 * $mockStats['attendance_rate'] / 100) }}" class="transition-all duration-500"></circle>
                                    </svg>
                                    <div class="absolute inset-x-0 inset-y-0 flex flex-col justify-center items-center">
                                        <span class="text-xl font-bold font-mono leading-none {{ $mockStats['attendance_rate'] >= 80 ? 'text-blue-600' : 'text-red-600' }}">{{ $mockStats['attendance_rate'] }}%</span>
                                        <span class="text-[8.5px] font-bold uppercase text-slate-400 mt-1 tracking-wider">HIỆN DIỆN</span>
                                    </div>
                                </div>
                            </div>
                            <div class="md:col-span-8 grid grid-cols-2 gap-4 text-center font-semibold text-slate-655 text-xs font-sans">
                                <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-2xl"><span class="text-[9px] text-slate-400 font-extrabold uppercase block select-none">Tổng số tiết</span><strong class="text-slate-800 text-lg font-black font-mono block mt-1">{{ $mockStats['total_sessions'] }}</strong><span class="text-[8.5px] text-slate-400 block mt-0.5">Tiết học phần</span></div>
                                <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-2xl"><span class="text-[9px] text-slate-400 font-extrabold uppercase block select-none">Có mặt</span><strong class="text-emerald-700 text-lg font-black font-mono block mt-1">{{ $mockStats['present'] }}</strong><span class="text-[8.5px] text-slate-400 block mt-0.5">Tiết học</span></div>
                                <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-2xl"><span class="text-[9px] text-slate-400 font-extrabold uppercase block select-none">Đi trễ</span><strong class="text-amber-600 text-lg font-black font-mono block mt-1">{{ $mockStats['late'] }}</strong><span class="text-[8.5px] text-slate-400 block mt-0.5">Buổi học</span></div>
                                <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-2xl"><span class="text-[9px] text-slate-400 font-extrabold uppercase block select-none">Vắng không phép</span><strong class="text-rose-650 text-lg font-black font-mono block mt-1">{{ $mockStats['absent'] }}</strong><span class="text-[8.5px] text-slate-405 block mt-0.5">Buổi học</span></div>
                            </div>
                        </div>
                        <div class="p-4 bg-amber-50/40 border border-amber-100 rounded-2xl text-[11px] text-slate-655 mt-5 leading-relaxed font-medium">⚠️ <strong class="text-amber-800">Cơ chế quản lý vắng học:</strong> Quy chế trường CĐKT Cao Thắng bắt buộc chuyên cần lý lý lý thuyết phải tối thiểu đạt <strong>80%</strong> để dự thi học kì. Các buổi xin vắng phép được ghi danh bổ sung nếu hồ sơ nộp chứng thực được duyệt thành công.</div>
                    </div>
                    
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                        <div class="border-b border-slate-100 pb-3 mb-4">
                            <span class="bg-blue-50 text-blue-700 text-[9px] font-bold px-2 py-0.5 rounded uppercase leading-none font-mono">DANH MỤC THỜI GIAN</span>
                            <h3 class="text-base font-black text-slate-900 uppercase mt-1">Danh sách các buổi điểm danh (Tổng số {{ $mockStats['total_sessions'] }} tiết)</h3>
                        </div>
                        <div class="space-y-3.5 max-h-[420px] overflow-y-auto pr-2">
                            @foreach ($mockSessions as $session)
                                <div class="flex items-center justify-between p-3.5 bg-slate-50 hover:bg-slate-105 border border-slate-100 rounded-2xl transition-colors font-semibold text-xs text-slate-655 font-sans">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8.5 h-8.5 rounded-full flex items-center justify-center font-bold text-[10px] font-mono border shrink-0 bg-{{ $session['color'] }}-50 border-{{ $session['color'] }}-100 text-{{ $session['color'] === 'rose' ? 'red' : ($session['color'] === 'emerald' ? 'emerald' : ($session['color'] === 'amber' ? 'amber' : 'blue')) }}-600">#{{ $session['id'] }}</div>
                                        <div><h4 class="font-extrabold text-slate-900 leading-none">Buổi thứ {{ $session['id'] }}: {{ $session['date'] }}</h4><p class="text-[10px] text-slate-450 font-semibold block mt-1.5 leading-none">{{ $session['note'] }}</p></div>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-[10px] font-mono text-slate-400 font-extrabold">{{ $session['time'] }}</span>
                                        <span class="bg-{{ $session['color'] }}-50 text-{{ $session['color'] === 'rose' ? 'red' : ($session['color'] === 'emerald' ? 'emerald' : ($session['color'] === 'amber' ? 'amber' : 'blue')) }}-700 border border-{{ $session['color'] }}-100 text-[9px] font-black px-2 py-0.5 rounded-xl uppercase leading-none">
                                            {{ $session['status'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- MOBILE DETAIL --}}
    <div class="lg:hidden w-full h-full font-sans pb-24" x-data="{ activeTab: 'info' }">
        <div class="p-4 space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
            <div class="space-y-3.5 animate-in fade-in duration-200">
                <a href="{{ route('student.stats') }}" class="inline-flex items-center gap-1 text-slate-550 hover:text-slate-800 font-bold text-xs cursor-pointer border-none bg-transparent no-underline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left w-4 h-4 text-slate-400 shrink-0" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg>Thống kê chuyên cần
                </a>
                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs space-y-1">
                    <span class="text-[8px] font-bold font-mono tracking-widest text-slate-400">{{ $classData['code'] }}</span>
                    <h2 class="text-sm font-black text-slate-900 leading-tight">{{ $classData['code'] }}: {{ $classData['name'] }}</h2>
                    <span class="text-[10px] text-slate-400 font-bold leading-none block">GV phụ trách: {{ $classData['teacher'] }}</span>
                </div>
                
                <div class="flex border-b border-slate-150 text-center">
                    <button @click="activeTab = 'info'" :class="activeTab === 'info' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-400'" class="flex-1 py-1.5 text-xs font-black border-b-2 cursor-pointer transition-all">Thông tin học</button>
                    <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-400'" class="flex-1 py-1.5 text-xs font-black border-b-2 cursor-pointer transition-all">Lịch sử</button>
                    <button @click="activeTab = 'stats'" :class="activeTab === 'stats' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-400'" class="flex-1 py-1.5 text-xs font-black border-b-2 cursor-pointer transition-all">Thống kê</button>
                </div>
                
                <div x-show="activeTab === 'info'" class="bg-white rounded-xl p-4 border border-slate-100 text-[11px] text-slate-655 space-y-3 font-semibold">
                    <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-400">Tên môn học:</span><span class="text-slate-800 font-bold">{{ $classData['name'] }}</span></div>
                    <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-400">Giảng viên:</span><span class="text-slate-800 font-bold">{{ $classData['teacher'] }}</span></div>
                    <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-400">Lịch học:</span><span class="text-slate-800 font-mono text-[10px]">{{ $classData['schedule'] }}</span></div>
                    <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-400">Số tín chỉ:</span><span class="text-slate-800 font-black">{{ $classData['credits'] }} tín chỉ</span></div>
                    <div class="flex justify-between pb-1"><span class="text-slate-400">Giảng đường:</span><span class="text-blue-600 font-black bg-blue-50/50 rounded px-1">{{ $classData['room'] }}</span></div>
                </div>
                
                <div x-show="activeTab === 'history'" class="space-y-3" style="display: none;">
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                        <h3 class="text-xs font-black text-slate-900 uppercase mb-3">Danh sách các buổi điểm danh</h3>
                        <div class="space-y-3 max-h-[300px] overflow-y-auto">
                            @foreach ($mockSessions as $session)
                                <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-100 rounded-xl">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-[9px] font-mono border bg-{{ $session['color'] }}-50 border-{{ $session['color'] }}-100 text-{{ $session['color'] === 'rose' ? 'red' : ($session['color'] === 'emerald' ? 'emerald' : ($session['color'] === 'amber' ? 'amber' : 'blue')) }}-650">#{{ $session['id'] }}</div>
                                        <div><h4 class="font-bold text-slate-900 text-[11px]">{{ $session['date'] }}</h4></div>
                                    </div>
                                    <span class="bg-{{ $session['color'] }}-50 text-{{ $session['color'] === 'rose' ? 'red' : ($session['color'] === 'emerald' ? 'emerald' : ($session['color'] === 'amber' ? 'amber' : 'blue')) }}-750 border border-{{ $session['color'] }}-150 text-[9px] font-black px-2 py-0.5 rounded-lg">
                                        {{ $session['status'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div x-show="activeTab === 'stats'" class="space-y-4" style="display: none;">
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                        <h3 class="text-xs font-black text-slate-900 uppercase mb-3">Tỷ lệ Chuyên cần</h3>
                        <div class="flex flex-col items-center justify-center mb-4">
                            <div class="relative w-24 h-24 flex items-center justify-center">
                                <svg class="w-full h-full rotate-[-90deg]">
                                    <circle cx="48" cy="48" r="40" fill="transparent" stroke="#f1f5f9" stroke-width="8"></circle>
                                    <circle cx="48" cy="48" r="40" fill="transparent" stroke="{{ $mockStats['attendance_rate'] >= 80 ? '#2563eb' : '#dc2626' }}" stroke-width="8" stroke-dasharray="251.2" stroke-dashoffset="{{ 251.2 - (251.2 * $mockStats['attendance_rate'] / 100) }}" class="transition-all duration-500"></circle>
                                </svg>
                                <div class="absolute inset-x-0 inset-y-0 flex flex-col justify-center items-center">
                                    <span class="text-lg font-bold font-mono leading-none {{ $mockStats['attendance_rate'] >= 80 ? 'text-blue-600' : 'text-red-650' }}">{{ $mockStats['attendance_rate'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-center text-[10px]">
                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <span class="text-slate-400 font-bold uppercase block">Tổng tiết</span><strong class="text-slate-800 text-sm">{{ $mockStats['total_sessions'] }}</strong>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <span class="text-slate-400 font-bold uppercase block">Có mặt</span><strong class="text-emerald-700 text-sm">{{ $mockStats['present'] }}</strong>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <span class="text-slate-400 font-bold uppercase block">Đi trễ</span><strong class="text-amber-600 text-sm">{{ $mockStats['late'] }}</strong>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <span class="text-slate-400 font-bold uppercase block">Vắng</span><strong class="text-rose-650 text-sm">{{ $mockStats['absent'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
