<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\MethodStringHelper;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\Stringable;

class ViewBlueprint implements WritableBlueprint
{
    use Stringable;
    use MethodStringHelper;

    /**
     * @var string
     */
    private $view;

    /**
     * @var string
     */
    private $createViewSql;

    /**
     * ViewBlueprint constructor.
     *
     * @param  string  $view  View name.
     */
    public function __construct(string $view)
    {
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
        $dbStatement = $this->connection('DB', 'statement');

        $query = $this->escapeDoubleQuote("DROP VIEW IF EXISTS $this->view");
        if ($this->createViewSql !== '') {
            $query = $this->escapeDoubleQuote($this->createViewSql);
        }

        return "$dbStatement(\"$query\");";
    }
}
