<?php

namespace JCFrane\ResourceScope\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ResourceScope
{
    /**
     * @param  string  $name  The scope name (e.g. 'listing', 'detail')
     * @param  array<string>  $fields  The fields included in this scope
     * @param  array<class-string, string>  $mappings  Nested resource scope mappings (e.g. [UserResource::class => 'summary'])
     */
    public function __construct(
        public string $name,
        public array $fields = [],
        public array $mappings = [],
    ) {}
}
