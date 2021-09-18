<?php

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use KitLoong\MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;

class DefaultModifier
{
    /**
     * Set default value.
     *
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @param  string  $type
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    public function chainDefault(ColumnMethod $method, string $type, Column $column): ColumnMethod
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
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    private function chainDefaultForInteger(ColumnMethod $method, Column $column): ColumnMethod
    {
        $method->chain(ColumnModifier::DEFAULT, (int) $column->getDefault());
        return $method;
    }

    /**
     * Set default value to method for decimal column.
     *
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    private function chainDefaultForDecimal(ColumnMethod $method, Column $column): ColumnMethod
    {
        $method->chain(ColumnModifier::DEFAULT, (float) $column->getDefault());
        return $method;
    }

    /**
     * Set default value to method for boolean column.
     *
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    private function chainDefaultForBoolean(ColumnMethod $method, Column $column): ColumnMethod
    {
        $method->chain(ColumnModifier::DEFAULT, ((int) $column->getDefault()) === 1);
        return $method;
    }

    /**
     * Set default value to method for datetime column.
     *
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    private function chainDefaultForDatetime(ColumnMethod $method, Column $column): ColumnMethod
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

    private function chainDefaultForString(ColumnMethod $method, Column $column): ColumnMethod
    {
        $quotes  = '\'';
        $default = $column->getDefault();
        // To replace from ' to \\\'
        $method->chain(ColumnModifier::DEFAULT, str_replace($quotes, '\\\\'.$quotes, $default));

        return $method;
    }
}
