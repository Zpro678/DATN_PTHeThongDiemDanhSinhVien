<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Góp ý & Phản hồi</h1>
            <p class="text-sm text-slate-500">Gửi phản hồi, báo lỗi hoặc góp ý tính năng cho Ban quản trị.</p>
        </div>
        <button wire:click="openCreateModal" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
            <x-user.icon name="plus" :size="18" />
            Gửi phản hồi mới
        </button>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($feedbacks->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-user.icon name="message-square" :size="32" />
                </div>
                <h3 class="text-lg font-bold text-slate-700">Chưa có phản hồi nào</h3>
                <p class="mt-1 text-sm text-slate-500">Bạn chưa gửi phản hồi nào cho hệ thống.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($feedbacks as $feedback)
                    <div class="flex flex-col gap-4 rounded-xl border border-slate-200 p-5 transition-colors hover:bg-slate-50 sm:flex-row sm:items-start">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full 
                            {{ $feedback->type === 'bug' ? 'bg-red-50 text-red-500' : ($feedback->type === 'feature' ? 'bg-blue-50 text-blue-500' : 'bg-slate-100 text-slate-500') }}">
                            <x-user.icon name="{{ $feedback->type === 'bug' ? 'alert-circle' : ($feedback->type === 'feature' ? 'lightbulb' : 'message-circle') }}" :size="24" />
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="text-base font-bold text-slate-800">{{ $feedback->title }}</h4>
                                    <p class="text-xs text-slate-500">{{ $feedback->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $feedback->status === 'resolved' ? 'bg-green-100 text-green-700' : ($feedback->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $feedback->status === 'resolved' ? 'Đã xử lý' : ($feedback->status === 'in_progress' ? 'Đang xử lý' : 'Chờ xử lý') }}
                                    </span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-slate-600 whitespace-pre-wrap">{{ $feedback->content }}</p>
                            
                            @if($feedback->attachment_path)
                                <div class="mt-3">
                                    <a href="{{ asset('storage/' . $feedback->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:underline">
                                        <x-user.icon name="image" :size="16" />
                                        Xem ảnh đính kèm
                                    </a>
                                </div>
                            @endif

                            @if($feedback->admin_reply)
                                <div class="mt-4 rounded-lg bg-blue-50/50 p-4 border border-blue-100">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white">
                                            <x-user.icon name="check" :size="14" />
                                        </div>
                                        <span class="text-sm font-bold text-slate-800">Phản hồi từ Admin ({{ $feedback->replied_at?->format('d/m/Y H:i') }})</span>
                                    </div>
                                    <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $feedback->admin_reply }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-lg font-bold text-slate-800">Gửi phản hồi mới</h3>
                <button wire:click="closeCreateModal" class="rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                    <x-user.icon name="x" :size="20" />
                </button>
            </div>
            
            <form wire:submit="submit" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Loại phản hồi <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="type" value="bug" class="h-4 w-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                <span class="text-sm text-slate-700">Báo lỗi</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="type" value="feature" class="h-4 w-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                <span class="text-sm text-slate-700">Góp ý tính năng</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="type" value="other" class="h-4 w-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                <span class="text-sm text-slate-700">Khác</span>
                            </label>
                        </div>
                        @error('type') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Tiêu đề <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="title" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" placeholder="Nhập tiêu đề ngắn gọn...">
                        @error('title') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Nội dung chi tiết <span class="text-red-500">*</span></label>
                        <textarea wire:model="content" rows="4" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" placeholder="Mô tả chi tiết lỗi hoặc góp ý của bạn..."></textarea>
                        @error('content') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Ảnh đính kèm (Tùy chọn)</label>
                        <input type="file" wire:model="attachment" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                        @error('attachment') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                        
                        @if ($attachment)
                            <div class="mt-2 text-sm text-green-600">
                                Đã chọn ảnh: {{ $attachment->getClientOriginalName() }}
                            </div>
                        @endif
                        <div wire:loading wire:target="attachment" class="mt-2 text-sm text-blue-600">Đang tải ảnh lên...</div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" wire:click="closeCreateModal" class="rounded-full px-6 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                        <span wire:loading.remove wire:target="submit">Gửi phản hồi</span>
                        <span wire:loading wire:target="submit">Đang xử lý...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
