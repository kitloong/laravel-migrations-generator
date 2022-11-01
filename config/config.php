<?php

return [
    // Where the templates for the generators are stored.
    'migration_template_path' => __DIR__ . '/../resources/stub/migration.stub',

    // Where the generated files will be saved.
    'migration_target_path'   => base_path('database/migrations'),

    // Migration filename pattern.
    'filename_pattern'        => [
        'table'       => '[datetime]_create_[name]_table.php',
        'view'        => '[datetime]_create_[name]_view.php',
        'procedure'   => '[datetime]_create_[name]_proc.php',
        'foreign_key' => '[datetime]_add_foreign_keys_to_[name]_table.php',
    ],
];
