<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace KitLoong\MigrationsGenerator\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class MultiLineStringType extends Type
{

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'MULTILINESTRING';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return ColumnType::MULTI_LINE_STRING;
    }
}
