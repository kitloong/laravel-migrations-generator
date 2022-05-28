<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

use Illuminate\Support\Facades\Config;

class ViewBlueprint implements WritableBlueprint
{
    use Stringable;

    private $connection;
    private $view;
    private $createViewSql;

    /**
     * ViewBlueprint constructor.
     *
     * @param  string  $connection  Connection name.
     * @param  string  $view  View name.
     */
    public function __construct(string $connection, string $view)
    {
        $this->connection    = $connection;
        $this->view          = $view;
        $this->createViewSql = '';
    }

    /**
     * @param  string  $createViewSql
     */
    public function setCreateViewSql(string $createViewSql): void
    {
        $this->createViewSql = $createViewSql;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $dbStatement = 'DB::statement';
        if ($this->connection !== Config::get('database.default')) {
            $dbStatement = "DB::connection('" . $this->connection . "')->statement";
        }

        $query = $this->escapeDoubleQuote("DROP VIEW IF EXISTS $this->view");
        if ($this->createViewSql !== '') {
            $query = $this->escapeDoubleQuote($this->createViewSql);
        }

        return "$dbStatement(\"$query\");";
    }
}
