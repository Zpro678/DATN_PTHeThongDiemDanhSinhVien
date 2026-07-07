<div class="w-full space-y-6 px-6 py-6 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">
    
    {{-- Hero Section --}}
    <div class="mb-8 rounded-2xl bg-primary px-8 py-12 text-center text-white shadow-lg shadow-primary/20">
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-white/20 backdrop-blur-md">
            <x-user.icon name="message-circle" :size="40" class="text-white" />
        </div>
        <h1 class="mb-4 text-3xl font-black md:text-4xl">Góp ý & Phản hồi</h1>
        <p class="mx-auto max-w-2xl text-primary-100 md:text-lg">Chúng tôi luôn lắng nghe để cải thiện hệ thống. Vui lòng gửi yêu cầu hỗ trợ hoặc báo cáo lỗi cho đội ngũ kỹ thuật của Attendia Tech.</p>
    </div>

    {{-- Kênh hỗ trợ khác nằm ngang --}}
    <div class="mb-8 grid gap-4 md:grid-cols-3">
        <a href="#" class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-md hover:ring-primary/30">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                <x-user.icon name="mail" :size="24" />
            </div>
            <div>
                <p class="text-sm font-bold text-on-surface">Gửi Email</p>
                <p class="text-xs text-on-surface-variant">supportattendia@gmail.com</p>
            </div>
        </a>

        <a href="#" class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-md hover:ring-tertiary/30">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-tertiary/10 text-tertiary transition-colors group-hover:bg-tertiary group-hover:text-white">
                <x-user.icon name="phone-call" :size="24" />
            </div>
            <div>
                <p class="text-sm font-bold text-on-surface">Hotline / Zalo</p>
                <p class="text-xs text-on-surface-variant">0785 850 551</p>
            </div>
        </a>

        <a href="{{ route('docs') }}" class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-md hover:ring-secondary/30">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-secondary/10 text-secondary transition-colors group-hover:bg-secondary group-hover:text-white">
                <x-user.icon name="book-open" :size="24" />
            </div>
            <div>
                <p class="text-sm font-bold text-on-surface">Tài liệu HDSD</p>
                <p class="text-xs text-on-surface-variant">Xem hướng dẫn chi tiết</p>
            </div>
        </a>
    </div>

    <div class="flex flex-col gap-8">


            
            {{-- Form Gửi Yêu Cầu --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 sm:p-8">
                <h2 class="mb-6 flex items-center gap-3 text-xl font-bold text-on-surface">
                    <x-user.icon name="message-square" :size="24" class="text-primary" />
                    Gửi phản hồi mới
                </h2>
                
                <form wire:submit.prevent="submit" class="space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label class="text-sm font-bold text-on-surface">Phân loại <span class="text-error">*</span></label>
                            <x-custom-select wire:model.live="type" placeholder="Chọn phân loại..." :options="[
                                ['value' => 'bug', 'label' => 'Báo lỗi hệ thống'],
                                ['value' => 'feature', 'label' => 'Góp ý tính năng'],
                                ['value' => 'other', 'label' => 'Yêu cầu hỗ trợ khác']
                            ]" />
                            @error('type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-bold text-on-surface">Tiêu đề <span class="text-error">*</span></label>
                            <input type="text" wire:model="title" placeholder="Tóm tắt vấn đề..." class="w-full rounded-xl border border-outline-variant/50 bg-surface-container-lowest px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                            @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-bold text-on-surface">Nội dung chi tiết <span class="text-error">*</span></label>
                        <textarea wire:model="content" rows="4" placeholder="Mô tả chi tiết vấn đề bạn đang gặp phải..." class="w-full resize-none rounded-xl border border-outline-variant/50 bg-surface-container-lowest px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
                        @error('content') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-bold text-on-surface">Ảnh đính kèm minh họa <span class="text-slate-400 font-normal italic">(Tối đa 3 ảnh, tùy chọn)</span></label>
                        <label for="file-upload" class="group relative flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-blue-200 bg-white py-10 transition hover:border-primary hover:bg-slate-50/50 mt-2" 
                             x-data="{ isUploading: false, progress: 0 }"
                             x-on:livewire-upload-start="isUploading = true"
                             x-on:livewire-upload-finish="isUploading = false"
                             x-on:livewire-upload-error="isUploading = false"
                             x-on:livewire-upload-progress="progress = $event.detail.progress">
                            <div class="w-full text-center" x-show="!isUploading">
                                @if (!empty($attachments))
                                    <div class="mb-6 grid grid-cols-2 gap-4 px-6 sm:grid-cols-3">
                                        @foreach ($attachments as $index => $image)
                                            <div class="group relative flex aspect-square items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                                                <img src="{{ $image->temporaryUrl() }}" class="h-full w-full rounded-lg object-cover shadow-sm">
                                                <button type="button" wire:click.prevent="removeAttachment({{ $index }})" class="absolute -right-2 -top-2 rounded-full bg-red-500 p-1.5 text-white shadow-sm transition-transform hover:scale-110 hover:bg-red-600 focus:outline-none">
                                                    <x-user.icon name="x" :size="14" stroke-width="3" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-[0_2px_15px_-3px_rgba(6,81,237,0.15)] ring-1 ring-slate-100/50 transition-transform group-hover:scale-110">
                                        <x-user.icon name="upload" :size="24" class="text-primary" stroke-width="2" />
                                    </div>
                                @endif
                                <div class="flex justify-center text-[15px]">
                                    <span class="font-bold text-primary group-hover:text-primary-600">
                                        Kéo thả tệp hoặc click để tải lên
                                        <input id="file-upload" wire:model="attachments" type="file" class="sr-only" accept="image/*" multiple>
                                    </span>
                                </div>
                                <p class="mt-1.5 text-sm text-slate-500">
                                    Chấp nhận JPG, PNG (Tối đa 2MB/ảnh)
                                </p>
                            </div>
                            <!-- Upload Progress -->
                            <div x-show="isUploading" class="w-full px-12">
                                <div class="mb-2 text-center text-sm font-semibold text-slate-700">Đang tải lên...</div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-primary transition-all duration-300" x-bind:style="'width: ' + progress + '%'"></div>
                                </div>
                            </div>
                        </label>
                        @error('attachments.*') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                        @error('attachments') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end border-t border-outline-variant/20 pt-6">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-bold text-white shadow-lg shadow-primary/30 transition-all hover:bg-primary/90 disabled:opacity-70 sm:w-auto" wire:loading.attr="disabled" wire:target="submit, attachment">
                            <span wire:loading.remove wire:target="submit">
                                <x-user.icon name="send" :size="18" />
                            </span>
                            <span wire:loading wire:target="submit">
                                <x-user.icon name="loader" :size="18" class="animate-spin" />
                            </span>
                            <span wire:loading.remove wire:target="submit">Gửi phản hồi</span>
                            <span wire:loading wire:target="submit">Đang gửi...</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Lịch sử phản hồi --}}
            <div wire:poll.5s class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 sm:p-8">
                <h2 class="mb-6 flex items-center gap-3 text-xl font-bold text-on-surface">
                    <x-user.icon name="history" :size="24" class="text-primary" />
                    Lịch sử phản hồi
                </h2>

                <div class="space-y-4">
                    @forelse($feedbacks as $feedback)
                        <div id="feedback-{{ $feedback->id }}" class="group relative rounded-2xl bg-surface-container-lowest p-5 ring-1 ring-outline-variant/30 transition-all hover:shadow-md hover:ring-primary/20">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider
                                            {{ $feedback->type === 'bug' ? 'bg-red-100 text-red-700' : ($feedback->type === 'feature' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                            {{ $feedback->type === 'bug' ? 'Báo lỗi' : ($feedback->type === 'feature' ? 'Góp ý' : 'Khác') }}
                                        </span>
                                        <h3 class="font-bold text-on-surface break-all">{{ $feedback->title }}</h3>
                                    </div>
                                    <p class="text-sm text-on-surface-variant line-clamp-2 break-all">{{ $feedback->content }}</p>
                                    <button wire:click="viewDetails({{ $feedback->id }})" class="mt-2 text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                                        <span>Xem chi tiết</span>
                                        <x-user.icon name="arrow-right" :size="14" />
                                    </button>
                                    @if(is_array($feedback->attachment_path) && count($feedback->attachment_path) > 0)
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach($feedback->attachment_path as $path)
                                                <a href="{{ asset('storage/' . $path) }}" target="_blank" class="block">
                                                    <img src="{{ asset('storage/' . $path) }}" class="h-16 w-16 rounded object-cover ring-1 ring-outline-variant/20 hover:opacity-80 transition-opacity">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="shrink-0 text-right flex flex-col items-end gap-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold
                                        {{ $feedback->status === 'resolved' ? 'bg-green-100 text-green-700' : ($feedback->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : ($feedback->status === 'cancelled' ? 'bg-slate-100 text-slate-500 line-through' : 'bg-blue-100 text-blue-700')) }}">
                                        {{ $feedback->status === 'resolved' ? 'Đã xử lý' : ($feedback->status === 'in_progress' ? 'Đang xử lý' : ($feedback->status === 'cancelled' ? 'Đã hủy' : 'Chờ xử lý')) }}
                                    </span>
                                    <p class="text-[11px] text-on-surface-variant">{{ $feedback->created_at->format('d/m/Y H:i') }}</p>
                                    @if($feedback->status === 'pending')
                                        <button wire:click="cancelFeedback({{ $feedback->id }})" wire:confirm="Bạn có chắc chắn muốn hủy yêu cầu này không?" class="text-xs text-red-500 hover:text-red-700 hover:underline">
                                            Hủy yêu cầu
                                        </button>
                                    @endif
                                </div>
                            </div>

                            @if($feedback->admin_reply)
                                <div class="mt-4 rounded-xl bg-blue-50/50 p-4 border border-blue-100">
                                    <div class="flex items-center gap-2 mb-1">
                                        <x-user.icon name="corner-down-right" :size="16" class="text-blue-500" />
                                        <span class="text-xs font-bold text-blue-800">Phản hồi từ Admin ({{ $feedback->replied_at?->format('d/m/Y H:i') }})</span>
                                    </div>
                                    <p class="text-sm text-blue-900">{{ $feedback->admin_reply }}</p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-surface-container">
                                <x-user.icon name="inbox" :size="24" class="text-on-surface-variant" />
                            </div>
                            <p class="mt-3 text-sm text-on-surface-variant">Bạn chưa có phản hồi nào.</p>
                        </div>
                    @endforelse
                </div>
        </div>
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $detailFeedback)
    <template x-teleport="body">
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 shrink-0">
                    <h3 class="text-lg font-bold text-slate-800">Chi tiết yêu cầu hỗ trợ</h3>
                    <button wire:click="closeDetailModal" class="rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-user.icon name="x" :size="20" />
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto space-y-6">
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg break-all">{{ $detailFeedback->title }}</h4>
                        <div class="mt-2 flex items-center gap-4 text-sm text-slate-500">
                            <span class="flex items-center gap-1"><x-user.icon name="clock" :size="16" /> {{ $detailFeedback->created_at->format('d/m/Y H:i') }}</span>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider
                                {{ $detailFeedback->type === 'bug' ? 'bg-red-100 text-red-700' : ($detailFeedback->type === 'feature' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ $detailFeedback->type === 'bug' ? 'Báo lỗi' : ($detailFeedback->type === 'feature' ? 'Góp ý' : 'Khác') }}
                            </span>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider
                                {{ $detailFeedback->status === 'resolved' ? 'bg-green-100 text-green-700' : ($detailFeedback->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : ($detailFeedback->status === 'cancelled' ? 'bg-slate-100 text-slate-500 line-through' : 'bg-blue-100 text-blue-700')) }}">
                                {{ $detailFeedback->status === 'resolved' ? 'Đã xử lý' : ($detailFeedback->status === 'in_progress' ? 'Đang xử lý' : ($detailFeedback->status === 'cancelled' ? 'Đã hủy' : 'Chờ xử lý')) }}
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
                            <h5 class="text-sm font-semibold text-slate-700 mb-3">Phản hồi từ Admin</h5>
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
