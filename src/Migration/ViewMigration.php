<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Migration\Blueprint\ViewBlueprint;
use KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType;
use KitLoong\MigrationsGenerator\Migration\Writer\MigrationWriter;
use KitLoong\MigrationsGenerator\Migration\Writer\SquashWriter;
use KitLoong\MigrationsGenerator\Schema\Models\View;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\FilenameHelper;

class ViewMigration
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
            $path = $this->filenameHelper->makeViewPath($view->getName()),
            $this->setting->getStubPath(),
            $this->filenameHelper->makeViewClassName($view->getName()),
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
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\ViewBlueprint
     */
    private function up(View $view): ViewBlueprint
    {
        $viewBlueprint = new ViewBlueprint($view->getQuotedName());
        $viewBlueprint->setCreateViewSql($view->getCreateViewSql());
        return $viewBlueprint;
    }

    /**
     * * Generates `down` db statement for view.
     *
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\View  $view
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\ViewBlueprint
     */
    private function down(View $view): ViewBlueprint
    {
        return new ViewBlueprint($view->getQuotedName());
    }
}
