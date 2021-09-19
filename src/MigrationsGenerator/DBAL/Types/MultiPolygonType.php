<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class MultiPolygonType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'MULTIPOLYGON';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::MULTI_POLYGON;
    }
}
