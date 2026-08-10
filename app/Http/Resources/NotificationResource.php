<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [

            'id' => $this->id,

            'title' => $this->title,

            'body' => $this->body,

            'module' => $this->module,

            'type' => $this->type,

            'data' => $this->data,

            'read_at' => $this->read_at,

            'is_read' => !is_null($this->read_at),

            'created_at' => $this->created_at,

            'time_ago' => $this->created_at->diffForHumans(),

            'creator' => $this->creator
                ? [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ]
                : null,
        ];
    }
}
