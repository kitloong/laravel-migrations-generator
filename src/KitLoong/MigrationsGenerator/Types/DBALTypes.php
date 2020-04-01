<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/01
 * Time: 15:05
 */

namespace KitLoong\MigrationsGenerator\Types;

final class DBALTypes
{
    /**
     * @see \Doctrine\DBAL\Types\Types::BIGINT
     */
    const BIGINT = 'bigint';

    /**
     * @see \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE
     */
    const DATETIME_MUTABLE = 'datetime';

    /**
     * @see \Doctrine\DBAL\Types\Types::DECIMAL
     */
    const DECIMAL = 'decimal';

    /**
     * @see \Doctrine\DBAL\Types\Types::FLOAT
     */
    const FLOAT = 'float';

    /**
     * @see \Doctrine\DBAL\Types\Types::INTEGER
     */
    const INTEGER = 'integer';

    /**
     * @see \Doctrine\DBAL\Types\Types::SMALLINT
     */
    const SMALLINT = 'smallint';

    /**
     * @see \Doctrine\DBAL\Types\Types::STRING
     */
    const STRING = 'string';

    // Custom types

    const DOUBLE = 'double';

    const ENUM = 'enum';

    const MEDIUMINT = 'mediumint';

    const SET = 'set';

    const TIMESTAMP = 'timestamp';
}
