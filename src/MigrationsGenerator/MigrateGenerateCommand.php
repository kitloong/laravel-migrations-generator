<?php

namespace MigrationsGenerator;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Support\Facades\Config;
use MigrationsGenerator\DBAL\Schema;
use MigrationsGenerator\Generators\Generator;

class MigrateGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'migrate:generate
                            {tables? : A list of Tables or Views you wish to Generate Migrations for separated by a comma: users,posts,comments}
                            {--c|connection= : The database connection to use}
                            {--t|tables= : A list of Tables or Views you wish to Generate Migrations for separated by a comma: users,posts,comments}
                            {--i|ignore= : A list of Tables or Views you wish to ignore, separated by a comma: users,posts,comments}
                            {--p|path= : Where should the file be created?}
                            {--tp|template-path= : The location of the template for this generator}
                            {--date= : Migrations will be created with specified date. Views and Foreign keys will be created with + 1 second. Date should be in format suitable for Carbon::parse}
                            {--table-filename= : Define table migration filename, default pattern: [datetime_prefix]_create_[table]_table.php}
                            {--view-filename= : Define view migration filename, default pattern: [datetime_prefix]_create_[table]_view.php}
                            {--fk-filename= : Define foreign key migration filename, default pattern: [datetime_prefix]_add_foreign_keys_to_[table]_table.php}
                            {--default-index-names : Don\'t use db index names for migrations}
                            {--default-fk-names : Don\'t use db foreign key names for migrations}
                            {--use-db-collation : Follow db collations for migrations}
                            {--skip-views : Don\'t generate views}
                            {--squash : Generate all migrations into a single file}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a migration from an existing table structure.';

    protected $repository;

    protected $shouldLog = false;

    protected $nextBatchNumber = 0;

    /**
     * Database connection name
     *
     * @var string
     */
    protected $connection;

    /** @var Schema */
    protected $schema;

    protected $generator;

    public function __construct(
        MigrationRepositoryInterface $repository,
        Generator $generator
    ) {
        parent::__construct();

        $this->generator  = $generator;
        $this->repository = $repository;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function handle()
    {
        $this->setup($this->connection = $this->option('connection') ?: Config::get('database.default'));

        $this->schema = app(Schema::class);
        $this->schema->initialize();

        $this->info('Using connection: '.$this->connection."\n");

        $tables       = $this->filterTables();
        $views        = $this->filterViews();
        $generateList = array_unique(array_merge($tables, $views));
        $this->info('Generating migrations for: '.implode(', ', $generateList)."\n");

        $this->askIfLogMigrationTable();

        $this->generateMigrationFiles($tables, $views);

        $this->info("\nFinished!\n");
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function setup(string $connection): void
    {
        $setting = app(MigrationsGeneratorSetting::class);
        $setting->setup($connection);
        $setting->setUseDBCollation($this->option('use-db-collation'));
        $setting->setIgnoreIndexNames($this->option('default-index-names'));
        $setting->setIgnoreForeignKeyNames($this->option('default-fk-names'));
        $setting->setSquash((bool) $this->option('squash'));

        $setting->setPath(
            $this->option('path') ?? Config::get('generators.config.migration_target_path')
        );

        $setting->setStubPath(
            $this->option('template-path') ?? Config::get('generators.config.migration_template_path')
        );

        $setting->setDate(
            Carbon::parse($this->option('date')) ?? Carbon::now()
        );

        $setting->setTableFilename(
            $this->option('table-filename') ?? Config::get('generators.config.filename_pattern.table')
        );

        $setting->setViewFilename(
            $this->option('view-filename') ?? Config::get('generators.config.filename_pattern.view')
        );

        $setting->setFkFilename(
            $this->option('fk-filename') ?? Config::get('generators.config.filename_pattern.foreign_key')
        );
    }

    /**
     * Get all tables from schema or return table list provided in option.
     * Then filter and exclude tables in --ignore option if any.
     * Also exclude migrations table
     *
     * @return string[]
     * @throws \Doctrine\DBAL\Exception
     */
    protected function filterTables(): array
    {
        $allTables = $this->schema->getTableNames();

        return $this->filterAndExcludeAsset($allTables);
    }

    /**
     * Get all views from schema or return table list provided in option.
     * Then filter and exclude tables in --ignore option if any.
     * Return empty if --skip-views
     *
     * @return string[]
     * @throws \Doctrine\DBAL\Exception
     */
    protected function filterViews(): array
    {
        if ($this->option('skip-views')) {
            return [];
        }

        $allViews = $this->schema->getViewNames();

        return $this->filterAndExcludeAsset($allViews);
    }

    /**
     * Filter and exclude tables in --ignore option if any.
     *
     * @param  string[]  $allAssets
     * @return array
     */
    protected function filterAndExcludeAsset(array $allAssets): array
    {
        if ($tableArg = (string) $this->argument('tables')) {
            $tables = explode(',', $tableArg);
        } elseif ($tableOpt = (string) $this->option('tables')) {
            $tables = explode(',', $tableOpt);
        } else {
            $tables = $allAssets;
        }

        $tables = array_intersect($tables, $allAssets);

        return array_diff($tables, $this->getExcludedTables());
    }

    /**
     * Get a list of tables to be excluded.
     *
     * @return string[]
     */
    protected function getExcludedTables(): array
    {
        $prefix         = app(MigrationsGeneratorSetting::class)->getConnection()->getTablePrefix();
        $migrationTable = $prefix.Config::get('database.migrations');

        $excludes = [$migrationTable];
        $ignore   = (string) $this->option('ignore');
        if (!empty($ignore)) {
            return array_merge([$migrationTable], explode(',', $ignore));
        }

        return $excludes;
    }

    /**
     * Asks user for log migration permission.
     */
    protected function askIfLogMigrationTable(): void
    {
        if (!$this->option('no-interaction')) {
            $this->shouldLog = $this->confirm('Do you want to log these migrations in the migrations table?', true);
        }

        if ($this->shouldLog) {
            $this->repository->setSource($this->connection);
            if ($this->connection !== Config::get('database.default')) {
                if (!$this->confirm('Log into current connection: '.$this->connection.'? [Y = '.$this->connection.', n = '.Config::get('database.default').' (default connection)]', true)) {
                    $this->repository->setSource(Config::get('database.default'));
                }
            }

            if (!$this->repository->repositoryExists()) {
                $this->repository->createRepository();
            }

            $this->nextBatchNumber = $this->askInt(
                'Next Batch Number is: '.$this->repository->getNextBatchNumber().'. We recommend using Batch Number 0 so that it becomes the "first" migration',
                0
            );
        }
    }

    /**
     * Ask user for a Numeric Value, or blank for default.
     *
     * @param  string  $question  Question to ask
     * @param  int|null  $default  Default Value (optional)
     * @return int Answer
     */
    protected function askInt(string $question, int $default = null): int
    {
        $ask = 'Your answer needs to be a numeric value';

        if (!is_null($default)) {
            $question .= ' [Default: '.$default.']';
            $ask      .= ' or blank for default. [Default: '.$default.']';
        }

        $answer = $this->ask($question, (string) $default);
        while (!ctype_digit($answer) && !($answer === '' && !is_null($default))) {
            $answer = $this->ask($ask, (string) $default);
        }

        if ($answer === '') {
            $answer = $default;
        }

        return (int) $answer;
    }

    /**
     * Generates table, view and foreign key migrations.
     *
     * @param  string[]  $tables  Table names.
     * @param  string[]  $views  View names.
     * @throws \Doctrine\DBAL\Exception
     */
    private function generateMigrationFiles(array $tables, array $views): void
    {
        if (app(MigrationsGeneratorSetting::class)->isSquash()) {
            $this->generator->cleanTemps();
        }

        $this->info("Setting up Tables and Index Migrations");

        $this->generateTables($tables);

        $this->info("\nSetting up Views Migrations");

        $this->generateViews($views);

        $this->info("\nSetting up Foreign Key Migrations");

        $this->generateForeignKeys($tables);

        if (app(MigrationsGeneratorSetting::class)->isSquash()) {
            $migrationFilepath = $this->generator->squashMigrations();

            $this->info("\nAll migrations squashed.");

            if ($this->shouldLog) {
                $this->logMigration($migrationFilepath);
            }
        }
    }

    /**
     * Generates table migrations.
     *
     * @param  string[]  $tables  Table names.
     * @throws \Doctrine\DBAL\Exception
     */
    private function generateTables(array $tables): void
    {
        foreach ($tables as $table) {
            $this->writeMigration(
                $table,
                function () use ($table) {
                    $this->generator->writeTableToTemp(
                        $this->schema->getTable($table),
                        $this->schema->getColumns($table),
                        $this->schema->getIndexes($table)
                    );
                },
                function () use ($table): string {
                    return $this->generator->writeTableToMigrationFile(
                        $this->schema->getTable($table),
                        $this->schema->getColumns($table),
                        $this->schema->getIndexes($table)
                    );
                }
            );
        }
    }

    /**
     * Generate view migration.
     *
     * @param  string[]  $views  Views name.
     * @throws \Doctrine\DBAL\Exception
     */
    protected function generateViews(array $views): void
    {
        $schemaViews = $this->schema->getViews();
        foreach ($schemaViews as $view) {
            if (!in_array($view->getName(), $views)) {
                continue;
            }
            $this->writeMigration(
                $view->getName(),
                function () use ($view) {
                    $this->generator->writeViewToTemp($view);
                },
                function () use ($view): string {
                    return $this->generator->writeViewToMigrationFile($view);
                }
            );
        }
    }

    /**
     * Generates foreign key migration.
     *
     * @param  string[]  $tables
     * @throws \Doctrine\DBAL\Exception
     */
    private function generateForeignKeys(array $tables): void
    {
        foreach ($tables as $table) {
            $foreignKeys = $this->schema->getForeignKeys($table);
            if (count($foreignKeys) > 0) {
                $this->writeMigration(
                    $table,
                    function () use ($table, $foreignKeys) {
                        $this->generator->writeForeignKeysToTemp(
                            $this->schema->getTable($table),
                            $foreignKeys
                        );
                    },
                    function () use ($table, $foreignKeys): string {
                        return $this->generator->writeForeignKeysToMigrationFile(
                            $this->schema->getTable($table),
                            $foreignKeys
                        );
                    }
                );
            }
        }
    }

    /**
     * Writes migration files.
     *
     * @param  string  $table  Table name.
     * @param  callable  $writeToTemp
     * @param  callable  $writeToMigrationFile
     */
    protected function writeMigration(string $table, callable $writeToTemp, callable $writeToMigrationFile): void
    {
        if (app(MigrationsGeneratorSetting::class)->isSquash()) {
            $writeToTemp();
            $this->info("Prepared: $table");
        } else {
            $migrationFilePath = $writeToMigrationFile();
            $this->info("Created: $migrationFilePath");
            if ($this->shouldLog) {
                $this->logMigration($migrationFilePath);
            }
        }
    }

    /**
     * Logs migration repository.
     *
     * @param  string  $migrationFilepath
     */
    protected function logMigration(string $migrationFilepath): void
    {
        $file = basename($migrationFilepath, '.php');
        $this->repository->log($file, $this->nextBatchNumber);
    }
}
