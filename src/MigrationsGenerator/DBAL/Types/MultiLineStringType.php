<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class MultiLineStringType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'MULTILINESTRING';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::MULTI_LINE_STRING;
    }
}
