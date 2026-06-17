<div id="shareClassModal" class="relative z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm animate-fade-in"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl sm:my-8 sm:w-full sm:max-w-md border border-slate-200 animate-pop-in">
                
                <div class="bg-white px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900" id="modal-title">Chia sẻ lớp học</h3>
                    <button onclick="document.getElementById('shareClassModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition bg-slate-50 hover:bg-red-50 rounded-full p-1.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-6">
                    <!-- Class Code -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Mã lớp học</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 flex items-center justify-between">
                                <span class="font-mono text-lg font-bold tracking-widest text-blue-700" id="classCodeText">X7B9QA</span>
                            </div>
                            <button onclick="navigator.clipboard.writeText(document.getElementById('classCodeText').innerText); alert('Đã sao chép mã lớp!')" class="shrink-0 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 font-bold px-4 py-3 rounded-xl transition-colors border border-blue-100 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                Copy
                            </button>
                        </div>
                        <p class="text-[12px] text-slate-500 mt-2">Sinh viên có thể sử dụng mã này để tự tham gia vào lớp học.</p>
                    </div>

                    <!-- Invite Link -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Đường liên kết tham gia</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 overflow-hidden">
                                <p class="text-sm text-slate-600 truncate" id="classLinkText">http://127.0.0.1:8000/student/join/X7B9QA</p>
                            </div>
                            <button onclick="navigator.clipboard.writeText(document.getElementById('classLinkText').innerText); alert('Đã sao chép đường liên kết!')" class="shrink-0 bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800 font-bold px-4 py-3 rounded-xl transition-colors border border-slate-200 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                Copy
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex justify-end rounded-b-2xl">
                    <button onclick="document.getElementById('shareClassModal').classList.add('hidden')" type="button" class="px-6 py-2.5 text-sm font-bold text-white bg-slate-800 hover:bg-slate-900 rounded-xl transition shadow-sm">
                        Đóng
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
