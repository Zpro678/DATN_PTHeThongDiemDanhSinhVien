<div>
    {{-- Toggle: Yêu cầu duyệt --}}
    <div>
        <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Yêu cầu duyệt</label>
        <div
            class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 h-[50px] transition-colors"
            x-data="{ loading: false }"
        >
            <span class="text-[13px] text-slate-500">Cần duyệt khi xin vào lớp</span>

            <button
                wire:click="toggleRequireApproval"
                wire:loading.attr="disabled"
                x-on:click="loading = true"
                wire:loading.class.remove="opacity-100"
                wire:finished.class.remove="opacity-50"
                class="relative ml-3 shrink-0 focus:outline-none"
                type="button"
                title="{{ $requireApproval ? 'Tắt yêu cầu duyệt' : 'Bật yêu cầu duyệt' }}"
            >
                {{-- Track --}}
                <div
                    class="w-11 h-6 rounded-full transition-colors duration-300 {{ $requireApproval ? 'bg-blue-600' : 'bg-slate-200' }}"
                ></div>
                {{-- Thumb --}}
                <div
                    class="absolute top-[2px] left-[2px] w-5 h-5 bg-white border border-slate-300 rounded-full shadow-sm transition-transform duration-300 {{ $requireApproval ? 'translate-x-5 border-white' : 'translate-x-0' }}"
                ></div>
                {{-- Loading spinner --}}
                <div wire:loading wire:target="toggleRequireApproval" class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-4 h-4 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </button>
        </div>
    </div>

    {{-- Toggle: Trạng thái lớp --}}
    <div>
        <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Trạng thái lớp</label>
        <div
            class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 h-[50px] transition-colors"
        >
            <span class="text-[13px] text-slate-500">
                {{ $isActive ? 'Đang hoạt động' : 'Đã lưu trữ' }}
            </span>

            <button
                wire:click="toggleStatus"
                wire:loading.attr="disabled"
                class="relative ml-3 shrink-0 focus:outline-none"
                type="button"
                title="{{ $isActive ? 'Lưu trữ lớp' : 'Kích hoạt lớp' }}"
            >
                {{-- Track --}}
                <div
                    class="w-11 h-6 rounded-full transition-colors duration-300 {{ $isActive ? 'bg-emerald-500' : 'bg-slate-200' }}"
                ></div>
                {{-- Thumb --}}
                <div
                    class="absolute top-[2px] left-[2px] w-5 h-5 bg-white border border-slate-300 rounded-full shadow-sm transition-transform duration-300 {{ $isActive ? 'translate-x-5 border-white' : 'translate-x-0' }}"
                ></div>
                {{-- Loading spinner --}}
                <div wire:loading wire:target="toggleStatus" class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-4 h-4 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </button>
        </div>
    </div>
</div>
