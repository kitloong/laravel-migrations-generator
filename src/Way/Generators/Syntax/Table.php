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
        if ($method === 'midrop') {
            return File::get(__DIR__.'/../templates/drop.txt');
        } elseif ($method === 'query') {
            return File::get(__DIR__.'/../GraphqlTemplates/query.txt');
        } elseif ($method === 'mutation') {
            return File::get(__DIR__.'/../GraphqlTemplates/mutation.txt');
        } elseif ($method === 'create') {
            return File::get(__DIR__.'/../templates/schema.txt');
        } elseif ($method === 'gqlcreate') {
            return File::get(__DIR__.'/../GraphqlTemplates/schema.txt');
        } elseif ($method === 'gqldrop') {
            return File::get(__DIR__.'/../GraphqlTemplates/drop.txt');
        } else {
            return File::get(__DIR__.'/../templates/schema.txt');
        }
    }

    /**
     * Replace $FIELDS$ in the given template
     * with the provided schema
     *
     * @param array $schema
     * @param string $template
     * @param string $type
     * @return string
     */
    protected function replaceFieldsWith(array $schema, string $template, string $type): string
    {
        $keepDeletingBlankData = true;
        while ($keepDeletingBlankData === true) {
            $index = array_search("", $schema);
            if ($index !== false) {
                unset($schema[$index]);
            } else {
                $keepDeletingBlankData = false;
            }
        }
        if ($type === "mi") {
            return str_replace('$FIELDS$', implode(PHP_EOL . str_repeat('  ', 8), $schema), $template);
        } else {
            $intermediate = (str_replace('$ID$', implode(PHP_EOL.str_repeat(' ', 4), array_slice($schema, 0, 1)), $template));
            return (str_replace('$FIELDS$', implode(PHP_EOL.str_repeat(' ', 4), array_slice($schema, 1)), $intermediate));
        }
    }
}
