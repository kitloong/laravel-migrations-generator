<?php

namespace MigrationsGenerator\Support;

trait CheckMigrationMethod
{
    use CheckLaravelVersion;

    /**
     * `useCurrentOnUpdate` add since Laravel 8.
     *
     * @return bool
     */
    public function hasUseCurrentOnUpdate(): bool
    {
        return $this->atLeastLaravel8();
    }
}
