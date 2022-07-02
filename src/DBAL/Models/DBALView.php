<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models;

use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Schema\Models\View;

abstract class DBALView implements View
{
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
    protected $createViewSQL;

    public function __construct(DoctrineDBALView $view)
    {
        $this->name          = $this->makeName($view->getName());
        $this->quotedName    = $this->makeQuotedName($this->name);

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
     * Trim quotes and set name.
     *
     * @param  string  $name
     * @return string
     */
    protected function makeName(string $name): string
    {
        return (string) str_replace(['`', '"', '[', ']'], '', $name);
    }

    /**
     * Set quoted name.
     *
     * @param  string  $name
     * @return string
     */
    protected function makeQuotedName(string $name): string
    {
        return DB::getDoctrineConnection()->quoteIdentifier($name);
    }

    /**
     * Set create view SQL.
     *
     * @param  string  $quotedName
     * @param  string  $sql
     * @return string
     * @throws \Doctrine\DBAL\Exception
     */
    protected function makeCreateViewSQL(string $quotedName, string $sql): string
    {
        return DB::getDoctrineConnection()
            ->getDatabasePlatform()
            ->getCreateViewSQL($quotedName, $sql);
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
    public function getQuotedName(): string
    {
        return $this->quotedName;
    }

    /**
     * @inheritDoc
     */
    public function getCreateViewSql(): string
    {
        return $this->createViewSQL;
    }
}
