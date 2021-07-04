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
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;
use KitLoong\MigrationsGenerator\Support\Regex;

class StringField
{
    const SQLSRV_TEXT_TYPE = 'nvarchar';
    const SQLSRV_TEXT_LENGTH = -1;

    private $collationModifier;
    private $charsetModifier;
    private $pgSQLRepository;
    private $sqlSrvRepository;
    private $regex;

    public function __construct(
        CollationModifier $collationModifier,
        CharsetModifier $charsetModifier,
        PgSQLRepository $pgSQLRepository,
        SQLSrvRepository $sqlSrvRepository,
        Regex $regex
    ) {
        $this->collationModifier = $collationModifier;
        $this->charsetModifier = $charsetModifier;
        $this->pgSQLRepository = $pgSQLRepository;
        $this->sqlSrvRepository = $sqlSrvRepository;
        $this->regex = $regex;
    }

    public function makeField(string $tableName, array $field, Column $column): array
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            // It could be pgsql enum
            case Platform::POSTGRESQL:
                if (($pgSQLEnum = $this->getPgSQLEnumValue($tableName, $column->getName())) !== '') {
                    $field['type'] = ColumnType::ENUM;
                    $field['args'][] = $pgSQLEnum;
                }
                break;
            // It could be sqlsrv text
            case Platform::SQLSERVER:
                $colDef = $this->sqlSrvRepository->getColumnDefinition($tableName, $column->getName());
                if ($colDef->getType() === self::SQLSRV_TEXT_TYPE &&
                    $colDef->getLength() === self::SQLSRV_TEXT_LENGTH) {
                    $field['type'] = ColumnType::TEXT;
                }
                break;
            default:
        }

        // Continue if type is `string`
        if ($field['type'] === ColumnType::STRING) {
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
        $definition = ($this->pgSQLRepository->getCheckConstraintDefinition($tableName, $column));
        if (!empty($definition)) {
            $enumValues = $this->regex->getTextBetweenAll($definition, "'", "'::");
            if (!empty($enumValues)) {
                return "['".implode("', '", $enumValues)."']";
            }
        }
        return '';
    }
}
