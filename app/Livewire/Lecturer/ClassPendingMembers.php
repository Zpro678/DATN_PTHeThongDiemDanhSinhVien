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

        if ($this->courseClass->owner_user_id !== auth()->id()) {
            abort(403);
        }

        // Lấy ID mới nhất lúc khởi tạo
        $this->lastRequestId = \App\Models\ClassJoinRequest::where('class_id', $this->courseClass->id)
            ->where('status', 'pending')
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
            ->where('status', 'pending')
            ->first();

        if ($request) {
            $request->update(['status' => 'approved']);
            
            ClassMember::firstOrCreate([
                'class_id' => $this->courseClass->id,
                'user_id' => $request->user_id,
            ], [
                'student_code' => $request->student_code,
                'full_name' => $request->full_name,
                'status' => 'active',
            ]);

            $this->dispatch('toast', message: 'Đã duyệt học viên ' . $request->full_name . ' thành công.', type: 'success');
        }
    }

    public function reject(int $requestId)
    {
        $request = ClassJoinRequest::where('class_id', $this->courseClass->id)
            ->where('id', $requestId)
            ->where('status', 'pending')
            ->first();

        if ($request) {
            $request->update(['status' => 'rejected']);
            $this->dispatch('toast', message: 'Đã từ chối học viên ' . $request->full_name . '.', type: 'success');
            $this->dispatch('close-modal', 'confirm-reject');
        }
    }

    public function approveAll()
    {
        $requests = ClassJoinRequest::where('class_id', $this->courseClass->id)
            ->where('status', 'pending')
            ->get();

        if ($requests->isNotEmpty()) {
            foreach ($requests as $request) {
                $request->update(['status' => 'approved']);
                ClassMember::firstOrCreate([
                    'class_id' => $this->courseClass->id,
                    'user_id' => $request->user_id,
                ], [
                    'student_code' => $request->student_code,
                    'full_name' => $request->full_name,
                    'status' => 'active',
                ]);
            }
            $this->dispatch('toast', message: 'Đã duyệt tất cả ' . $requests->count() . ' học viên thành công.', type: 'success');
            $this->dispatch('close-modal', 'confirm-approve-all');
        }
    }

    public function render()
    {
        $query = ClassJoinRequest::with('user')
            ->where('class_id', $this->courseClass->id)
            ->where('status', 'pending')
            ->whereDoesntHave('user.classMemberships', function ($q) {
                $q->where('class_id', $this->courseClass->id)->withTrashed();
            });

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('student_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($q2) {
                      $q2->where('email', 'like', '%' . $this->search . '%');
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
