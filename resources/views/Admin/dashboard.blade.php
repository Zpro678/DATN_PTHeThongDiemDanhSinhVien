<x-app-layout variant="admin">
    <!-- Header Dashboard (Tiêu đề, Ngày tháng, Nút tải lại) -->
    @include('Admin.Dashboard.dashboard-header')

    <!-- Bốn thẻ thống kê tổng quan -->
    @include('Admin.Dashboard.stats-overview')

    <!-- Nội dung chính của Dashboard -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Biểu đồ phân tích và Bảng cảnh báo (chiếm 2/3 màn hình lớn) -->
        <div class="xl:col-span-2 flex flex-col gap-6">
            @include('Admin.Dashboard.attendance-chart')
            @include('Admin.Dashboard.warning-students')
        </div>
        
        <!-- Các widget phụ (chiếm 1/3 màn hình lớn) -->
        <div class="xl:col-span-1 flex flex-col gap-6">
            @include('Admin.Dashboard.realtime-attendance')
        </div>
    </div>

    <!-- Biểu đồ Phân bố Khoa & Lớp học -->
    @include('Admin.Dashboard.faculty-chart')
</x-app-layout>
