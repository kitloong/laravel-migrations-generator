<?php

namespace KitLoong\MigrationsGenerator\Enum\Migrations\Method;

use MyCLabs\Enum\Enum;

/**
 * Predefined index types of the framework.
 *
 * @see https://laravel.com/docs/master/migrations#available-index-types
 * @method static self FULLTEXT()
 * @method static self FULLTEXT_CHAIN()
 * @method static self INDEX()
 * @method static self PRIMARY()
 * @method static self SPATIAL_INDEX()
 * @method static self UNIQUE()
 * @extends \MyCLabs\Enum\Enum<string>
 */
final class IndexType extends Enum
{
    public const FULLTEXT       = 'fullText';
    public const FULLTEXT_CHAIN = 'fulltext'; // Use lowercase.
    public const INDEX          = 'index';
    public const PRIMARY        = 'primary';
    public const SPATIAL_INDEX  = 'spatialIndex';
    public const UNIQUE         = 'unique';
}
