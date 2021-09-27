<?php

namespace MigrationsGenerator\Schema\SQLSrv;

class Column
{
    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var int */
    private $length = 0;

    /** @var bool */
    private $notnull;

    /** @var string|null */
    private $default;

    /** @var int */
    private $scale = 0;

    /** @var int */
    private $precision = 0;

    /** @var bool */
    private $autoincrement = false;

    /** @var string|null */
    private $collation;

    /** @var string|null */
    private $comment;

    /**
     * Column constructor.
     * @param  string  $name
     * @param  string  $type
     * @param  int  $length
     * @param  bool  $notnull
     * @param  int  $scale
     * @param  int  $precision
     * @param  bool  $autoincrement
     * @param  string|null  $default
     * @param  string|null  $collation
     * @param  string|null  $comment
     */
    public function __construct(
        string $name,
        string $type,
        int $length,
        bool $notnull,
        int $scale,
        int $precision,
        bool $autoincrement,
        ?string $default,
        ?string $collation,
        ?string $comment
    ) {
        $this->name          = $name;
        $this->type          = $type;
        $this->length        = $length;
        $this->notnull       = $notnull;
        $this->default       = $default;
        $this->scale         = $scale;
        $this->precision     = $precision;
        $this->autoincrement = $autoincrement;
        $this->collation     = $collation;
        $this->comment       = $comment;
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
