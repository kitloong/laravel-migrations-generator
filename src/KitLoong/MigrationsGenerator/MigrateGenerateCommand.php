<?php namespace KitLoong\MigrationsGenerator;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use KitLoong\MigrationsGenerator\Generators\Decorator;
use KitLoong\MigrationsGenerator\Generators\SchemaGenerator;
use Way\Generators\Commands\GeneratorCommand;
use Way\Generators\Generator;
use Xethron\MigrationsGenerator\Syntax\AddForeignKeysToTable;
use Xethron\MigrationsGenerator\Syntax\AddToTable;
use Xethron\MigrationsGenerator\Syntax\DroppedTable;
use Xethron\MigrationsGenerator\Syntax\RemoveForeignKeysFromTable;

class MigrateGenerateCommand extends GeneratorCommand
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'migrate:generate
                {tables? : A list of Tables you wish to Generate Migrations for separated by a comma: users,posts,comments}
                {--c|connection= : The database connection to use}
                {--t|tables= : A list of Tables you wish to Generate Migrations for separated by a comma: users,posts,comments}
                {--i|ignore= : A list of Tables you wish to ignore, separated by a comma: users,posts,comments}
                {--p|path= : Where should the file be created?}
                {--tp|templatePath= : The location of the template for this generator}
                {--defaultIndexNames : Don\'t use db index names for migrations}
                {--defaultFKNames : Don\'t use db foreign key names for migrations}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a migration from an existing table structure.';

    /**
     * @var MigrationRepositoryInterface $repository
     */
    protected $repository;

    /**
     * @var SchemaGenerator
     */
    protected $schemaGenerator;

    /**
     * Array of Fields to create in a new Migration
     * Namely: Columns, Indexes and Foreign Keys
     */
    protected $fields = array();

    /**
     * List of Migrations that has been done
     */
    protected $migrations = array();

    protected $log = false;

    /**
     * @var int
     */
    protected $batch;

    /**
     * Filename date prefix (Y_m_d_His)
     * @var string
     */
    protected $datePrefix;

    /**
     * @var string
     */
    protected $migrationName;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $table;

    /**
     * Will append connection method if not default connection
     * @var string
     */
    protected $connection;

    protected $decorator;

    public function __construct(
        Generator $generator,
        SchemaGenerator $schemaGenerator,
        MigrationRepositoryInterface $repository,
        Decorator $decorator
    ) {
        $this->schemaGenerator = $schemaGenerator;
        $this->repository = $repository;
        $this->decorator = $decorator;

        parent::__construct($generator);
    }

    /**
     * Execute the console command. Added for Laravel 5.5
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function handle()
    {
        $this->setup($this->connection = $this->option('connection') ?: Config::get('database.default'));

        $this->info('Using connection: '.$this->connection."\n");

        $this->schemaGenerator->initialize();

        $tables = $this->filterTables();
        $this->info('Generating migrations for: '.implode(', ', $tables));

        $this->askIfLogMigrationTable();

        $this->generateMigrationFiles($tables);

        $this->info("\nFinished!\n");
    }

    protected function setup(string $connection): void
    {
        /** @var MigrationsGeneratorSetting $setting */
        $setting = app(MigrationsGeneratorSetting::class);
        $setting->setConnection($connection);
        $setting->setIgnoreIndexNames($this->option('defaultIndexNames'));
        $setting->setIgnoreForeignKeyNames($this->option('defaultFKNames'));
    }

    /**
     * Get all tables from schema or return table list provided in option.
     * Then filter and exclude tables in --ignore option if any.
     * Also exclude migrations table
     *
     * @return string[]
     */
    protected function filterTables()
    {
        if ($tableArg = (string) $this->argument('tables')) {
            $tables = explode(',', $tableArg);
        } elseif ($tableOpt = (string) $this->option('tables')) {
            $tables = explode(',', $tableOpt);
        } else {
            $tables = $this->schemaGenerator->getTables();
        }

        return $this->filterAndExcludeTables($tables);
    }

    protected function askIfLogMigrationTable(): void
    {
        if (!$this->option('no-interaction')) {
            $this->log = $this->askYn('Do you want to log these migrations in the migrations table?');
        }

        if ($this->log) {
            $migrationSource = $this->connection;

            if ($migrationSource !== Config::get('database.default')) {
                if (!$this->askYn('Log into current connection: '.$this->connection.'? [Y = '.$this->connection.', n = '.Config::get('database.default').' (default connection)]')) {
                    $migrationSource = Config::get('database.default');
                }
            }

            $this->repository->setSource($migrationSource);
            if (!$this->repository->repositoryExists()) {
                $options = array('--database' => $migrationSource);
                $this->call('migrate:install', $options);
            }
            $this->batch = $this->askNumeric(
                'Next Batch Number is: '.$this->repository->getNextBatchNumber().'. We recommend using Batch Number 0 so that it becomes the "first" migration',
                0
            );
        }
    }

    protected function generateMigrationFiles(array $tables): void
    {
        $this->info("Setting up Tables and Index Migrations");
        $this->datePrefix = date('Y_m_d_His');
        $this->generateTablesAndIndices($tables);

        $this->info("\nSetting up Foreign Key Migrations\n");

        // Plus 1 second to have foreign key migrations generate after table migrations generated
        $this->datePrefix = date('Y_m_d_His', strtotime('+1 second'));
        $this->generateForeignKeys($tables);
    }

    /**
     * Ask for user input: Yes/No.
     *
     * @param  string  $question  Question to ask
     * @return boolean          Answer from user
     */
    protected function askYn(string $question): bool
    {
        $answer = $this->ask($question.' [Y/n] ') ?? 'y';

        while (!in_array(strtolower($answer), ['y', 'n', 'yes', 'no'])) {
            $answer = $this->ask('Please choose either yes or no. [Y/n]') ?? 'y';
        }
        return in_array(strtolower($answer), ['y', 'yes']);
    }

    /**
     * Ask user for a Numeric Value, or blank for default.
     *
     * @param  string  $question  Question to ask
     * @param  int|null  $default  Default Value (optional)
     * @return int           Answer
     */
    protected function askNumeric(string $question, $default = null): int
    {
        $ask = 'Your answer needs to be a numeric value';

        if (!is_null($default)) {
            $question .= ' [Default: '.$default.'] ';
            $ask .= ' or blank for default';
        }

        $answer = $this->ask($question);

        while (!is_numeric($answer) and !($answer == '' and !is_null($default))) {
            $answer = $this->ask($ask.'. ');
        }
        if ($answer == '') {
            $answer = $default;
        }
        return $answer;
    }

    /**
     * Generate tables and index migrations.
     *
     * @param  string[]  $tables  List of tables to create migrations for
     * @return void
     */
    protected function generateTablesAndIndices($tables)
    {
        $this->method = 'create';

        foreach ($tables as $tableName) {
            $this->table = $tableName;
            $this->migrationName = 'create_'.$this->decorator->tableUsedInFilename($tableName).'_table';
            $indexes = $this->schemaGenerator->getIndexes($tableName);

            $fields = $this->schemaGenerator->getFields($tableName, $indexes['single']);
            $this->fields = array_merge($fields, $indexes['multi']->toArray());

            $this->generate();
        }
    }

    /**
     * Generate foreign key migrations.
     *
     * @param  array  $tables  List of tables to create migrations for
     * @return void
     */
    protected function generateForeignKeys(array $tables)
    {
        $this->method = 'table';

        foreach ($tables as $tableName) {
            $this->table = $tableName;
            $this->migrationName = 'add_foreign_keys_to_'.$this->decorator->tableUsedInFilename($tableName).'_table';
            $this->fields = $this->schemaGenerator->getForeignKeyConstraints($tableName);

            $this->generate();
        }
    }

    /**
     * Generate Migration for the current table.
     *
     * @return void
     */
    protected function generate()
    {
        if (!empty($this->fields)) {
            $this->create();

            if ($this->log) {
                $file = $this->datePrefix.'_'.$this->migrationName;
                $this->repository->log($file, $this->batch);
            }
        }
    }

    /**
     * The path where the file will be created.
     *
     * @return string
     */
    protected function getFileGenerationPath(): string
    {
        $path = $this->getPathByOptionOrConfig('path', 'migration_target_path');
        $fileName = $this->datePrefix.'_'.$this->migrationName.'.php';

        return "{$path}/{$fileName}";
    }

    /**
     * Fetch the template data.
     *
     * @return array
     */
    protected function getTemplateData(): array
    {
        if ($this->method == 'create') {
            $up = app(AddToTable::class)->run(
                $this->fields,
                $this->table,
                $this->connection,
                'create'
            );
            $down = app(DroppedTable::class)->run(
                $this->fields,
                $this->table,
                $this->connection,
                'drop'
            );
        } else {
            $up = app(AddForeignKeysToTable::class)->run(
                $this->fields,
                $this->table,
                $this->connection
            );
            $down = app(RemoveForeignKeysFromTable::class)->run(
                $this->fields,
                $this->table,
                $this->connection
            );
        }

        return [
            'CLASS' => ucwords(Str::camel($this->migrationName)),
            'UP' => $up,
            'DOWN' => $down
        ];
    }

    /**
     * Get path to template for generator.
     *
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return $this->getPathByOptionOrConfig('templatePath', 'migration_template_path');
    }

    /**
     * Remove all the tables to exclude from the array of tables.
     *
     * @param  string[]  $tables
     *
     * @return string[]
     */
    protected function filterAndExcludeTables($tables)
    {
        $excludes = $this->getExcludedTables();
        $tables = array_diff($tables, $excludes);

        return $tables;
    }

    /**
     * Get a list of tables to be excluded.
     *
     * @return string[]
     */
    protected function getExcludedTables()
    {
        /** @var MigrationsGeneratorSetting $setting */
        $setting = app(MigrationsGeneratorSetting::class);

        $excludes = [$setting->getConnection()->getTablePrefix().Config::get('database.migrations')];
        $ignore = (string) $this->option('ignore');
        if (!empty($ignore)) {
            return array_merge($excludes, explode(',', $ignore));
        }

        return $excludes;
    }
}
