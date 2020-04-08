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
     * @return array
     */
    abstract protected function getTemplateData(): array;

    /**
     * The path to where the file will be created.
     *
     * @return string
     */
    abstract protected function getFileGenerationPath(): string;

    /**
     * Get the path to the generator template.
     *
     * @return string
     */
    abstract protected function getTemplatePath(): string;

    /**
     * Compile and generate the file.
     */
    public function create()
    {
        $filePathToGenerate = $this->getFileGenerationPath();

        try {
            $this->generator->make(
                $this->getTemplatePath(),
                $this->getTemplateData(),
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
