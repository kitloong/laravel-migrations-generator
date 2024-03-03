<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLite;

use KitLoong\MigrationsGenerator\Database\Models\DatabaseView;

class SQLiteView extends DatabaseView
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
