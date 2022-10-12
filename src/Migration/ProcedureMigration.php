<?php

namespace KitLoong\MigrationsGenerator\Migration;

use KitLoong\MigrationsGenerator\Migration\Blueprint\CustomBlueprint;
use KitLoong\MigrationsGenerator\Schema\Models\Procedure;

class ProcedureMigration
{
    public function up(Procedure $procedure): CustomBlueprint
    {
        return new CustomBlueprint($procedure->getDefinition());
    }

    public function down(Procedure $procedure): CustomBlueprint
    {
        return new CustomBlueprint($procedure->getDropDefinition());
    }
}
