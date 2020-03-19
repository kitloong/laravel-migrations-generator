<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Where the templates for the generators are stored...
    |--------------------------------------------------------------------------
    |
    */
    'model_template_path' => base_path('vendor/kitloong/laravel-migrations-generator/src/Way/Generators/templates/model.txt'),

    'scaffold_model_template_path' => base_path('vendor/kitloong/laravel-migrations-generator/src/Way/Generators/templates/scaffolding/model.txt'),

    'controller_template_path' => base_path('vendor/kitloong/laravel-migrations-generator/src/Way/Generators/templates/controller.txt'),

    'scaffold_controller_template_path' => base_path('vendor/kitloong/laravel-migrations-generator/src/Way/Generators/templates/scaffolding/controller.txt'),

    'migration_template_path' => base_path('vendor/kitloong/laravel-migrations-generator/src/Way/Generators/templates/migration.txt'),

    'seed_template_path' => base_path('vendor/kitloong/laravel-migrations-generator/src/Way/Generators/templates/seed.txt'),

    'view_template_path' => base_path('vendor/kitloong/laravel-migrations-generator/src/Way/Generators/templates/view.txt'),


    /*
    |--------------------------------------------------------------------------
    | Where the generated files will be saved...
    |--------------------------------------------------------------------------
    |
    */
    'model_target_path'   => app_path(),

    'controller_target_path'   => app_path('Http/Controllers'),

    'migration_target_path'   => base_path('database/migrations'),

    'seed_target_path'   => base_path('database/seeds'),

    'view_target_path'   => base_path('resources/views')

];
