<?php

namespace KitLoong\MigrationsGenerator\Support;

class Regex
{
    public function getTextBetween(string $text, string $left = '(', string $right = ')'): ?string
    {
        $matched = preg_match('/\\'.$left.'(.*?)\\'.$right.'/', $text, $output);
        if ($matched === 1) {
            return $output[1];
        }
        return null;
    }
}
