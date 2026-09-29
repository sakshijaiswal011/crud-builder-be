<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Generation Paths
    |--------------------------------------------------------------------------
    |
    | Define the exact absolute paths where the CRUD builder should generate
    | each type of file.
    |
    */
    'paths' => [
        'models' => app_path('Models'),
        'controllers' => app_path('Http/Controllers/Api'),
        'services' => app_path('Services'),
        'resources' => app_path('Http/Resources'),
        'requests' => app_path('Http/Requests'),
        'policies' => app_path('Policies'),
        'routes' => base_path('routes/modules'),
        'migrations' => database_path('migrations'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | Define the base namespaces to use inside the generated PHP classes.
    |
    */
    'namespaces' => [
        'models' => 'App\\Models',
        'controllers' => 'App\\Http\\Controllers\\Api',
        'services' => 'App\\Services',
        'resources' => 'App\\Http\\Resources',
        'requests' => 'App\\Http\\Requests',
        'policies' => 'App\\Policies',
    ],
];
