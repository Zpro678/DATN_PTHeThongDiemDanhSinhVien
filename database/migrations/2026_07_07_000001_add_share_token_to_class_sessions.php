<?php

use App\Models\ClassSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // share_token: token ỔN ĐỊNH của phiên dùng cho LINK chia sẻ (không xoay như qr_token).
        // Link sống suốt lúc phiên còn mở; QR ảnh vẫn xoay theo qr_token để chống chụp màn hình.
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('token_expires_at');
        });

        // Cấp token ổn định cho các phiên đã có (để link cũ vẫn dùng được).
        ClassSession::withTrashed()->whereNull('share_token')->get()->each(function (ClassSession $session) {
            $session->forceFill(['share_token' => ClassSession::generateShareToken()])->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropUnique(['share_token']);
            $table->dropColumn('share_token');
        });
    }
};
