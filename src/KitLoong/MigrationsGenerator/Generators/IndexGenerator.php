<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Index;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\MigrationMethod\IndexType;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Repositories\SQLSrvRepository;

class IndexGenerator
{
    private $decorator;
    private $pgSQLRepository;
    private $sqlSrvRepository;

    public function __construct(Decorator $decorator, PgSQLRepository $pgSQLRepository, SQLSrvRepository $sqlSrvRepository)
    {
        $this->decorator = $decorator;
        $this->pgSQLRepository = $pgSQLRepository;
        $this->sqlSrvRepository = $sqlSrvRepository;
    }

    /**
     * @param  string  $table
     * @param  Index[]  $indexes
     * @param  bool  $ignoreIndexNames
     * @return Collection[]
     */
    public function generate(string $table, $indexes, bool $ignoreIndexNames): array
    {
        $singleColIndexes = collect([]);
        $multiColIndexes = collect([]);

        // Doctrine/Dbal doesn't return spatial information from PostgreSQL
        // Use raw SQL here to create $spatial index name list.
        $spatials = $this->getSpatialList($table);

        foreach ($indexes as $index) {
            $indexField = [
                'field' => array_map([$this->decorator, 'addSlash'], $index->getColumns()),
                'type' => IndexType::INDEX,
                'args' => [],
            ];

            if ($index->isPrimary()) {
                $indexField['type'] = IndexType::PRIMARY;
            } elseif ($index->isUnique()) {
                $indexField['type'] = IndexType::UNIQUE;
            } elseif ((
                    count($index->getFlags()) > 0 && $index->hasFlag('spatial')
                ) || $spatials->contains($index->getName())) {
                $indexField['type'] = IndexType::SPATIAL_INDEX;
            }

            if (!$index->isPrimary()) {
                if (!$ignoreIndexNames && !$this->useLaravelStyleDefaultName($table, $index, $indexField['type'])) {
                    $indexField['args'][] = $this->decorateName($index->getName());
                }
            }

            if (count($index->getColumns()) === 1) {
                $singleColIndexes->put($this->decorator->addSlash($index->getColumns()[0]), $indexField);
            } else {
                $multiColIndexes->push($indexField);
            }
        }

        return ['single' => $singleColIndexes, 'multi' => $multiColIndexes];
    }

    private function getLaravelStyleDefaultName(string $table, array $columns, string $type): string
    {
        if ($type === IndexType::PRIMARY) {
            return 'PRIMARY';
        }

        $index = strtolower($table.'_'.implode('_', $columns).'_'.$type);
        return str_replace(['-', '.'], '_', $index);
    }

    private function useLaravelStyleDefaultName(string $table, Index $index, string $type): bool
    {
        return $this->getLaravelStyleDefaultName($table, $index->getColumns(), $type) === $index->getName();
    }

    private function decorateName(string $name): string
    {
        return "'".$this->decorator->addSlash($name)."'";
    }

    /**
     * Doctrine/Dbal doesn't return spatial information from PostgreSQL
     * Use raw SQL here to create $spatial index name list.
     * @param  string  $table
     * @return \Illuminate\Support\Collection Spatial index name list
     */
    private function getSpatialList(string $table): Collection
    {
        switch (app(MigrationsGeneratorSetting::class)->getPlatform()) {
            case Platform::POSTGRESQL:
                return $this->pgSQLRepository->getSpatialIndexNames($table);
            case Platform::SQLSERVER:
                return $this->sqlSrvRepository->getSpatialIndexNames($table);
            default:
                return collect([]);
        }
    }
}
