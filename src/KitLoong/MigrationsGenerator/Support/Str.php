<?php

namespace KitLoong\MigrationsGenerator\Support;

class Str
{
    public function replacePlaceholder(string $placeholder, string $replace, string $subject): string
    {
        return str_replace($placeholder, $replace, $subject);
    }
}
