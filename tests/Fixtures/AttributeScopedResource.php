<?php

namespace JCFrane\ResourceScope\Tests\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JCFrane\ResourceScope\Attributes\ResourceScope;
use JCFrane\ResourceScope\Concerns\HasResourceScope;

#[ResourceScope('listing', fields: ['id', 'name', 'email'])]
#[ResourceScope('detail', fields: ['id', 'name', 'email', 'bio', 'skills', 'address'])]
class AttributeScopedResource extends JsonResource
{
    use HasResourceScope;

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
