<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "title" => $this->title,
            "name" => substr_replace($this->original_name, "", -4),
            "path" => $this->path,
            "stream_url" => route('stream', $this->path),
            "poster_url" => url("/storage/$this->poster_path"),
            "thumbnail_url" => url("/storage/$this->thumbnail_path"),
            "created_at" => $this->created_at
        ];
    }
}
