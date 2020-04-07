<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/07
 */

namespace KitLoong\MigrationsGenerator\Repositories;

interface PlatformRepository
{
    public function getTypeByColumnName(string $table, string $columnName): ?string;
}
