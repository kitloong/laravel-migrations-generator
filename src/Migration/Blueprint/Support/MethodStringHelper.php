<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint\Support;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\Setting;

trait MethodStringHelper
{
    /**
     * Generates method string with `connection` if `--connection=other` option is used.
     */
    public function connection(string $class, string $method): string
    {
        if (DB::getName() === app(Setting::class)->getDefaultConnection()) {
            return "$class::$method";
        }

        return "$class::" . SchemaBuilder::CONNECTION() . "('" . DB::getName() . "')->$method";
    }
}
