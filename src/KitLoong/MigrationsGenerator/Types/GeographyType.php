<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/06
 */

namespace KitLoong\MigrationsGenerator\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class GeographyType extends Type
{

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'GEOGRAPHY';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return ColumnType::GEOMETRY;
    }
}
