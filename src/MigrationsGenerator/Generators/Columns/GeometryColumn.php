<?php

namespace MigrationsGenerator\Generators\Columns;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\PgSQLGeography;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Repositories\PgSQLRepository;

class GeometryColumn implements GeneratableColumn
{
    private $pgsqlRepository;

    public function __construct(PgSQLRepository $pgSQLRepository)
    {
        $this->pgsqlRepository = $pgSQLRepository;
    }

    public function generate(string $type, Table $table, Column $column): Method
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
        return new Method($type, $column->getName());
    }
}
