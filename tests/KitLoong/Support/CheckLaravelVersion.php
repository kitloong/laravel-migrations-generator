<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2021/01/02
 */

namespace Tests\KitLoong\Support;

trait CheckLaravelVersion
{
    protected function atLeastLaravel5Dot7(): bool
    {
        return version_compare(app()->version(), '5.7.0', '>=');
    }

    protected function atLeastLaravel5Dot8(): bool
    {
        return version_compare(app()->version(), '5.8.0', '>=');
    }

    protected function atLeastLaravel6(): bool
    {
        return version_compare(app()->version(), '6.0', '>=');
    }

    protected function atLeastLaravel7(): bool
    {
        return version_compare(app()->version(), '7.0', '>=');
    }

    protected function atLeastLaravel8(): bool
    {
        return version_compare(app()->version(), '8.0', '>=');
    }
}
