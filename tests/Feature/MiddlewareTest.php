<?php

use Illuminate\Http\Request;
use JCFrane\ResourceScope\Middleware\SetResourceScope;
use JCFrane\ResourceScope\ResourceScopeManager;

beforeEach(function () {
    $this->manager = app(ResourceScopeManager::class);
    $this->manager->reset();

    $this->middleware = new SetResourceScope($this->manager);
});

it('sets scope from query parameter', function () {
    $request = Request::create('/?scope=listing');

    $this->middleware->handle($request, fn ($req) => response('ok'));

    expect($this->manager->getActiveScope())->toBe('listing');
});

it('sets scope from header', function () {
    $request = Request::create('/');
    $request->headers->set('X-Resource-Scope', 'detail');

    $this->middleware->handle($request, fn ($req) => response('ok'));

    expect($this->manager->getActiveScope())->toBe('detail');
});

it('query parameter takes priority over header by default', function () {
    $request = Request::create('/?scope=listing');
    $request->headers->set('X-Resource-Scope', 'detail');

    $this->middleware->handle($request, fn ($req) => response('ok'));

    expect($this->manager->getActiveScope())->toBe('listing');
});

it('does not set scope when neither query param nor header is present', function () {
    $request = Request::create('/');

    $this->middleware->handle($request, fn ($req) => response('ok'));

    expect($this->manager->getActiveScope())->toBeNull();
});

it('uses header when query param is absent', function () {
    $request = Request::create('/');
    $request->headers->set('X-Resource-Scope', 'summary');

    $this->middleware->handle($request, fn ($req) => response('ok'));

    expect($this->manager->getActiveScope())->toBe('summary');
});
