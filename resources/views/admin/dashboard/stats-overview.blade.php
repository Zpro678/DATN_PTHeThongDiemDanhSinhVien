<div class="admin-grid-equal grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <x-stats-card
        title="Tổng Học Viên"
        value="{{ number_format($totalStudents) }}"
        change=""
        :isPositive="true"
        icon="users"
        iconBg="bg-blue-50 border-blue-100 text-blue-600"
    />

    <x-stats-card
        title="Tỷ lệ Chuyên cần"
        value="{{ $attendanceRate }}%"
        change=""
        :isPositive="true"
        icon="check-square"
        iconBg="bg-emerald-50 border-emerald-100 text-emerald-600"
    />

    <x-stats-card
        title="Lớp học đang mở"
        value="{{ number_format($activeClasses) }}"
        change=""
        :isPositive="false"
        icon="book-open"
        iconBg="bg-amber-50 border-amber-100 text-amber-600"
    />

    <x-stats-card
        title="Cảnh báo chuyên cần"
        value="{{ number_format($warningCount) }}"
        change=""
        :isPositive="false"
        icon="alert-triangle"
        iconBg="bg-rose-50 border-rose-100 text-rose-600"
    />
</div>
