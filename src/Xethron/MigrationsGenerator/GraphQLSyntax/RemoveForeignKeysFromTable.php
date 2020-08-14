<?php namespace Xethron\MigrationsGenerator\GraphQLSyntax;

/**
 * Class RemoveForeignKeysFromTable
 * @package Xethron\MigrationsGenerator\Syntax
 */
class RemoveForeignKeysFromTable extends Table
{
    /**
     * Return string for dropping a foreign key
     *
     * @param array $item
     * @param bool $lastIndex
     * @return string
     */
    protected function getItem(array $item, bool $lastIndex): string
    {
        $name = empty($item['name']) ? $this->createIndexName($item['field']) : $item['name'];
        return sprintf("\$table->dropForeign('%s');", $name);
    }

    /**
     * Create a default index name for the table.
     *
     * @param  string  $column
     * @return string
     */
    protected function createIndexName(string $column): string
    {
        $index = strtolower($this->table.'_'.$column.'_foreign');

        return str_replace(['-', '.'], '_', $index);
    }
}
