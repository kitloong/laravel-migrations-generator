<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

/**
 * Predefined index types of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#available-index-types
 */
enum IndexType: string implements MethodName
{
    case FULLTEXT       = 'fullText';
    case FULLTEXT_CHAIN = 'fulltext'; // Use lowercase.
    case INDEX          = 'index';
    case PRIMARY        = 'primary';
    case SPATIAL_INDEX  = 'spatialIndex';
    case UNIQUE         = 'unique';
}
