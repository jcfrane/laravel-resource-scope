<?php

use Illuminate\Http\Request;
use JCFrane\ResourceScope\ResourceScopeManager;
use JCFrane\ResourceScope\Tests\Fixtures\AttributeScopedResource;
use JCFrane\ResourceScope\Tests\Fixtures\MethodScopedResource;
use JCFrane\ResourceScope\Tests\Fixtures\UnscopedResource;

beforeEach(function () {
    $this->manager = app(ResourceScopeManager::class);
    $this->manager->reset();

    $this->testData = [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'bio' => 'A developer',
        'skills' => ['php', 'laravel'],
        'address' => '123 Main St',
        'secret' => 'should-not-appear',
    ];
});

it('returns all fields when no scope is active', function () {
    $resource = new MethodScopedResource($this->testData);
    $result = $resource->toArray(Request::create('/'));

    expect($result)->toHaveKeys(['id', 'name', 'email', 'bio', 'skills', 'address', 'secret']);
});

it('filters fields when listing scope is active (method-based)', function () {
    $this->manager->setActiveScope('listing');

    $resource = new MethodScopedResource($this->testData);
    $result = $resource->toArray(Request::create('/'));

    expect($result)->toHaveKeys(['id', 'name', 'email']);
    expect($result)->not->toHaveKeys(['bio', 'skills', 'address', 'secret']);
});

it('filters fields when detail scope is active (method-based)', function () {
    $this->manager->setActiveScope('detail');

    $resource = new MethodScopedResource($this->testData);
    $result = $resource->toArray(Request::create('/'));

    expect($result)->toHaveKeys(['id', 'name', 'email', 'bio', 'skills', 'address']);
    expect($result)->not->toHaveKeys(['secret']);
});

it('filters fields when listing scope is active (attribute-based)', function () {
    $this->manager->setActiveScope('listing');

    $resource = new AttributeScopedResource($this->testData);
    $result = $resource->toArray(Request::create('/'));

    expect($result)->toHaveKeys(['id', 'name', 'email']);
    expect($result)->not->toHaveKeys(['bio', 'skills', 'address', 'secret']);
});

it('filters fields when detail scope is active (attribute-based)', function () {
    $this->manager->setActiveScope('detail');

    $resource = new AttributeScopedResource($this->testData);
    $result = $resource->toArray(Request::create('/'));

    expect($result)->toHaveKeys(['id', 'name', 'email', 'bio', 'skills', 'address']);
    expect($result)->not->toHaveKeys(['secret']);
});

it('returns all fields when scope is unknown', function () {
    $this->manager->setActiveScope('nonexistent');

    $resource = new MethodScopedResource($this->testData);
    $result = $resource->toArray(Request::create('/'));

    expect($result)->toHaveKeys(['id', 'name', 'email', 'bio', 'skills', 'address', 'secret']);
});

it('returns all fields when resource has no scope definitions', function () {
    $this->manager->setActiveScope('listing');

    $resource = new UnscopedResource($this->testData);
    $result = $resource->toArray(Request::create('/'));

    expect($result)->toHaveKeys(['id', 'name', 'email']);
});

it('works with resource collections', function () {
    $this->manager->setActiveScope('listing');

    $collection = MethodScopedResource::collection([
        $this->testData,
        array_merge($this->testData, ['id' => 2, 'name' => 'Jane Doe']),
    ]);

    $result = $collection->toArray(Request::create('/'));

    expect($result)->toHaveCount(2);
    expect($result[0])->toHaveKeys(['id', 'name', 'email']);
    expect($result[0])->not->toHaveKeys(['bio', 'secret']);
    expect($result[1])->toHaveKeys(['id', 'name', 'email']);
    expect($result[1]['name'])->toBe('Jane Doe');
});
