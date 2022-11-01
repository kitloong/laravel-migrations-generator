<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models;

use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Schema\Models\View;
use KitLoong\MigrationsGenerator\Support\AssetNameQuote;

abstract class DBALView implements View
{
    use AssetNameQuote;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $quotedName;

    /**
     * @var string
     */
    protected $definition;

    /**
     * @var string
     */
    protected $dropDefinition;

    public function __construct(DoctrineDBALView $view)
    {
        $this->name           = $this->trimQuotes($view->getName());
        $this->quotedName     = DB::getDoctrineConnection()->quoteIdentifier($this->name);
        $this->definition     = '';
        $this->dropDefinition = "DROP VIEW IF EXISTS $this->quotedName";

        $this->handle($view);
    }

    /**
     * Instance extend this abstract may run special handling.
     *
     * @param  \Doctrine\DBAL\Schema\View  $view
     * @return void
     */
    abstract protected function handle(DoctrineDBALView $view): void;

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
