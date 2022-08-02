<?php

return [
    // Where the templates for the generators are stored.
    'migration_template_path'      => __DIR__ . '/../resources/stub/migration.stub',

    // Where the generated files will be saved.
    'migration_target_path'        => base_path('database/migrations'),

    // Migration filename pattern.
    'filename_pattern'             => [
        'table'       => '[datetime_prefix]_create_[table]_table.php',
        'view'        => '[datetime_prefix]_create_[table]_view.php',
        'foreign_key' => '[datetime_prefix]_add_foreign_keys_to_[table]_table.php',
    ],

    /**
     * Laravel 9x+ migration
     */
    'anonymous_class_migration' => [
        'enabled' => env('LMG_ANONYMOUS_CLASS_MIGRATION_ENABLED', false),
        'template_path' => env(
            'LMG_ANONYMOUS_CLASS_MIGRATION_TEMPLATE_PATH',
            __DIR__ . '/../resources/stub/anonymous-class-migration.stub'
        ),
    ],
];
