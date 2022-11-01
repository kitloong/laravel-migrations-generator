<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\MethodStringHelper;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\Stringable;

/**
 * Create migration lines with `DB::unprepared`.
 *
 * eg 1:
 * ```
 * DB::statement("CREATE PROCEDURE myProcedure() BEGIN SELECT * from table END");
 * ```
 *
 * eg 2:
 * ```
 * DB::connection('sqlite')->statement("CREATE PROCEDURE myProcedure() BEGIN SELECT * from table END");
 * ```
 */
class DBUnpreparedBlueprint implements WritableBlueprint
{
    use Stringable;
    use MethodStringHelper;

    /**
     * @var string
     */
    private $sql;

    /**
     * DBStatementBlueprint constructor.
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
        $method = $this->connection('DB', 'unprepared');
        $query  = $this->escapeDoubleQuote($this->sql);
        return "$method(\"$query\");";
    }
}
