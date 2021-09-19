<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TimestampType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'TIMESTAMP';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::TIMESTAMP;
    }
}
