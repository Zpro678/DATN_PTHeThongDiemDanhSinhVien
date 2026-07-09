<?php

namespace App\Http\Controllers\Lecturer;

use App\Exports\ClassAttendanceHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Services\SubscriptionService;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ClassAttendanceHistoryExportController extends Controller
{
    public function __invoke(CourseClass $courseClass)
    {
        abort_unless($courseClass->isManagedBy(auth()->id()), 403);

        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            return redirect()
                ->route('upgrade', ['ma_user' => auth()->id()])
                ->with('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');
        }

        $fileCode = $courseClass->class_code ?: $courseClass->join_key ?: $courseClass->name;
        $slug = Str::slug($fileCode) ?: 'lop';
        $fileName = 'lich_su_diem_danh_'.$slug.'_'.now()->format('Ymd_His').'.xlsx';

        try {
            return Excel::download(new ClassAttendanceHistoryExport($courseClass), $fileName);
        } catch (Throwable $e) {
            report($e);

            return back()->with('status', 'Đã xảy ra lỗi trong quá trình tạo file. Vui lòng thử lại.');
        }
    }
}
