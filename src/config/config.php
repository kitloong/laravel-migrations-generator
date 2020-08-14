<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Where the templates for the generators are stored...
    |--------------------------------------------------------------------------
    |
    */

    'migration_template_path' => base_path('vendor/kitloong/laravel-migrations-generator/src/Way/Generators/templates/migration.txt'),
    'graphql_template_path' => base_path('vendor/kitloong/laravel-migrations-generator/src/Way/Generators/GraphqlTemplates/migration.txt'),

    /*
    |--------------------------------------------------------------------------
    | Where the generated files will be saved...
    |--------------------------------------------------------------------------
    |
    */

    'migration_target_path'   => base_path('database/migrations'),
    'graphql_target_path'   => base_path('graphql'),

];
