<?php

namespace KitLoong\MigrationsGenerator\Support;

class Regex
{
    /**
     * Get first occurred string between 2 tags.
     * Given: Hello, (this is (hot) chocolate).
     * Return: this is (hot
     *
     * @param  string  $text  Subject.
     * @param  string  $left  Left tag.
     * @param  string  $right  Right tag.
     * @return string|null
     */
    public static function getTextBetweenFirst(string $text, string $left = '\(', string $right = '\)'): ?string
    {
        $matched = preg_match('/' . $left . '(.*?)' . $right . '/', $text, $output);

        if ($matched === 1) {
            return $output[1];
        }

        return null;
    }

    /**
     * Get string between 2 tags.
     * Given: Hello, (this is (hot) chocolate).
     * Return: this is (hot) chocolate
     *
     * @param  string  $text  Subject.
     * @param  string  $left  Left tag.
     * @param  string  $right  Right tag.
     * @return string|null
     */
    public static function getTextBetween(string $text, string $left = '\(', string $right = '\)'): ?string
    {
        $matched = preg_match('/' . $left . '(.*)' . $right . '/', $text, $output);

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
    public static function getTextBetweenAll(string $text, string $left = '\(', string $right = '\)'): ?array
    {
        $matched = preg_match_all('/' . $left . '(.*?)' . $right . '/', $text, $output);

        if ($matched > 0) {
            return $output[1];
        }

        return null;
    }

    /**
     * Get the string matching the given pattern.
     *
     * @param  string  $pattern
     * @param  string  $subject
     * @return string
     * @see \Illuminate\Support\Str::match() Available since Laravel v8
     */
    public static function match(string $pattern, string $subject): string
    {
        preg_match($pattern, $subject, $matches);

        if (!$matches) {
            return '';
        }

        return $matches[1] ?? $matches[0];
    }
}
