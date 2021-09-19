<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TinyIntegerType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'TINYINT';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::TINY_INTEGER;
    }
}
