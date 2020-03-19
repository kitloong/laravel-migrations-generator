<?php namespace Way\Generators\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Way\Generators\Templates\Data\Migration as MigrationData;
use Way\Generators\Generator;

class MigrationGeneratorCommand extends GeneratorCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'generate:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new migration';

    /**
     * Execute the console command.
     */
    public function fire()
    {
        parent::fire();

        // We'll run dump-autoload to refresh everything.
        if ( ! $this->option('testing'))
        {
            $this->call('dump-autoload');
        }
    }

    /**
     * The path to where the file will be created.
     *
     * @return mixed
     */
    protected function getFileGenerationPath()
    {
        $path = $this->getPathByOptionOrConfig('path', 'migration_target_path');
        $fileName = $this->getDatePrefix() . '_' . $this->argument('migrationName') . '.php';

        return "{$path}/{$fileName}";
    }

    /**
     * Get a date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Fetch the template data for the migration generator.
     *
     * @return array
     */
    protected function getTemplateData()
    {
        return (new MigrationData($this->argument('migrationName'), $this->option('fields')))->fetch();
    }

    /**
     * Get the path to the generator template.
     *
     * @return mixed
     */
    protected function getTemplatePath()
    {
        return $this->getPathByOptionOrConfig('templatePath', 'migration_template_path');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['migrationName', InputArgument::REQUIRED, 'The migration name']
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['fields', null, InputOption::VALUE_OPTIONAL, 'Fields for the migration'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Where should the file be created?'],
            ['templatePath', null, InputOption::VALUE_OPTIONAL, 'The location of the template for this generator'],
            ['testing', null, InputOption::VALUE_OPTIONAL, 'For internal use only.']
        ];
    }

}
