<div class="relative" 
    x-data="{
        startDate: @entangle('startDate'),
        endDate: @entangle('endDate'),
        dateDisplay: 'Chọn khoảng thời gian',
        init() {
            // Cập nhật giá trị hiển thị ban đầu
            if (this.startDate && this.endDate) {
                this.dateDisplay = this.startDate + ' - ' + this.endDate;
            }

            // Khởi tạo Flatpickr
            flatpickr(this.$refs.pickerBtn, {
                mode: 'range',
                dateFormat: 'd/m/Y',
                locale: 'vn',
                defaultDate: (this.startDate && this.endDate) ? [this.startDate, this.endDate] : [],
                onChange: (selectedDates, dateStr, instance) => {
                    if (selectedDates.length === 2) {
                        let parts = dateStr.split(' to ');
                        
                        // Cập nhật giá trị lên Alpine (nhờ @entangle sẽ tự động gửi qua Livewire PHP)
                        this.startDate = parts[0];
                        this.endDate = parts[1];
                        
                        // Cập nhật text hiển thị trên nút bấm
                        this.dateDisplay = parts[0] + ' - ' + parts[1];
                    }
                }
            });
        }
    }"
>
    <!-- Thêm x-ref="pickerBtn" để Alpine tìm được phần tử này mà không cần dùng ID -->
    <button x-ref="pickerBtn" type="button" class="group inline-flex items-center gap-2.5 px-4 py-2.5 bg-white border border-slate-200 rounded-lg shadow-sm text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-blue-300 hover:text-blue-600 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/20">

        <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
        </svg>

        <!-- Thẻ span này sẽ tự thay đổi text dựa vào biến dateDisplay -->
        <span x-text="dateDisplay"></span>

        <svg class="w-4 h-4 text-slate-400 ml-1 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>
</div>
