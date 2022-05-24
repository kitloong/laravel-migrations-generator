<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Migration\Blueprint\ViewBlueprint;
use KitLoong\MigrationsGenerator\Schema\Models\View;

class ViewMigration
{
    /**
     * Generates `up` db statement for view.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\View  $view
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\ViewBlueprint
     */
    public function up(View $view): ViewBlueprint
    {
        $viewBlueprint = new ViewBlueprint(DB::getName(), $view->getQuotedName());
        $viewBlueprint->setCreateViewSql($view->getCreateViewSql());
        return $viewBlueprint;
    }

    /**
     * * Generates `down` db statement for view.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\View  $view
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\ViewBlueprint
     */
    public function down(View $view): ViewBlueprint
    {
        return new ViewBlueprint(DB::getName(), $view->getQuotedName());
    }
}
