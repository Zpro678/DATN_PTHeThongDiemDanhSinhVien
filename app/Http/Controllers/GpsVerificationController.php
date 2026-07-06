<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\ClassMember;
use App\Services\GpsValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GpsVerificationController extends Controller
{
    public function issueToken(Request $request, GpsValidationService $service): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|integer|exists:class_sessions,id',
            'member_id' => 'required|integer|exists:class_members,id',
        ]);

        $session = ClassSession::findOrFail($validated['session_id']);
        $member = ClassMember::findOrFail($validated['member_id']);

        $verification = $service->issueVerificationToken($session, $member, $request->ip());

        return response()->json([
            'success' => true,
            'verification_token' => $verification->token,
        ]);
    }

    public function verify(Request $request, GpsValidationService $service): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|size:64',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'accuracy' => 'required|numeric',
            'altitude' => 'nullable|numeric',
            'altitude_accuracy' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
            'samples' => 'nullable|array|max:10',
            'samples.*.lat' => 'required_with:samples|numeric',
            'samples.*.lng' => 'required_with:samples|numeric',
        ]);

        // Tín hiệu bổ sung để chấm nghi vấn fake GPS (altitude gửi kèm để phân biệt null thật).
        $signals = [
            'altitude' => $request->input('altitude'),
            'altitude_accuracy' => $request->input('altitude_accuracy'),
            'speed' => $request->input('speed'),
            'heading' => $request->input('heading'),
            'samples' => $validated['samples'] ?? [],
        ];

        $result = $service->verifyLocation(
            $validated['token'],
            (float) $validated['lat'],
            (float) $validated['lng'],
            (float) $validated['accuracy'],
            $request->ip(),
            $signals,
        );

        return response()->json($result);
    }
}
