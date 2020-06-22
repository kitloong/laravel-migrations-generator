<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/30
 */

namespace KitLoong\MigrationsGenerator\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class UUIDType extends Type
{

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'UUID';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return ColumnType::UUID;
    }
}
