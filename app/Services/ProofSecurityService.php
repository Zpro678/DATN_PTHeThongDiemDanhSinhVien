<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Storage;

class ProofSecurityService
{
    /**
     * Generate a presigned temporary URL for a proof image.
     * Fallback to local route if S3 is not configured.
     *
     * @param LeaveRequest $leaveRequest
     * @param string $filename
     * @param int $minutes
     * @return string
     */
    public function generateTemporaryUrl(LeaveRequest $leaveRequest, string $filename, int $minutes = 5): string
    {
        $proofs = $leaveRequest->proof_image ?? [];
        $foundPath = null;
        foreach ($proofs as $path) {
            if (basename($path) === $filename) {
                $foundPath = $path;
                break;
            }
        }

        if (!$foundPath) {
            abort(404, 'File minh chứng không tồn tại.');
        }

        // Try S3 first if it exists there
        try {
            if (config('filesystems.disks.s3-private.key') && Storage::disk('s3-private')->exists($foundPath)) {
                return Storage::disk('s3-private')->temporaryUrl(
                    $foundPath,
                    now()->addMinutes($minutes)
                );
            }
        } catch (\Exception $e) {
            // Ignore exception if S3 is not configured properly and fallback to local
        }

        // Fallback for local files
        if (Storage::disk('local')->exists($foundPath)) {
            return Storage::disk('local')->path($foundPath);
        } elseif (Storage::disk('public')->exists($foundPath)) {
            return Storage::disk('public')->path($foundPath);
        }

        abort(404, 'File không tồn tại trên hệ thống.');
    }
}
