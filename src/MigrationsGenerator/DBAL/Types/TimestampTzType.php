<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TimestampTzType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'TIMESTAMPTZ';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::TIMESTAMP_TZ;
    }
}
