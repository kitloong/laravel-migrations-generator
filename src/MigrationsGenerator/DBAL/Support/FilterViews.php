<?php

namespace MigrationsGenerator\DBAL\Support;

use Closure;
use Doctrine\DBAL\Schema\View;
use Illuminate\Support\Facades\DB;
use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\MigrationsGeneratorSetting;

trait FilterViews
{
    /**
     * Filter views.
     * For PgSQL, we get only views from user defined `schema`.
     *
     * @return \Closure
     */
    public function filterViewCallback(): Closure
    {
        return function (View $view) {
            if (app(MigrationsGeneratorSetting::class)->getPlatform() === Platform::POSTGRESQL) {
                return $this->isPgSQLWantedView($view);
            }

            return true;
        };
    }

    /**
     * Checks if view is from user defined `schema`.
     *
     * @param  \Doctrine\DBAL\Schema\View  $view
     * @return bool
     */
    protected function isPgSQLWantedView(View $view): bool
    {
        if (in_array($view->getName(), ['public.geography_columns', 'public.geometry_columns'])) {
            return false;
        }

        return $view->getNamespaceName() === DB::connection()->getConfig('schema');
    }
}
