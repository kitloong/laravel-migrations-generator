<?php

namespace KitLoong\MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationMethod\PgSQLGeography;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;

class GeometryColumn implements GeneratableColumn
{
    private $pgsqlRepository;

    public function __construct(PgSQLRepository $pgSQLRepository)
    {
        $this->pgsqlRepository = $pgSQLRepository;
    }

    public function generate(string $type, Table $table, Column $column): ColumnMethod
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            case Platform::POSTGRESQL:
                $typeFromDB = $this->pgsqlRepository->getTypeByColumnName($table->getName(), $column->getName());
                if ($typeFromDB !== null) {
                    $typeFromDB = strtolower($typeFromDB);
                    $typeFromDB = preg_replace('/\s+/', '', $typeFromDB);

                    if (isset(PgSQLGeography::MAP[$typeFromDB])) {
                        $type = PgSQLGeography::MAP[$typeFromDB];
                    }
                }
                break;
            default:
        }
        return new ColumnMethod($type, $column->getName());
    }
}
