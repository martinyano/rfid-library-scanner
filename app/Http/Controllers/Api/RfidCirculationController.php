<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CirculationException;
use App\Http\Controllers\Controller;
use App\Services\RfidCirculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidCirculationController extends Controller
{
    public function __invoke(Request $request, RfidCirculationService $circulation): JsonResponse
    {
        $validated = $request->validate([
            'rfid_uid' => ['required', 'string', 'max:100'],
            'device_id' => ['nullable', 'string', 'max:60'],
        ]);

        try {
            $result = $circulation->scan(
                $validated['rfid_uid'],
                $validated['device_id'] ?? null,
                $request->ip(),
                $request->userAgent()
            );
        } catch (CirculationException $exception) {
            return response()->json([
                'status' => 'error',
                'action' => null,
                'message' => $exception->getMessage(),
            ], $exception->statusCode());
        }

        return response()->json($result);
    }
}
