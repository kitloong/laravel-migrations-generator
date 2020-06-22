<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/27
 */

namespace KitLoong\MigrationsGenerator\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class PointType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'POINT';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return ColumnType::POINT;
    }
}
