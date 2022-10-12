<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Migration\Blueprint\CustomBlueprint;
use KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType;
use KitLoong\MigrationsGenerator\Migration\Writer\MigrationWriter;
use KitLoong\MigrationsGenerator\Migration\Writer\SquashWriter;
use KitLoong\MigrationsGenerator\Schema\Models\Procedure;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\FilenameHelper;

class ProcedureMigration
{
    private $filenameHelper;
    private $migrationWriter;
    private $setting;
    private $squashWriter;

    public function __construct(
        FilenameHelper $filenameHelper,
        MigrationWriter $migrationWriter,
        Setting $setting,
        SquashWriter $squashWriter
    ) {
        $this->filenameHelper  = $filenameHelper;
        $this->migrationWriter = $migrationWriter;
        $this->setting         = $setting;
        $this->squashWriter    = $squashWriter;
    }

    /**
     * Create stored procedure migration.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Procedure  $procedure
     * @return string The migration file path.
     */
    public function write(Procedure $procedure): string
    {
        $up   = $this->up($procedure);
        $down = $this->down($procedure);

        $this->migrationWriter->writeTo(
            $path = $this->filenameHelper->makeProcedurePath($procedure->getName()),
            $this->setting->getStubPath(),
            $this->filenameHelper->makeProcedureClassName($procedure->getName()),
            new Collection([$up]),
            new Collection([$down]),
            MigrationFileType::PROCEDURE()
        );
        return $path;
    }

    /**
     * Write stored procedure migration into temporary file.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Procedure  $procedure
     */
    public function writeToTemp(Procedure $procedure): void
    {
        $up   = $this->up($procedure);
        $down = $this->down($procedure);

        $this->squashWriter->writeToTemp(new Collection([$up]), new Collection([$down]));
    }

    private function up(Procedure $procedure): CustomBlueprint
    {
        return new CustomBlueprint($procedure->getDefinition());
    }

    private function down(Procedure $procedure): CustomBlueprint
    {
        return new CustomBlueprint($procedure->getDropDefinition());
    }
}
