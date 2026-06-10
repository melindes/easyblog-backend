<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'content'     => $this->content,
            'visibility'  => $this->visibility,
            'count_view'  => $this->count_view,
            'likes_count' => $this->likes->count(),
            'author'      => new UserResource($this->author),
            'comments'    => CommentResource::collection($this->comments),
            'likes'       => LikeResource::collection($this->likes),
            'created_at'  => $this->created_at->format('d/m/Y H:i'),
            'updated_at'  => $this->updated_at->format('d/m/Y H:i'),
        ];
    }
}