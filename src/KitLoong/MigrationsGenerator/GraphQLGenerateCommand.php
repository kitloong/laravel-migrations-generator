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

class GraphQLGenerateCommand extends GeneratorCommand
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'graphql:generate';

    /**
     * The console command description.
     */
    protected $description = 'blank';

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
        $this->info('Custom Command!' . "\n");

        // mimic handle from migrategeneratecommand
    }

    protected function getTemplateData(string $type): array
    {
        // TODO: Implement getTemplateData() method.
    }

    protected function getFileGenerationPath(string $type): string
    {
        // TODO: Implement getFileGenerationPath() method.
    }

    protected function getTemplatePath(string $type): string
    {
        // TODO: Implement getTemplatePath() method.
    }
}
