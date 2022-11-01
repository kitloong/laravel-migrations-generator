<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint\Support;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Enum\Driver;
use KitLoong\MigrationsGenerator\Enum\Migrations\ColumnName;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;

trait MergeTimestamps
{
    /**
     * Merges created_at and updated_at into timestamps or timestampsTz.
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Property[]|\KitLoong\MigrationsGenerator\Migration\Blueprint\Method[]|string[]  $lines  TableBlueprint lines.
     * @param  bool  $tz  Is timezone.
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Property[]|\KitLoong\MigrationsGenerator\Migration\Blueprint\Method[]|string[]  TableBlueprint lines after merged.
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

            if (!$this->checkTimestamps(ColumnName::CREATED_AT(), $line, $tz)) {
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

        if (!$this->checkTimestamps(ColumnName::UPDATED_AT(), $updatedAt, $tz)) {
            return $lines;
        }

        $updatedAtLength = $updatedAt->getValues()[1] ?? 0; // Get length from values or default to 0.

        if ($length === $updatedAtLength) {
            $isTimestamps = true;
        }

        if ($isTimestamps === true) {
            $lines[$createdAtLineKey] = $this->makeMethod($length, $tz);
            unset($lines[$updatedAtLineKey]);
        }

        return $lines;
    }

    /**
     * Check if column name (created_at or updated_at) is possible a timestamps.
     *
     * @param  string  $name  Column name, created_at or updated_at.
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
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

        return $method->getChains()[0]->getName() === ColumnModifier::NULLABLE()->getValue()
            && empty($method->getChains()[0]->getValues());
    }

    /**
     * Check if column type is possible a timestamps.
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
     * @param  bool  $tz  Is timezone.
     * @return bool
     */
    private function isPossibleTimestampsColumn(Method $method, bool $tz): bool
    {
        if (Driver::SQLSRV()->getValue() === DB::getDriverName()) {
            return $method->getName() === $this->sqlSrvTimestampsColumnType($tz)->getValue();
        }

        return $method->getName() === $this->timestampsColumnType($tz)->getValue();
    }

    /**
     * SQL server uses datetime.
     * Only datetime or datetimeTz can be merged into timestamps.
     *
     * @param  bool  $tz  Is timezone.
     * @return \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType Column type.
     */
    private function sqlSrvTimestampsColumnType(bool $tz): ColumnType
    {
        if ($tz) {
            return ColumnType::DATETIME_TZ();
        }

        return ColumnType::DATETIME();
    }

    /**
     * Only timestamp or timestampTz can be merged into timestamps.
     *
     * @param  bool  $tz  Is timezone.
     * @return \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType Column type.
     */
    private function timestampsColumnType(bool $tz): ColumnType
    {
        if ($tz) {
            return ColumnType::TIMESTAMP_TZ();
        }

        return ColumnType::TIMESTAMP();
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
        }

        return ColumnType::TIMESTAMPS;
    }

    /**
     * @param  int  $length
     * @param  bool  $tz
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method
     */
    private function makeMethod(int $length, bool $tz): Method
    {
        if ($length === 0) { // MIGRATION_DEFAULT_PRECISION = 0, no need specify length 0.
            return new Method($this->timestamps($tz));
        }

        return new Method($this->timestamps($tz), $length);
    }
}
