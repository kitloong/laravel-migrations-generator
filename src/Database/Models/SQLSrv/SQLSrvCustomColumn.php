<?php

namespace KitLoong\MigrationsGenerator\Database\Models\SQLSrv;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Database\Models\DatabaseCustomColumn;
use KitLoong\MigrationsGenerator\Support\TableName;

class SQLSrvCustomColumn extends DatabaseCustomColumn
{
    use SQLSrvParser;

    use TableName;

    /**
     * @inheritDoc
     */
    public function __construct(string $table, array $column)
    {
        parent::__construct($table, $column);

        $blueprint = new Blueprint($this->stripTablePrefix($table));
        $blueprint->addColumn('string', $column['name'], [
            'autoIncrement' => $column['auto_increment'],
            'default'       => $this->parseDefault($column['default']),
            'nullable'      => $column['nullable'],
            //            'after' => "id",
        ]);

        $sqls    = $blueprint->toSql(Schema::getConnection(), Schema::getConnection()->getSchemaGrammar());
        $sqls[0] = Str::replaceFirst(' nvarchar() ', ' ' . $column['type'] . ' ', $sqls[0]);

        $this->sqls = $sqls;
    }
}
