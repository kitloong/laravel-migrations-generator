<?php

namespace KitLoong\MigrationsGenerator\Support;

use Illuminate\Support\Facades\App;

trait CheckLaravelVersion
{
    public function atLeastLaravel5Dot7(): bool
    {
        return $this->atLeastLaravelVersion('5.7.0');
    }

    public function atLeastLaravel5Dot8(): bool
    {
        return $this->atLeastLaravelVersion('5.8.0');
    }

    public function atLeastLaravel6(): bool
    {
        return $this->atLeastLaravelVersion('6.0');
    }

    public function atLeastLaravel7(): bool
    {
        return $this->atLeastLaravelVersion('7.0');
    }

    public function atLeastLaravel8(): bool
    {
        return $this->atLeastLaravelVersion('8.0');
    }

    public function atLeastLaravel9(): bool
    {
        return $this->atLeastLaravelVersion('9.0');
    }

    private function atLeastLaravelVersion(string $version): bool
    {
        return version_compare(App::version(), $version, '>=');
    }
}
