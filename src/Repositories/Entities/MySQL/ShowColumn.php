<?php

namespace KitLoong\MigrationsGenerator\Repositories\Entities\MySQL;

use Illuminate\Support\Collection;
use stdClass;

/**
 * Class ShowColumn
 *
 * Entity from MySQL SHOW COLUMNS statement
 *
 * @see https://dev.mysql.com/doc/refman/5.7/en/show-columns.html
 * @see https://dev.mysql.com/doc/refman/8.0/en/show-columns.html
 */
class ShowColumn
{
    /** @var string */
    private $field;

    /** @var string */
    private $type;

    /** @var string */
    private $null;

    /** @var string */
    private $key;

    /** @var string|null */
    private $default;

    /** @var string */
    private $extra;

    public function __construct(stdClass $column)
    {
        // Convert column property to case-insensitive
        // Issue https://github.com/kitloong/laravel-migrations-generator/issues/34
        $lowerKey = (new Collection($column))->mapWithKeys(function ($item, $key) {
            return [strtolower($key) => $item];
        });

        $this->field   = $lowerKey['field'];
        $this->type    = $lowerKey['type'];
        $this->null    = $lowerKey['null'];
        $this->key     = $lowerKey['key'];
        $this->default = $lowerKey['default'];
        $this->extra   = $lowerKey['extra'];
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getNull(): string
    {
        return $this->null;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function getExtra(): string
    {
        return $this->extra;
    }
}
