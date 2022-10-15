<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\SQLite;

use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALView;

class SQLiteView extends DBALView
{
    /**
     * @inheritDoc
     */
    protected function handle(DoctrineDBALView $view): void
    {
        $this->definition = $view->getSql();
    }
}
