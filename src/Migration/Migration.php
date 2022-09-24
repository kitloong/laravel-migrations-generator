<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Migration\Enum\MigrationFileType;
use KitLoong\MigrationsGenerator\Migration\Writer\MigrationWriter;
use KitLoong\MigrationsGenerator\Migration\Writer\SquashWriter;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Schema\Models\View;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\FilenameHelper;
use Illuminate\Support\Facades\File;

class Migration implements MigrationInterface
{
    private $migrationWriter;
    private $squashWriter;
    private $filenameHelper;
    private $foreignKeyMigration;
    private $tableMigration;
    private $viewMigration;
    private $setting;

    public function __construct(
        MigrationWriter $migrationWriter,
        SquashWriter $squashWriter,
        FilenameHelper $filenameHelper,
        ForeignKeyMigration $foreignKeyMigration,
        TableMigration $tableMigration,
        ViewMigration $viewMigration,
        Setting $setting
    ) {
        $this->migrationWriter     = $migrationWriter;
        $this->squashWriter        = $squashWriter;
        $this->filenameHelper      = $filenameHelper;
        $this->foreignKeyMigration = $foreignKeyMigration;
        $this->tableMigration      = $tableMigration;
        $this->viewMigration       = $viewMigration;
        $this->setting             = $setting;
    }

    /**
     * @inheritDoc
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function writeTable(Table $table, $makeModel): string
    {
        $upList = new Collection();
        $upList->push($this->tableMigration->up($table));

        if ($table->getCustomColumns()->isNotEmpty()) {
            foreach ($this->tableMigration->upAdditionalStatements($table) as $statement) {
                $upList->push($statement);
            }
        }

        $down = $this->tableMigration->down($table);

        $this->migrationWriter->writeTo(
            $path = $this->filenameHelper->makeTablePath($table->getName()),
            $this->setting->getStubPath(),
            $this->filenameHelper->makeTableClassName($table->getName()),
            $upList,
            new Collection([$down]),
            MigrationFileType::TABLE()
        );
        if($makeModel){
            $this->makeModel($table->getName());
        }
        return $path;
    }

    /**
     * @inheritDoc
     */
    public function writeTableToTemp(Table $table): void
    {
        $upList = new Collection();
        $upList->push($this->tableMigration->up($table));
        if ($table->getCustomColumns()->isNotEmpty()) {
            foreach ($this->tableMigration->upAdditionalStatements($table) as $statement) {
                $upList->push($statement);
            }
        }

        $down = $this->tableMigration->down($table);

        $this->squashWriter->writeToTemp($upList, new Collection([$down]));
    }

    /**
     * @inheritDoc
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function writeView(View $view): string
    {
        $up   = $this->viewMigration->up($view);
        $down = $this->viewMigration->down($view);

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
     * @inheritDoc
     */
    public function writeViewToTemp(View $view): void
    {
        $up   = $this->viewMigration->up($view);
        $down = $this->viewMigration->down($view);

        $this->squashWriter->writeToTemp(new Collection([$up]), new Collection([$down]));
    }

    /**
     * @inheritDoc
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function writeTableForeignKeys(string $table, Collection $foreignKeys): string
    {
        $up   = $this->foreignKeyMigration->up($table, $foreignKeys);
        $down = $this->foreignKeyMigration->down($table, $foreignKeys);

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
     * @inheritDoc
     */
    public function writeForeignKeysToTemp(string $table, Collection $foreignKeys): void
    {
        $up   = $this->foreignKeyMigration->up($table, $foreignKeys);
        $down = $this->foreignKeyMigration->down($table, $foreignKeys);

        $this->squashWriter->writeToTemp(new Collection([$up]), new Collection([$down]));
    }

    /**
     * @inheritDoc
     */
    public function cleanTemps(): void
    {
        $this->squashWriter->cleanTemps();
    }

    /**
     * @inheritDoc
     */
    public function squashMigrations(): string
    {
        $database  = DB::getDatabaseName();
        $path      = $this->filenameHelper->makeTablePath($database);
        $className = $this->filenameHelper->makeTableClassName($database);
        $this->squashWriter->squashMigrations($path, $this->setting->getStubPath(), $className);
        return $path;
    }

    public function makeModel(string $table)
    {
        $tableName = explode('_',ucfirst($table)); 
        $finalName = '';
        if( count($tableName)>1){
            foreach($tableName as $name ){
                $finalName = $finalName.ucfirst($name);
            }
        }else{
            $finalName = ucfirst($table);
        }
        File::put('app/Models/'.$finalName.'.php', '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class '.$finalName.' extends Model
{
    use HasFactory;
}');
    
    }
}
