<?php

namespace MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;

class DefaultModifier
{
    /**
     * Set default value.
     *
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  string  $type
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function chainDefault(Method $method, string $type, Column $column): Method
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
                $method = $this->chainDefaultForDatetime($method, $column);
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
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    private function chainDefaultForDatetime(Method $method, Column $column): Method
    {
        switch ($column->getDefault()) {
            case 'now()':
            case 'CURRENT_TIMESTAMP':
                $method->chain(ColumnModifier::USE_CURRENT);
                break;
            default:
                $method->chain(ColumnModifier::DEFAULT, $column->getDefault());
        }

        return $method;
    }

    private function chainDefaultForString(Method $method, Column $column): Method
    {
        $quotes  = '\'';
        $default = $column->getDefault();
        // To replace from ' to \\\'
        $method->chain(ColumnModifier::DEFAULT, str_replace($quotes, '\\\\'.$quotes, $default));

        return $method;
    }
}
