<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id', // ID người thực hiện hành động.
        'class_id', // ID lớp học liên quan đến hành động.
        'action', // Loại hành động được ghi log.
        'table_name', // Tên bảng vật lý bị tác động.
        'row_id', // ID bản ghi bị thay đổi.
        'old_values', // Dữ liệu cũ trước khi thay đổi.
        'new_values', // Dữ liệu mới hoặc payload chi tiết của sự kiện.
        'ip_address', // IP của người thực hiện hành động.
        'user_agent', // Thông tin trình duyệt/thiết bị thực hiện hành động.
        'created_at', // Thời điểm ghi log bất biến.
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array', // Ép kiểu dữ liệu cũ dạng JSON.
            'new_values' => 'array', // Ép kiểu dữ liệu mới dạng JSON.
            'created_at' => 'datetime', // Ép kiểu thời điểm ghi log.
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    /**
     * Ánh xạ action key -> câu tiếng Việt rõ ràng ("đã làm gì"), để nhật ký admin đọc ra ngay
     * người dùng đã làm gì thay vì hiện raw key hay "đã cập nhật bản ghi" chung chung.
     *
     * Danh sách bám theo các action đang được ghi thực tế qua AuditLogService/SaveAuditLogJob.
     */
    public function actionLabel(): string
    {
        $key = strtolower(trim((string) $this->action));

        $map = [
            // Tài khoản & đăng nhập
            'login' => 'đã đăng nhập vào hệ thống',
            'login_success' => 'đã đăng nhập thành công',
            'login_failed' => 'đăng nhập thất bại',
            'logout' => 'đã đăng xuất khỏi hệ thống',
            'password_changed' => 'đã đổi mật khẩu',
            // Quản trị người dùng
            'user_created' => 'đã tạo mới người dùng',
            'user_edited' => 'đã cập nhật thông tin người dùng',
            'user_deleted' => 'đã xóa người dùng',
            'user_status_changed' => 'đã thay đổi trạng thái người dùng',
            // Lớp học
            'class_created' => 'đã tạo lớp học mới',
            'class_joined' => 'đã tham gia lớp học',
            'classes_archived_downgrade' => 'đã lưu trữ bớt lớp khi hạ gói',
            // Điểm danh
            'session_created' => 'đã tạo phiên điểm danh',
            'session_closed' => 'đã đóng phiên điểm danh',
            'attendance_check_in' => 'đã điểm danh',
            'attendance_updated' => 'đã cập nhật điểm danh',
            'manual_attendance' => 'đã điểm danh thủ công',
            // Đơn xin nghỉ phép
            'leave_request_submitted' => 'đã gửi đơn xin nghỉ phép',
            'leave_request_edited' => 'đã chỉnh sửa đơn xin nghỉ phép',
            'leave_request_approved' => 'đã duyệt đơn xin nghỉ phép',
            'leave_request_rejected' => 'đã từ chối đơn xin nghỉ phép',
            'duyệt đơn xin phép' => 'đã duyệt đơn xin nghỉ phép',
            'từ chối đơn xin phép' => 'đã từ chối đơn xin nghỉ phép',
            // Khác
            'feedback_submitted' => 'đã gửi phản hồi/góp ý',
            'subscription_upgraded' => 'đã nâng cấp gói dịch vụ',
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        // Action chung created/updated/deleted (từ trait Auditable cũ): kèm tên bảng thân thiện
        // để không còn "đã cập nhật bản ghi" trống nghĩa.
        $verbs = ['created' => 'đã tạo mới', 'updated' => 'đã cập nhật', 'deleted' => 'đã xóa'];
        if (isset($verbs[$key])) {
            $table = $this->tableLabel();
            return $table ? $verbs[$key] . ' ' . $table : $verbs[$key] . ' bản ghi';
        }

        // Không rõ -> thay '_' bằng khoảng trắng cho dễ đọc, tránh phơi raw key.
        return $this->action ? str_replace('_', ' ', (string) $this->action) : 'đã thực hiện thao tác';
    }

    /**
     * Tên bảng vật lý -> tên nghiệp vụ tiếng Việt để hiển thị (badge, tiêu đề modal).
     * Trả null nếu không có ánh xạ (để nơi gọi tự fallback về tên bảng thô).
     */
    public function tableLabel(): ?string
    {
        $map = [
            'users' => 'người dùng',
            'classes' => 'lớp học',
            'course_classes' => 'lớp học',
            'class_members' => 'thành viên lớp',
            'class_meetings' => 'buổi học',
            'class_sessions' => 'phiên điểm danh',
            'attendance_records' => 'bản ghi điểm danh',
            'leave_requests' => 'đơn xin nghỉ phép',
            'coupons' => 'mã giảm giá',
            'plans' => 'gói dịch vụ',
            'transactions' => 'giao dịch',
            'settings' => 'cấu hình hệ thống',
        ];

        return $map[$this->table_name] ?? null;
    }
}
