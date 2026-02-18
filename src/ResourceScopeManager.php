<?php

namespace JCFrane\ResourceScope;

class ResourceScopeManager
{
    /**
     * The active scope set by middleware from the request.
     */
    protected ?string $activeScope = null;

    /**
     * Nested scope mappings: resource class => scope name.
     * Set by parent resources to control how nested resources are scoped.
     *
     * @var array<class-string, string>
     */
    protected array $nestedScopeMappings = [];

    /**
     * Get the active scope name.
     */
    public function getActiveScope(): ?string
    {
        return $this->activeScope;
    }

    /**
     * Set the active scope (typically called by middleware).
     */
    public function setActiveScope(?string $scope): void
    {
        $this->activeScope = $scope;
    }

    /**
     * Get the effective scope for a specific resource class.
     *
     * Priority:
     * 1. Explicit nested scope mapping (set by parent resource)
     * 2. Active scope from the request
     */
    public function getScopeForResource(string $resourceClass): ?string
    {
        return $this->nestedScopeMappings[$resourceClass] ?? $this->activeScope;
    }

    /**
     * Set a nested scope mapping for a specific resource class.
     * This is used by parent resources to control how nested resources are scoped.
     */
    public function setNestedScopeMapping(string $resourceClass, string $scope): void
    {
        $this->nestedScopeMappings[$resourceClass] = $scope;
    }

    /**
     * Set multiple nested scope mappings at once.
     *
     * @param  array<class-string, string>  $mappings
     */
    public function setNestedScopeMappings(array $mappings): void
    {
        foreach ($mappings as $resourceClass => $scope) {
            $this->nestedScopeMappings[$resourceClass] = $scope;
        }
    }

    /**
     * Remove a nested scope mapping.
     */
    public function removeNestedScopeMapping(string $resourceClass): void
    {
        unset($this->nestedScopeMappings[$resourceClass]);
    }

    /**
     * Clear all nested scope mappings.
     */
    public function clearNestedScopeMappings(): void
    {
        $this->nestedScopeMappings = [];
    }

    /**
     * Reset all state (useful for testing).
     */
    public function reset(): void
    {
        $this->activeScope = null;
        $this->nestedScopeMappings = [];
    }
}
