<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Notification;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;

class NotificationController extends ApiController
{
    public function index(Request $request)
    {
        $notifications = Notification::query()
            ->where('user_id', auth()->id())
            ->with('creator:id,name')
            ->latest()
            ->paginate(20);

        return NotificationResource::collection($notifications);
    }

    public function unreadCount()
    {
        $count = Notification::query()
            ->where('user_id', auth()->id())
            ->unread()
            ->count();

        return response()->json([
            'count' => $count
        ]);
    }

    // marcar como leida
    public function markAsRead(Notification $notification){
        abort_unless(
            $notification->user_id === auth()->id(),
            403
        );

        if(!$notification->read_at){
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return new NotificationResource($notification->fresh('creator'));
    }

    // test
    public function test()
{
    NotificationService::send(
        user: auth()->user(),
        payload: [
            'created_by' => auth()->id(),
            'module' => 'tracking',
            'type' => 'test',
            'title' => 'Prueba',
            'body' => 'Notificación de prueba.',
            'data' => [
                'type' => 'tracking',
                'subtype' => 'formalizado'
            ],
        ]
    );

    return response()->json([
        'message' => 'Notificación enviada.'
    ]);
}
}
