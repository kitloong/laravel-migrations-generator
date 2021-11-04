<?php

namespace MigrationsGenerator\Generators;

use MigrationsGenerator\Generators\Blueprint\ViewBlueprint;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Models\View;

class ViewMigration
{
    private $setting;

    public function __construct(MigrationsGeneratorSetting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * Generates `up` db statement for view.
     *
     * @param  \MigrationsGenerator\Models\View  $view
     * @return \MigrationsGenerator\Generators\Blueprint\ViewBlueprint
     */
    public function up(View $view): ViewBlueprint
    {
        $viewBlueprint = new ViewBlueprint($this->setting->getConnection()->getName(), $view->getQuotedName());
        $viewBlueprint->setCreateViewSql($view->getCreateViewSql());
        return $viewBlueprint;
    }

    /**
     * * Generates `down` db statement for view.
     *
     * @param  \MigrationsGenerator\Models\View  $view
     * @return \MigrationsGenerator\Generators\Blueprint\ViewBlueprint
     */
    public function down(View $view): ViewBlueprint
    {
        return new ViewBlueprint($this->setting->getConnection()->getName(), $view->getQuotedName());
    }
}
