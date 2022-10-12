<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Migration\Blueprint\TableBlueprint;
use KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType;
use KitLoong\MigrationsGenerator\Migration\Generator\ForeignKeyGenerator;
use KitLoong\MigrationsGenerator\Migration\Writer\MigrationWriter;
use KitLoong\MigrationsGenerator\Migration\Writer\SquashWriter;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\FilenameHelper;

class ForeignKeyMigration
{
    private $foreignKeyGenerator;
    private $filenameHelper;
    private $migrationWriter;
    private $setting;
    private $squashWriter;

    public function __construct(
        ForeignKeyGenerator $foreignKeyGenerator,
        FilenameHelper $filenameHelper,
        MigrationWriter $migrationWriter,
        Setting $setting,
        SquashWriter $squashWriter
    ) {
        $this->foreignKeyGenerator = $foreignKeyGenerator;
        $this->filenameHelper      = $filenameHelper;
        $this->migrationWriter     = $migrationWriter;
        $this->setting             = $setting;
        $this->squashWriter        = $squashWriter;
    }

    /**
     * Create foreign key migration.
     *
     * @param  string  $table
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\ForeignKey>  $foreignKeys
     * @return string The migration file path.
     */
    public function write(string $table, Collection $foreignKeys): string
    {
        $up   = $this->up($table, $foreignKeys);
        $down = $this->down($table, $foreignKeys);

        $this->migrationWriter->writeTo(
            $path = $this->filenameHelper->makeForeignKeyPath($table),
            $this->setting->getStubPath(),
            $this->filenameHelper->makeForeignKeyClassName($table),
            new Collection([$up]),
            new Collection([$down]),
            MigrationFileType::FOREIGN_KEY()
        );

        return $path;
    }

    /**
     * Write foreign key migration into temporary file.
     *
     * @param  string  $table
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\ForeignKey>  $foreignKeys
     */
    public function writeToTemp(string $table, Collection $foreignKeys): void
    {
        $up   = $this->up($table, $foreignKeys);
        $down = $this->down($table, $foreignKeys);

        $this->squashWriter->writeToTemp(new Collection([$up]), new Collection([$down]));
    }

    /**
     * Generates `up` schema for foreign key.
     *
     * @param  string  $table
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\ForeignKey>  $foreignKeys
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint
     */
    private function up(string $table, Collection $foreignKeys): SchemaBlueprint
    {
        $up          = $this->getSchemaBlueprint($table);
        $upBlueprint = new TableBlueprint();
        foreach ($foreignKeys as $foreignKey) {
            $method = $this->foreignKeyGenerator->generate($foreignKey);
            $upBlueprint->setMethod($method);
        }
        $up->setBlueprint($upBlueprint);

        return $up;
    }

    /**
     * Generates `down` schema for foreign key.
     *
     * @param  string  $table
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\ForeignKey>  $foreignKeys
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint
     */
    private function down(string $table, Collection $foreignKeys): SchemaBlueprint
    {
        $down          = $this->getSchemaBlueprint($table);
        $downBlueprint = new TableBlueprint();
        foreach ($foreignKeys as $foreignKey) {
            $method = $this->foreignKeyGenerator->generateDrop($foreignKey);
            $downBlueprint->setMethod($method);
        }
        $down->setBlueprint($downBlueprint);

        return $down;
    }

    /**
     * @param  string  $table
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint
     */
    private function getSchemaBlueprint(string $table): SchemaBlueprint
    {
        return new SchemaBlueprint(
            $table,
            SchemaBuilder::TABLE()
        );
    }
}
