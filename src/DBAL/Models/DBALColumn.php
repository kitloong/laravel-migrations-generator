<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models;

use Doctrine\DBAL\Schema\Column as DoctrineDBALColumn;
use KitLoong\MigrationsGenerator\DBAL\Types\Types;
use KitLoong\MigrationsGenerator\Enum\Migrations\ColumnName;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Schema\Models\Column;

abstract class DBALColumn implements Column
{
    protected $autoincrement;
    protected $charset;
    protected $collation;
    protected $comment;
    protected $default;
    protected $fixed;
    protected $length;
    protected $name;
    protected $notNull;
    protected $onUpdateCurrentTimestamp;
    protected $precision;
    protected $presetValues;
    protected $scale;
    protected $tableName;
    protected $type;
    protected $unsigned;

    private const REMEMBER_TOKEN_LENGTH = 100;

    /**
     * @param  string  $table
     * @param  \Doctrine\DBAL\Schema\Column  $column
     */
    public function __construct(string $table, DoctrineDBALColumn $column)
    {
        $this->tableName                = $table;
        $this->name                     = $column->getName();
        $this->type                     = $this->mapToColumnType($column->getType()->getName());
        $this->length                   = $column->getLength();
        $this->scale                    = $column->getScale();
        $this->precision                = $column->getPrecision();
        $this->comment                  = $column->getComment();
        $this->fixed                    = $column->getFixed();
        $this->unsigned                 = $column->getUnsigned();
        $this->notNull                  = $column->getNotnull();
        $this->default                  = $column->getDefault();
        $this->collation                = $column->getPlatformOptions()['collation'] ?? null;
        $this->charset                  = $column->getPlatformOptions()['charset'] ?? null;
        $this->autoincrement            = $column->getAutoincrement();
        $this->presetValues             = [];
        $this->onUpdateCurrentTimestamp = false;

        $this->setTypeToIncrements();
        $this->setTypeToUnsigned();
        $this->setTypeToSoftDeletes();
        $this->setTypeToRememberToken();
        $this->setTypeToChar();
        $this->fixDoubleLength();

        $this->handle();
    }

    /**
     * Instance extend this abstract may run special handling.
     *
     * @return void
     */
    abstract protected function handle(): void;

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @inheritDoc
     */
    public function getType(): ColumnType
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @inheritDoc
     */
    public function getScale(): int
    {
        return $this->scale;
    }

    /**
     * @inheritDoc
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @inheritDoc
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @inheritDoc
     */
    public function isFixed(): bool
    {
        return $this->fixed;
    }

    /**
     * @inheritDoc
     */
    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    /**
     * @inheritDoc
     */
    public function isNotNull(): bool
    {
        return $this->notNull;
    }

    /**
     * @inheritDoc
     */
    public function getDefault(): ?string
    {
        return $this->default;
    }

    /**
     * @inheritDoc
     */
    public function getCollation(): ?string
    {
        return $this->collation;
    }

    /**
     * @inheritDoc
     */
    public function getCharset(): ?string
    {
        return $this->charset;
    }

    /**
     * @inheritDoc
     */
    public function isAutoincrement(): bool
    {
        return $this->autoincrement;
    }

    /**
     * @inheritDoc
     */
    public function getPresetValues(): array
    {
        return $this->presetValues;
    }

    /**
     * @inheritDoc
     */
    public function isOnUpdateCurrentTimestamp(): bool
    {
        return $this->onUpdateCurrentTimestamp;
    }

    /**
     * Converts built-in DBALTypes to ColumnType (Laravel column).
     *
     * @param  string  $dbalType
     * @return \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType
     */
    private function mapToColumnType(string $dbalType): ColumnType
    {
        $map = [
            Types::BIGINT               => ColumnType::BIG_INTEGER(),
            Types::BLOB                 => ColumnType::BINARY(),
            Types::DATE_MUTABLE         => ColumnType::DATE(),
            Types::DATE_IMMUTABLE       => ColumnType::DATE(),
            Types::DATETIME_MUTABLE     => ColumnType::DATETIME(),
            Types::DATETIME_IMMUTABLE   => ColumnType::DATETIME(),
            Types::DATETIMETZ_MUTABLE   => ColumnType::DATETIME_TZ(),
            Types::DATETIMETZ_IMMUTABLE => ColumnType::DATETIME_TZ(),
            Types::SMALLINT             => ColumnType::SMALL_INTEGER(),
            Types::GUID                 => ColumnType::UUID(),
            Types::TIME_MUTABLE         => ColumnType::TIME(),
            Types::TIME_IMMUTABLE       => ColumnType::TIME(),
        ];

        // $dbalType outside from the map has the same name with ColumnType.
        return $map[$dbalType] ?? ColumnType::from($dbalType);
    }

    /**
     * Set the column type to "increments" or "*Increments" if the column is auto increment and unsigned.
     *
     * @return void
     */
    private function setTypeToIncrements(): void
    {
        if (
            in_array($this->type, [
                ColumnType::BIG_INTEGER(),
                ColumnType::INTEGER(),
                ColumnType::MEDIUM_INTEGER(),
                ColumnType::SMALL_INTEGER(),
                ColumnType::TINY_INTEGER(),
            ])
            && $this->autoincrement
            && $this->unsigned
        ) {
            if ($this->type->equals(ColumnType::INTEGER())) {
                $this->type = ColumnType::INCREMENTS();
                return;
            }

            $this->type = ColumnType::from(str_replace('Integer', 'Increments', $this->type));
        }
    }

    /**
     * Set the column type to "unsigned*" if the column is unsigned.
     *
     * @return void
     */
    private function setTypeToUnsigned(): void
    {
        if (
            in_array($this->type, [
                ColumnType::BIG_INTEGER(),
                ColumnType::INTEGER(),
                ColumnType::MEDIUM_INTEGER(),
                ColumnType::SMALL_INTEGER(),
                ColumnType::TINY_INTEGER(),
                ColumnType::DECIMAL(),
            ])
            && $this->unsigned
        ) {
            $this->type = ColumnType::from('unsigned' . ucfirst($this->type));
        }
    }

    /**
     * Set the column type to "softDeletes" or "softDeletesTz".
     *
     * @return void
     */
    private function setTypeToSoftDeletes(): void
    {
        if ($this->name === ColumnName::DELETED_AT()->getValue()) {
            switch ($this->type) {
                case ColumnType::TIMESTAMP():
                    $this->type = ColumnType::SOFT_DELETES();
                    return;
                case ColumnType::TIMESTAMP_TZ():
                    $this->type = ColumnType::SOFT_DELETES_TZ();
                    return;
            }
        }
    }

    /**
     * Set the column type to "rememberToken".
     *
     * @return void
     */
    private function setTypeToRememberToken(): void
    {
        if (
            ColumnName::REMEMBER_TOKEN()->getValue() === $this->name
            && $this->length === self::REMEMBER_TOKEN_LENGTH
            && !$this->fixed
        ) {
            $this->type = ColumnType::REMEMBER_TOKEN();
        }
    }

    /**
     * Set the column type to "char".
     *
     * @return void
     */
    private function setTypeToChar(): void
    {
        if ($this->fixed) {
            $this->type = ColumnType::CHAR();
        }
    }

    /**
     * When double is created without total and places, $table->double('double');
     * Doctrine DBAL return precisions 10 and scale 0.
     * Reset precisions and scale to 0 here.
     *
     * @return void
     */
    private function fixDoubleLength(): void
    {
        if (
            $this->type->equals(ColumnType::DOUBLE())
            && $this->getPrecision() === 10
            && $this->getScale() === 0
        ) {
            $this->precision = 0;
            $this->scale     = 0;
        }
    }
}
