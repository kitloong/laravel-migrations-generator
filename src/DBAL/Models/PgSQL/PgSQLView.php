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
        $searchPath = DB::connection()->getConfig('search_path') ?: DB::connection()->getConfig('schema');

        if ($view->getNamespaceName() !== $searchPath) {
            $this->definition = DB::getDoctrineConnection()
                ->getDatabasePlatform()
                ->getCreateViewSQL($this->quotedName, $view->getSql());
            return;
        }

        // Strip namespace from name.
        $name                 = $view->getShortestName($view->getNamespaceName());
        $this->name           = $this->trimQuotes($name);
        $this->quotedName     = DB::getDoctrineConnection()->quoteIdentifier($this->name);
        $this->definition     = DB::getDoctrineConnection()
            ->getDatabasePlatform()
            ->getCreateViewSQL($this->quotedName, $view->getSql());
        $this->dropDefinition = "DROP VIEW IF EXISTS $this->quotedName";
    }
}
