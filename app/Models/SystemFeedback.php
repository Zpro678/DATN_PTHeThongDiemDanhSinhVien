<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemFeedback extends Model
{
    protected $table = 'system_feedbacks';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'content',
        'attachment_path',
        'status',
        'admin_reply',
        'replied_by',
        'replied_at',
    ];

    protected $casts = [
        'attachment_path' => 'array',
        'replied_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replier()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
