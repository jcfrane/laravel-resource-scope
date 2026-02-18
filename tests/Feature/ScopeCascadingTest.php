<?php

use Illuminate\Http\Request;
use JCFrane\ResourceScope\ResourceScopeManager;
use JCFrane\ResourceScope\Support\ScopeResolver;
use JCFrane\ResourceScope\Tests\Fixtures\AttributeParentResource;
use JCFrane\ResourceScope\Tests\Fixtures\ParentResource;

beforeEach(function () {
    $this->manager = app(ResourceScopeManager::class);
    $this->manager->reset();
    app(ScopeResolver::class)->clearCache();
});

it('cascades scope to nested resource via scope mappings', function () {
    $this->manager->setActiveScope('listing');

    $data = [
        'id' => 1,
        'title' => 'Senior Developer',
        'description' => 'A job description',
        'child' => [
            'id' => 10,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'bio' => 'A developer',
            'skills' => ['php'],
            'address' => '123 Main St',
            'secret' => 'hidden',
        ],
    ];

    $resource = new ParentResource($data);
    $result = $resource->toArray(Request::create('/'));

    // Parent should be filtered to listing scope
    expect($result)->toHaveKeys(['id', 'title', 'child']);
    expect($result)->not->toHaveKey('description');

    // Child should be filtered to listing scope (via scopeMappings)
    $child = $result['child']->toArray(Request::create('/'));
    expect($child)->toHaveKeys(['id', 'name', 'email']);
    expect($child)->not->toHaveKeys(['bio', 'skills', 'address', 'secret']);
});

it('returns all parent fields when no scope is active', function () {
    $data = [
        'id' => 1,
        'title' => 'Senior Developer',
        'description' => 'A job description',
        'child' => [
            'id' => 10,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'bio' => 'A developer',
            'skills' => ['php'],
            'address' => '123 Main St',
            'secret' => 'hidden',
        ],
    ];

    $resource = new ParentResource($data);
    $result = $resource->toArray(Request::create('/'));

    expect($result)->toHaveKeys(['id', 'title', 'description', 'child']);
});

it('cascades scope to nested resource via attribute-based mappings', function () {
    $this->manager->setActiveScope('listing');

    $data = [
        'id' => 1,
        'title' => 'Senior Developer',
        'description' => 'A job description',
        'child' => [
            'id' => 10,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'bio' => 'A developer',
            'skills' => ['php'],
            'address' => '123 Main St',
            'secret' => 'hidden',
        ],
    ];

    $resource = new AttributeParentResource($data);
    $result = $resource->toArray(Request::create('/'));

    // Parent should be filtered to listing scope
    expect($result)->toHaveKeys(['id', 'title', 'child']);
    expect($result)->not->toHaveKey('description');

    // Child should be filtered to listing scope (via attribute mappings)
    $child = $result['child']->toArray(Request::create('/'));
    expect($child)->toHaveKeys(['id', 'name', 'email']);
    expect($child)->not->toHaveKeys(['bio', 'skills', 'address', 'secret']);
});
