<?php

namespace App\Livewire\Lecturer\Attendance\Concerns;

use App\Models\ClassSession;
use App\Models\CourseClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait OwnsAttendanceSessions
{
    private function ownedClasses(): Collection
    {
        return CourseClass::query()
            ->where('owner_user_id', auth()->id())
            ->orderBy('name')
            ->get();
    }

    private function ownedClass(int $classId): CourseClass
    {
        return CourseClass::query()
            ->where('owner_user_id', auth()->id())
            ->findOrFail($classId);
    }

    private function ownedSession(int $sessionId): ClassSession
    {
        return ClassSession::query()
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->findOrFail($sessionId);
    }
}
