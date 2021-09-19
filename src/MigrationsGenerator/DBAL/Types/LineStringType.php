<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LineStringType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'LINESTRING';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::LINE_STRING;
    }
}
