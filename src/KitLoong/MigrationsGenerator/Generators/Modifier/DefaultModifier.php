<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 18:41
 */

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;
use KitLoong\MigrationsGenerator\Generators\DatetimeField;
use KitLoong\MigrationsGenerator\Generators\Decorator;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;

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
            case Types::SMALLINT:
            case Types::INTEGER:
            case Types::BIGINT:
            case 'mediumint':
            case Types::DECIMAL:
            case Types::FLOAT:
            case 'double':
                $default = $column->getDefault();
                break;
            case Types::DATETIME_MUTABLE:
            case 'timestamp':
                return $this->datetimeField->makeDefault($column);
            default:
                $default = $this->decorator->columnDefaultToString($column->getDefault());
        }

        return $this->decorator->decorate(ColumnModifier::DEFAULT, [$default]);
    }
}
