<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class GeomCollectionType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'GEOMCOLLECTION';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::GEOMETRY_COLLECTION;
    }
}
