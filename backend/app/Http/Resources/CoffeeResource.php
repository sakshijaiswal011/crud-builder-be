<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoffeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'color_id' => $this->color_id,
            'desc' => $this->desc,
            'name' => $this->name,
            'color' => $this->whenLoaded('color'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
