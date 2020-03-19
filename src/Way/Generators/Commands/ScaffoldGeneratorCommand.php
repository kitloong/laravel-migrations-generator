<?php namespace Way\Generators\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Config;

class ScaffoldGeneratorCommand extends ResourceGeneratorCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'generate:scaffold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a new resource (with boilerplate)';

    /**
     * Call model generator if user confirms
     *
     * @param $resource
     */
    protected function callModel($resource)
    {
        $modelName = $this->getModelName($resource);

        if ($this->confirm("Do you want me to create a $modelName model? [yes|no]"))
        {
            $this->call('generate:model', [
                'modelName' => $modelName,
                '--templatePath' => Config::get("generators::config.scaffold_model_template_path")
            ]);
        }
    }

    /**
     * Call controller generator if user confirms
     *
     * @param $resource
     */
    protected function callController($resource)
    {
        $controllerName = $this->getControllerName($resource);

        if ($this->confirm("Do you want me to create a $controllerName controller? [yes|no]"))
        {
            $this->call('generate:controller', [
                'controllerName' => $controllerName,
                '--templatePath' => Config::get("generators::config.scaffold_controller_template_path")
            ]);
        }
    }

}
