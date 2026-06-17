<?php
$file = 'D:\DATN_PTHeThongDiemDanhSinhVien\resources\views\student\attendance\studentAttendanceStat.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);
$lines[0] = '<x-app-layout variant="student" pageTitle="Thống kê chuyên cần">' . "\n" . '    <div x-data="{ activeSubject: null, showList: false }" class="w-full h-full">';
file_put_contents($file, implode("\n", $lines));
