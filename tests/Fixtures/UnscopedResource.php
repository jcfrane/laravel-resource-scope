<?php

namespace JCFrane\ResourceScope\Tests\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JCFrane\ResourceScope\Concerns\HasResourceScope;

class UnscopedResource extends JsonResource
{
    use HasResourceScope;

    public function toArray(Request $request): array
    {
        return $this->scoped([
            'id' => $this->resource['id'],
            'name' => $this->resource['name'],
            'email' => $this->resource['email'],
        ]);
    }
}
