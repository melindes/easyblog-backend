<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LikeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'liker' => new UserResource($this->liker),
        ];
    }
}