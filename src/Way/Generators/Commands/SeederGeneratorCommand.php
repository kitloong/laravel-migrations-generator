<?php namespace Way\Generators\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Config;

class SeederGeneratorCommand extends GeneratorCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'generate:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a database table seeder';

    /**
     * The path where the file will be created
     *
     * @return mixed
     */
    protected function getFileGenerationPath()
    {
        $path = $this->getPathByOptionOrConfig('path', 'seed_target_path');
        $tableName = $this->getTableName();

        return "{$path}/{$tableName}TableSeeder.php";
    }

    /**
     * Fetch the template data
     *
     * @return array
     */
    protected function getTemplateData()
    {
        $tableName = $this->getTableName();

        return [
            'CLASS' => "{$tableName}TableSeeder",
            'MODEL' => str_singular($tableName)
        ];
    }

    /**
     * Get path to template for generator
     *
     * @return mixed
     */
    protected function getTemplatePath()
    {
        return $this->getPathByOptionOrConfig('templatePath', 'seed_template_path');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['tableName', InputArgument::REQUIRED, 'The name of the table to seed']
        ];
    }

    /**
     * Format the table name
     */
    protected function getTableName()
    {
        return Str::studly($this->argument('tableName'));
    }

}
