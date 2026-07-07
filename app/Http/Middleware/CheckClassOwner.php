<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseClass;
use Symfony\Component\HttpFoundation\Response;

class CheckClassOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Try to find the class ID in the route parameters
        $classId = $request->route('classId') ?? $request->route('courseClass');
        
        if ($classId) {
            $id = $classId instanceof CourseClass ? $classId->id : $classId;
            $classroom = CourseClass::findOrFail($id);
            
            if ($classroom->owner_user_id !== Auth::id()) {
                abort(403, 'Bạn không có quyền quản trị lớp học này.');
            }
        }
        
        return $next($request);
    }
}
