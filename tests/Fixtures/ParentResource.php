<?php

namespace JCFrane\ResourceScope\Tests\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JCFrane\ResourceScope\Concerns\HasResourceScope;

class ParentResource extends JsonResource
{
    use HasResourceScope;

    protected function scopeDefinitions(): array
    {
        return [
            'listing' => ['id', 'title', 'child'],
        ];
    }

    protected function scopeMappings(): array
    {
        return [
            'listing' => [
                MethodScopedResource::class => 'listing',
            ],
        ];
    }

    public function toArray(Request $request): array
    {
        return $this->scoped([
            'id' => $this->resource['id'],
            'title' => $this->resource['title'],
            'description' => $this->resource['description'] ?? null,
            'child' => new MethodScopedResource($this->resource['child'] ?? []),
        ]);
    }
}
