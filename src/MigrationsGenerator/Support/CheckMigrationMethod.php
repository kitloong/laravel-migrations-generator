<?php

namespace MigrationsGenerator\Support;

trait CheckMigrationMethod
{
    use CheckLaravelVersion;

    public function hasUseCurrentOnUpdate(): bool
    {
        return $this->atLeastLaravel8();
    }
}
