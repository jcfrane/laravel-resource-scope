# Laravel Resource Scope

[![Tests](https://github.com/jcfrane/laravel-resource-scope/actions/workflows/tests.yml/badge.svg)](https://github.com/jcfrane/laravel-resource-scope/actions/workflows/tests.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/jcfrane/laravel-resource-scope.svg)](https://packagist.org/packages/jcfrane/laravel-resource-scope)
[![License](https://img.shields.io/packagist/l/jcfrane/laravel-resource-scope.svg)](https://packagist.org/packages/jcfrane/laravel-resource-scope)

Control which fields your Laravel API Resources return based on context. Define scopes like `listing`, `detail`, or `summary` and let the frontend request only the data it needs.

Inspired by Symfony's serialization groups.

## Requirements

- PHP 8.2+
- Laravel 11, 12, or 13

## Installation

```bash
composer require jcfrane/laravel-resource-scope
```

The package auto-discovers its service provider. No manual registration needed.

### Publish Config (Optional)

```bash
php artisan vendor:publish --tag=resource-scope-config
```

## Quick Start

### 1. Add the trait to your resource

```php
use JCFrane\ResourceScope\Concerns\HasResourceScope;

class UserResource extends JsonResource
{
    use HasResourceScope;

    protected function scopeDefinitions(): array
    {
        return [
            'listing' => ['id', 'name', 'email', 'avatar'],
            'detail'  => ['id', 'name', 'email', 'avatar', 'bio', 'created_at', 'settings'],
        ];
    }

    public function toArray(Request $request): array
    {
        return $this->scoped([
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar_url,
            'bio' => $this->bio,
            'created_at' => $this->created_at,
            'settings' => $this->whenLoaded('settings'),
        ]);
    }
}
```

### 2. Register the middleware

In `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(append: [
        \JCFrane\ResourceScope\Middleware\SetResourceScope::class,
    ]);
})
```

Or apply it to specific route groups:

```php
Route::middleware('resource.scope')->group(function () {
    Route::apiResource('users', UserController::class);
});
```

### 3. Request scoped data

```
# Listing — returns only id, name, email, avatar
GET /api/users?scope=listing

# Detail — returns all detail fields
GET /api/users/1?scope=detail

# No scope — returns everything (backwards compatible)
GET /api/users
```

You can also pass the scope via header:

```
X-Resource-Scope: listing
```

## Defining Scopes

### Method-based (recommended)

Define a `scopeDefinitions()` method that returns scope name => allowed field keys:

```php
protected function scopeDefinitions(): array
{
    return [
        'listing' => ['id', 'name', 'email'],
        'detail'  => ['id', 'name', 'email', 'bio', 'skills', 'documents'],
    ];
}
```

### Attribute-based

Use PHP 8 attributes on the resource class:

```php
use JCFrane\ResourceScope\Attributes\ResourceScope;

#[ResourceScope('listing', fields: ['id', 'name', 'email'])]
#[ResourceScope('detail', fields: ['id', 'name', 'email', 'bio', 'skills', 'documents'])]
class UserResource extends JsonResource
{
    use HasResourceScope;

    public function toArray(Request $request): array
    {
        return $this->scoped([
            // ...
        ]);
    }
}
```

If both are present, `scopeDefinitions()` takes priority.

## Scope Cascading

When a resource contains nested resources, you can control how scopes propagate. There are two ways to define mappings:

### Method-based mappings

```php
class PostResource extends JsonResource
{
    use HasResourceScope;

    protected function scopeDefinitions(): array
    {
        return [
            'listing' => ['id', 'title', 'author', 'created_at'],
        ];
    }

    protected function scopeMappings(): array
    {
        return [
            'listing' => [
                UserResource::class => 'summary',
            ],
        ];
    }

    public function toArray(Request $request): array
    {
        return $this->scoped([
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'author' => new UserResource($this->whenLoaded('author')),
            'created_at' => $this->created_at,
        ]);
    }
}
```

### Attribute-based mappings

You can also define mappings directly in the `#[ResourceScope]` attribute using the `mappings` parameter:

```php
use JCFrane\ResourceScope\Attributes\ResourceScope;

#[ResourceScope('listing', fields: ['id', 'title', 'author', 'created_at'], mappings: [
    UserResource::class => 'summary',
])]
#[ResourceScope('detail', fields: ['id', 'title', 'body', 'author', 'created_at'])]
class PostResource extends JsonResource
{
    use HasResourceScope;

    public function toArray(Request $request): array
    {
        return $this->scoped([
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'author' => new UserResource($this->whenLoaded('author')),
            'created_at' => $this->created_at,
        ]);
    }
}
```

When the `listing` scope is active on `PostResource`, the nested `UserResource` will automatically use the `summary` scope.

If both `scopeMappings()` method and attribute mappings are present, the method takes priority. If no mapping is defined, the parent's scope name passes through to nested resources. If the nested resource doesn't define that scope, it returns all fields.

## Works with Laravel's Conditional Fields

Scoping works alongside `whenLoaded()`, `whenHas()`, and `when()`. Both conditions apply — Laravel's conditional checks run first, then scoping filters the keys:

```php
return $this->scoped([
    'id' => $this->id,
    'name' => $this->name,
    'documents' => DocumentResource::collection($this->whenLoaded('documents')),
    'is_admin' => $this->when($user->isAdmin(), true),
]);
```

## Backwards Compatible

- No scope parameter = all fields returned (existing behavior)
- Scope not defined on a resource = all fields returned
- Unknown scope name = all fields returned

No breaking changes to existing API responses.

## Configuration

Published config file (`config/resource-scope.php`):

```php
return [
    // Query parameter name (default: 'scope')
    'query_param' => 'scope',

    // HTTP header name (default: 'X-Resource-Scope')
    'header' => 'X-Resource-Scope',

    // Query param takes priority over header (default: true)
    'query_param_priority' => true,
];
```

## Testing

```bash
composer test
```

## License

MIT
