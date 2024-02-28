<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLSrv;

use KitLoong\MigrationsGenerator\Database\Models\DatabaseView;

class SQLSrvView extends DatabaseView
{
    /**
     * @inheritDoc
     */
    public function __construct(array $view)
    {
        parent::__construct($view);

        $this->definition = $view['definition'];
    }
}
