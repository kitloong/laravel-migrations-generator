<?php

namespace KitLoong\MigrationsGenerator\Database\Models;

use KitLoong\MigrationsGenerator\Schema\Models\View;
use KitLoong\MigrationsGenerator\Support\AssetNameQuote;

/**
 * @phpstan-import-type SchemaView from \KitLoong\MigrationsGenerator\Database\DatabaseSchema
 */
abstract class DatabaseView implements View
{
    use AssetNameQuote;

    protected string $name;

    protected string $quotedName;

    protected string $definition;

    protected string $dropDefinition;

    /**
     * @param  SchemaView  $view
     */
    public function __construct(array $view)
    {
        $this->name           = $view['name'];
        $this->quotedName     = $this->quoteIdentifier($view['name']);
        $this->definition     = '';
        $this->dropDefinition = "DROP VIEW IF EXISTS $this->quotedName";
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
    public function getDefinition(): string
    {
        return $this->definition;
    }

    /**
     * @inheritDoc
     */
    public function getDropDefinition(): string
    {
        return $this->dropDefinition;
    }
}
