<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Quản lý phản hồi</h1>
            <p class="text-sm text-slate-500">Xem và xử lý các góp ý, báo lỗi từ người dùng hệ thống.</p>
        </div>
        <div class="w-48">
            <select wire:model.live="statusFilter" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Chờ xử lý</option>
                <option value="in_progress">Đang xử lý</option>
                <option value="resolved">Đã xử lý</option>
            </select>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-800 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Người gửi</th>
                        <th class="px-6 py-4 font-semibold">Phân loại</th>
                        <th class="px-6 py-4 font-semibold">Tiêu đề & Nội dung</th>
                        <th class="px-6 py-4 font-semibold">Trạng thái</th>
                        <th class="px-6 py-4 font-semibold">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($feedbacks as $feedback)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $feedback->user->name ?? 'Người dùng đã xóa' }}</div>
                                <div class="text-xs text-slate-500">{{ $feedback->user->email ?? '' }}</div>
                                <div class="text-xs text-slate-400 mt-1">{{ $feedback->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $feedback->type === 'bug' ? 'bg-red-100 text-red-700' : ($feedback->type === 'feature' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $feedback->type === 'bug' ? 'Báo lỗi' : ($feedback->type === 'feature' ? 'Góp ý tính năng' : 'Khác') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-normal min-w-[300px]">
                                <div class="font-semibold text-slate-800">{{ $feedback->title }}</div>
                                <p class="text-sm mt-1 line-clamp-2">{{ $feedback->content }}</p>
                                @if($feedback->attachment_path)
                                    <a href="{{ asset('storage/' . $feedback->attachment_path) }}" target="_blank" class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:underline">
                                        <x-user.icon name="image" :size="14" /> Xem đính kèm
                                    </a>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $feedback->status === 'resolved' ? 'bg-green-100 text-green-700' : ($feedback->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $feedback->status === 'resolved' ? 'Đã xử lý' : ($feedback->status === 'in_progress' ? 'Đang xử lý' : 'Chờ xử lý') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($feedback->status !== 'resolved')
                                    <div class="flex items-center gap-2">
                                        @if($feedback->status === 'pending')
                                            <button wire:click="markAsInProgress({{ $feedback->id }})" class="text-amber-600 hover:text-amber-800 font-medium text-sm transition-colors" title="Đánh dấu đang xử lý">
                                                Đang xử lý
                                            </button>
                                            <span class="text-slate-300">|</span>
                                        @endif
                                        <button wire:click="openReplyModal({{ $feedback->id }})" class="text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors" title="Trả lời phản hồi">
                                            Trả lời
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500" title="Đã trả lời lúc {{ $feedback->replied_at?->format('d/m/Y H:i') }}">Bởi: {{ $feedback->replier->name ?? 'Admin' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Không tìm thấy phản hồi nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($feedbacks->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $feedbacks->links() }}
            </div>
        @endif
    </div>

    <!-- Reply Modal -->
    @if($showReplyModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-lg font-bold text-slate-800">Trả lời phản hồi</h3>
                <button wire:click="closeReplyModal" class="rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                    <x-user.icon name="x" :size="20" />
                </button>
            </div>
            
            <form wire:submit="submitReply" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Nội dung trả lời <span class="text-red-500">*</span></label>
                        <textarea wire:model="replyContent" rows="4" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" placeholder="Nhập nội dung trả lời cho người dùng..."></textarea>
                        @error('replyContent') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                        <p class="mt-2 text-xs text-slate-500">Người dùng sẽ nhận được thông báo qua hệ thống sau khi bạn trả lời, và phản hồi này sẽ được chuyển sang trạng thái "Đã xử lý".</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" wire:click="closeReplyModal" class="rounded-full px-6 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                        <span wire:loading.remove wire:target="submitReply">Gửi trả lời</span>
                        <span wire:loading wire:target="submitReply">Đang gửi...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
