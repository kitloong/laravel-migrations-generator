<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class IpAddressType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'INET';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::IP_ADDRESS;
    }
}
