<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class SetType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'SET';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::SET;
    }
}
