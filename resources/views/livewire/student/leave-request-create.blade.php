<div class="w-full space-y-6 px-6 py-6 sm:px-10 lg:px-16 animate-in fade-in slide-in-from-bottom-4 duration-500">
    {{-- Header --}}
    <div class="flex justify-end mb-6">
        <a href="{{ route('student.leave-requests.history') }}" wire:navigate class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-[14px] font-semibold text-slate-700 transition-colors hover:bg-slate-50 shadow-sm">
            <x-user.icon name="history" :size="18" />
            Lịch sử đơn từ
        </a>
    </div>

    @if (session('status'))
        <div class="mb-6 flex items-center rounded-xl border border-green-200 bg-green-50 p-4 text-[15px] font-medium text-green-700">
            <x-user.icon name="check-circle" :size="20" class="mr-3 text-green-600" />
            {{ session('status') }}
        </div>
    @endif

    {{-- Form Wrapper --}}
    <div class="rounded-2xl border border-outline-variant/10 bg-white p-6 shadow-sm sm:p-10">
        <form wire:submit="submit" class="space-y-8">
                    
                    {{-- Alert Box --}}
                    <div class="flex items-start gap-3 rounded-xl bg-[#f8fbff] border border-blue-50/80 p-5 text-[17px] leading-relaxed text-slate-600">
                        <x-user.icon name="info" class="mt-0.5 shrink-0 text-primary" :size="20" />
                        <p><strong>Lưu ý:</strong> Hệ thống tiếp nhận đơn xin phép cho cả các buổi học <strong>đã diễn ra</strong> và <strong>sắp diễn ra</strong>. Vui lòng tải lên minh chứng rõ nét (giấy khám bệnh, đơn xin phép có chữ ký...) để được duyệt nhanh nhất.</p>
                    </div>

                    {{-- Dropdowns Grid --}}
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <!-- Chọn lớp -->
                        <div>
                            <label for="class_id" class="mb-2 block text-[16px] font-bold text-slate-700">Chọn lớp học</label>
                            <div x-data="{ open: false }" class="relative w-full">
                                <button @click="open = !open" @click.away="open = false" type="button" 
                                    class="flex w-full items-center justify-between rounded-lg border border-transparent bg-slate-50 px-4 py-3 text-[17px] text-slate-600 transition hover:bg-slate-100 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary">
                                    <span class="truncate">
                                        @if($class_id)
                                            @php $selectedClass = collect($this->classes())->firstWhere('id', $class_id); @endphp
                                            {{ $selectedClass ? $selectedClass->join_key . ' - ' . $selectedClass->name : '-- Chọn lớp --' }}
                                        @else
                                            -- Chọn lớp --
                                        @endif
                                    </span>
                                    <x-user.icon name="chevron-down" class="h-4 w-4 text-slate-500 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                                </button>
                                <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" 
                                    class="absolute left-0 top-full z-10 mt-1 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                                    <ul class="max-h-60 overflow-y-auto p-1.5">
                                        <li>
                                            <button @click="$wire.set('class_id', ''); open = false;" type="button" 
                                                class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-[16px] transition-colors {{ empty($class_id) ? 'bg-primary/10 font-bold text-primary' : 'text-slate-700 hover:bg-slate-50' }}">
                                                <span>-- Chọn lớp --</span>
                                                @if(empty($class_id)) <x-user.icon name="check" class="h-4 w-4" /> @endif
                                            </button>
                                        </li>
                                        @foreach($this->classes() as $class)
                                            <li>
                                                <button @click="$wire.set('class_id', '{{ $class->id }}'); open = false;" type="button" 
                                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-[16px] transition-colors {{ $class_id == $class->id ? 'bg-primary/10 font-bold text-primary' : 'text-slate-700 hover:bg-slate-50' }}">
                                                    <span>{{ $class->join_key }} - {{ $class->name }}</span>
                                                    @if($class_id == $class->id) <x-user.icon name="check" class="h-4 w-4" /> @endif
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Chọn buổi học -->
                        <div>
                            <label for="class_meeting_id" class="mb-2 block text-[16px] font-bold text-slate-700">Chọn buổi học</label>
                            <div x-data="{ open: false }" class="relative w-full {{ empty($this->meetings) ? 'opacity-50 pointer-events-none' : '' }}">
                                <button @click="open = !open" @click.away="open = false" type="button" 
                                    class="flex w-full items-center justify-between rounded-lg border border-transparent bg-slate-50 px-4 py-3 text-[17px] text-slate-600 transition hover:bg-slate-100 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                                    {{ empty($this->meetings) ? 'disabled' : '' }}>
                                    <span class="truncate">
                                        @if($class_meeting_id)
                                            @php $selectedMeeting = collect($this->meetings)->firstWhere('id', $class_meeting_id); @endphp
                                            {{ $selectedMeeting ? \Carbon\Carbon::parse($selectedMeeting->date)->format('d/m/Y') . ' - ' . $selectedMeeting->name : '-- Chọn buổi học --' }}
                                        @else
                                            -- Chọn buổi học --
                                        @endif
                                    </span>
                                    <x-user.icon name="chevron-down" class="h-4 w-4 text-slate-500 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                                </button>
                                <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" 
                                    class="absolute left-0 top-full z-10 mt-1 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                                    <ul class="max-h-60 overflow-y-auto p-1.5">
                                        <li>
                                            <button @click="$wire.set('class_meeting_id', ''); open = false;" type="button" 
                                                class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-[16px] transition-colors {{ empty($class_meeting_id) ? 'bg-primary/10 font-bold text-primary' : 'text-slate-700 hover:bg-slate-50' }}">
                                                <span>-- Chọn buổi học --</span>
                                                @if(empty($class_meeting_id)) <x-user.icon name="check" class="h-4 w-4" /> @endif
                                            </button>
                                        </li>
                                        @foreach($this->meetings as $meeting)
                                            <li>
                                                <button @click="$wire.set('class_meeting_id', '{{ $meeting->id }}'); open = false;" type="button" 
                                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-[16px] transition-colors {{ $class_meeting_id == $meeting->id ? 'bg-primary/10 font-bold text-primary' : 'text-slate-700 hover:bg-slate-50' }}">
                                                    <span>{{ \Carbon\Carbon::parse($meeting->date)->format('d/m/Y') }} - {{ $meeting->name }}</span>
                                                    @if($class_meeting_id == $meeting->id) <x-user.icon name="check" class="h-4 w-4" /> @endif
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @if(empty($this->meetings) && $class_id)
                                <span class="mt-1 block text-sm text-slate-500">Không có buổi học nào cho lớp này.</span>
                            @endif
                            @error('class_meeting_id') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Lý do --}}
                    <div>
                        <label for="reason" class="mb-2 block text-[16px] font-bold text-slate-700">Lý do xin nghỉ</label>
                        <textarea wire:model="reason" id="reason" rows="4" 
                            class="block w-full rounded-lg border border-transparent bg-slate-50 p-4 text-[17px] text-slate-600 placeholder-slate-400 transition hover:bg-slate-100 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary" 
                            placeholder="Trình bày chi tiết lý do xin phép của bạn..."></textarea>
                        @error('reason') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Minh chứng --}}
                    <div>
                        <label class="mb-2 block text-[16px] font-bold text-slate-700">Minh chứng đính kèm <span class="text-slate-400 font-normal italic">(nếu có)</span></label>
                        <label for="file-upload" class="group relative flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-blue-200 bg-white py-10 transition hover:border-primary hover:bg-slate-50/50" 
                             x-data="{ isUploading: false, progress: 0 }"
                             x-on:livewire-upload-start="isUploading = true"
                             x-on:livewire-upload-finish="isUploading = false"
                             x-on:livewire-upload-error="isUploading = false"
                             x-on:livewire-upload-progress="progress = $event.detail.progress">
                            <div class="w-full text-center" x-show="!isUploading">
                                @if (!empty($proof_images) || !empty($existing_images))
                                    <div class="mb-6 grid grid-cols-2 gap-4 px-6 sm:grid-cols-3 md:grid-cols-4">
                                        @foreach ($existing_images as $index => $image)
                                            <div class="group relative flex aspect-square items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                                                @if(Str::endsWith(strtolower($image), '.pdf'))
                                                    <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-red-50 text-red-500">
                                                        <x-user.icon name="file-text" :size="32" />
                                                        <span class="mt-2 text-[10px] font-bold uppercase">PDF</span>
                                                    </div>
                                                @else
                                                    <img src="{{ route('leave-requests.proof', ['leaveRequest' => $isEdit ? $this->leaveRequest->id : 0, 'filename' => basename($image)]) }}" class="h-full w-full rounded-lg object-cover shadow-sm">
                                                @endif
                                                <button type="button" wire:click.prevent="removeExistingImage({{ $index }})" class="absolute -right-2 -top-2 rounded-full bg-red-500 p-1.5 text-white shadow-sm transition-transform hover:scale-110 hover:bg-red-600 focus:outline-none">
                                                    <x-user.icon name="x" :size="14" stroke-width="3" />
                                                </button>
                                            </div>
                                        @endforeach
                                        @foreach ($proof_images as $index => $image)
                                            <div class="group relative flex aspect-square items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                                                @if(in_array(strtolower($image->getClientOriginalExtension()), ['pdf']))
                                                    <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-red-50 text-red-500">
                                                        <x-user.icon name="file-text" :size="32" />
                                                        <span class="mt-2 text-[10px] font-bold uppercase">PDF</span>
                                                    </div>
                                                @else
                                                    <img src="{{ $image->temporaryUrl() }}" class="h-full w-full rounded-lg object-cover shadow-sm">
                                                @endif
                                                <button type="button" wire:click.prevent="removeImage({{ $index }})" class="absolute -right-2 -top-2 rounded-full bg-red-500 p-1.5 text-white shadow-sm transition-transform hover:scale-110 hover:bg-red-600 focus:outline-none">
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
                                <div class="flex justify-center text-[17px]">
                                    <span class="font-bold text-primary group-hover:text-primary-600">
                                        Kéo thả tệp hoặc click để tải lên
                                        <input id="file-upload" wire:model="proof_images" type="file" class="sr-only" accept="image/*,application/pdf" multiple>
                                    </span>
                                </div>
                                <p class="mt-1.5 text-[16px] text-slate-500">
                                    Chấp nhận JPG, PNG, PDF (Tối đa 5MB)
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
                        @error('proof_images.*') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                        @error('proof_images') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                    
                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end pt-4">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('student.leave-requests.history') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-6 py-2.5 text-[17px] font-bold text-slate-700 transition-colors hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300 active:scale-95">
                                Hủy bỏ
                            </a>
                            <div wire:loading wire:target="submit" class="text-sm font-medium text-slate-500">
                                Đang xử lý...
                            </div>
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center rounded-lg bg-primary px-8 py-2.5 text-[17px] font-bold text-white shadow-md shadow-primary/20 transition-all hover:bg-primary-600 hover:shadow-lg hover:shadow-primary/30 active:scale-95 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50">
                                {{ $isEdit ?? false ? 'Lưu thay đổi' : 'Gửi đơn xin nghỉ' }}
                            </button>
                        </div>
                    </div>
                </form>
    </div>
</div>
