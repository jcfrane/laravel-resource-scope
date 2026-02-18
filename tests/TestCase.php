<?php

namespace JCFrane\ResourceScope\Tests;

use JCFrane\ResourceScope\ResourceScopeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ResourceScopeServiceProvider::class,
        ];
    }
}
