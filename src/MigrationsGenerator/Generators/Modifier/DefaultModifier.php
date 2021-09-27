<?php

namespace MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\Columns\DatetimeColumn;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;

class DefaultModifier
{
    private $datetimeColumn;

    public function __construct(DatetimeColumn $datetimeColumn)
    {
        $this->datetimeColumn = $datetimeColumn;
    }

    /**
     * Set default value.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  string  $type  Column type.
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function chainDefault(Table $table, Method $method, string $type, Column $column): Method
    {
        if ($column->getDefault() === null) {
            return $method;
        }

        switch ($type) {
            case ColumnType::INTEGER:
            case ColumnType::BIG_INTEGER:
            case ColumnType::MEDIUM_INTEGER:
            case ColumnType::SMALL_INTEGER:
            case ColumnType::TINY_INTEGER:
                $method = $this->chainDefaultForInteger($method, $column);
                break;
            case ColumnType::DECIMAL:
            case ColumnType::FLOAT:
            case ColumnType::DOUBLE:
                $method = $this->chainDefaultForDecimal($method, $column);
                break;
            case ColumnType::BOOLEAN:
                $method = $this->chainDefaultForBoolean($method, $column);
                break;
            case ColumnType::SOFT_DELETES:
            case ColumnType::DATETIME:
            case ColumnType::TIMESTAMP:
                $method = $this->chainDefaultForDatetime($method, $table, $column);
                break;
            default:
                $method = $this->chainDefaultForString($method, $column);
        }
        return $method;
    }

    /**
     * Set default value to method for integer column.
     *
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    private function chainDefaultForInteger(Method $method, Column $column): Method
    {
        $method->chain(ColumnModifier::DEFAULT, (int) $column->getDefault());
        return $method;
    }

    /**
     * Set default value to method for decimal column.
     *
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    private function chainDefaultForDecimal(Method $method, Column $column): Method
    {
        $method->chain(ColumnModifier::DEFAULT, (float) $column->getDefault());
        return $method;
    }

    /**
     * Set default value to method for boolean column.
     *
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    private function chainDefaultForBoolean(Method $method, Column $column): Method
    {
        $method->chain(ColumnModifier::DEFAULT, ((int) $column->getDefault()) === 1);
        return $method;
    }

    /**
     * Set default value to method for datetime column.
     *
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    private function chainDefaultForDatetime(Method $method, Table $table, Column $column): Method
    {
        switch ($column->getDefault()) {
            case 'now()':
            case 'CURRENT_TIMESTAMP':
                // Fallback for old Laravel version which doesn't have `useCurrentOnUpdate` yet.
                if (!$this->datetimeColumn->hasOnUpdateCurrentTimestamp($column, $table) ||
                    $method->hasChain(ColumnModifier::USE_CURRENT_ON_UPDATE)) {
                    $method->chain(ColumnModifier::USE_CURRENT);
                }
                break;
            default:
                $method->chain(ColumnModifier::DEFAULT, $column->getDefault());
        }

        return $method;
    }

    /**
     * Set default value to method, which support string.
     *
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    private function chainDefaultForString(Method $method, Column $column): Method
    {
        $quotes  = '\'';
        $default = $column->getDefault();
        // To replace from ' to \\\'
        $method->chain(ColumnModifier::DEFAULT, str_replace($quotes, '\\\\'.$quotes, $default));

        return $method;
    }
}
