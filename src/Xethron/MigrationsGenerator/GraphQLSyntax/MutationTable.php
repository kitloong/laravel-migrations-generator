<?php

namespace Xethron\MigrationsGenerator\GraphQLSyntax;

class MutationTable extends Table
{
    /**
     * @inheritDoc
     */
    protected function getItem(array $item, bool $lastIndex): string
    {
//        if (isset($item['table']) && (strtolower($item['field']) === "id" || strtolower($item['field']) === strtolower($item['table']."_id"))) {
        if ($lastIndex === true) {
            $property = $item['field'];

            // If the field is an array,
            // make it an array in the Migration
            if (is_array($property)) {
                $property = "['" . implode("', '", $property) . "']";
            } else {
                $property = $property ? "'$property'" : null;
            }

            $type = $item['type'];
            $table = ucfirst(strtolower($item['table']));

            $property = str_replace("'", "", $property);

            $output = sprintf(
                "create%s(input: Create%sInput! @spread): %s @create
    update%s(input: Update%sInput! @spread): %s @update(scopes: [\"AuthProduct\"])
    delete%s(id: ID! @rename(attribute: \"%s_id\")): %s @delete(scopes: [\"AuthProduct\"])",
                $table,
                $table,
                $table,
                $table,
                $table,
                $table,
                $table,
                strtolower($table),
                $table
            );
            return $output;
        }
        else {
            return "";
        }
    }
}
