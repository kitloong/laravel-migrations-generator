<?php namespace Way\Generators\Syntax;

use Illuminate\Support\Facades\File;
use Way\Generators\Compilers\TemplateCompiler;

abstract class Table
{
    /**
     * @var TemplateCompiler
     */
    protected $compiler;

    /**
     * @param  TemplateCompiler  $compiler
     */
    public function __construct(TemplateCompiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * Fetch the template of the schema
     *
     * @return string
     */
    protected function getTemplate(): string
    {
        return File::get(__DIR__.'/../templates/schema.txt');
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
