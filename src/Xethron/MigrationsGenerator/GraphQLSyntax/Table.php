<?php namespace Xethron\MigrationsGenerator\GraphQLSyntax;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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

    public function run(array $fields, string $table, string $connection, $method = 'table', string $type): string
    {
        $table = $this->decorator->tableWithoutPrefix($table);
        $this->table = $table;
//        if ($connection !== Config::get('database.default')) {
//            $method = 'connection(\''.$connection.'\')->'.$method;
//        }

        $compiled = $this->compiler->compile($this->getTemplate($method), ['table' => ucfirst($table), 'method' => $method]);
        return $this->replaceFieldsWith($this->getItems($fields), $compiled, $type);
    }

    /**
     * Return string for adding all foreign keys
     *
     * @param  array  $items
     * @return array
     */
    protected function getItems(array $items): array
    {
        $lastIndex = false;
        $result = [];
        $count = 0;
        $numInArray = count($items);
        foreach ($items as $item) {
            if (++$count == $numInArray) {
                break;
            }
            if ($count+2 === $numInArray) {
                $lastIndex = true;
            }
            $result[] = $this->getItem($item, $lastIndex);
        }
        return $result;
    }

    /**
     * @param array $item
     * @param bool $lastIndex
     * @return string
     */
    abstract protected function getItem(array $item, bool $lastIndex);

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
