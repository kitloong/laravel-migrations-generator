<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 18:41
 */

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\DatetimeField;
use KitLoong\MigrationsGenerator\Generators\Decorator;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\Types\DBALTypes;

class DefaultModifier
{
    private $datetimeField;
    private $decorator;

    public function __construct(DatetimeField $datetimeField, Decorator $decorator)
    {
        $this->datetimeField = $datetimeField;
        $this->decorator = $decorator;
    }

    /**
     * @param  string  $dbalType
     * @param  Column  $column
     * @return string
     */
    public function generate(string $dbalType, Column $column): string
    {
        switch ($dbalType) {
            case DBALTypes::SMALLINT:
            case DBALTypes::INTEGER:
            case DBALTypes::BIGINT:
            case DBALTypes::MEDIUMINT:
            case DBALTypes::DECIMAL:
            case DBALTypes::FLOAT:
            case DBALTypes::DOUBLE:
            case DBALTypes::BOOLEAN:
                $default = $column->getDefault();
                break;
            case DBALTypes::DATETIME_MUTABLE:
            case DBALTypes::TIMESTAMP:
                return $this->datetimeField->makeDefault($column);
            default:
                $default = $this->decorator->columnDefaultToString($column->getDefault());
        }

        return $this->decorator->decorate(ColumnModifier::DEFAULT, [$default]);
    }
}
