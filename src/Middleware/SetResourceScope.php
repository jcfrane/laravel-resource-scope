<?php

namespace JCFrane\ResourceScope\Middleware;

use Closure;
use Illuminate\Http\Request;
use JCFrane\ResourceScope\ResourceScopeManager;
use Symfony\Component\HttpFoundation\Response;

class SetResourceScope
{
    public function __construct(
        protected ResourceScopeManager $manager,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $queryParam = config('resource-scope.query_param', 'scope');
        $header = config('resource-scope.header', 'X-Resource-Scope');
        $queryParamPriority = config('resource-scope.query_param_priority', true);

        $fromQuery = $request->query($queryParam);
        $fromHeader = $request->header($header);

        if ($queryParamPriority) {
            $scope = $fromQuery ?? $fromHeader;
        } else {
            $scope = $fromHeader ?? $fromQuery;
        }

        if ($scope !== null) {
            $this->manager->setActiveScope($scope);
        }

        return $next($request);
    }
}
