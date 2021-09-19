<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TimeTzType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'TIMETZ';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::TIME_TZ;
    }
}
