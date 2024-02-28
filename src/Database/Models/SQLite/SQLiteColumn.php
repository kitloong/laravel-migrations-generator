<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLite;

use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Database\Models\DatabaseColumn;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Repositories\SQLiteRepository;
use KitLoong\MigrationsGenerator\Support\Regex;

class SQLiteColumn extends DatabaseColumn
{
    private SQLiteRepository $repository;

    /**
     * @inheritDoc
     */
    public function __construct(string $table, array $column)
    {
        parent::__construct($table, $column);

        $this->default = $this->parseDefault($column['default']);
        $this->default = $this->escapeDefault($this->default);

        $this->repository = app(SQLiteRepository::class);

        $this->setAutoincrement();
        $this->setTypeToIncrements(false);

        switch ($this->type) {
            case ColumnType::INTEGER:
                if ($this->isBoolean($column['type'])) {
                    $this->type = ColumnType::BOOLEAN;
                }

                break;

            case ColumnType::STRING:
                $this->presetValues = $this->getEnumPresetValues();

                if (count($this->presetValues) > 0) {
                    $this->type = ColumnType::ENUM;
                }

                break;

            case ColumnType::DATETIME:
                if ($this->default === 'CURRENT_TIMESTAMP') {
                    $this->type = ColumnType::TIMESTAMP;
                }

                break;

            default:
        }
    }

    /**
     * @inheritDoc
     */
    protected function getColumnType(string $type): ColumnType
    {
        return SQLiteColumnType::toColumnType($type);
    }

    /**
     * If column is integer and primary key,
     * The column is autoincrement, but it could be wrong.
     * Should check full sql statement from sqlite_master to ensure autoincrement is written correctly.
     */
    private function setAutoincrement(): void
    {
        // We need to check if the autoincrement is set correctly.
        // Proceed only if this column is autoincrement.
        if (!$this->isAutoincrement()) {
            return;
        }

        // First, disable autoincrement
        $this->autoincrement = false;

        $sql = $this->repository->getSql($this->tableName);

        if (!Str::contains($sql, 'autoincrement')) {
            return;
        }

        $sqlColumn = Regex::getTextBetween($sql);

        if ($sqlColumn === null) {
            return;
        }

        $columns = explode(',', $sqlColumn);

        foreach ($columns as $column) {
            if (!Str::startsWith(trim($column), '"' . $this->name . '"')) {
                continue;
            }

            if (Str::contains($column, 'autoincrement')) {
                $this->autoincrement = true;
            }

            break;
        }
    }

    /**
     * Check if the column is "tinyint(1)".
     */
    private function isBoolean(string $fullDefinitionType): bool
    {
        if ($this->autoincrement) {
            return false;
        }

        return $fullDefinitionType === 'tinyint(1)';
    }

    /**
     * Get the preset values if the column is "enum".
     *
     * @return string[]
     */
    private function getEnumPresetValues(): array
    {
        $sql = $this->repository->getSql($this->tableName);

        if (!preg_match('/\(\"' . $this->name . '\" in \((.*?)\)/', $sql, $matched)) {
            return [];
        }

        // Get content from (.*?) from index 1
        $explodes = explode(',', $matched[1]);
        $values   = [];

        foreach ($explodes as $value) {
            $values[] = trim(trim($value), "'");
        }

        return $values;
    }

    /**
     * Parse the default value.
     */
    private function parseDefault(?string $default): ?string
    {
        if ($default === null) {
            return null;
        }

        while (preg_match('/^\'(.*)\'$/s', $default, $matches)) {
            $default = str_replace("''", "'", $matches[1]);
        }

        return $default;
    }
}
