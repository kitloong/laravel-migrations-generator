<?php namespace Way\Generators\Compilers;

interface Compiler
{
    /**
     * Compile the template using
     * the given data
     *
     * @param  string  $template
     * @param  array  $data
     * @return string
     */
    public function compile(string $template, array $data): string;
}
