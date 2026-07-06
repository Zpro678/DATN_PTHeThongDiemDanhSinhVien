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
    public function show(LeaveRequest $leaveRequest, string $filename)
    {
        $user = auth()->user();

        // 1. Kiểm tra file có tồn tại trong đơn này không
        $proofs = $leaveRequest->proof_image ?? [];
        $foundPath = null;
        foreach ($proofs as $path) {
            if (basename($path) === $filename) {
                $foundPath = $path;
                break;
            }
        }

        abort_if(! $foundPath, 404, 'File minh chứng không tồn tại.');

        // 2. Kiểm tra quyền truy cập (Người nộp đơn HOẶC Chủ lớp HOẶC Admin)
        $isOwner = $leaveRequest->classMember && $leaveRequest->classMember->user_id === $user->id;
        $isLecturer = $leaveRequest->classMember &&
                      $leaveRequest->classMember->courseClass &&
                      $leaveRequest->classMember->courseClass->isManagedBy($user->id);
        $isAdmin = $user->isAdmin();

        abort_if(! ($isOwner || $isLecturer || $isAdmin), 403, 'Bạn không có quyền truy cập file này.');

        // 3. Trả về file từ disk local (hoặc public nếu file cũ chưa migrate)
        if (Storage::disk('local')->exists($foundPath)) {
            return response()->file(Storage::disk('local')->path($foundPath));
        } elseif (Storage::disk('public')->exists($foundPath)) {
            return response()->file(Storage::disk('public')->path($foundPath));
        }

        abort(404, 'File không tồn tại trên hệ thống.');
    }
}
