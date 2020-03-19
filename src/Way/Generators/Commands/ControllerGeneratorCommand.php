<?php namespace Way\Generators\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Way\Generators\Templates\Data\Controller as ControllerData;

class ControllerGeneratorCommand extends GeneratorCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'generate:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a controller';

    /**
     * Get the path to the template for the generator.
     *
     * @return mixed
     */
    protected function getTemplatePath()
    {
        return $this->getPathByOptionOrConfig('templatePath', 'controller_template_path');
    }

    /**
     * Fetch the template data.
     *
     * @return array
     */
    protected function getTemplateData()
    {
        return (new ControllerData($this->argument('controllerName')))->fetch();
    }

    /**
     * The path to where the file will be created.
     *
     * @return mixed
     */
    protected function getFileGenerationPath()
    {
        $path = $this->getPathByOptionOrConfig('path', 'controller_target_path');

        return $path. '/' . $this->argument('controllerName') . '.php';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['controllerName', InputArgument::REQUIRED, 'The name of the desired controller.']
        ];
    }

}
