<?php namespace Way\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileExistsException;
use Illuminate\Support\Facades\Config;
use Way\Generators\Generator;

abstract class GeneratorCommand extends Command
{
    /**
     * The Generator instance.
     *
     * @var Generator
     */
    protected $generator;

    /**
     * Create a new GeneratorCommand instance.
     *
     * @param  Generator  $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;

        parent::__construct();
    }

    /**
     * Fetch the template data.
     *
     * @param string $type
     * @return array
     */
    abstract protected function getTemplateData(string $type): array;

    /**
     * The path to where the file will be created.
     *
     * @param string $type
     * @return string
     */
    abstract protected function getFileGenerationPath(string $type): string;

    /**
     * Get the path to the generator template.
     *
     * @param string $type
     * @return string
     */
    abstract protected function getTemplatePath(string $type): string;

    /**
     * Compile and generate the file.
     * @param string $type
     */
    public function create(string $type)
    {
        $filePathToGenerate = $this->getFileGenerationPath($type);

        try {
            $this->generator->make(
                $this->getTemplatePath($type),
                $this->getTemplateData($type),
                $filePathToGenerate
            );

            $this->info("Created: {$filePathToGenerate}");
        } catch (FileExistsException $e) {
            $this->error("The file, {$filePathToGenerate}, already exists! I don't want to overwrite it.");
        }
    }

    /**
     * Get a directory path through a command option, or from the configuration.
     *
     * @param  string  $option
     * @param  string  $configName
     * @return string
     */
    protected function getPathByOptionOrConfig(string $option, string $configName): string
    {
        if ($path = (string) $this->option($option)) {
            return $path;
        }

        return (string) Config::get("generators.config.{$configName}");
    }
}
