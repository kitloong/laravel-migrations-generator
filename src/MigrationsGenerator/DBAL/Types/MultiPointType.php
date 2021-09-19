<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class MultiPointType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'MULTIPOINT';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::MULTI_POINT;
    }
}
