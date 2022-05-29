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
        $lowerKey = (new Collection($column))->mapWithKeys(function ($item, $key) {
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return bool
     */
    public function isNotnull(): bool
    {
        return $this->notnull;
    }

    /**
     * @return string|null
     */
    public function getDefault(): ?string
    {
        return $this->default;
    }

    /**
     * @return int
     */
    public function getScale(): int
    {
        return $this->scale;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @return bool
     */
    public function isAutoincrement(): bool
    {
        return $this->autoincrement;
    }

    /**
     * @return string|null
     */
    public function getCollation(): ?string
    {
        return $this->collation;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }
}
