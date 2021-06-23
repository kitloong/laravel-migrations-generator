<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/30
 */

namespace KitLoong\MigrationsGenerator\MigrationMethod;

final class ColumnModifier
{
    const CHARSET = 'charset';
    const COLLATION = 'collation';
    const COMMENT = 'comment';
    const DEFAULT = 'default';
    const NULLABLE = 'nullable';
    const UNSIGNED = 'unsigned';
    const USE_CURRENT = 'useCurrent';
    const USE_CURRENT_ON_UPDATE = 'useCurrentOnUpdate';
}
