<?php

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod;
use KitLoong\MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;

class NullableModifier
{
    /**
     * Set nullable.
     *
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @param  string  $type
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    public function chainNullable(ColumnMethod $method, string $type, Column $column): ColumnMethod
    {
        if ($column->getNotnull()) {
            if ($this->shouldAddNotNullModifier($type)) {
                $method->chain(ColumnModifier::NULLABLE, false);
            }

            return $method;
        }

        if ($this->shouldAddNullableModifier($type)) {
            $method->chain(ColumnModifier::NULLABLE);
        }

        return $method;
    }

    /**
     * Check if column should add nullable, by check the $type.
     * `softDeletes`, `rememberToken`, `timestamps` type are skipped.
     *
     * @param  string  $type
     * @return bool
     */
    private function shouldAddNullableModifier(string $type): bool
    {
        return !in_array($type, [ColumnType::SOFT_DELETES, ColumnType::REMEMBER_TOKEN, ColumnType::TIMESTAMPS]);
    }

    /**
     * Check if column should add nullable(false), by check the $type.
     * Only check `softDeletes`, `rememberToken`
     *
     * @param  string  $type
     * @return bool
     */
    private function shouldAddNotNullModifier(string $type): bool
    {
        if (!in_array($type, [ColumnType::SOFT_DELETES, ColumnType::REMEMBER_TOKEN])) {
            return false;
        }

        return true;
    }
}
