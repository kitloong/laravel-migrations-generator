<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\SQLSrv;

use Doctrine\DBAL\Schema\View as DoctrineDBALView;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALView;
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;

class SQLSrvView extends DBALView
{
    /**
     * @inheritDoc
     */
    protected function handle(DoctrineDBALView $view): void
    {
        $repository = app(SQLSrvRepository::class);

        // Stop if Doctrine DBAL contains CREATE VIEW SQL
        if ($view->getSql() !== '') {
            $this->createViewSQL = $view->getSql();
            return;
        }

        // Use repository to get view definition.
        $viewDefinition = $repository->getView($view->getName());
        if ($viewDefinition === null) {
            return;
        }

        $this->createViewSQL = $viewDefinition->getDefinition();
    }
}
