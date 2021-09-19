<?php

namespace MigrationsGenerator\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class MediumTextType extends Type
{
    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'MEDIUMTEXT';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return DBALTypes::MEDIUM_TEXT;
    }
}
