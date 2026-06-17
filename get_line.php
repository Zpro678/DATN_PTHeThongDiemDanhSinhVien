<?php
$content = file_get_contents('D:\DATN_PTHeThongDiemDanhSinhVien\resources\views\student\attendance\studentAttendanceStat.blade.php');
$lines = explode("\n", $content);
echo base64_encode($lines[0]);
