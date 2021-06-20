<?php

namespace KitLoong\MigrationsGenerator\Support;

class Regex
{
    /**
     * @param  string  $text
     * @param  string  $left
     * @param  string  $right
     * @return string|null
     */
    public function getTextBetween(string $text, string $left = '\(', string $right = '\)'): ?string
    {
        $matched = preg_match('/'.$left.'(.*?)'.$right.'/', $text, $output);
        if ($matched === 1) {
            return $output[1];
        }
        return null;
    }

    /**
     * @param  string  $text
     * @param  string  $left
     * @param  string  $right
     * @return array|null|string[]
     */
    public function getTextBetweenAll(string $text, string $left = '\(', string $right = '\)'): ?array
    {
        $matched = preg_match_all('/'.$left.'(.*?)'.$right.'/', $text, $output);
        if ($matched > 0) {
            return $output[1];
        }
        return null;
    }
}
