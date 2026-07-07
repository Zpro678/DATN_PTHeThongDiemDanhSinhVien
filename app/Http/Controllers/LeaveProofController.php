<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveProofController extends Controller
{
    /**
     * Display or download the leave request proof file.
     */
    public function show(LeaveRequest $leaveRequest, string $filename, \App\Services\ProofSecurityService $proofSecurityService)
    {
        $user = auth()->user();

        // 1. Kiểm tra quyền truy cập (Người nộp đơn HOẶC Chủ lớp HOẶC Admin)
        $isOwner = $leaveRequest->classMember && $leaveRequest->classMember->user_id === $user->id;
        $isLecturer = $leaveRequest->classMember &&
                      $leaveRequest->classMember->courseClass &&
                      $leaveRequest->classMember->courseClass->isManagedBy($user->id);
        $isAdmin = $user->isAdmin();

        abort_if(! ($isOwner || $isLecturer || $isAdmin), 403, 'Bạn không có quyền truy cập file này.');

        // 2. Lấy URL tạm thời từ S3 hoặc đường dẫn local
        $pathOrUrl = $proofSecurityService->generateTemporaryUrl($leaveRequest, $filename);

        // 3. Trả về kết quả
        if (str_starts_with($pathOrUrl, 'http')) {
            // S3 URL
            return redirect()->away($pathOrUrl);
        } else {
            // Local path
            return response()->file($pathOrUrl);
        }
    }
}
