<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class GeometryType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'GEOMETRY';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::GEOMETRY;
    }
}
