<x-app-layout variant="student" pageTitle="Thông báo">
    <div class="p-6 lg:p-8 space-y-6 animate-in fade-in duration-300">
        <div class="flex items-center gap-2">
            <x-sams.icon name="bell" class="w-6 h-6 text-blue-600 shrink-0" />
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Thông báo</h1>
        </div>
        <p class="text-xs text-slate-500 font-semibold -mt-4">Các thông báo từ giảng viên và hệ thống</p>

        {{-- TODO: Bổ sung danh sách thông báo từ bảng notifications --}}
        <div class="rounded-2xl border border-dashed border-slate-200 p-12 text-center bg-white">
            <x-sams.icon name="bell" class="w-10 h-10 text-slate-200 mx-auto mb-3" />
            <p class="text-sm font-bold text-slate-400">Chưa có thông báo nào</p>
        </div>
    </div>
</x-app-layout>
