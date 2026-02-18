<?php

use JCFrane\ResourceScope\Support\ScopeResolver;
use JCFrane\ResourceScope\Tests\Fixtures\AttributeScopedResource;
use JCFrane\ResourceScope\Tests\Fixtures\MethodScopedResource;
use JCFrane\ResourceScope\Tests\Fixtures\UnscopedResource;

beforeEach(function () {
    $this->resolver = new ScopeResolver;
});

it('resolves scope definitions from method', function () {
    $definitions = $this->resolver->resolveAll(MethodScopedResource::class);

    expect($definitions)->toBe([
        'listing' => ['id', 'name', 'email'],
        'detail' => ['id', 'name', 'email', 'bio', 'skills', 'address'],
    ]);
});

it('resolves scope definitions from attributes', function () {
    $definitions = $this->resolver->resolveAll(AttributeScopedResource::class);

    expect($definitions)->toBe([
        'listing' => ['id', 'name', 'email'],
        'detail' => ['id', 'name', 'email', 'bio', 'skills', 'address'],
    ]);
});

it('returns null for resources with no scope definitions', function () {
    $definitions = $this->resolver->resolveAll(UnscopedResource::class);

    expect($definitions)->toBeNull();
});

it('resolves specific scope fields', function () {
    $fields = $this->resolver->resolve(MethodScopedResource::class, 'listing');

    expect($fields)->toBe(['id', 'name', 'email']);
});

it('returns null for undefined scope', function () {
    $fields = $this->resolver->resolve(MethodScopedResource::class, 'nonexistent');

    expect($fields)->toBeNull();
});

it('caches resolved definitions', function () {
    $this->resolver->resolveAll(MethodScopedResource::class);
    $this->resolver->resolveAll(MethodScopedResource::class);

    // If caching works, this should not throw or produce different results
    $definitions = $this->resolver->resolveAll(MethodScopedResource::class);

    expect($definitions)->toBe([
        'listing' => ['id', 'name', 'email'],
        'detail' => ['id', 'name', 'email', 'bio', 'skills', 'address'],
    ]);
});

it('clears cache', function () {
    $this->resolver->resolveAll(MethodScopedResource::class);
    $this->resolver->clearCache();

    // Should re-resolve after cache clear
    $definitions = $this->resolver->resolveAll(MethodScopedResource::class);

    expect($definitions)->not->toBeNull();
});
