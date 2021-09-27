<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class DoubleType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'DOUBLE';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::DOUBLE;
    }
}
