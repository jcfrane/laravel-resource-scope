<?php

namespace JCFrane\ResourceScope\Tests\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JCFrane\ResourceScope\Attributes\ResourceScope;
use JCFrane\ResourceScope\Concerns\HasResourceScope;

#[ResourceScope('listing', fields: ['id', 'title', 'child'], mappings: [
    MethodScopedResource::class => 'listing',
])]
class AttributeParentResource extends JsonResource
{
    use HasResourceScope;

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
