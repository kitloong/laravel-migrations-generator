<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\Modifier\CharsetModifier;
use KitLoong\MigrationsGenerator\Generators\Modifier\CollationModifier;

class OtherField
{
    private $collationModifier;
    private $charsetModifier;

    public function __construct(CollationModifier $collationModifier, CharsetModifier $charsetModifier)
    {
        $this->collationModifier = $collationModifier;
        $this->charsetModifier = $charsetModifier;
    }

    public function makeField(string $tableName, array $field, Column $column): array
    {
        if (isset(FieldGenerator::$fieldTypeMap[$field['type']])) {
            $field['type'] = FieldGenerator::$fieldTypeMap[$field['type']];
        }

        $charset = $this->charsetModifier->generate($tableName, $column);
        if ($charset !== '') {
            $field['decorators'][] = $charset;
        }

        $collation = $this->collationModifier->generate($tableName, $column);
        if ($collation !== '') {
            $field['decorators'][] = $collation;
        }

        return $field;
    }
}
