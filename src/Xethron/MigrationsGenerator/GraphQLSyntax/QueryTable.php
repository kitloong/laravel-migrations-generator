<?php

namespace Xethron\MigrationsGenerator\GraphQLSyntax;

class QueryTable extends Table
{
    /**
     * @inheritDoc
     */
    protected function getItem(array $item, bool $lastIndex): string
    {
        if ($lastIndex === true) {
            $property = $item['field'];
            $backslash = "\\";

            // If the field is an array,
            // make it an array in the Migration
            if (is_array($property)) {
                $property = "['" . implode("', '", $property) . "']";
            } else {
                $property = $property ? "'$property'" : null;
            }

            $type = $item['type'];
            $table = ucfirst(strtolower($item['table']));

            $tableNameS = strtolower($table);

            if (substr($tableNameS, -1) != "s"){
                $tableNameS .= 's';
            }

            $property = str_replace("'", "", $property);

            $output = sprintf(
                "%s(id: ID @eq(key: \"%s_id\")): %s @find(model: \"App\\\\\\\\Models\\\\\\\\%s\", scopes: [\"AuthProduct\"])
  %s(
    orderBy: [OrderByClause!] @orderBy(columnsEnum: \"%sOrderByColumns\")
    where: _ @whereConditions(columnsEnum: \"%sSearchByColumns\")
  ): [%s!]! @paginate(type: \"paginator\", model: \"App\\\\\\\\Models\\\\\\\\%s\", scopes: [\"AuthProduct\"])",
                strtolower($table),
                strtolower($table),
                $table,
                $table,
                $tableNameS,
                $table,
                $table,
                $table,
                $table
            );
            return $output;
        } else {
            return "";
        }
    }
}
