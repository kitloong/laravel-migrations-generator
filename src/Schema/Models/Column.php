<?php

namespace KitLoong\MigrationsGenerator\Schema\Models;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;

/**
 * Table column. Column type supported by the framework.
 * See https://laravel.com/docs/9.x/migrations#available-column-types
 */
interface Column extends Model
{
    /**
     * Get the column name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTableName(): string;

    /**
     * Get the column type.
     *
     * @return \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType
     */
    public function getType(): ColumnType;

    /**
     * Get the column length.
     *
     * @return int|null
     */
    public function getLength(): ?int;

    /**
     * Get the column scale.
     *
     * @return int
     */
    public function getScale(): int;

    /**
     * Check if the column is unsigned.
     *
     * @return bool
     */
    public function isUnsigned(): bool;

    /**
     * Check if the column is fixed.
     *
     * @return bool
     */
    public function isFixed(): bool;

    /**
     * Check if the column is not null.
     *
     * @return bool
     */
    public function isNotNull(): bool;

    /**
     * Get the column default value.
     *
     * @return string|null
     */
    public function getDefault(): ?string;

    /**
     * Get the column collation.
     *
     * @return string|null
     */
    public function getCollation(): ?string;

    /**
     * Get the column charset.
     *
     * @return string|null
     */
    public function getCharset(): ?string;

    /**
     * Check if the column is autoincrement.
     *
     * @return bool
     */
    public function isAutoincrement(): bool;

    /**
     * Get the column precision.
     *
     * @return int
     */
    public function getPrecision(): int;

    /**
     * Get the column comment.
     *
     * @return string|null
     */
    public function getComment(): ?string;

    /**
     * Get the column preset values.
     * This is usually used for `enum` and `set`.
     *
     * @return string[]
     */
    public function getPresetValues(): array;

    /**
     * Check if the column uses "on update CURRENT_TIMESTAMP".
     * This is usually used for MySQL `timestamp` and `timestampTz`.
     *
     * @return bool
     */
    public function isOnUpdateCurrentTimestamp(): bool;

    /**
     * Check if default should set as raw.
     * Raw default will be generated with DB::raw().
     *
     * @return bool
     */
    public function isRawDefault(): bool;
}
