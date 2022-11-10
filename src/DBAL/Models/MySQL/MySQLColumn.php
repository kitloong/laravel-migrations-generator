<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\MySQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALColumn;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Repositories\MariaDBRepository;
use KitLoong\MigrationsGenerator\Repositories\MySQLRepository;
use KitLoong\MigrationsGenerator\Support\CheckMigrationMethod;
use PDO;

class MySQLColumn extends DBALColumn
{
    use CheckMigrationMethod;

    /**
     * @var \KitLoong\MigrationsGenerator\Repositories\MySQLRepository
     */
    private $mysqlRepository;

    /**
     * @var \KitLoong\MigrationsGenerator\Repositories\MariaDBRepository
     */
    private $mariaDBRepository;

    protected function handle(): void
    {
        $this->mysqlRepository   = app(MySQLRepository::class);
        $this->mariaDBRepository = app(MariaDBRepository::class);

        $this->setTypeToIncrements(true);
        $this->setTypeToUnsigned();

        switch ($this->type) {
            case ColumnType::UNSIGNED_TINY_INTEGER():
            case ColumnType::TINY_INTEGER():
                if ($this->isBoolean()) {
                    $this->type = ColumnType::BOOLEAN();
                }

                break;

            case ColumnType::ENUM():
                $this->presetValues = $this->getEnumPresetValues();
                break;

            case ColumnType::SET():
                $this->useSetOrString();
                break;

            case ColumnType::SOFT_DELETES():
            case ColumnType::SOFT_DELETES_TZ():
            case ColumnType::TIMESTAMP():
            case ColumnType::TIMESTAMP_TZ():
                $this->onUpdateCurrentTimestamp = $this->hasOnUpdateCurrentTimestamp();
                break;

            default:
        }

        $this->setVirtualDefinition();
        $this->setStoredDefinition();

        if (!$this->isMaria()) {
            return;
        }

        // Extra logic for MariaDB
        switch ($this->type) {
            case ColumnType::LONG_TEXT():
                if ($this->isJson()) {
                    $this->type = ColumnType::JSON();
                }

                break;

            default:
        }
    }

    /**
     * Determine if the connected database is a MariaDB database.
     *
     * @return bool
     */
    private function isMaria(): bool
    {
        return str_contains(DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION), 'MariaDB');
    }

    /**
     * Check if the column is "tinyint(1)", if yes then generate as boolean.
     *
     * @return bool
     */
    private function isBoolean(): bool
    {
        if ($this->autoincrement) {
            return false;
        }

        $showColumn = $this->mysqlRepository->showColumn($this->tableName, $this->name);

        if ($showColumn === null) {
            return false;
        }

        return Str::startsWith($showColumn->getType(), 'tinyint(1)');
    }

    /**
     * Get the preset values if the column is "enum".
     *
     * @return string[]
     */
    private function getEnumPresetValues(): array
    {
        return $this->mysqlRepository->getEnumPresetValues(
            $this->tableName,
            $this->name
        )->toArray();
    }

    /**
     * Get the preset values if the column is "set".
     *
     * @return string[]
     */
    private function getSetPresetValues(): array
    {
        return $this->mysqlRepository->getSetPresetValues(
            $this->tableName,
            $this->name
        )->toArray();
    }

    /**
     * Check if "set" method is available, then get "set" preset values.
     * If not available, change type to string with 255 length.
     *
     * @return void
     */
    private function useSetOrString(): void
    {
        if ($this->hasSet()) {
            $this->presetValues = $this->getSetPresetValues();
            return;
        }

        $this->type   = ColumnType::STRING();
        $this->length = 255; // Framework default string length.
    }

    /**
     * Check if the column uses "on update CURRENT_TIMESTAMP".
     *
     * @return bool
     */
    private function hasOnUpdateCurrentTimestamp(): bool
    {
        return $this->mysqlRepository->isOnUpdateCurrentTimestamp($this->tableName, $this->name);
    }

    /**
     * MariaDB return `longText` instead of `json` column.
     * Check the check constraint of this column to check if type is `json`.
     * Return true if check constraint contains `json_valid` keyword.
     *
     * @return bool
     */
    private function isJson(): bool
    {
        $checkConstraint = $this->mariaDBRepository->getCheckConstraintForJson($this->tableName, $this->name);
        return $checkConstraint !== null;
    }

    /**
     * Set virtual definition if the column is virtual.
     *
     * @return void
     */
    private function setVirtualDefinition(): void
    {
        $virtualDefinition = $this->mysqlRepository->getVirtualDefinition($this->tableName, $this->name);

        if ($virtualDefinition === null) {
            return;
        }

        // The definition of MySQL8 returned `concat(string,_utf8mb4\' \',string_255)`.
        // Replace `\'` to `'` here to avoid double escape.
        $this->virtualDefinition = str_replace("\'", "'", $virtualDefinition);
    }

    /**
     * Set stored definition if the column is stored.
     *
     * @return void
     */
    private function setStoredDefinition(): void
    {
        $storedDefinition = $this->mysqlRepository->getStoredDefinition($this->tableName, $this->name);

        if ($storedDefinition === null) {
            return;
        }

        // The definition of MySQL8 returned `concat(string,_utf8mb4\' \',string_255)`.
        // Replace `\'` to `'` here to avoid double escape.
        $this->storedDefinition = str_replace("\'", "'", $storedDefinition);
    }

    /**
     * @inheritDoc
     */
    protected function escapeDefault(?string $default): ?string
    {
        $default = parent::escapeDefault($default);

        if ($default === null) {
            return null;
        }

        return addcslashes($default, '\\');
    }
}
