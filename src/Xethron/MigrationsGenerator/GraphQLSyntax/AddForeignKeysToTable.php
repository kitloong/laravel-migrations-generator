<?php namespace Xethron\MigrationsGenerator\GraphQLSyntax;

/**
 * Class AddForeignKeysToTable
 * @package Xethron\MigrationsGenerator\Syntax
 */
class AddForeignKeysToTable extends Table
{
    /**
     * Return string for adding a foreign key
     *
     * @param array $item
     * @param bool $lastIndex
     * @return string
     */
    protected function getItem(array $item, bool $lastIndex): string
    {
        $value = $item['field'];
        if (!empty($item['name'])) {
            $value .= "', '".$item['name'];
        }
        $output = sprintf(
            "\$table->foreign('%s')->references('%s')->on('%s')",
            $value,
            $item['references'],
            $item['on']
        );
        if ($item['onUpdate']) {
            $output .= sprintf("->onUpdate('%s')", $item['onUpdate']);
        }
        if ($item['onDelete']) {
            $output .= sprintf("->onDelete('%s')", $item['onDelete']);
        }
        if (isset($item['decorators'])) {
            $output .= $this->addDecorators($item['decorators']);
        }
        return $output.';';
    }
}
