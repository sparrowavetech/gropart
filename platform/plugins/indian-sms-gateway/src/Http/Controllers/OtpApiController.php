<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class OtpApiController
{
    public function request(Request $request, OtpService $otp): JsonResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:30'], 'purpose' => ['nullable', 'string', 'max:60']]);
        try {
            $record = $otp->request($data['phone'], $data['purpose'] ?? 'otp', $request->ip() ?: 'unknown');
            return response()->json(['success' => true, 'message' => 'OTP sent.', 'request_id' => $record->uuid, 'expires_at' => $record->expires_at->toIso8601String()]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function verify(Request $request, OtpService $otp): JsonResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:30'], 'code' => ['required', 'string', 'min:4', 'max:8'], 'purpose' => ['nullable', 'string', 'max:60']]);
        $verified = $otp->verify($data['phone'], $data['code'], $data['purpose'] ?? 'otp');
        return response()->json(['success' => $verified, 'message' => $verified ? 'Phone number verified.' : 'Invalid or expired OTP.'], $verified ? 200 : 422);
    }
}
