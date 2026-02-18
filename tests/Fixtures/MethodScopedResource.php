<?php

namespace JCFrane\ResourceScope\Tests\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JCFrane\ResourceScope\Concerns\HasResourceScope;

class MethodScopedResource extends JsonResource
{
    use HasResourceScope;

    protected function scopeDefinitions(): array
    {
        return [
            'listing' => ['id', 'name', 'email'],
            'detail' => ['id', 'name', 'email', 'bio', 'skills', 'address'],
        ];
    }

    public function toArray(Request $request): array
    {
        return $this->scoped([
            'id' => $this->resource['id'],
            'name' => $this->resource['name'],
            'email' => $this->resource['email'],
            'bio' => $this->resource['bio'] ?? null,
            'skills' => $this->resource['skills'] ?? [],
            'address' => $this->resource['address'] ?? null,
            'secret' => $this->resource['secret'] ?? null,
        ]);
    }
}
