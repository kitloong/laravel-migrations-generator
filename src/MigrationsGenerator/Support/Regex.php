<?php

namespace MigrationsGenerator\Support;

class Regex
{
    /**
     * Get first string between 2 tags.
     *
     * @param  string  $text  Subject.
     * @param  string  $left  Left tag.
     * @param  string  $right  Right tag.
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
     * Get all strings between 2 tags.
     *
     * @param  string  $text  Subject.
     * @param  string  $left  Left tag.
     * @param  string  $right  Right tag.
     * @return string[]|null
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
