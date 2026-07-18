<?php

namespace App\Policies;

use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Quyền quản trị lớp học.
 *
 * Chia làm 2 mức:
 *  - Chủ chính HOẶC đồng chủ  → xem & sửa cấu hình lớp.
 *  - Chỉ chủ chính            → xóa lớp, thêm/gỡ đồng chủ.
 *
 * Laravel 11 tự nhận policy này cho model CourseClass theo quy ước tên,
 * không cần đăng ký thêm ở provider.
 */
class CourseClassPolicy
{
    /** Mở trang cài đặt lớp. */
    public function view(User $user, CourseClass $courseClass): Response
    {
        return $courseClass->isManagedBy($user->id)
            ? Response::allow()
            : Response::deny('Bạn không có quyền quản trị lớp học này.');
    }

    /** Sửa cấu hình lớp (thông tin chung, điểm danh, mã tham gia, trạng thái). */
    public function update(User $user, CourseClass $courseClass): Response
    {
        return $courseClass->isManagedBy($user->id)
            ? Response::allow()
            : Response::deny('Bạn không có quyền chỉnh sửa cài đặt lớp này.');
    }

    /** Xóa lớp — thao tác không hoàn tác được nên giữ riêng cho chủ chính. */
    public function delete(User $user, CourseClass $courseClass): Response
    {
        return $courseClass->isPrimaryOwner($user->id)
            ? Response::allow()
            : Response::deny('Chỉ chủ chính của lớp mới được xóa lớp học.');
    }

    /** Thêm / gỡ đồng chủ — chỉ chủ chính, tránh đồng chủ tự gỡ nhau. */
    public function manageCoOwners(User $user, CourseClass $courseClass): Response
    {
        return $courseClass->isPrimaryOwner($user->id)
            ? Response::allow()
            : Response::deny('Chỉ chủ chính của lớp mới được quản lý đồng chủ.');
    }
}
