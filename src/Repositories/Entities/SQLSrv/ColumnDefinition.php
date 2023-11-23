<?php

namespace KitLoong\MigrationsGenerator\Repositories\Entities\SQLSrv;

use Illuminate\Support\Collection;
use stdClass;

class ColumnDefinition
{
    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var int */
    private $length;

    /** @var bool */
    private $notnull;

    /** @var string|null */
    private $default;

    /** @var int */
    private $scale;

    /** @var int */
    private $precision;

    /** @var bool */
    private $autoincrement;

    /** @var string|null */
    private $collation;

    /** @var string|null */
    private $comment;

    public function __construct(stdClass $column)
    {
        // Convert column property to case-insensitive
        $lowerKey = (new Collection((array) $column))->mapWithKeys(function ($item, $key) {
            return [strtolower($key) => $item];
        });

        $this->name          = $lowerKey['name'];
        $this->type          = $lowerKey['type'];
        $this->length        = $lowerKey['length'];
        $this->notnull       = $lowerKey['notnull'];
        $this->default       = $lowerKey['default'];
        $this->scale         = $lowerKey['scale'];
        $this->precision     = $lowerKey['precision'];
        $this->autoincrement = $lowerKey['autoincrement'];
        $this->collation     = $lowerKey['collation'];
        $this->comment       = $lowerKey['comment'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function isNotnull(): bool
    {
        return $this->notnull;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function getScale(): int
    {
        return $this->scale;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function isAutoincrement(): bool
    {
        return $this->autoincrement;
    }

    public function getCollation(): ?string
    {
        return $this->collation;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
