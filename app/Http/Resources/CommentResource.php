<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'content_comment' => $this->content_comment,
            'commentator'     => new UserResource($this->commentator),
            'created_at'      => $this->created_at->format('d/m/Y H:i'),
        ];
    }
}