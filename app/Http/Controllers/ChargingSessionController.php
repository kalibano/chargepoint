<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ProcessChargingSession;

class ChargingSessionController extends Controller
{
    public function start(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|uuid',
            'driver_token' => 'required|string|min:20|max:80|regex:/^[A-Za-z0-9\-._~]+$/',
            'callback_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 400);
        }

        // Dispatch the job to RabbitMQ
        ProcessChargingSession::dispatch(
            $request->input('station_id'),
            $request->input('driver_token'),
            $request->input('callback_url')
        );

        // Respond with acknowledgment
        return response()->json([
            'status' => 'accepted',
            'message' => 'Request is being processed asynchronously. The result will be sent to the provided callback URL.',
        ]);
    }
}