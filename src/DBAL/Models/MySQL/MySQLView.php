<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\MySQL;

use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use KitLoong\MigrationsGenerator\DBAL\Connection;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALView;

class MySQLView extends DBALView
{
    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    protected function handle(DoctrineDBALView $view): void
    {
        $this->definition = app(Connection::class)->getDoctrineConnection()
            ->getDatabasePlatform()
            ->getCreateViewSQL($this->quotedName, $view->getSql());
    }
}
