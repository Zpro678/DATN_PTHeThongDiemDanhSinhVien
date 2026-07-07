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

    <div wire:poll.5s class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
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
                            <td class="px-6 py-4 whitespace-normal min-w-[250px] max-w-sm">
                                <div class="font-semibold text-slate-800 break-all">{{ $feedback->title }}</div>
                                <p class="text-sm mt-1 line-clamp-2 break-all">{{ $feedback->content }}</p>
                                @if(is_array($feedback->attachment_path) && count($feedback->attachment_path) > 0)
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($feedback->attachment_path as $path)
                                            <a href="{{ asset('storage/' . $path) }}" target="_blank" class="block">
                                                <img src="{{ asset('storage/' . $path) }}" class="h-12 w-12 rounded object-cover ring-1 ring-slate-200 hover:opacity-80 transition-opacity">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                                        {{ $feedback->status === 'resolved' ? 'bg-green-100 text-green-800' : ($feedback->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : ($feedback->status === 'cancelled' ? 'bg-slate-100 text-slate-500 line-through' : 'bg-blue-100 text-blue-800')) }}">
                                        {{ $feedback->status === 'resolved' ? 'Đã xử lý' : ($feedback->status === 'in_progress' ? 'Đang xử lý' : ($feedback->status === 'cancelled' ? 'Đã hủy' : 'Chờ xử lý')) }}
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="viewDetails({{ $feedback->id }})" title="Xem chi tiết" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                                        <x-user.icon name="eye" :size="18" />
                                    </button>
                                    @if($feedback->status === 'pending')
                                        <button wire:click="markAsInProgress({{ $feedback->id }})" title="Đánh dấu đang xử lý" class="rounded-lg p-2 text-slate-400 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                            <x-user.icon name="clock" :size="18" />
                                        </button>
                                    @endif
                                    @if($feedback->status !== 'resolved' && $feedback->status !== 'cancelled')
                                        <button wire:click="openReplyModal({{ $feedback->id }})" title="Trả lời" class="rounded-lg p-2 text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                            <x-user.icon name="message-square" :size="18" />
                                        </button>
                                    @endif
                                    <button wire:click="deleteFeedback({{ $feedback->id }})" wire:confirm="Bạn có chắc chắn muốn xóa phản hồi này không? Thao tác này không thể hoàn tác." title="Xóa" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                        <x-user.icon name="trash-2" :size="18" />
                                    </button>
                                </div>
                                @if($feedback->status === 'resolved')
                                    <div class="mt-1 text-right">
                                        <span class="text-xs text-slate-500" title="Đã trả lời lúc {{ $feedback->replied_at?->format('d/m/Y H:i') }}">Bởi: {{ $feedback->replier->name ?? 'Admin' }}</span>
                                    </div>
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
    <template x-teleport="body">
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
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
    </template>
    @endif

    <!-- Detail Modal -->
    @if($showDetailModal && $detailFeedback)
    <template x-teleport="body">
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 shrink-0">
                    <h3 class="text-lg font-bold text-slate-800">Chi tiết phản hồi</h3>
                    <button wire:click="closeDetailModal" class="rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-user.icon name="x" :size="20" />
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto space-y-6">
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg break-all">{{ $detailFeedback->title }}</h4>
                        <div class="mt-2 flex items-center gap-4 text-sm text-slate-500">
                            <span class="flex items-center gap-1"><x-user.icon name="user" :size="16" /> {{ $detailFeedback->user->name ?? 'Người dùng đã xóa' }}</span>
                            <span class="flex items-center gap-1"><x-user.icon name="clock" :size="16" /> {{ $detailFeedback->created_at->format('d/m/Y H:i') }}</span>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider
                                {{ $detailFeedback->type === 'bug' ? 'bg-red-100 text-red-700' : ($detailFeedback->type === 'feature' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ $detailFeedback->type === 'bug' ? 'Báo lỗi' : ($detailFeedback->type === 'feature' ? 'Góp ý' : 'Khác') }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                        <p class="whitespace-pre-wrap break-all text-slate-700 leading-relaxed">{{ $detailFeedback->content }}</p>
                    </div>

                    @if(is_array($detailFeedback->attachment_path) && count($detailFeedback->attachment_path) > 0)
                        <div>
                            <h5 class="text-sm font-semibold text-slate-700 mb-3">Hình ảnh đính kèm</h5>
                            <div class="flex flex-wrap gap-3">
                                @foreach($detailFeedback->attachment_path as $path)
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank" class="block">
                                        <img src="{{ asset('storage/' . $path) }}" class="h-24 w-24 rounded-lg object-cover ring-1 ring-slate-200 hover:opacity-80 transition-opacity">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($detailFeedback->admin_reply)
                        <div>
                            <h5 class="text-sm font-semibold text-slate-700 mb-3">Nội dung đã trả lời (Bởi: {{ $detailFeedback->replier->name ?? 'Admin' }})</h5>
                            <div class="rounded-xl bg-blue-50 p-4 border border-blue-100">
                                <p class="whitespace-pre-wrap break-all text-blue-900 leading-relaxed">{{ $detailFeedback->admin_reply }}</p>
                                <p class="text-xs text-blue-700 mt-2">Trả lời lúc: {{ $detailFeedback->replied_at?->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4 shrink-0">
                    <button type="button" wire:click="closeDetailModal" class="rounded-full bg-slate-100 px-6 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-200">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </template>
    @endif
</div>
