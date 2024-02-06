<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\PgSQL;

use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\DBAL\Models\DBALColumn;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Repositories\PgSQLRepository;
use KitLoong\MigrationsGenerator\Support\Regex;

class PgSQLColumn extends DBALColumn
{
    /**
     * @var \KitLoong\MigrationsGenerator\Repositories\PgSQLRepository
     */
    private $repository;

    protected function handle(): void
    {
        $this->repository = app(PgSQLRepository::class);

        $this->setTypeToIncrements(false);

        switch ($this->type) {
            case ColumnType::DATE():
            case ColumnType::DATETIME():
            case ColumnType::DATETIME_TZ():
            case ColumnType::TIME():
            case ColumnType::TIME_TZ():
            case ColumnType::TIMESTAMP():
            case ColumnType::TIMESTAMP_TZ():
            case ColumnType::SOFT_DELETES():
            case ColumnType::SOFT_DELETES_TZ():
                $this->length = $this->getDataTypeLength();
                $this->setRawDefault();
                break;

            case ColumnType::FLOAT():
                $this->fixFloatLength();
                break;

            case ColumnType::GEOGRAPHY():
            case ColumnType::GEOMETRY():
                $this->setRealSpatialColumn();
                break;

            case ColumnType::STRING():
                $this->presetValues = $this->getEnumPresetValues();

                if (count($this->presetValues) > 0) {
                    $this->type = ColumnType::ENUM();
                }

                break;

            default:
        }

        $this->setStoredDefinition();
    }

    /**
     * Get the column length from DB.
     */
    private function getDataTypeLength(): ?int
    {
        $dataType = $this->repository->getTypeByColumnName($this->tableName, $this->name);

        if ($dataType === null) {
            return null;
        }

        $length = Regex::getTextBetweenFirst($dataType);

        if ($length === null) {
            return null;
        }

        return (int) $length;
    }

    /**
     * Check and set to use raw default.
     * Raw default will be generated with DB::raw().
     */
    private function setRawDefault(): void
    {
        // Reserve now() to generate as `useCurrent`.
        if ($this->default === 'now()') {
            return;
        }

        $default = $this->repository->getDefaultByColumnName($this->tableName, $this->name);

        if ($default === null) {
            return;
        }

        // If default value is expression, eg: timezone('Europe/Rome'::text, now())
        if (!preg_match('/\((.?)\)/', $default)) {
            return;
        }

        $this->default    = $default;
        $this->rawDefault = true;
    }

    /**
     * Get geometry mapping.
     *
     * @return array<string, \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType>
     */
    private function getGeometryMap(): array
    {
        return [
            'geometry'           => ColumnType::GEOMETRY(),
            'geometrycollection' => ColumnType::GEOMETRY_COLLECTION(),
            'linestring'         => ColumnType::LINE_STRING(),
            'multilinestring'    => ColumnType::MULTI_LINE_STRING(),
            'multipoint'         => ColumnType::MULTI_POINT(),
            'multipolygon'       => ColumnType::MULTI_POLYGON(),
            'point'              => ColumnType::POINT(),
            'polygon'            => ColumnType::POLYGON(),
        ];
    }

    /**
     * Set to geometry type base on geography map.
     */
    private function setRealSpatialColumn(): void
    {
        $dataType = $this->repository->getTypeByColumnName($this->tableName, $this->name);

        if ($dataType === null) {
            return;
        }

        $dataType = strtolower($dataType);
        $dataType = preg_replace('/\s+/', '', $dataType);

        if ($dataType === 'geography' || $dataType === 'geometry') {
            return;
        }

        if (!preg_match('/(\w+)(?:\((\w+)(?:,\s*(\w+))?\))?/', $dataType, $matches)) {
            return;
        }

        $spatialSubType = $matches[2];
        $spatialSrID    = isset($matches[3]) ? (int) $matches[3] : null;

        if (!$this->atLeastLaravel11()) {
            $map = $this->getGeometryMap();

            if (!isset($map[$spatialSubType])) {
                return;
            }

            $this->type = $map[$spatialSubType];
            return;
        }

        $this->spatialSubType = $spatialSubType;
        $this->spatialSrID    = $spatialSrID;
    }

    /**
     * Get the preset values if the column is "enum".
     *
     * @return string[]
     */
    private function getEnumPresetValues(): array
    {
        $definition = $this->repository->getCheckConstraintDefinition($this->tableName, $this->name);

        if ($definition === null) {
            return [];
        }

        if ($definition === '') {
            return [];
        }

        $presetValues = Regex::getTextBetweenAll($definition, "'", "'::");

        if ($presetValues === null) {
            return [];
        }

        return $presetValues;
    }

    /**
     * The framework always create float without precision.
     * However, Doctrine DBAL always return precisions 10 and scale 0.
     * Reset precisions and scale to 0 here.
     */
    private function fixFloatLength(): void
    {
        if ($this->precision !== 10 || $this->scale !== 0) {
            return;
        }

        $this->precision = 0;
        $this->scale     = 0;
    }

    /**
     * Set stored definition if the column is stored.
     */
    private function setStoredDefinition(): void
    {
        $this->storedDefinition = $this->repository->getStoredDefinition($this->tableName, $this->name);

        // A generated column cannot have a column default or an identity definition.
        if ($this->storedDefinition === null) {
            return;
        }

        $this->default = null;
    }

    protected function setTypeToIncrements(bool $supportUnsigned): void
    {
        // https://www.postgresqltutorial.com/postgresql-tutorial/postgresql-identity-column/
        // https://github.com/doctrine/dbal/pull/5396
        if (
            !in_array($this->type, [
                ColumnType::BIG_INTEGER(),
                ColumnType::INTEGER(),
                ColumnType::MEDIUM_INTEGER(),
                ColumnType::SMALL_INTEGER(),
                ColumnType::TINY_INTEGER(),
            ])
        ) {
            return;
        }

        if (
            $this->default !== null && (
                Str::endsWith($this->default, '_seq') || Str::endsWith($this->default, '_seq"')
            )
        ) {
            $this->default       = null;
            $this->autoincrement = true;
        }

        parent::setTypeToIncrements($supportUnsigned);
    }
}
