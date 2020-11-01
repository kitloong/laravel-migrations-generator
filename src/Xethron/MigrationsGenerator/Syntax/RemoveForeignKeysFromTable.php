<?php namespace Xethron\MigrationsGenerator\Syntax;

use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

/**
 * Class RemoveForeignKeysFromTable
 * @package Xethron\MigrationsGenerator\Syntax
 */
class RemoveForeignKeysFromTable extends Table
{
    /**
     * Return string for dropping a foreign key
     *
     * @param  array  $foreignKey
     * @return string
     */
    protected function getItem(array $foreignKey): string
    {
        $name = empty($foreignKey['name']) ? $this->createIndexName($foreignKey['fields']) : $foreignKey['name'];
        return sprintf("\$table->dropForeign('%s');", $name);
    }

    /**
     * Create a default index name for the table.
     *
     * @param  array  $columns
     * @return string
     */
    protected function createIndexName(array $columns): string
    {
        $setting = app(MigrationsGeneratorSetting::class);
        $tableConcatPrefix = $setting->getConnection()->getTablePrefix().$this->table;

        $index = strtolower($tableConcatPrefix.'_'.implode('_', $columns).'_foreign');

        return str_replace(['-', '.'], '_', $index);
    }
}
