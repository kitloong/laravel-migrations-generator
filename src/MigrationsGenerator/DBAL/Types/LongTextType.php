<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LongTextType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'LONGTEXT';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::LONG_TEXT;
    }
}
