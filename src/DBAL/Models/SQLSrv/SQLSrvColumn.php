<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv;

use KitLoong\MigrationsGenerator\DBAL\Models\DBALColumn;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;

class SQLSrvColumn extends DBALColumn
{
    // Doctrine DBAL return scale = 3 and length = 8 if datetime is created with default precision.
    private const DATETIME_EMPTY_SCALE  = 3;
    private const DATETIME_EMPTY_LENGTH = 8;

    // Doctrine DBAL return scale = 7 and length = 10 if datetimeTz is created with default precision.
    private const DATETIME_TZ_EMPTY_SCALE  = 7;
    private const DATETIME_TZ_EMPTY_LENGTH = 10;

    private const TEXT_TYPE   = 'nvarchar';
    private const TEXT_LENGTH = -1;

    /**
     * @var \KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository
     */
    private $repository;

    /**
     * @inheritDoc
     */
    protected function handle(): void
    {
        $this->repository = app(SQLSrvRepository::class);

        $this->setTypeToIncrements(false);

        switch ($this->type) {
            case ColumnType::DATE():
            case ColumnType::DATETIME():
            case ColumnType::DATETIME_TZ():
            case ColumnType::TIME():
            case ColumnType::TIME_TZ():
            case ColumnType::TIMESTAMP():
            case ColumnType::TIMESTAMP_TZ():
            case ColumnType::SOFT_DELETES():
            case ColumnType::SOFT_DELETES_TZ():
                $this->length = $this->getDataTypeLength();
                break;

            case ColumnType::FLOAT():
                $this->fixFloatLength();
                break;

            case ColumnType::STRING():
                if ($this->isText()) {
                    $this->type = ColumnType::TEXT();
                    break;
                }

                $this->presetValues = $this->getEnumPresetValues();

                if (count($this->presetValues) > 0) {
                    $this->type = ColumnType::ENUM();
                }

                break;

            default:
        }
    }

    /**
     * Get the datetime column length.
     * MySQL and PgSQL use "length" for precision while SQLSrv uses "scale".
     * Return "scale" as "length".
     *
     * @return int|null
     */
    private function getDataTypeLength(): ?int
    {
        $columnDef = $this->repository->getColumnDefinition($this->tableName, $this->name);

        switch ($this->type) {
            case ColumnType::DATETIME():
                if (
                    $columnDef->getScale() === self::DATETIME_EMPTY_SCALE
                    && $columnDef->getLength() === self::DATETIME_EMPTY_LENGTH
                ) {
                    return null;
                }

                return $this->scale;

            case ColumnType::DATETIME_TZ():
                if (
                    $columnDef->getScale() === self::DATETIME_TZ_EMPTY_SCALE
                    && $columnDef->getLength() === self::DATETIME_TZ_EMPTY_LENGTH
                ) {
                    return null;
                }

                return $this->scale;

            default:
                return $this->scale;
        }
    }

    /**
     * Check if the column type is "text".
     *
     * @return bool
     */
    private function isText(): bool
    {
        $columnDef = $this->repository->getColumnDefinition($this->tableName, $this->name);

        if ($columnDef === null) {
            return false;
        }

        return $columnDef->getType() === self::TEXT_TYPE && $columnDef->getLength() === self::TEXT_LENGTH;
    }

    /**
     * The framework always create float without precision.
     * However, Doctrine DBAL always return precisions 53 and scale 0.
     * Reset precisions and scale to 0 here.
     *
     * @return void
     */
    private function fixFloatLength(): void
    {
        if ($this->precision !== 53 || $this->scale !== 0) {
            return;
        }

        $this->precision = 0;
        $this->scale     = 0;
    }

    /**
     * Get the preset values if the column is `enum`.
     *
     * @return string[]
     */
    private function getEnumPresetValues(): array
    {
        return $this->repository->getEnumPresetValues(
            $this->tableName,
            $this->name
        )->toArray();
    }
}
