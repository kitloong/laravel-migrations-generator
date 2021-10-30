<?php

namespace MigrationsGenerator\Generators\Blueprint;

interface WritableBlueprint
{
    /**
     * @return string
     */
    public function toString(): string;
}
