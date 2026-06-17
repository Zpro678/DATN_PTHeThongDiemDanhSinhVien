<div id="importStudentModal" class="relative z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm animate-fade-in"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 animate-pop-in">
                
                <div class="bg-white px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900" id="modal-title">Import danh sách sinh viên</h3>
                    <button onclick="document.getElementById('importStudentModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition bg-slate-50 hover:bg-red-50 rounded-full p-1.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form action="#" method="POST" enctype="multipart/form-data">
                    <div class="px-6 py-5 space-y-5">
                        <!-- Alert Info -->
                        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 flex gap-3">
                            <div class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-blue-900 mb-1">Hướng dẫn Import</h4>
                                <p class="text-xs text-blue-700 leading-relaxed mb-2">Vui lòng tải file mẫu về, điền thông tin sinh viên theo đúng định dạng các cột và tải lên lại hệ thống. Chỉ chấp nhận file <b>.xlsx, .csv</b>.</p>
                                <a href="#" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Tải file mẫu
                                </a>
                            </div>
                        </div>

                        <!-- File Input Drag & Drop -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Chọn file từ máy tính <span class="text-red-500">*</span></label>
                            <label class="flex justify-center w-full h-32 px-4 transition bg-white border-2 border-slate-300 border-dashed rounded-xl appearance-none cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 focus:outline-none group">
                                <span class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <span class="font-medium text-slate-500 group-hover:text-blue-600 transition-colors">Kéo thả file hoặc <span class="text-blue-600 underline">chọn file</span></span>
                                </span>
                                <input type="file" name="file" class="hidden" accept=".xlsx, .xls, .csv">
                            </label>
                            <p class="text-[11px] text-slate-400 mt-2 text-center">Dung lượng tối đa: 5MB</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-2xl">
                        <button onclick="document.getElementById('importStudentModal').classList.add('hidden')" type="button" class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition shadow-sm">
                            Hủy
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition shadow-[0_2px_10px_rgb(37,99,235,0.3)] flex justify-center items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Tải file lên
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
