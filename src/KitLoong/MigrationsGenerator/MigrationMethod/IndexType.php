<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/30
 * Time: 21:39
 */

namespace KitLoong\MigrationsGenerator\MigrationMethod;

final class IndexType
{
    const PRIMARY = 'primary';
    const UNIQUE = 'unique';
    const INDEX = 'index';
    const SPATIAL_INDEX = 'spatialIndex';
}
