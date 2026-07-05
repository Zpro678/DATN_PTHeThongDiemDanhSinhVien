<div wire:poll.3s>
    <a href="{{ route('lecturer.classes.pending-members', $classId) }}" wire:navigate class="relative inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-600 transition-colors hover:bg-blue-100">
        <x-user.icon name="user-check" :size="16" />
        Duyệt học viên
        @if($pendingCount > 0)
            <span class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white animate-in zoom-in duration-300">
                {{ $pendingCount }}
            </span>
        @endif
    </a>
</div>
