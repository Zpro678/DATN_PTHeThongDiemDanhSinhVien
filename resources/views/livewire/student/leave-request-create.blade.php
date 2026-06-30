<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8">
    <section class="flex flex-col justify-between gap-4 rounded-2xl border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-center">
        <div>
            <x-user.workspace-badge type="student" />
            <h1 class="flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900">
                <x-user.icon name="file-text" class="text-tertiary" />
                {{ $isEdit ?? false ? 'Chỉnh sửa đơn xin phép' : 'Xin nghỉ phép' }}
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Điền thông tin và gửi minh chứng để xin phép nghỉ học một buổi cụ thể.
            </p>
        </div>
        <a href="{{ route('student.leave-requests.history') }}" class="flex w-full items-center justify-center gap-2 rounded-xl border border-outline-variant/20 bg-surface-container-lowest px-6 py-3 font-bold text-on-surface transition-all hover:bg-surface-container-low hover:shadow-sm active:scale-95 md:w-auto shrink-0">
            <x-user.icon name="history" />
            Lịch sử đơn từ
        </a>
    </section>

    <div class="bg-white shadow-sm overflow-hidden rounded-2xl border border-slate-200">
        <div class="px-4 py-6 sm:p-8">

            @if (session('status'))
                <div class="mb-6 font-medium text-sm text-green-600 bg-green-50 p-4 rounded-lg border border-green-200 flex items-center">
                    <x-user.icon name="check-circle" :size="20" class="mr-3 text-green-500" />
                    {{ session('status') }}
                </div>
            @endif

            <form wire:submit="submit" class="space-y-8">
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 flex gap-3 items-start">
                    <x-user.icon name="info" class="shrink-0 text-blue-600" />
                    <p><strong>Lưu ý:</strong> Hệ thống tiếp nhận đơn xin phép cho cả các buổi học <strong>đã diễn ra</strong> và <strong>sắp diễn ra</strong>. Vui lòng tải lên minh chứng rõ nét (giấy khám bệnh, đơn xin phép có chữ ký...) để được duyệt nhanh nhất.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Chọn lớp -->
                    <div>
                        <label for="class_id" class="block text-lg font-bold text-slate-700 mb-2">Chọn lớp học</label>
                        <div x-data="{ open: false }" class="relative w-full">
                            <button @click="open = !open" @click.away="open = false" type="button" 
                                class="flex w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3 text-lg text-slate-700 transition hover:border-primary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                <span class="truncate">
                                    @if($class_id)
                                        @php $selectedClass = collect($this->classes())->firstWhere('id', $class_id); @endphp
                                        {{ $selectedClass ? $selectedClass->join_key . ' - ' . $selectedClass->name : '-- Chọn lớp --' }}
                                    @else
                                        -- Chọn lớp --
                                    @endif
                                </span>
                                <x-user.icon name="chevron-down" class="h-5 w-5 text-slate-400 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" 
                                class="absolute left-0 top-full z-10 mt-1 w-full overflow-hidden rounded-xl border border-slate-100 bg-white shadow-lg ring-1 ring-black/5">
                                <ul class="max-h-60 overflow-y-auto p-1">
                                    <li>
                                        <button @click="$wire.set('class_id', ''); open = false;" type="button" 
                                            class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-base transition-colors {{ empty($class_id) ? 'bg-primary/10 text-primary font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                            <span>-- Chọn lớp --</span>
                                            @if(empty($class_id)) <x-user.icon name="check" class="h-4 w-4" /> @endif
                                        </button>
                                    </li>
                                    @foreach($this->classes() as $class)
                                        <li>
                                            <button @click="$wire.set('class_id', '{{ $class->id }}'); open = false;" type="button" 
                                                class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-base transition-colors {{ $class_id == $class->id ? 'bg-primary/10 text-primary font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
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
                        <label for="class_session_id" class="block text-lg font-bold text-slate-700 mb-2">Chọn buổi học</label>
                        <div x-data="{ open: false }" class="relative w-full {{ empty($this->sessions()) ? 'opacity-50 pointer-events-none' : '' }}">
                            <button @click="open = !open" @click.away="open = false" type="button" 
                                class="flex w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3 text-lg text-slate-700 transition hover:border-primary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                {{ empty($this->sessions()) ? 'disabled' : '' }}>
                                <span class="truncate">
                                    @if($class_session_id)
                                        @php $selectedSession = collect($this->sessions())->firstWhere('id', $class_session_id); @endphp
                                        {{ $selectedSession ? \Carbon\Carbon::parse($selectedSession->date)->format('d/m/Y') . ' - ' . $selectedSession->name : '-- Chọn buổi học --' }}
                                    @else
                                        -- Chọn buổi học --
                                    @endif
                                </span>
                                <x-user.icon name="chevron-down" class="h-5 w-5 text-slate-400 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                            </button>
                            <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" 
                                class="absolute left-0 top-full z-10 mt-1 w-full overflow-hidden rounded-xl border border-slate-100 bg-white shadow-lg ring-1 ring-black/5">
                                <ul class="max-h-60 overflow-y-auto p-1">
                                    <li>
                                        <button @click="$wire.set('class_session_id', ''); open = false;" type="button" 
                                            class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-base transition-colors {{ empty($class_session_id) ? 'bg-primary/10 text-primary font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                            <span>-- Chọn buổi học --</span>
                                            @if(empty($class_session_id)) <x-user.icon name="check" class="h-4 w-4" /> @endif
                                        </button>
                                    </li>
                                    @foreach($this->sessions() as $session)
                                        <li>
                                            <button @click="$wire.set('class_session_id', '{{ $session->id }}'); open = false;" type="button" 
                                                class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-base transition-colors {{ $class_session_id == $session->id ? 'bg-primary/10 text-primary font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                                <span>{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }} - {{ $session->name }}</span>
                                                @if($class_session_id == $session->id) <x-user.icon name="check" class="h-4 w-4" /> @endif
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @if(empty($this->sessions()) && $class_id)
                            <span class="text-gray-500 text-sm mt-1 block">Không có buổi học nào cho lớp này.</span>
                        @endif
                        @error('class_session_id') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Lý do -->
                <div>
                    <label for="reason" class="block text-lg font-bold text-slate-700 mb-2">Lý do xin nghỉ</label>
                    <textarea wire:model="reason" id="reason" rows="5" class="shadow-sm focus:ring-primary focus:border-primary block w-full text-lg border border-slate-300 rounded-xl placeholder-slate-400 p-4 transition" placeholder="Trình bày chi tiết lý do xin phép của bạn..."></textarea>
                    @error('reason') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Minh chứng -->
                <div>
                    <label class="block text-lg font-bold text-slate-700 mb-2">Minh chứng đính kèm (Ảnh)</label>
                    <label for="file-upload" class="mt-1 flex justify-center px-6 py-10 border-2 border-slate-300 border-dashed rounded-xl transition hover:border-primary/50 hover:bg-slate-100/50 cursor-pointer relative overflow-hidden bg-slate-50/50 group" 
                         x-data="{ isUploading: false, progress: 0 }"
                         x-on:livewire-upload-start="isUploading = true"
                         x-on:livewire-upload-finish="isUploading = false"
                         x-on:livewire-upload-error="isUploading = false"
                         x-on:livewire-upload-progress="progress = $event.detail.progress">
                        <div class="w-full text-center" x-show="!isUploading">
                            @if (!empty($proof_images) || !empty($existing_images))
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-6">
                                    @foreach ($existing_images as $index => $image)
                                        <div class="relative group aspect-square rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center p-1">
                                            <img src="{{ asset('storage/'.$image) }}" class="w-full h-full object-cover rounded shadow-sm">
                                            <button type="button" wire:click.prevent="removeExistingImage({{ $index }})" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600 focus:outline-none transition-transform">
                                                <x-user.icon name="x" :size="14" stroke-width="3" />
                                            </button>
                                        </div>
                                    @endforeach
                                    @foreach ($proof_images as $index => $image)
                                        <div class="relative group aspect-square rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center p-1">
                                            <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover rounded shadow-sm">
                                            <button type="button" wire:click.prevent="removeImage({{ $index }})" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600 focus:outline-none transition-transform">
                                                <x-user.icon name="x" :size="14" stroke-width="3" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @endif
                            <div class="flex text-sm text-gray-600 justify-center">
                                <span class="relative font-medium text-primary group-hover:text-primary/80">
                                    Tải ảnh lên
                                    <input id="file-upload" wire:model="proof_images" type="file" class="sr-only" accept="image/*" multiple>
                                </span>
                                <p class="pl-1">hoặc kéo thả vào đây</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                PNG, JPG, GIF lên tới 2MB (Có thể chọn nhiều ảnh)
                            </p>
                        </div>
                        <!-- Upload Progress -->
                        <div x-show="isUploading" class="w-full">
                            <div class="text-sm font-medium text-gray-700 mb-1">Đang tải lên...</div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-primary h-2.5 rounded-full transition-all duration-300" x-bind:style="'width: ' + progress + '%'"></div>
                            </div>
                        </div>
                    </label>
                    @error('proof_images.*') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    @error('proof_images') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="pt-5 border-t border-gray-200 flex items-center justify-between">
                    <a href="javascript:history.back()" class="inline-flex items-center justify-center py-2.5 px-6 border border-slate-300 shadow-sm text-sm font-bold rounded-lg text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition">
                        <x-user.icon name="arrow-left" :size="16" class="mr-2" />
                        Quay lại
                    </a>
                    <div class="flex items-center">
                        <div wire:loading wire:target="submit" class="text-sm text-gray-500 mr-4">
                            Đang xử lý...
                        </div>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-bold rounded-lg text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition disabled:opacity-50">
                            {{ $isEdit ?? false ? 'Lưu thay đổi' : 'Gửi đơn xin phép' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
