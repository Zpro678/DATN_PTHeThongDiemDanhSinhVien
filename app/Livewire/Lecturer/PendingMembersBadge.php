<?php

namespace App\Livewire\Lecturer;

use Livewire\Component;

class PendingMembersBadge extends Component
{
    public $classId;

    public function mount($classId)
    {
        $this->classId = $classId;
    }

    public function render()
    {
        $pendingCount = \App\Models\ClassJoinRequest::where('class_id', $this->classId)
            ->whereIn('status', [\App\Models\ClassJoinRequest::STATUS_PENDING, 'pending'])
            ->count();

        return view('livewire.lecturer.pending-members-badge', [
            'pendingCount' => $pendingCount
        ]);
    }
}
