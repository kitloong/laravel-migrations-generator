<?php namespace Way\Generators;

use Way\Generators\Filesystem\Filesystem;
use Way\Generators\Compilers\TemplateCompiler;
use Way\Generators\UndefinedTemplate;

class Generator {

    /**
     * @var Filesystem
     */
    protected $file;

    /**
     * @param Filesystem $file
     */
    public function __construct(Filesystem $file)
    {
        $this->file = $file;
    }

    /**
     * Run the generator
     *
     * @param $templatePath
     * @param $templateData
     * @param $filePathToGenerate
     */
    public function make($templatePath, $templateData, $filePathToGenerate)
    {
        // We first need to compile the template,
        // according to the data that we provide.
        $template = $this->compile($templatePath, $templateData, new TemplateCompiler);

        // Now that we have the compiled template,
        // we can actually generate the file.
        $this->file->make($filePathToGenerate, $template);
    }

    /**
     * Compile the file
     *
     * @param $templatePath
     * @param array $data
     * @param TemplateCompiler $compiler
     * @throws UndefinedTemplate
     * @return mixed
     */
    public function compile($templatePath, array $data, TemplateCompiler $compiler)
    {
        return $compiler->compile($this->file->get($templatePath), $data);
    }

}
