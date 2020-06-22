<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/28
 */

namespace KitLoong\MigrationsGenerator\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class EnumType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'ENUM';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return ColumnType::ENUM;
    }
}
