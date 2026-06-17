<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\User::with('roles')->latest();

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.accounts.index', compact('users'));
    }

    public function show(\App\Models\User $user)
    {
        $user->loadMissing(['roles', 'ownedClasses', 'joinedClasses', 'tenant.package']);
        
        $stats = [
            'owned_classes_count' => $user->ownedClasses()->count(),
            'joined_classes_count' => $user->joinedClasses()->count(),
            'total_attendance' => 0, // Placeholder if needed
        ];

        return view('admin.accounts.show', compact('user', 'stats'));
    }

    public function edit(\App\Models\User $user)
    {
        $roles = \Spatie\Permission\Models\Role::all();
        return view('admin.accounts.edit', compact('user', 'roles'));
    }

    public function update(Request $request, \App\Models\User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'status' => 'required|in:active,blocked',
            'role' => 'required|string|exists:roles,name',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($user->id === auth()->id() && $validated['status'] === 'blocked') {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình.');
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.accounts.index')->with('success', 'Cập nhật thông tin tài khoản thành công.');
    }

    public function toggleStatus(\App\Models\User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình.');
        }

        $user->status = $user->status === 'active' ? 'blocked' : 'active';
        $user->save();

        $message = $user->status === 'active' ? 'Đã mở khóa tài khoản thành công.' : 'Đã khóa tài khoản thành công.';
        return back()->with('success', $message);
    }
}
