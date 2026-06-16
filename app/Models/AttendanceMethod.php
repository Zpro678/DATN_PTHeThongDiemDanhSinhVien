<?php

namespace App\Models;

use Database\Factories\AttendanceMethodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceMethod extends Model
{
    /** @use HasFactory<AttendanceMethodFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'method_id');
    }
}
