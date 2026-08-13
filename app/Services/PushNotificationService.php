<?php

namespace App\Services;

use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public static function send(
        User $user,
        string $title,
        string $body,
        array $data = []
    ): void {

        $devices = $user
            ->devices()
            ->whereNotNull('expo_token')
            ->get();

        if ($devices->isEmpty()) {
            return;
        }

        foreach ($devices as $device) {
            self::sendToDevice(
                $device,
                $title,
                $body,
                $data
            );
        }
    }

    private static function sendToDevice(
        Device $device,
        string $title,
        string $body,
        array $data = []
    ): void {

        try {

            $response = Http::post(
                config('services.expo.url'),
                [
                    'to' => $device->expo_token,

                    'title' => $title,

                    'body' => $body,

                    'sound' => 'default',

                    'data' => $data,
                ]
            );

            $json = $response->json();

            Log::info('Expo Push', [
                'device_id' => $device->id,
                'status' => $response->status(),
                'response' => $json,
            ]);

            // Token inválido
            if (
                isset($json['data']['details']['error']) &&
                $json['data']['details']['error'] === 'DeviceNotRegistered'
            ) {

                Log::warning(
                    "Token inválido eliminado: {$device->expo_token}"
                );

                $device->delete();
            }

        } catch (\Throwable $e) {

            Log::error(
                'Error enviando Push Notification',
                [
                    'device_id' => $device->id,
                    'message' => $e->getMessage(),
                ]
            );

        }
    }
}