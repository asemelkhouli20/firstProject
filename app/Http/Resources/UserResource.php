<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            $this->merge(
                Arr::only(
                    $this->resource->toArray(),
                    ['id', 'name', 'profile_photo_url']
                )
            ),
        ];
    }
}
