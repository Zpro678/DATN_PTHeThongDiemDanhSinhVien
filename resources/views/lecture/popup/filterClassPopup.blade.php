<style>
    @keyframes fade-in {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }
    @keyframes pop-in {
        0% { opacity: 0; transform: scale(0.95) translateY(10px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    .animate-fade-in { animation: fade-in 0.2s ease-out forwards; }
    .animate-pop-in { animation: pop-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
</style>

<div id="filterModalClass" class="relative z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">

    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm animate-fade-in"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">

            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 animate-pop-in">

                <div class="bg-white px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900" id="modal-title">Lọc danh sách lớp học</h3>
                    <button onclick="document.getElementById('filterModalClass').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition bg-slate-50 hover:bg-red-50 rounded-full p-1.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Học kỳ</label>
                            <select class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] hover:bg-slate-100 transition cursor-pointer">
                                <option>Tất cả học kỳ</option>
                                <option>Học kỳ 1</option>
                                <option>Học kỳ 2</option>
                                <option>Học kỳ 3</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Năm học</label>
                            <select class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] hover:bg-slate-100 transition cursor-pointer">
                                <option>Tất cả năm học</option>
                                <option>2023 - 2024</option>
                                <option selected>2024 - 2025</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Môn học</label>
                        <select class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb] hover:bg-slate-100 transition cursor-pointer">
                            <option>Tất cả môn học</option>
                            <option>Advanced Neural Networks</option>
                            <option>Data Structures</option>
                            <option>Machine Learning</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Trạng thái</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="status" class="w-4 h-4 text-[#2563eb] border-slate-300 focus:ring-[#2563eb]" checked>
                                <span class="text-sm font-medium text-slate-700">Tất cả</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="status" class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                <span class="text-sm font-medium text-slate-700">Đang diễn ra</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="status" class="w-4 h-4 text-amber-500 border-slate-300 focus:ring-amber-500">
                                <span class="text-sm font-medium text-slate-700">Sắp diễn ra</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="status" class="w-4 h-4 text-slate-500 border-slate-300 focus:ring-slate-500">
                                <span class="text-sm font-medium text-slate-700">Đã kết thúc</span>
                            </label>
                        </div>
                    </div>

                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <button onclick="document.getElementById('filterModalClass').classList.add('hidden')" type="button" class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition shadow-sm">
                        Hủy
                    </button>
                    <button onclick="document.getElementById('filterModalClass').classList.add('hidden')" type="button" class="w-full sm:w-auto px-5 py-2.5 bg-[#2563eb] text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition shadow-sm shadow-blue-500/30 flex justify-center items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Áp dụng bộ lọc
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>