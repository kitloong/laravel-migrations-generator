<?php

namespace MigrationsGenerator\Generators\Blueprint\Support;

use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\Blueprint\Property;
use MigrationsGenerator\Generators\MigrationConstants\ColumnName;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;
use MigrationsGenerator\MigrationsGeneratorSetting;

class MergeTimestamps
{
    /**
     * Merges created_at and updated_at into timestamps or timestampsTz.
     *
     * @param  Property[]|Method[]|string[]  $lines  TableBlueprint lines.
     * @param  bool  $tz  Is timezone.
     * @return Property[]|Method[]|string[]  TableBlueprint lines after merged.
     */
    public function merge(array $lines, bool $tz): array
    {
        $length           = 0; // Default length.
        $createdAtLineKey = 0;
        $updatedAtLineKey = 0;
        $isTimestamps     = false;

        foreach ($lines as $key => $line) {
            if (!$line instanceof Method) {
                continue;
            }

            if (!$this->checkTimestamps(ColumnName::CREATED_AT, $line, $tz)) {
                continue;
            }

            $length           = $line->getValues()[1] ?? 0; // Get length from values or default to 0.
            $createdAtLineKey = $key;
            $updatedAtLineKey = $key + 1; // updated_at should be the next column.
            break;
        }

        $updatedAt = $lines[$updatedAtLineKey] ?? null;
        if (!$updatedAt instanceof Method) {
            return $lines;
        }

        if (!$this->checkTimestamps(ColumnName::UPDATED_AT, $updatedAt, $tz)) {
            return $lines;
        }

        $updatedAtLength = $updatedAt->getValues()[1] ?? 0; // Get length from values or default to 0.
        if ($length === $updatedAtLength) {
            $isTimestamps = true;
        }

        if ($isTimestamps === true) {
            if ($length === 0) { // MIGRATION_DEFAULT_PRECISION = 0, no need specify length 0.
                $lines[$createdAtLineKey] = new Method($this->timestamps($tz));
            } else {
                $lines[$createdAtLineKey] = new Method($this->timestamps($tz), $length);
            }

            unset($lines[$updatedAtLineKey]);
        }

        return $lines;
    }

    /**
     * Checks if column name (created_at or updated_at) is possible a timestamps.
     *
     * @param  string  $name  Column name, created_at or updated_at.
     * @param  Method  $method
     * @param  bool  $tz  Is timezone.
     * @return bool
     */
    private function checkTimestamps(string $name, Method $method, bool $tz): bool
    {
        if (!$this->isPossibleTimestampsColumn($method, $tz)) {
            return false;
        }

        if ($method->getValues()[0] !== $name) {
            return false;
        }

        if ($method->countChain() !== 1) {
            return false;
        }

        return $method->getChains()[0]->getName() === ColumnModifier::NULLABLE && empty($method->getChains()[0]->getValues());
    }

    /**
     * Checks if column type is possible a timestamps.
     *
     * @param  Method  $method
     * @param  bool  $tz  Is timezone.
     * @return bool
     */
    private function isPossibleTimestampsColumn(Method $method, bool $tz): bool
    {
        if (app(MigrationsGeneratorSetting::class)->getPlatform() === Platform::SQLSERVER) {
            switch ($method->getName()) {
                case $this->sqlSrvTimestampsColumnType($tz):
                    return true;
                default:
            }
        } else {
            switch ($method->getName()) {
                case $this->timestampsColumnType($tz):
                    return true;
                default:
            }
        }

        return false;
    }

    /**
     * SQL server uses datetime.
     * Only datetime or datetimeTz can be merged into timestamps.
     *
     * @param  bool  $tz  Is timezone.
     * @return string Column type.
     */
    private function sqlSrvTimestampsColumnType(bool $tz): string
    {
        if ($tz) {
            return ColumnType::DATETIME_TZ;
        } else {
            return ColumnType::DATETIME;
        }
    }

    /**
     * Only timestamp or timestampTz can be merged into timestamps.
     *
     * @param  bool  $tz  Is timezone.
     * @return string Column type.
     */
    private function timestampsColumnType(bool $tz): string
    {
        if ($tz) {
            return ColumnType::TIMESTAMP_TZ;
        } else {
            return ColumnType::TIMESTAMP;
        }
    }

    /**
     * Could merge into timestamps or timestampsTz.
     *
     * @param  bool  $tz  Is timezone.
     * @return string
     */
    private function timestamps(bool $tz): string
    {
        if ($tz) {
            return ColumnType::TIMESTAMPS_TZ;
        } else {
            return ColumnType::TIMESTAMPS;
        }
    }
}
