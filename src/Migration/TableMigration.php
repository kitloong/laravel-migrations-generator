<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Enum\Driver;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\TableMethod;
use KitLoong\MigrationsGenerator\Enum\Migrations\Property\TableProperty;
use KitLoong\MigrationsGenerator\Migration\Blueprint\DBStatementBlueprint;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Migration\Blueprint\TableBlueprint;
use KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType;
use KitLoong\MigrationsGenerator\Migration\Generator\ColumnGenerator;
use KitLoong\MigrationsGenerator\Migration\Generator\IndexGenerator;
use KitLoong\MigrationsGenerator\Migration\Writer\MigrationWriter;
use KitLoong\MigrationsGenerator\Migration\Writer\SquashWriter;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\CheckMigrationMethod;
use KitLoong\MigrationsGenerator\Support\MigrationNameHelper;
use KitLoong\MigrationsGenerator\Support\TableName;

class TableMigration
{
    use CheckMigrationMethod;
    use TableName;

    private $columnGenerator;
    private $migrationNameHelper;
    private $indexGenerator;
    private $migrationWriter;
    private $setting;
    private $squashWriter;

    public function __construct(
        ColumnGenerator $columnGenerator,
        MigrationNameHelper $migrationNameHelper,
        IndexGenerator $indexGenerator,
        MigrationWriter $migrationWriter,
        Setting $setting,
        SquashWriter $squashWriter
    ) {
        $this->columnGenerator     = $columnGenerator;
        $this->migrationNameHelper = $migrationNameHelper;
        $this->indexGenerator      = $indexGenerator;
        $this->migrationWriter     = $migrationWriter;
        $this->setting             = $setting;
        $this->squashWriter        = $squashWriter;
    }

    /**
     * Create table migration.
     *
     * @return string The migration file path.
     */
    public function write(Table $table): string
    {
        $upList = new Collection();
        $upList->push($this->up($table));

        if ($table->getCustomColumns()->isNotEmpty()) {
            foreach ($this->upAdditionalStatements($table) as $statement) {
                $upList->push($statement);
            }
        }

        $down = $this->down($table);

        $this->migrationWriter->writeTo(
            $path = $this->makeMigrationPath($table->getName()),
            $this->setting->getStubPath(),
            $this->makeMigrationClassName($table->getName()),
            $upList,
            new Collection([$down]),
            MigrationFileType::TABLE()
        );

        return $path;
    }

    /**
     * Write table migration into temporary file.
     */
    public function writeToTemp(Table $table): void
    {
        $upList = new Collection();
        $upList->push($this->up($table));

        if ($table->getCustomColumns()->isNotEmpty()) {
            foreach ($this->upAdditionalStatements($table) as $statement) {
                $upList->push($statement);
            }
        }

        $down = $this->down($table);

        $this->squashWriter->writeToTemp($upList, new Collection([$down]));
    }

    /**
     * Generates `up` schema for table.
     */
    private function up(Table $table): SchemaBlueprint
    {
        $up = $this->getSchemaBlueprint($table, SchemaBuilder::CREATE());

        $blueprint = new TableBlueprint();

        if ($this->shouldSetCharset()) {
            $blueprint = $this->setTableCharset($blueprint, $table);
            $blueprint->setLineBreak();
        }

        if ($this->hasTableComment() && $table->getComment() !== null && $table->getComment() !== '') {
            $blueprint->setMethod(new Method(TableMethod::COMMENT(), $table->getComment()));
        }

        $chainableIndexes    = $this->indexGenerator->getChainableIndexes($table->getName(), $table->getIndexes());
        $notChainableIndexes = $this->indexGenerator->getNotChainableIndexes($table->getIndexes(), $chainableIndexes);

        foreach ($table->getColumns() as $column) {
            $method = $this->columnGenerator->generate($table, $column, $chainableIndexes);
            $blueprint->setMethod($method);
        }

        $blueprint->mergeTimestamps();

        if ($notChainableIndexes->isNotEmpty()) {
            $blueprint->setLineBreak();

            foreach ($notChainableIndexes as $index) {
                $method = $this->indexGenerator->generate($table, $index);
                $blueprint->setMethod($method);
            }
        }

        $up->setBlueprint($blueprint);

        return $up;
    }

    /**
     * Generate custom statements.
     *
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\DBStatementBlueprint[]
     */
    private function upAdditionalStatements(Table $table): array
    {
        $statements = [];

        foreach ($table->getCustomColumns() as $column) {
            foreach ($column->getSqls() as $sql) {
                $statements[] = new DBStatementBlueprint($sql);
            }
        }

        return $statements;
    }

    /**
     * Generates `down` schema for table.
     */
    private function down(Table $table): SchemaBlueprint
    {
        return $this->getSchemaBlueprint($table, SchemaBuilder::DROP_IF_EXISTS());
    }

    /**
     * Makes class name for table migration.
     *
     * @param  string  $table  Table name.
     */
    private function makeMigrationClassName(string $table): string
    {
        $withoutPrefix = $this->stripTablePrefix($table);
        return $this->migrationNameHelper->makeClassName(
            $this->setting->getTableFilename(),
            $withoutPrefix
        );
    }

    /**
     * Makes file path for table migration.
     *
     * @param  string  $table  Table name.
     */
    private function makeMigrationPath(string $table): string
    {
        $withoutPrefix = $this->stripTablePrefix($table);
        return $this->migrationNameHelper->makeFilename(
            $this->setting->getTableFilename(),
            $this->setting->getDateForMigrationFilename(),
            $withoutPrefix
        );
    }

    /**
     * Checks should set charset into table.
     */
    private function shouldSetCharset(): bool
    {
        if (DB::getDriverName() !== Driver::MYSQL()->getValue()) {
            return false;
        }

        return $this->setting->isUseDBCollation();
    }

    private function setTableCharset(TableBlueprint $blueprint, Table $table): TableBlueprint
    {
        $blueprint->setProperty(
            TableProperty::COLLATION(),
            $collation = $table->getCollation()
        );

        $charset = Str::before($collation, '_');
        $blueprint->setProperty(TableProperty::CHARSET(), $charset);

        return $blueprint;
    }

    private function getSchemaBlueprint(Table $table, SchemaBuilder $schemaBuilder): SchemaBlueprint
    {
        return new SchemaBlueprint(
            $table->getName(),
            $schemaBuilder
        );
    }
}
