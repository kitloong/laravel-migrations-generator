<?php

namespace KitLoong\MigrationsGenerator\DBAL\Models\PgSQL;

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

            case ColumnType::GEOMETRY():
                $this->type = $this->setGeometryType();
                break;

            case ColumnType::STRING():
                $this->presetValues = $this->getEnumPresetValues();

                if (count($this->presetValues) > 0) {
                    $this->type = ColumnType::ENUM();
                }

                break;

            default:
        }
    }

    /**
     * Get the column length from DB.
     *
     * @return int|null
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
     *
     * @return void
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
     * Get geography mapping.
     *
     * @return array<string, \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType>
     */
    private function getGeographyMap(): array
    {
        return [
            'geography(geometry,4326)'           => ColumnType::GEOMETRY(),
            'geography(geometrycollection,4326)' => ColumnType::GEOMETRY_COLLECTION(),
            'geography(linestring,4326)'         => ColumnType::LINE_STRING(),
            'geography(multilinestring,4326)'    => ColumnType::MULTI_LINE_STRING(),
            'geography(multipoint,4326)'         => ColumnType::MULTI_POINT(),
            'geography(multipolygon,4326)'       => ColumnType::MULTI_POLYGON(),
            'geography(point,4326)'              => ColumnType::POINT(),
            'geography(polygon,4326)'            => ColumnType::POLYGON(),
        ];
    }

    /**
     * Set to geometry type base on geography map.
     *
     * @return \KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType
     */
    private function setGeometryType(): ColumnType
    {
        $dataType = $this->repository->getTypeByColumnName($this->tableName, $this->name);

        if ($dataType === null) {
            return $this->type;
        }

        $dataType = strtolower($dataType);
        $dataType = preg_replace('/\s+/', '', $dataType);

        $map = $this->getGeographyMap();

        if (!isset($map[$dataType])) {
            return $this->type;
        }

        return $map[$dataType];
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
     *
     * @return void
     */
    private function fixFloatLength(): void
    {
        if ($this->precision !== 10 || $this->scale !== 0) {
            return;
        }

        $this->precision = 0;
        $this->scale     = 0;
    }
}
