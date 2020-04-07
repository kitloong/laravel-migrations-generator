<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/07
 */

namespace KitLoong\MigrationsGenerator\Generators;

use KitLoong\MigrationsGenerator\MigrationGeneratorSetting;
use KitLoong\MigrationsGenerator\MigrationMethod\PgSQLGeography;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;

class GeometryField
{
    private $pgsqlRepository;

    public function __construct(PgSQLRepository $pgSQLRepository)
    {
        $this->pgsqlRepository = $pgSQLRepository;
    }

    public function makeField(string $tableName, array $field)
    {
        /** @var MigrationGeneratorSetting $setting */
        $setting = app(MigrationGeneratorSetting::class);

        switch ($setting->getPlatform()) {
            case Platform::POSTGRESQL:
                $columnType = $this->pgsqlRepository->getTypeByColumnName($tableName, $field['field']);
                if ($columnType !== null) {
                    $type = strtolower($columnType);
                    $type = preg_replace('/\s+/', '', $type);

                    if (isset(PgSQLGeography::MAP[$type])) {
                        $field['type'] = PgSQLGeography::MAP[$type];
                    }
                }
                break;
            default:
        }
        return $field;
    }
}
