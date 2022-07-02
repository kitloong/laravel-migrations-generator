<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use KitLoong\MigrationsGenerator\Schema\Models\ForeignKey;

abstract class DBALForeignKey implements ForeignKey
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string[]
     */
    protected $localColumns;

    /**
     * @var string[]
     */
    protected $foreignColumns;

    /**
     * @var string
     */
    protected $foreignTableName;

    /**
     * @var string|null
     */
    protected $onUpdate;

    /**
     * @var string|null
     */
    protected $onDelete;

    public function __construct(string $table, ForeignKeyConstraint $foreignKeyConstraint)
    {
        $this->tableName        = $table;
        $this->name             = $foreignKeyConstraint->getName();
        $this->localColumns     = $foreignKeyConstraint->getUnquotedLocalColumns();
        $this->foreignColumns   = $foreignKeyConstraint->getUnquotedForeignColumns();
        $this->foreignTableName = $foreignKeyConstraint->getForeignTableName();
        $this->onUpdate         = $foreignKeyConstraint->getOption('onUpdate');
        $this->onDelete         = $foreignKeyConstraint->getOption('onDelete');
    }

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
    public function getLocalColumns(): array
    {
        return $this->localColumns;
    }

    /**
     * @inheritDoc
     */
    public function getForeignColumns(): array
    {
        return $this->foreignColumns;
    }

    /**
     * @inheritDoc
     */
    public function getForeignTableName(): string
    {
        return $this->foreignTableName;
    }

    /**
     * @inheritDoc
     */
    public function getOnUpdate(): ?string
    {
        return $this->onUpdate;
    }

    /**
     * @inheritDoc
     */
    public function getOnDelete(): ?string
    {
        return $this->onDelete;
    }
}
