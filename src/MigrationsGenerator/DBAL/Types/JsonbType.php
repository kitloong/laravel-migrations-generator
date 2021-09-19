<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class JsonbType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'JSONB';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::JSONB;
    }
}
