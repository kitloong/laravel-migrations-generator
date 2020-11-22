<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\Modifier\CollationModifier;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;

class EnumField
{
    private $collationModifier;

    private $decorator;

    private $mysqlRepository;

    public function __construct(CollationModifier $collationModifier, Decorator $decorator, MySQLRepository $mySQLRepository)
    {
        $this->collationModifier = $collationModifier;
        $this->decorator = $decorator;
        $this->mysqlRepository = $mySQLRepository;
    }

    public function makeField(string $tableName, array $field, Column $column): array
    {
        $value = $this->mysqlRepository->getEnumPresetValues($tableName, $field['field']);
        if ($value !== null) {
            $field['args'][] = $value;
        }

        $collation = $this->collationModifier->generate($tableName, $column);
        if ($collation !== '') {
            $field['decorators'][] = $collation;
        }

        return $field;
    }
}
