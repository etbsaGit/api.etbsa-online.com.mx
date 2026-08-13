<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use InvalidArgumentException;

class NotificationService
{
    /**
     * Crea una notificación y opcionalmente envía una Push.
     */
    public static function send(
        User $user,
        array $payload
    ): Notification {

        self::validatePayload($payload);

        $notification = Notification::create([
            'user_id'    => $user->id,
            'created_by' => $payload['created_by'] ?? null,
            'module'     => $payload['module'],
            'type'       => $payload['type'],
            'title'      => $payload['title'],
            'body'       => $payload['body'],
            'data'       => $payload['data'] ?? null,
        ]);

        if ($payload['send_push'] ?? true) {
            PushNotificationService::send(
                user: $user,
                title: $payload['title'],
                body: $payload['body'],
                data: $payload['data'] ?? []
            );
        }

        return $notification;
    }

    /**
     * Envía la misma notificación a varios usuarios.
     */
    public static function sendMany(
        iterable $users,
        array $payload
    ): void {

        foreach ($users as $user) {

            if (! $user instanceof User) {
                continue;
            }

            self::send(
                user: $user,
                payload: $payload
            );
        }
    }

    /**
     * Valida que el payload tenga los campos mínimos necesarios.
     */
    private static function validatePayload(array $payload): void
    {
        $required = [
            'module',
            'type',
            'title',
            'body',
        ];

        foreach ($required as $field) {
            if (! array_key_exists($field, $payload)) {
                throw new InvalidArgumentException(
                    "El campo '{$field}' es obligatorio en el payload de la notificación."
                );
            }
        }
    }
}