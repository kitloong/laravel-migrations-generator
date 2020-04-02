<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 */

namespace KitLoong\MigrationsGenerator\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;

class TimestampTzType extends Type
{

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'TIMESTAMPTZ';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return ColumnType::TIMESTAMP_TZ;
    }
}
