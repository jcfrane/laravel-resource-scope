<?php

use JCFrane\ResourceScope\ResourceScopeManager;
use JCFrane\ResourceScope\Tests\Fixtures\MethodScopedResource;

beforeEach(function () {
    $this->manager = new ResourceScopeManager;
});

it('starts with no active scope', function () {
    expect($this->manager->getActiveScope())->toBeNull();
});

it('sets and gets the active scope', function () {
    $this->manager->setActiveScope('listing');

    expect($this->manager->getActiveScope())->toBe('listing');
});

it('returns active scope for resource without nested mapping', function () {
    $this->manager->setActiveScope('listing');

    expect($this->manager->getScopeForResource(MethodScopedResource::class))->toBe('listing');
});

it('returns nested scope mapping over active scope', function () {
    $this->manager->setActiveScope('detail');
    $this->manager->setNestedScopeMapping(MethodScopedResource::class, 'listing');

    expect($this->manager->getScopeForResource(MethodScopedResource::class))->toBe('listing');
});

it('sets multiple nested scope mappings', function () {
    $this->manager->setNestedScopeMappings([
        'App\\Resources\\A' => 'summary',
        'App\\Resources\\B' => 'listing',
    ]);

    expect($this->manager->getScopeForResource('App\\Resources\\A'))->toBe('summary');
    expect($this->manager->getScopeForResource('App\\Resources\\B'))->toBe('listing');
});

it('removes a nested scope mapping', function () {
    $this->manager->setActiveScope('detail');
    $this->manager->setNestedScopeMapping(MethodScopedResource::class, 'listing');
    $this->manager->removeNestedScopeMapping(MethodScopedResource::class);

    expect($this->manager->getScopeForResource(MethodScopedResource::class))->toBe('detail');
});

it('clears all nested scope mappings', function () {
    $this->manager->setActiveScope('detail');
    $this->manager->setNestedScopeMapping(MethodScopedResource::class, 'listing');
    $this->manager->clearNestedScopeMappings();

    expect($this->manager->getScopeForResource(MethodScopedResource::class))->toBe('detail');
});

it('resets all state', function () {
    $this->manager->setActiveScope('listing');
    $this->manager->setNestedScopeMapping(MethodScopedResource::class, 'summary');
    $this->manager->reset();

    expect($this->manager->getActiveScope())->toBeNull();
    expect($this->manager->getScopeForResource(MethodScopedResource::class))->toBeNull();
});
