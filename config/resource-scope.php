<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Query Parameter Name
    |--------------------------------------------------------------------------
    |
    | The query parameter name used to pass the scope from the frontend.
    | Example: /api/candidates?scope=listing
    |
    */

    'query_param' => 'scope',

    /*
    |--------------------------------------------------------------------------
    | Request Header Name
    |--------------------------------------------------------------------------
    |
    | The HTTP header name used to pass the scope from the frontend.
    | Example: X-Resource-Scope: listing
    |
    */

    'header' => 'X-Resource-Scope',

    /*
    |--------------------------------------------------------------------------
    | Query Parameter Priority
    |--------------------------------------------------------------------------
    |
    | When both query parameter and header are present, the query parameter
    | takes priority if this is set to true.
    |
    */

    'query_param_priority' => true,

];
