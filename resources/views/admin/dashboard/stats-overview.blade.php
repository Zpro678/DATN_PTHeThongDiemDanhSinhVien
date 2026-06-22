<div class="admin-grid-equal grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <x-stats-card
        title="Tổng Sinh Viên"
        value="{{ number_format($totalStudents) }}"
        change=""
        :isPositive="true"
        icon="users"
        iconBg="bg-blue-50 border-blue-100 text-blue-600"
        sparklineColor="stroke-blue-500"
        sparklinePath="M 0,15 L 10,12 L 20,18 L 30,8 L 40,10 L 50,2"
    />

    <x-stats-card
        title="Tỷ lệ Chuyên cần"
        value="{{ $attendanceRate }}%"
        change=""
        :isPositive="true"
        icon="check-square"
        iconBg="bg-emerald-50 border-emerald-100 text-emerald-600"
        sparklineColor="stroke-emerald-500"
        sparklinePath="M 0,18 L 10,15 L 20,16 L 30,10 L 40,5 L 50,0"
    />

    <x-stats-card
        title="Lớp học đang mở"
        value="{{ number_format($activeClasses) }}"
        change=""
        :isPositive="false"
        icon="book-open"
        iconBg="bg-amber-50 border-amber-100 text-amber-600"
        sparklineColor="stroke-amber-500"
        sparklinePath="M 0,5 L 10,8 L 20,6 L 30,12 L 40,18 L 50,15"
    />

    <x-stats-card
        title="Cảnh báo chuyên cần"
        value="{{ number_format($warningCount) }}"
        change=""
        :isPositive="false"
        icon="alert-triangle"
        iconBg="bg-rose-50 border-rose-100 text-rose-600"
        sparklineColor="stroke-rose-500"
        sparklinePath="M 0,2 L 10,6 L 20,4 L 30,15 L 40,12 L 50,18"
    />
</div>
