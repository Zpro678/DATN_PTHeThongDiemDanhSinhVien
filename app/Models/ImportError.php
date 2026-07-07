<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_token',  // Mã phiên import (gom các lỗi cùng một lần import file danh sách)
        'row_index',     // Số thứ tự dòng trong file bị lỗi, để người dùng biết dòng nào cần sửa
        'error_message', // Nội dung lỗi cụ thể của dòng đó (ví dụ: thiếu MSSV, email sai định dạng)
    ];
}
