<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class MacAddressType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'MACADDR';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::MAC_ADDRESS;
    }
}
