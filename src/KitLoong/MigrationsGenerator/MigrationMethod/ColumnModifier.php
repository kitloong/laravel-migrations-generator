<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/30
 */

namespace KitLoong\MigrationsGenerator\MigrationMethod;

final class ColumnModifier
{
    const COMMENT = 'comment';
    const DEFAULT = 'default';
    const NULLABLE = 'nullable';
    const UNSIGNED = 'unsigned';
    const USE_CURRENT = 'useCurrent';
}
