<?php

namespace App\Services;

use App\Models\CourseClass;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\ClassJoinRequest;
use App\Models\AttendanceRecord;

class DashboardStatisticService
{
    private function getClassesLessonProgress(int $userId, ?int $classId = null): array
    {
        return CourseClass::query()
            ->leftJoin('class_sessions', function ($join) {
                $join->on('classes.id', '=', 'class_sessions.class_id')
                    ->where('class_sessions.status', '=', 'closed');
            })
            ->where('classes.owner_user_id', $userId)
            ->when($classId, function ($query) use ($classId) {
                $query->where('classes.id', $classId);
            })
            ->select([
                'classes.id',
                'classes.name',
                'classes.total_sessions',
                'classes.lessons_per_session',
            ])
            ->selectRaw('(classes.total_sessions * classes.lessons_per_session) as required_lessons')
            ->selectRaw('COUNT(class_sessions.id) as closed_sessions')
            ->selectRaw('(COUNT(class_sessions.id) * classes.lessons_per_session) as studied_lessons')
            ->groupBy(
                'classes.id',
                'classes.name',
                'classes.total_sessions',
                'classes.lessons_per_session'
            )
            ->orderBy('classes.name')
            ->get()
            ->map(function ($class) {
                $requiredLessons = (int) $class->required_lessons;
                $studiedLessons = (int) $class->studied_lessons;

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'total_sessions' => (int) $class->total_sessions,
                    'lessons_per_session' => (int) $class->lessons_per_session,
                    'closed_sessions' => (int) $class->closed_sessions,
                    'required_lessons' => $requiredLessons,
                    'studied_lessons' => $studiedLessons,
                    'progress_percent' => $requiredLessons > 0
                        ? round(($studiedLessons / $requiredLessons) * 100, 2)
                        : 0,
                ];
            })
            ->values()
            ->toArray();
    }
    public function getOwnerOverview(int $userId, ?int $classId = null): array
    {
        $ownedClassesQuery = CourseClass::query()
            ->where('owner_user_id',$userId)
            ->when($classId, function ($query) use ($classId){
                $query->where('id',$classId);
            });
        $classIds = (clone $ownedClassesQuery)->pluck('id');

            if ($classIds->isEmpty()) {
                return [
                    'total_students' => 0,
                    'total_required_lessons' => 0,
                    'total_studied_lessons' => 0,
                    'lesson_progress_percent' => 0,
                    'total_present' => 0,
                    'total_absent' => 0,
                    'classes_progress' => [],
                ];
            }
                // 1. Tiến độ từng lớp (Lấy trước để tái sử dụng kết quả, tiết kiệm Query)
                $classesProgress = $this->getClassesLessonProgress($userId, $classId);
    
                // 2. Tính tổng số tiết từ dữ liệu đã lấy ở bước 1 (Loại bỏ hoàn toàn 2 câu Query nặng)
                $totalRequiredLessons = 0;
                $totalStudiedLessons = 0;
    
                foreach ($classesProgress as $classStats) {
                    $totalRequiredLessons += $classStats['required_lessons'];
                    $totalStudiedLessons += $classStats['studied_lessons'];
                }

                // 3. Tỷ lệ tiến độ học
                $lessonProgressPercent = $totalRequiredLessons > 0
                    ? round(($totalStudiedLessons / $totalRequiredLessons) * 100, 2)
                    : 0;

                // 4. Tổng sinh viên
                $totalStudents = ClassMember::query()
                    ->whereIn('class_id', $classIds)
                    ->where('status', 'active')
                    ->count();

                // 5. Tổng có mặt / vắng theo bản ghi điểm danh
                $attendance = AttendanceRecord::query()
                    ->join('class_sessions', 'attendance_records.class_session_id', '=', 'class_sessions.id')
                    ->whereIn('class_sessions.class_id', $classIds)
                    ->where('class_sessions.status', 'closed')
                    ->selectRaw("
                        SUM(CASE WHEN attendance_records.status IN ('present', 'late') THEN 1 ELSE 0 END) AS total_present,
                        SUM(CASE WHEN attendance_records.status IN ('absent', 'excused') THEN 1 ELSE 0 END) AS total_absent
                    ")
                    ->first();

                $totalPresent = (int) ($attendance->total_present ?? 0);
                $totalAbsent = (int) ($attendance->total_absent ?? 0);

                return [
                    'total_students' => $totalStudents,
                    'total_required_lessons' => $totalRequiredLessons,
                    'total_studied_lessons' => $totalStudiedLessons,
                    'lesson_progress_percent' => $lessonProgressPercent,
                    'total_present' => $totalPresent,
                    'total_absent' => $totalAbsent,
                    'classes_progress' => $classesProgress,
                ];
    
        }
}
