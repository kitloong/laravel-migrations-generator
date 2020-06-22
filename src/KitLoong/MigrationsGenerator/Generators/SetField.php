<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 */

namespace KitLoong\MigrationsGenerator\Generators;

use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;

class SetField
{
    private $decorator;

    private $mysqlRepository;

    public function __construct(Decorator $decorator, MySQLRepository $mySQLRepository)
    {
        $this->decorator = $decorator;
        $this->mysqlRepository = $mySQLRepository;
    }

    public function makeField(string $tableName, array $field): array
    {
        $value = $this->mysqlRepository->getSetPresetValues($tableName, $field['field']);
        if ($value !== null) {
            $field['args'][] = $value;
        }

        return $field;
    }
}
