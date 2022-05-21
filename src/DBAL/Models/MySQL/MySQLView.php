<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\MySQL;

use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALView;

class MySQLView extends DBALView
{
    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    protected function handle(DoctrineDBALView $view): void
    {
        $this->createViewSQL = $this->makeCreateViewSQL($this->quotedName, $view->getSql());
    }
}
