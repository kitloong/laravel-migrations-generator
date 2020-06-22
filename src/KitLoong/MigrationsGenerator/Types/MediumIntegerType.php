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

class MediumIntegerType extends Type
{

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'MEDIUMINT';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return ColumnType::MEDIUM_INTEGER;
    }
}
