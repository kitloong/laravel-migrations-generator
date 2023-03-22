<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\SQLite;

use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALColumn;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Repositories\SQLiteRepository;
use KitLoong\MigrationsGenerator\Support\CheckMigrationMethod;
use KitLoong\MigrationsGenerator\Support\Regex;

class SQLiteColumn extends DBALColumn
{
    use CheckMigrationMethod;

    /**
     * @var \KitLoong\MigrationsGenerator\Repositories\SQLiteRepository
     */
    private $repository;

    /**
     * @inheritDoc
     */
    protected function handle(): void
    {
        $this->repository = app(SQLiteRepository::class);

        $this->setAutoincrement();
        $this->setTypeToIncrements(false);

        switch ($this->type) {
            case ColumnType::STRING():
                $this->presetValues = $this->getEnumPresetValues();

                if (count($this->presetValues) > 0) {
                    $this->type = ColumnType::ENUM();
                }

                break;

            case ColumnType::DATETIME():
                if ($this->default === 'CURRENT_TIMESTAMP') {
                    $this->type = ColumnType::TIMESTAMP();
                }

                break;

            case ColumnType::DATETIME_TZ():
                if ($this->default === 'CURRENT_TIMESTAMP') {
                    $this->type = ColumnType::TIMESTAMP_TZ();
                }

                break;

            default:
        }
    }

    /**
     * If column is integer and primary key,
     * doctrine/dbal assume the column is autoincrement, but it could be wrong.
     * Should check full sql statement from sqlite_master to ensure autoincrement is written corretly.
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
}
