<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\MethodStringHelper;

class CustomBlueprint implements WritableBlueprint
{
    use Stringable;
    use MethodStringHelper;

    /**
     * @var string
     */
    private $sql;

    /**
     * CustomBlueprint constructor.
     *
     * @param  string  $sql  The SQL statement.
     */
    public function __construct(string $sql)
    {
        $this->sql = $sql;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $method = $this->connection('DB', 'statement');
        $query  = $this->escapeDoubleQuote($this->sql);
        return "$method(\"$query\");";
    }
}
