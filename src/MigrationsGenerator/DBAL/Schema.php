<?php

namespace MigrationsGenerator\DBAL;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\View as DBALView;
use MigrationsGenerator\DBAL\Mapper\ViewMapper;
use MigrationsGenerator\DBAL\Support\FilterTables;
use MigrationsGenerator\DBAL\Support\FilterViews;
use MigrationsGenerator\MigrationsGeneratorSetting;
use MigrationsGenerator\Models\View;
use MigrationsGenerator\Support\AssetNameHelper;

class Schema
{
    use FilterTables;
    use FilterViews;
    use AssetNameHelper;

    private $setting;
    private $registerColumnType;
    private $assetNameHelper;

    public function __construct(MigrationsGeneratorSetting $setting, RegisterColumnType $registerColumnType)
    {
        $this->setting            = $setting;
        $this->registerColumnType = $registerColumnType;
    }

    /**
     * Register custom column type into doctrine dbal.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function initialize(): void
    {
        $this->registerColumnType->handle();
    }

    /**
     * Get a list of table names.
     *
     * @return string[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTableNames(): array
    {
        return collect($this->setting->getDBALSchema()->listTableNames())
            ->map(function ($table) {
                if ($this->isIdentifierQuoted($table)) {
                    return $this->trimQuotes($table);
                }
                return $table;
            })
            ->filter(call_user_func([$this, 'filterTableNameCallback']))
            ->values()
            ->toArray();
    }

    /**
     * Get single table detail.
     *
     * @param  string  $table  Table name.
     * @return \Doctrine\DBAL\Schema\Table
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTable(string $table): Table
    {
        return $this->setting->getDBALSchema()->listTableDetails($table);
    }

    /**
     * Get a list of table indexes.
     *
     * @param  string  $table  Table name.
     * @return \Doctrine\DBAL\Schema\Index[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getIndexes(string $table): array
    {
        return $this->setting->getDBALSchema()->listTableIndexes($table);
    }

    /**
     * Get a list of table columns.
     *
     * @param  string  $table  Table name.
     * @return \Doctrine\DBAL\Schema\Column[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getColumns(string $table): array
    {
        return $this->setting->getDBALSchema()->listTableColumns($table);
    }

    /**
     * Get a list of table foreign keys.
     *
     * @param  string  $table  Table name.
     * @return \Doctrine\DBAL\Schema\ForeignKeyConstraint[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getForeignKeys(string $table): array
    {
        return $this->setting->getDBALSchema()->listTableForeignKeys($table);
    }

    /**
     * Get a list of view names.
     *
     * @return string[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getViewNames(): array
    {
        return collect($this->getViews())->map(function (View $view) {
            return $view->getName();
        })->toArray();
    }

    /**
     * Get a list of views.
     *
     * @return \MigrationsGenerator\Models\View[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getViews(): array
    {
        return collect($this->setting->getDBALSchema()->listViews())
            ->filter(call_user_func([$this, 'filterViewCallback']))
            ->map(function (DBALView $view) {
                return ViewMapper::toModel($view);
            })
            ->filter(function (View $view) {
                return $view->getCreateViewSql() !== '';
            })
            ->toArray();
    }
}
