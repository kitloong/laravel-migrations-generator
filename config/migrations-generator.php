<?php

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;

return [
    // Where the templates for the generators are stored.
    'migration_template_path' => __DIR__ . '/../stubs/migration.generate.stub',

    // Where the generated files will be saved.
    'migration_target_path'   => base_path('database/migrations'),

    // Migration filename pattern.
    'filename_pattern'        => [
        'table'       => '[datetime]_create_[name]_table.php',
        'view'        => '[datetime]_create_[name]_view.php',
        'procedure'   => '[datetime]_create_[name]_proc.php',
        'foreign_key' => '[datetime]_add_foreign_keys_to_[name]_table.php',
    ],
    //cast rule
    'cast'                    => [
        /**
         * @var array<string, callback>
         */
        'allow'     => [
            //element is regex expression
            //value is a callback function
            'tinyint(1)' => function (string $table, string $column): ColumnType | false {
                return ColumnType::TINY_INTEGER;
            },
        ],
    ],
];
