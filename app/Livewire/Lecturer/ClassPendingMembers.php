<?php

namespace App\Livewire\Lecturer;

use App\Models\CourseClass;
use App\Models\ClassJoinRequest;
use App\Models\ClassMember;
use Livewire\Component;

class ClassPendingMembers extends Component
{
    public CourseClass $courseClass;
    public string $search = '';

    public ?int $rejectingId = null;
    public ?string $rejectingName = null;

    public ?int $lastRequestId = null;

    public function mount(CourseClass $courseClass)
    {
        $this->courseClass = $courseClass;

        if (! $this->courseClass->isManagedBy(auth()->id())) {
            abort(403);
        }

        // Lấy ID mới nhất lúc khởi tạo
        $this->lastRequestId = \App\Models\ClassJoinRequest::where('class_id', $this->courseClass->id)
            ->whereIn('status', [ClassJoinRequest::STATUS_PENDING, 'pending'])
            ->max('id');
    }

    public function updatedSearch()
    {
        // Khi search không cần resetPage nữa vì không còn dùng phân trang
    }

    public function confirmReject(int $id, string $name)
    {
        $this->rejectingId = $id;
        $this->rejectingName = $name;
        $this->dispatch('open-modal', 'confirm-reject');
    }

    public function approve(int $requestId)
    {
        $request = ClassJoinRequest::where('class_id', $this->courseClass->id)
            ->where('id', $requestId)
            ->whereIn('status', [ClassJoinRequest::STATUS_PENDING, 'pending'])
            ->first();

        if ($request) {
            $request->update(['status' => ClassJoinRequest::STATUS_APPROVED]);
            
            $member = ClassMember::withTrashed()->firstOrCreate([
                'class_id' => $this->courseClass->id,
                'user_id' => $request->user_id,
            ], [
                'status' => ClassMember::STATUS_ACTIVE,
            ]);

            if ($member->trashed()) {
                $member->restore();
            }

            $member->update(['status' => ClassMember::STATUS_ACTIVE, 'status_changed_at' => null]);
            $member->syncProfile([
                'full_name' => $request->user?->name,
                'email' => $request->user?->email,
            ]);

            if ($request->user) {
                $request->user->notify(new \App\Notifications\ClassJoinedNotification($this->courseClass));
            }

            $this->dispatch('toast', message: 'Đã duyệt học viên ' . ($request->user?->name ?? 'này') . ' thành công.', type: 'success');
        }
    }

    public function reject(int $requestId)
    {
        $request = ClassJoinRequest::where('class_id', $this->courseClass->id)
            ->where('id', $requestId)
            ->whereIn('status', [ClassJoinRequest::STATUS_PENDING, 'pending'])
            ->first();

        if ($request) {
            $request->update(['status' => ClassJoinRequest::STATUS_REJECTED]);
            
            if ($request->user) {
                $request->user->notify(new \App\Notifications\ClassJoinRejectedNotification($this->courseClass));
            }

            $this->dispatch('toast', message: 'Đã từ chối học viên ' . ($request->user?->name ?? 'này') . '.', type: 'success');
            $this->dispatch('close-modal', 'confirm-reject');
        }
    }

    public function approveAll()
    {
        $requests = ClassJoinRequest::where('class_id', $this->courseClass->id)
            ->whereIn('status', [ClassJoinRequest::STATUS_PENDING, 'pending'])
            ->get();

        if ($requests->isNotEmpty()) {
            foreach ($requests as $request) {
                $request->update(['status' => ClassJoinRequest::STATUS_APPROVED]);
                $member = ClassMember::withTrashed()->firstOrCreate([
                    'class_id' => $this->courseClass->id,
                    'user_id' => $request->user_id,
                ], [
                    'status' => ClassMember::STATUS_ACTIVE,
                ]);

                if ($member->trashed()) {
                    $member->restore();
                }

                $member->update(['status' => ClassMember::STATUS_ACTIVE, 'status_changed_at' => null]);
                $member->syncProfile([
                    'full_name' => $request->user?->name,
                    'email' => $request->user?->email,
                ]);

                if ($request->user) {
                    $request->user->notify(new \App\Notifications\ClassJoinedNotification($this->courseClass));
                }
            }
            $this->dispatch('toast', message: 'Đã duyệt tất cả ' . $requests->count() . ' học viên thành công.', type: 'success');
            $this->dispatch('close-modal', 'confirm-approve-all');
        }
    }

    public function render()
    {
        $query = ClassJoinRequest::with('user')
            ->where('class_id', $this->courseClass->id)
            ->whereIn('status', [ClassJoinRequest::STATUS_PENDING, 'pending'])
            ->whereDoesntHave('user.classMemberships', function ($q) {
                $q->where('class_id', $this->courseClass->id)->withTrashed();
            });

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->whereHas('user', function($q2) {
                      $q2->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Kiểm tra xem có yêu cầu nào mới không (id lớn hơn lastRequestId)
        $currentMaxId = (clone $query)->max('id');
        if ($this->lastRequestId !== null && $currentMaxId > $this->lastRequestId) {
            $this->dispatch('toast', message: 'Có học viên mới vừa gửi yêu cầu tham gia lớp!', type: 'success');
        }
        
        if ($currentMaxId !== null) {
            $this->lastRequestId = $currentMaxId;
        }

        $pendingMembers = $query->latest()->get();

        return view('livewire.lecturer.class-pending-members', [
            'pendingMembers' => $pendingMembers
        ])->layout('layouts.user', ['title' => 'Duyệt học viên: ' . $this->courseClass->name]);
    }
}
