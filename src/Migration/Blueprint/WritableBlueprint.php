<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

interface WritableBlueprint
{
    /**
     * @return string
     */
    public function toString(): string;
}
