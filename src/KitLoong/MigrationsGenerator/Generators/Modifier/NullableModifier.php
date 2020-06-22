<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class NullableModifier
{
    public function shouldAddNullableModifier(string $type): bool
    {
        return !in_array($type, [ColumnType::SOFT_DELETES, ColumnType::REMEMBER_TOKEN, ColumnType::TIMESTAMPS]);
    }
}
