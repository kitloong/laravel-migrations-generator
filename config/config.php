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
];
