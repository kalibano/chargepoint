<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessChargingSession implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    private string $stationId;
    private string $driverToken;
    private string $callbackUrl;

    public function __construct(string $stationId, string $driverToken, string $callbackUrl)
    {
        $this->stationId = $stationId;
        $this->driverToken = $driverToken;
        $this->callbackUrl = $callbackUrl;
    }

    public function handle(): void
    {
        //  calling an internal authorization service
        $status = $this->callAuthorizationService($this->driverToken);

        // Send callback
        $this->sendCallback($this->callbackUrl, [
            'station_id' => $this->stationId,
            'driver_token' => $this->driverToken,
            'status' => $status,
        ]);
    }

    private function callAuthorizationService(string $driverToken): string
    {
        try {
            //  HTTP call to an internal service
            $response = Http::timeout(5)->post('http://internal-auth-service/check', [
                'driver_token' => $driverToken,
            ]);

            return $response->json('status');

        } catch (\Exception $e) {
            Log::error('Authorization service call failed: ' . $e->getMessage());
        }

        // Default to 'unknown' if the service does not respond within the timeout
        return 'unknown';
    }

    private function sendCallback(string $callbackUrl, array $decision): void
    {
        try {
            $response = Http::post($callbackUrl, $decision);

            if ($response->failed()) {
                Log::error('Callback failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Callback URL request failed: ' . $e->getMessage());
        }
    }
}