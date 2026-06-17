<x-app-layout variant="lecturer" pageTitle="Thêm sinh viên vào lớp">
    <div class="hidden lg:block w-full h-full p-6 lg:p-8 max-w-4xl mx-auto">
        <div class="space-y-6 animate-in fade-in duration-300">
            <div class="flex items-center justify-between">
                <a href="{{ route('classes.members.index', $class) }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border-none rounded-xl px-4 py-2 font-bold text-xs cursor-pointer select-none transition-all no-underline">
                     Trở lại danh sách
                </a>
                <span class="text-[10.5px] font-mono text-slate-400 font-semibold select-none uppercase tracking-wider">Mã lớp: {{ $class->code }}</span>
            </div>

            <div class="bg-white border border-slate-200 rounded-3xl p-6 lg:p-8 shadow-sm">
                <div class="border-b border-slate-100 pb-5 mb-6">
                    <div class="flex items-center gap-3">
                        
                        <div>
                            <span class="bg-blue-50 text-blue-700 text-[9px] font-bold px-2 py-0.5 rounded uppercase leading-none font-mono">THÔNG TIN SINH VIÊN</span>
                            <h2 class="text-xl font-black text-slate-900 tracking-tight mt-1">Thêm Sinh Viên Mới</h2>
                            <p class="text-xs text-slate-500 font-medium mt-1">Điền thông tin định danh để đăng ký học bạ cho sinh viên vào môn học SAMS.</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('classes.members.store', $class) }}" method="POST" class="space-y-6">
                    @csrf

                    @if($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-xs font-semibold">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="student_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Mã Số Sinh Viên (MSSV) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <x-sams.icon name="hash" class="h-4 w-4 text-slate-400" />
                                </div>
                                <input type="text" name="student_code" id="student_code" value="{{ old('student_code') }}" required placeholder="VD: 0306231108" class="pl-10 w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder:text-slate-400 placeholder:font-medium outline-none">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="full_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Họ và Tên <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <x-sams.icon name="user" class="h-4 w-4 text-slate-400" />
                                </div>
                                <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required placeholder="VD: Trần Minh Hoàng" class="pl-10 w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder:text-slate-400 placeholder:font-medium outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Trạng thái học vụ</label>
                        <select name="status" id="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all outline-none appearance-none cursor-pointer">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>🟢 Đang học (Active)</option>
                            <option value="dropped" {{ old('status') == 'dropped' ? 'selected' : '' }}>🔴 Bỏ học/Rút môn (Dropped)</option>
                        </select>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <a href="{{ route('classes.members.index', $class) }}" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 font-bold text-xs transition-colors no-underline flex items-center">Hủy bỏ</a>
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/30 transition-all active:scale-95 border-none cursor-pointer">
                            
                            Ghi danh sinh viên
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- MOBILE UI --}}
    <div class="lg:hidden w-full h-full font-sans pb-24">
        <div class="p-4 space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
            <a href="{{ route('classes.members.index', $class) }}" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-800 font-bold text-xs cursor-pointer border-none bg-transparent no-underline">
                <x-sams.icon name="chevron-left" class="w-4 h-4 text-slate-400 shrink-0" /> Quay lại
            </a>
            
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs space-y-5">
                <div>
                    <span class="text-[8px] font-bold font-mono tracking-widest text-blue-600 uppercase">{{ $class->code }}</span>
                    <h2 class="text-lg font-black text-slate-900 leading-tight">Thêm Sinh Viên</h2>
                </div>
                
                <form action="{{ route('classes.members.store', $class) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    @if($errors->any())
                    <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-[11px] font-semibold">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">Mã Số Sinh Viên *</label>
                        <input type="text" name="student_code" value="{{ old('student_code') }}" required placeholder="Nhập MSSV" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-xs font-semibold text-slate-800 focus:border-blue-500 outline-none">
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">Họ và Tên *</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Nhập họ tên" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-xs font-semibold text-slate-800 focus:border-blue-500 outline-none">
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">Trạng thái</label>
                        <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-xs font-semibold text-slate-800 focus:border-blue-500 outline-none appearance-none">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Đang học</option>
                            <option value="dropped" {{ old('status') == 'dropped' ? 'selected' : '' }}>Đã rút môn</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full mt-2 inline-flex justify-center items-center gap-2 px-4 py-3.5 rounded-xl bg-blue-600 active:bg-blue-700 text-white font-black text-xs shadow-md shadow-blue-500/20 transition-all border-none">
                        <x-sams.icon name="save" class="w-4 h-4" /> Xác nhận Thêm
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
