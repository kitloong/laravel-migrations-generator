<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Migration\Blueprint\DBUnpreparedBlueprint;
use KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType;
use KitLoong\MigrationsGenerator\Migration\Writer\MigrationWriter;
use KitLoong\MigrationsGenerator\Migration\Writer\SquashWriter;
use KitLoong\MigrationsGenerator\Schema\Models\Procedure;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\MigrationNameHelper;

class ProcedureMigration
{
    private $migrationNameHelper;
    private $migrationWriter;
    private $setting;
    private $squashWriter;

    public function __construct(
        MigrationNameHelper $migrationNameHelper,
        MigrationWriter $migrationWriter,
        Setting $setting,
        SquashWriter $squashWriter
    ) {
        $this->migrationNameHelper = $migrationNameHelper;
        $this->migrationWriter     = $migrationWriter;
        $this->setting             = $setting;
        $this->squashWriter        = $squashWriter;
    }

    /**
     * Create stored procedure migration.
     *
     * @return string The migration file path.
     */
    public function write(Procedure $procedure): string
    {
        $up   = $this->up($procedure);
        $down = $this->down($procedure);

        $this->migrationWriter->writeTo(
            $path = $this->makeMigrationPath($procedure->getName()),
            $this->setting->getStubPath(),
            $this->makeMigrationClassName($procedure->getName()),
            new Collection([$up]),
            new Collection([$down]),
            MigrationFileType::PROCEDURE()
        );
        return $path;
    }

    /**
     * Write stored procedure migration into temporary file.
     */
    public function writeToTemp(Procedure $procedure): void
    {
        $up   = $this->up($procedure);
        $down = $this->down($procedure);

        $this->squashWriter->writeToTemp(new Collection([$up]), new Collection([$down]));
    }

    /**
     * Generates `up` db statement for stored procedure.
     */
    private function up(Procedure $procedure): DBUnpreparedBlueprint
    {
        return new DBUnpreparedBlueprint($procedure->getDefinition());
    }

    /**
     * Generates `down` db statement for stored procedure.
     */
    private function down(Procedure $procedure): DBUnpreparedBlueprint
    {
        return new DBUnpreparedBlueprint($procedure->getDropDefinition());
    }

    /**
     * Makes class name for stored procedure migration.
     *
     * @param  string  $procedure  Stored procedure name.
     */
    private function makeMigrationClassName(string $procedure): string
    {
        return $this->migrationNameHelper->makeClassName(
            $this->setting->getProcedureFilename(),
            $procedure
        );
    }

    /**
     * Makes file path for stored procedure migration.
     *
     * @param  string  $procedure  Stored procedure name.
     */
    private function makeMigrationPath(string $procedure): string
    {
        return $this->migrationNameHelper->makeFilename(
            $this->setting->getProcedureFilename(),
            $this->setting->getDateForMigrationFilename(),
            $procedure
        );
    }
}
