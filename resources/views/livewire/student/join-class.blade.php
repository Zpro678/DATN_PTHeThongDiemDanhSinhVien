<div class="max-w-3xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 leading-tight">
            Tham gia lớp học
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Nhập mã lớp và thông tin sinh viên của bạn để tham gia vào lớp học.
        </p>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-xl">
        <div class="px-4 py-5 sm:p-6">
            @if (session('status'))
                <div class="mb-6 font-medium text-sm text-green-600 bg-green-50 p-4 rounded-lg border border-green-200 flex items-center">
                    <x-user.icon name="check-circle" :size="20" class="mr-3 text-green-500" />
                    {{ session('status') }}
                </div>
            @endif

            <form wire:submit="submit" class="space-y-6">
                <!-- Mã lớp -->
                <div>
                    <label for="class_code" class="block text-sm font-medium text-gray-700">Mã lớp học <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="class_code" id="class_code" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150 uppercase" placeholder="Nhập mã lớp (VD: LOP-123)">
                    @error('class_code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Mã sinh viên -->
                <div>
                    <label for="student_code" class="block text-sm font-medium text-gray-700">Mã sinh viên <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="student_code" id="student_code" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150" placeholder="Nhập mã sinh viên của bạn">
                    @error('student_code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Họ và tên -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Họ và tên <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="full_name" id="full_name" class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md transition duration-150" placeholder="Họ và tên hiển thị trong danh sách lớp">
                    @error('full_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="pt-5 border-t border-gray-200 flex items-center justify-end">
                    <div wire:loading wire:target="submit" class="text-sm text-gray-500 mr-4">
                        Đang xử lý...
                    </div>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-bold rounded-lg text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition disabled:opacity-50">
                        Tham gia lớp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
