<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\DBBuilder;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\MethodStringHelper;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\Stringable;

/**
 * Create migration lines with `DB::statement`.
 *
 * eg 1:
 * ```
 * DB::statement("CREATE VIEW active_users AS select * from users where status = 1");
 * ```
 *
 * eg 2:
 * ```
 * DB::connection('sqlite')->statement("CREATE VIEW active_users AS select * from users where status = 1");
 * ```
 */
class DBStatementBlueprint implements WritableBlueprint
{
    use Stringable;
    use MethodStringHelper;

    /**
     * DBStatementBlueprint constructor.
     *
     * @param  string  $sql  The SQL statement.
     */
    public function __construct(private readonly string $sql)
    {
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $method = $this->connection('DB', DBBuilder::STATEMENT);
        $query  = $this->escapeDoubleQuote($this->sql);
        return "$method(\"$query\");";
    }
}
