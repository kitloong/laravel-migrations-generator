<?php namespace Way\Generators\Syntax;

use Way\Generators\Compilers\TemplateCompiler;
use Way\Generators\Filesystem\Filesystem;

abstract class Table
{
    /**
     * @var Filesystem
     */
    protected $file;

    /**
     * @var TemplateCompiler
     */
    protected $compiler;

    /**
     * @param  Filesystem  $file
     * @param  TemplateCompiler  $compiler
     */
    public function __construct(Filesystem $file, TemplateCompiler $compiler)
    {
        $this->compiler = $compiler;
        $this->file = $file;
    }

    /**
     * Fetch the template of the schema
     *
     * @return string
     * @throws \Way\Generators\Filesystem\FileNotFound
     */
    protected function getTemplate(): string
    {
        return $this->file->get(__DIR__.'/../templates/schema.txt');
    }


    /**
     * Replace $FIELDS$ in the given template
     * with the provided schema
     *
     * @param  array  $schema
     * @param  string  $template
     * @return mixed
     */
    protected function replaceFieldsWith(array $schema, string $template): string
    {
        return str_replace('$FIELDS$', implode(PHP_EOL."\t\t\t", $schema), $template);
    }
}
