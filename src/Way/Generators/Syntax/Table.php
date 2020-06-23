<?php namespace Way\Generators\Syntax;

use Illuminate\Support\Facades\File;
use KitLoong\MigrationsGenerator\Generators\Decorator;
use Way\Generators\Compilers\TemplateCompiler;

abstract class Table
{
    /**
     * @var TemplateCompiler
     */
    protected $compiler;

    protected $decorator;

    public function __construct(TemplateCompiler $compiler, Decorator $decorator)
    {
        $this->compiler = $compiler;
        $this->decorator = $decorator;
    }

    /**
     * Fetch the template of the schema
     *
     * @param  string  $method
     * @return string
     */
    protected function getTemplate(string $method): string
    {
        if ($method === 'drop') {
            return File::get(__DIR__.'/../templates/drop.txt');
        } else {
            return File::get(__DIR__.'/../templates/schema.txt');
        }
    }

    /**
     * Replace $FIELDS$ in the given template
     * with the provided schema
     *
     * @param  array  $schema
     * @param  string  $template
     * @return string
     */
    protected function replaceFieldsWith(array $schema, string $template): string
    {
        return str_replace('$FIELDS$', implode(PHP_EOL.str_repeat(' ', 12), $schema), $template);
    }
}
