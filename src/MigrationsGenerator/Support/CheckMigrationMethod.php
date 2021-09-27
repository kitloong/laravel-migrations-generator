<?php

namespace MigrationsGenerator\Support;

trait CheckMigrationMethod
{
    use CheckLaravelVersion;

    /**
     * `useCurrentOnUpdate` added since Laravel 8.
     *
     * @return bool
     */
    public function hasUseCurrentOnUpdate(): bool
    {
        return $this->atLeastLaravel8();
    }

    /**
     * `set` added since Laravel 5.8.
     *
     * @return bool
     */
    public function hasSet(): bool
    {
        return $this->atLeastLaravel5Dot8();
    }
}
