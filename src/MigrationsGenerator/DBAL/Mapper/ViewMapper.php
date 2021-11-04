<?php

namespace MigrationsGenerator\DBAL\Mapper;

use Doctrine\DBAL\Schema\View as DBALView;
use Illuminate\Support\Facades\DB;
use MigrationsGenerator\DBAL\Platform;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Models\View;
use MigrationsGenerator\Repositories\SQLSrvRepository;

class ViewMapper
{
    /**
     * Map from \Doctrine\DBAL\Schema\View to \MigrationsGenerator\Models\View
     *
     * @param  \Doctrine\DBAL\Schema\View  $from
     * @return \MigrationsGenerator\Models\View
     * @throws \Doctrine\DBAL\Exception
     */
    public static function toModel(DBALView $from): View
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            case Platform::POSTGRESQL:
                return self::makePgSQLView($from);
            case Platform::SQLSERVER:
                return self::makeSQLSrvView($from);
            default:
                return self::makeView($from->getName(), $from->getSql());
        }
    }

    /**
     * Handle PgSQL view.
     *
     * @param  \Doctrine\DBAL\Schema\View  $from
     * @return \MigrationsGenerator\Models\View
     * @throws \Doctrine\DBAL\Exception
     */
    private static function makePgSQLView(DBALView $from): View
    {
        if ($from->getNamespaceName() === DB::connection()->getConfig('schema')) {
            // Strip namespace from name.
            $name = $from->getShortestName($from->getNamespaceName());
            return self::makeView($name, $from->getSql());
        }
        return self::makeView($from->getName(), $from->getSql());
    }

    /**
     * Handle SQLSvr view.
     *
     * @param  \Doctrine\DBAL\Schema\View  $from
     * @return \MigrationsGenerator\Models\View
     * @throws \Doctrine\DBAL\Exception
     */
    private static function makeSQLSrvView(DBALView $from): View
    {
        if ($from->getSql() !== '') {
            return self::makeView($from->getName(), $from->getSql());
        }

        // `doctrine/dbal` return empty `getSql()`.
        // Use repository to get view definition.
        $view = app(SQLSrvRepository::class)->getView($from->getName());
        if ($view !== null) {
            $sql = $view->definition;
            return self::makeView($from->getName(), $sql);
        }

        return self::makeView($from->getName(), '');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private static function makeView(string $name, string $sql): View
    {
        // trim quotes
        $unquotedName = str_replace(['`', '"', '[', ']'], '', $name);

        $quotedName = app(MigrationsGeneratorSetting::class)
            ->getDatabasePlatform()
            ->quoteIdentifier($unquotedName);

        $createViewSql = self::getCreateViewSql($quotedName, $sql);

        return new View($unquotedName, $quotedName, $createViewSql);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private static function getCreateViewSql(string $name, string $sql): string
    {
        if (app(MigrationsGeneratorSetting::class)->getPlatform() === Platform::SQLSERVER) {
            return $sql;
        } else {
            return app(MigrationsGeneratorSetting::class)
                ->getDatabasePlatform()
                ->getCreateViewSQL($name, $sql);
        }
    }
}
