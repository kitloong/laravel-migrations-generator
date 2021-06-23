<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Schema\Builder;
use KitLoong\MigrationsGenerator\Generators\Modifier\CharsetModifier;
use KitLoong\MigrationsGenerator\Generators\Modifier\CollationModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnName;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Support\Regex;

class StringField
{
    private $collationModifier;
    private $charsetModifier;
    private $pgSQLRepository;
    private $regex;

    public function __construct(
        CollationModifier $collationModifier,
        CharsetModifier $charsetModifier,
        PgSQLRepository $pgSQLRepository,
        Regex $regex
    ) {
        $this->collationModifier = $collationModifier;
        $this->charsetModifier = $charsetModifier;
        $this->pgSQLRepository = $pgSQLRepository;
        $this->regex = $regex;
    }

    public function makeField(string $tableName, array $field, Column $column): array
    {
        if (($pgSQLEnum = $this->getPgSQLEnumValue($tableName, $column->getName())) !== '') {
            $field['type'] = ColumnType::ENUM;
            $field['args'][] = $pgSQLEnum;
        } else {
            if ($field['field'] === ColumnName::REMEMBER_TOKEN && $column->getLength() === 100 && !$column->getFixed()) {
                $field['type'] = ColumnType::REMEMBER_TOKEN;
                $field['field'] = null;
                $field['args'] = [];
            } else {
                if ($column->getFixed()) {
                    $field['type'] = ColumnType::CHAR;
                }

                if ($column->getLength() && $column->getLength() !== Builder::$defaultStringLength) {
                    $field['args'][] = $column->getLength();
                }
            }
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

    private function getPgSQLEnumValue(string $tableName, string $column): string
    {
        if (app(MigrationsGeneratorSetting::class)->getPlatform() === Platform::POSTGRESQL) {
            $definition = ($this->pgSQLRepository->getCheckConstraintDefinition($tableName, $column));
            if (!empty($definition)) {
                $enumValues = $this->regex->getTextBetweenAll($definition, "'", "'::");
                if (!empty($enumValues)) {
                    return "['".implode("', '", $enumValues)."']";
                }
            }
        }
        return '';
    }
}
