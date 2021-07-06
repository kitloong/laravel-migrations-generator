<?php namespace Xethron\MigrationsGenerator\Syntax;

use Illuminate\Support\Facades\Config;
use KitLoong\MigrationsGenerator\Generators\Decorator;
use KitLoong\MigrationsGenerator\Generators\Platform;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use Way\Generators\Compilers\TemplateCompiler;
use Way\Generators\Syntax\Table as WayTable;

/**
 * Class Table
 * @package Xethron\MigrationsGenerator\Syntax
 */
abstract class Table extends WayTable
{
    /**
     * @var string
     */
    protected $table;

    public function __construct(TemplateCompiler $compiler, Decorator $decorator)
    {
        parent::__construct($compiler, $decorator);
    }

    public function run(array $fields, string $table, string $connection, $method = 'table'): string
    {
        $table = $this->decorator->tableWithoutPrefix($table);
        $this->table = $table;
        if ($connection !== Config::get('database.default')) {
            $method = 'connection(\''.$connection.'\')->'.$method;
        }

        $compiled = $this->compiler->compile($this->getTemplate($method), ['table' => $table, 'method' => $method]);

        $content = $this->getItems($fields);

        if ($method === 'create') {
            $tableCollation = $this->getTableCollation($table);
            if (!empty($tableCollation)) {
                $content = array_merge(
                    $tableCollation,
                    [''], // New line
                    $content
                );
            }
        }

        return $this->replaceFieldsWith($content, $compiled);
    }

    /**
     * Return string for adding all foreign keys
     *
     * @param  array  $items
     * @return array
     */
    protected function getItems(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $this->getItem($item);
        }
        return $result;
    }

    /**
     * Get table collation migration lines if not equal to DB collation.
     *
     * @param  string  $tableName
     * @return array|string[]
     */
    protected function getTableCollation(string $tableName): array
    {
        $setting = app(MigrationsGeneratorSetting::class);
        if ($setting->getPlatform() === Platform::MYSQL) {
            if ($setting->isUseDBCollation()) {
                $tableCollation = $setting->getSchema()->listTableDetails($tableName)->getOptions()['collation'];
                $tableCharset = explode('_', $tableCollation)[0];
                return [
                    '$table->charset = \''.$tableCharset.'\';',
                    '$table->collation = \''.$tableCollation.'\';',
                ];
            }
        }
        return [];
    }

    /**
     * @param  array  $item
     * @return string
     */
    abstract protected function getItem(array $item): string;

    /**
     * @param  string[]  $decorators
     * @return string
     */
    protected function addDecorators(array $decorators): string
    {
        $output = '';
        foreach ($decorators as $decorator) {
            $output .= sprintf("->%s", $decorator);
            // Do we need to tack on the parentheses?
            if (strpos($decorator, '(') === false) {
                $output .= '()';
            }
        }
        return $output;
    }
}
