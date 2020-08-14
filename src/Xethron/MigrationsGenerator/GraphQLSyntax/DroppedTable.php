<?php

namespace Xethron\MigrationsGenerator\GraphQLSyntax;

class DroppedTable extends Table
{
    /**
     * @inheritDoc
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

            if ($rename === true) {
                $output = sprintf(
                    "%s @enum(value: \"%s_%s\")",
                    strtoupper($property),
                    strtolower($item['table']),
                    $property
                );
            } else {
                $output = sprintf(
                    "%s @enum(value: \"%s\")",
                    strtoupper($property),
                    $property
                );
            }


            // If we have args, then it needs
            // to be formatted a bit differently
//        if (!empty($field['args'])) {
//            if ($property !== null) {
//                $output = sprintf(
//                    "\$table->%s(%s, %s)",
//                    $type,
//                    $property,
//                    implode(', ', $field['args'])
//                );
//            } else {
//                $output = sprintf(
//                    "\$table->%s(%s)",
//                    $type,
//                    implode(', ', $field['args'])
//                );
//            }
//        }
            return $output;
        }
        return "";
    }
}
