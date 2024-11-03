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
     */
    public function getName(): string;

    /**
     * Get the table name.
     */
    public function getTableName(): string;

    /**
     * Get the column type.
     */
    public function getType(): ColumnType;

    /**
     * Get the column length.
     */
    public function getLength(): ?int;

    /**
     * Get the column scale.
     */
    public function getScale(): int;

    /**
     * Check if the column is unsigned.
     */
    public function isUnsigned(): bool;

    /**
     * Check if the column is not null.
     */
    public function isNotNull(): bool;

    /**
     * Get the column default value.
     */
    public function getDefault(): ?string;

    /**
     * Get the column collation.
     */
    public function getCollation(): ?string;

    /**
     * Get the column charset.
     */
    public function getCharset(): ?string;

    /**
     * Check if the column is autoincrement.
     */
    public function isAutoincrement(): bool;

    /**
     * Get the column precision.
     */
    public function getPrecision(): ?int;

    /**
     * Get the column comment.
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
     * Get the spatial column subtype.
     */
    public function getSpatialSubType(): ?string;

    /**
     * Get the spatial column srID.
     */
    public function getSpatialSrID(): ?int;

    /**
     * Check if the column uses "on update CURRENT_TIMESTAMP".
     * This is usually used for MySQL `timestamp` and `datetime`.
     */
    public function isOnUpdateCurrentTimestamp(): bool;

    /**
     * Check if default should set as raw.
     * Raw default will be generated with DB::raw().
     */
    public function isRawDefault(): bool;

    /**
     * Get the virtual column definition.
     */
    public function getVirtualDefinition(): ?string;

    /**
     * Get the stored column definition.
     */
    public function getStoredDefinition(): ?string;
}
