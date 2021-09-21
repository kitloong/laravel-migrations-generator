<?php

namespace MigrationsGenerator\Generators\Blueprint\Support;

use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\ColumnName;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;
use MigrationsGenerator\MigrationsGeneratorSetting;

class MergeTimestamps
{
    public function merge(array $lines, bool $tz): array
    {
        $length           = 0;
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

            $length           = $line->getValues()[1] ?? 0;
            $createdAtLineKey = $key;
            $updatedAtLineKey = $key + 1;
            break;
        }

        $updatedAt = $lines[$updatedAtLineKey] ?? null;
        if (!$updatedAt instanceof Method) {
            return $lines;
        }

        if (!$this->checkTimestamps(ColumnName::UPDATED_AT, $updatedAt, $tz)) {
            return $lines;
        }

        $updatedAtLength = $updatedAt->getValues()[1] ?? 0;
        if ($length === $updatedAtLength) {
            $isTimestamps = true;
        }

        if ($isTimestamps === true) {
            if ($length === 0) { // MIGRATION_DEFAULT_PRECISION = 0
                $lines[$createdAtLineKey] = new Method($this->timestamps($tz));
            } else {
                $lines[$createdAtLineKey] = new Method($this->timestamps($tz), $length);
            }

            unset($lines[$updatedAtLineKey]);
        }

        return $lines;
    }

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
                    return false;
                default:
            }
        }

        return false;
    }

    private function sqlSrvTimestampsColumnType(bool $tz): string
    {
        if ($tz) {
            return ColumnType::DATETIME_TZ;
        } else {
            return ColumnType::DATETIME;
        }
    }

    private function timestampsColumnType(bool $tz): string
    {
        if ($tz) {
            return ColumnType::TIMESTAMP_TZ;
        } else {
            return ColumnType::TIMESTAMP;
        }
    }

    private function timestamps(bool $tz): string
    {
        if ($tz) {
            return ColumnType::TIMESTAMPS_TZ;
        } else {
            return ColumnType::TIMESTAMPS;
        }
    }
}
