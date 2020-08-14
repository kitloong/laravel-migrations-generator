<?php namespace Xethron\MigrationsGenerator\GraphQLSyntax;

/**
 * Class AddToTable
 * @package Xethron\MigrationsGenerator\Syntax
 */
class AddToTable extends Table
{
    /**
     * Return string for adding a column
     *
     * @param array $item
     * @param bool $lastIndex
     * @return string
     */
    protected function getItem(array $item, bool $lastIndex): string
    {
        if (isset($item['table'])) {
            $property = $item['field'];

            // If the field is an array,
            // make it an array in the Migration
            if (is_array($property)) {
                $property = "['" . implode("', '", $property) . "']";
            } else {
                $property = $property ? "'$property'" : null;
            }

            $type = $item['type'];

            $property = str_replace("'", "", $property);

            $rename = false;

            if (str_contains(strtolower($property), strtolower($item['table'])) && (strtolower($property) != strtolower($item['table']))) {
                $property = str_replace(strtolower($item['table'] . "_"), "", $property);
                $rename = true;
            }

            if (str_contains(strtolower($type), "integer")) {
                $type = 'integer';
            }

            switch ($type) {
                case 'decimal':
                case 'float':
                case 'double':
                case 'integer':
                case 'increments':
                case 'tinyIncrements':
                    $type = 'Int';
                    break;
                case 'dateTime':
                case 'string':
                case 'timestamp':
                case 'char':
                case 'text':
                case 'date':
                    $type = 'String';
                    break;
                case 'boolean':
                    $type = 'Boolean';
                    break;
                case 'json':
                    $type = 'JSON';
                    break;
                case 'geometry':
                case 'set':
                case 'enum':
                default:
                    $type = 'UNKNOWN';
                    break;
            }

            if (strtolower($property) === 'id') {
                $type = "ID!";
            }

            if ($rename === true) {
                $output = sprintf(
                    "%s: %s @rename(attribute: \"%s_%s\")",
                    $property,
                    $type,
                    strtolower($item['table']),
                    $property
                );
            } else {
                $output = sprintf(
                    "%s: %s",
                    $property,
                    $type
                );
            }

            return $output;
        } else {
            return "";
        }
    }
}
