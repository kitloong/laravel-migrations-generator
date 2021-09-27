<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class MediumIntegerType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'MEDIUMINT';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::MEDIUM_INTEGER;
    }
}
