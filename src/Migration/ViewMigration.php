<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Migration\Blueprint\DBStatementBlueprint;
use KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType;
use KitLoong\MigrationsGenerator\Migration\Writer\MigrationWriter;
use KitLoong\MigrationsGenerator\Migration\Writer\SquashWriter;
use KitLoong\MigrationsGenerator\Schema\Models\View;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\MigrationNameHelper;
use KitLoong\MigrationsGenerator\Support\TableName;

class ViewMigration
{
    use TableName;

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
     * Create view migration.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\View  $view
     * @return string The migration file path.
     */
    public function write(View $view): string
    {
        $up   = $this->up($view);
        $down = $this->down($view);

        $this->migrationWriter->writeTo(
            $path = $this->makeMigrationPath($view->getName()),
            $this->setting->getStubPath(),
            $this->makeMigrationClassName($view->getName()),
            new Collection([$up]),
            new Collection([$down]),
            MigrationFileType::VIEW()
        );

        return $path;
    }

    /**
     * Write view migration into temporary file.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\View  $view
     */
    public function writeToTemp(View $view): void
    {
        $up   = $this->up($view);
        $down = $this->down($view);

        $this->squashWriter->writeToTemp(new Collection([$up]), new Collection([$down]));
    }

    /**
     * Generates `up` db statement for view.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\View  $view
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\DBStatementBlueprint
     */
    private function up(View $view): DBStatementBlueprint
    {
        return new DBStatementBlueprint($view->getDefinition());
    }

    /**
     * Generates `down` db statement for view.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\View  $view
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\DBStatementBlueprint
     */
    private function down(View $view): DBStatementBlueprint
    {
        return new DBStatementBlueprint($view->getDropDefinition());
    }

    /**
     * Makes class name for view migration.
     *
     * @param  string  $view  View name.
     * @return string
     */
    private function makeMigrationClassName(string $view): string
    {
        $withoutPrefix = $this->stripTablePrefix($view);
        return $this->migrationNameHelper->makeClassName(
            $this->setting->getViewFilename(),
            $withoutPrefix
        );
    }

    /**
     * Makes file path for view migration.
     *
     * @param  string  $view  View name.
     * @return string
     */
    private function makeMigrationPath(string $view): string
    {
        $withoutPrefix = $this->stripTablePrefix($view);
        return $this->migrationNameHelper->makeFilename(
            $this->setting->getViewFilename(),
            Carbon::parse($this->setting->getDate())->addSecond()->format('Y_m_d_His'),
            $withoutPrefix
        );
    }
}
