<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\PgSQL;

use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALView;

class PgSQLView extends DBALView
{
    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    protected function handle(DoctrineDBALView $view): void
    {
        $this->createViewSQL = $this->makeCreateViewSQL($this->quotedName, $view->getSql());

        $searchPath = DB::connection()->getConfig('search_path') ?: DB::connection()->getConfig('schema');

        if ($view->getNamespaceName() === $searchPath) {
            // Strip namespace from name.
            $name                = $view->getShortestName($view->getNamespaceName());
            $this->name          = $this->makeName($name);
            $this->quotedName    = $this->makeQuotedName($this->name);
            $this->createViewSQL = $this->makeCreateViewSQL($this->quotedName, $view->getSql());
        }
    }
}
