<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassMemberProfile extends Model
{
    use HasFactory;

    protected $table = 'class_member_profiles';

    protected $fillable = [
        'class_member_id', // ID thành viên lớp (1-1).
        'student_code', // MSSV khai báo/import.
        'full_name', // Họ tên SV.
        'email', // Email SV.
    ];

    public function classMember(): BelongsTo
    {
        return $this->belongsTo(ClassMember::class);
    }
}
