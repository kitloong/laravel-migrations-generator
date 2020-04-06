<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/04/06
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;

class BooleanField
{
    public function makeDefault(Column $column)
    {
        return (int) $column->getDefault();
    }
}
