<?php

namespace JCFrane\ResourceScope\Concerns;

use JCFrane\ResourceScope\ResourceScopeManager;
use JCFrane\ResourceScope\Support\ScopeResolver;

trait HasResourceScope
{
    /**
     * Filter the given data array based on the active scope.
     *
     * When no scope is active, all fields are returned (backwards compatible).
     * When a scope is active but not defined on this resource, all fields are returned.
     *
     * @param  array<string, mixed>  $data  The full resource data array
     * @return array<string, mixed>  The filtered data array
     */
    protected function scoped(array $data): array
    {
        $manager = app(ResourceScopeManager::class);
        $scope = $manager->getScopeForResource(static::class);

        // No scope = return everything (backwards compatible)
        if ($scope === null) {
            return $data;
        }

        $resolver = app(ScopeResolver::class);
        $allowedFields = $resolver->resolve(static::class, $scope);

        // Scope not defined on this resource = return everything (graceful fallback)
        if ($allowedFields === null) {
            return $data;
        }

        // Register nested scope mappings before filtering
        $this->registerNestedScopeMappings($manager, $scope);

        // Filter to only allowed keys
        return array_intersect_key($data, array_flip($allowedFields));
    }

    /**
     * Register nested scope mappings.
     *
     * Checks scopeMappings() method first, then falls back to attribute-based mappings.
     */
    protected function registerNestedScopeMappings(ResourceScopeManager $manager, string $scope): void
    {
        if (method_exists($this, 'scopeMappings')) {
            $allMappings = $this->scopeMappings();
            $mappingsForScope = $allMappings[$scope] ?? [];

            if (! empty($mappingsForScope)) {
                $manager->setNestedScopeMappings($mappingsForScope);

                return;
            }
        }

        // Fall back to attribute-based mappings
        $resolver = app(ScopeResolver::class);
        $mappings = $resolver->resolveMappings(static::class, $scope);

        if ($mappings !== null) {
            $manager->setNestedScopeMappings($mappings);
        }
    }
}
