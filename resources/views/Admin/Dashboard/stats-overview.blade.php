<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
    <x-stats-card 
        title="Tổng Sinh Viên" 
        value="12,450" 
        change="+2.5%" 
        :isPositive="true" 
        icon="users" 
        iconBg="bg-blue-50 border-blue-100 text-blue-600 dark:bg-blue-950/30 dark:border-blue-900/50 dark:text-blue-400"
        sparklineColor="stroke-blue-500"
        sparklinePath="M 0,15 L 10,12 L 20,18 L 30,8 L 40,10 L 50,2"
    />
    
    <x-stats-card 
        title="Tỷ lệ Chuyên cần" 
        value="94.2%" 
        change="+0.8%" 
        :isPositive="true" 
        icon="check-square" 
        iconBg="bg-emerald-50 border-emerald-100 text-emerald-600 dark:bg-emerald-950/30 dark:border-emerald-900/50 dark:text-emerald-400"
        sparklineColor="stroke-emerald-500"
        sparklinePath="M 0,18 L 10,15 L 20,16 L 30,10 L 40,5 L 50,0"
    />

    <x-stats-card 
        title="Lớp học đang mở" 
        value="342" 
        change="-12" 
        :isPositive="false" 
        icon="book-open" 
        iconBg="bg-amber-50 border-amber-100 text-amber-600 dark:bg-amber-950/30 dark:border-amber-900/50 dark:text-amber-400"
        sparklineColor="stroke-amber-500"
        sparklinePath="M 0,5 L 10,8 L 20,6 L 30,12 L 40,18 L 50,15"
    />

    <x-stats-card 
        title="Cảnh báo cấm thi" 
        value="128" 
        change="-4%" 
        :isPositive="true" 
        icon="alert-triangle" 
        iconBg="bg-rose-50 border-rose-100 text-rose-600 dark:bg-rose-950/30 dark:border-rose-900/50 dark:text-rose-400"
        sparklineColor="stroke-rose-500"
        sparklinePath="M 0,2 L 10,6 L 20,4 L 30,15 L 40,12 L 50,18"
    />
</div>
