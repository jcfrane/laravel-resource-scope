<?php

namespace JCFrane\ResourceScope\Support;

use JCFrane\ResourceScope\Attributes\ResourceScope;
use ReflectionClass;

class ScopeResolver
{
    /**
     * Cached scope definitions per resource class.
     *
     * @var array<class-string, array<string, array<string>>>
     */
    protected array $cache = [];

    /**
     * Cached scope mappings per resource class.
     *
     * @var array<class-string, array<string, array<class-string, string>>>
     */
    protected array $mappingsCache = [];

    /**
     * Resolve all scope definitions for a resource class.
     *
     * Method-based definitions take priority over attribute-based definitions.
     *
     * @return array<string, array<string>>|null Returns null if no scopes are defined.
     */
    public function resolveAll(string $resourceClass): ?array
    {
        if (array_key_exists($resourceClass, $this->cache)) {
            return $this->cache[$resourceClass];
        }

        $definitions = $this->resolveFromMethod($resourceClass)
            ?? $this->resolveFromAttributes($resourceClass);

        $this->cache[$resourceClass] = $definitions;

        return $definitions;
    }

    /**
     * Resolve the allowed fields for a specific scope on a resource class.
     *
     * @return array<string>|null Returns null if the scope is not defined.
     */
    public function resolve(string $resourceClass, string $scope): ?array
    {
        $all = $this->resolveAll($resourceClass);

        if ($all === null) {
            return null;
        }

        return $all[$scope] ?? null;
    }

    /**
     * Resolve the scope mappings for a specific scope on a resource class.
     *
     * Method-based scopeMappings() takes priority over attribute-based mappings.
     *
     * @return array<class-string, string>|null Returns null if no mappings are defined.
     */
    public function resolveMappings(string $resourceClass, string $scope): ?array
    {
        if (method_exists($resourceClass, 'scopeMappings')) {
            $allMappings = $this->invokeResourceMethod($resourceClass, 'scopeMappings');

            return is_array($allMappings) && ! empty($allMappings[$scope]) ? $allMappings[$scope] : null;
        }

        // Resolve from attributes and check mappings cache
        $this->resolveAll($resourceClass);

        return $this->mappingsCache[$resourceClass][$scope] ?? null;
    }

    /**
     * Resolve scope definitions from the scopeDefinitions() method.
     *
     * @return array<string, array<string>>|null
     */
    protected function resolveFromMethod(string $resourceClass): ?array
    {
        if (! method_exists($resourceClass, 'scopeDefinitions')) {
            return null;
        }

        $definitions = $this->invokeResourceMethod($resourceClass, 'scopeDefinitions');

        return is_array($definitions) && ! empty($definitions) ? $definitions : null;
    }

    /**
     * Resolve scope definitions from #[ResourceScope] attributes on the class.
     *
     * @return array<string, array<string>>|null
     */
    protected function resolveFromAttributes(string $resourceClass): ?array
    {
        $reflection = new ReflectionClass($resourceClass);
        $attributes = $reflection->getAttributes(ResourceScope::class);

        if (empty($attributes)) {
            return null;
        }

        $definitions = [];
        $mappings = [];

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $definitions[$instance->name] = $instance->fields;

            if (! empty($instance->mappings)) {
                $mappings[$instance->name] = $instance->mappings;
            }
        }

        if (! empty($mappings)) {
            $this->mappingsCache[$resourceClass] = $mappings;
        }

        return ! empty($definitions) ? $definitions : null;
    }

    /**
     * Clear the cache (useful for testing).
     */
    public function clearCache(): void
    {
        $this->cache = [];
        $this->mappingsCache = [];
    }

    /**
     * Invoke a resource method without running the constructor.
     *
     * This supports protected/private scope methods on JsonResource classes.
     */
    protected function invokeResourceMethod(string $resourceClass, string $method): mixed
    {
        $reflection = new ReflectionClass($resourceClass);
        $instance = $reflection->newInstanceWithoutConstructor();

        return $reflection->getMethod($method)->invoke($instance);
    }
}
