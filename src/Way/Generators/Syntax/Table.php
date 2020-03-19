<?php namespace Way\Generators\Syntax;

use Way\Generators\Compilers\TemplateCompiler;
use Way\Generators\Filesystem\Filesystem;

abstract class Table {

    /**
     * @var \Way\Generators\Filesystem\Filesystem
     */
    protected $file;

    /**
     * @var \Way\Generators\Compilers\TemplateCompiler
     */
    protected $compiler;

    /**
     * @param Filesystem $file
     * @param TemplateCompiler $compiler
     */
    function __construct(Filesystem $file, TemplateCompiler $compiler)
    {
        $this->compiler = $compiler;
        $this->file = $file;
    }

    /**
     * Fetch the template of the schema
     *
     * @return string
     */
    protected function getTemplate()
    {
        return $this->file->get(__DIR__.'/../templates/schema.txt');
    }


    /**
     * Replace $FIELDS$ in the given template
     * with the provided schema
     *
     * @param $schema
     * @param $template
     * @return mixed
     */
    protected function replaceFieldsWith($schema, $template)
    {
        return str_replace('$FIELDS$', implode(PHP_EOL."\t\t\t", $schema), $template);
    }

}