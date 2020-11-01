<?php namespace Xethron\MigrationsGenerator\Syntax;

/**
 * Class AddForeignKeysToTable
 * @package Xethron\MigrationsGenerator\Syntax
 */
class AddForeignKeysToTable extends Table
{
    /**
     * Return string for adding a foreign key
     *
     * @param  array  $foreignKey
     * @return string
     */
    protected function getItem(array $foreignKey): string
    {
        // Check for multiple columns
        if (count($foreignKey['fields']) > 1) {
            $value = "['" . implode("', '", $foreignKey['fields']) . "']";
            $references = "['" . implode("', '", $foreignKey['references']) . "']";
        } else {
            $value = "'" . $foreignKey['fields'][0] . "'";
            $references = "'" . $foreignKey['references'][0] . "'";
        }

        if (!empty($foreignKey['name'])) {
            $value .= ", '" . $foreignKey['name'] . "'";
        }

        $output = sprintf(
            "\$table->foreign(%s)->references(%s)->on('%s')",
            $value,
            $references,
            $foreignKey['on']
        );
        if ($foreignKey['onUpdate']) {
            $output .= sprintf("->onUpdate('%s')", $foreignKey['onUpdate']);
        }
        if ($foreignKey['onDelete']) {
            $output .= sprintf("->onDelete('%s')", $foreignKey['onDelete']);
        }
        if (isset($foreignKey['decorators'])) {
            $output .= $this->addDecorators($foreignKey['decorators']);
        }
        return $output.';';
    }
}
