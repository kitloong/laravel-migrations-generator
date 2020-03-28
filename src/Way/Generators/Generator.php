<?php namespace Way\Generators;

use Illuminate\Contracts\Filesystem\FileExistsException;
use Illuminate\Support\Facades\File;
use Way\Generators\Compilers\TemplateCompiler;

class Generator
{
    /**
     * Run the generator
     *
     * @param $templatePath
     * @param $templateData
     * @param $filePathToGenerate
     * @throws FileExistsException
     */
    public function make($templatePath, $templateData, $filePathToGenerate)
    {
        // We first need to compile the template,
        // according to the data that we provide.
        $template = $this->compile($templatePath, $templateData, new TemplateCompiler);

        // Now that we have the compiled template,
        // we can actually generate the file.
        if (File::exists($filePathToGenerate)) {
            throw new FileExistsException();
        }

        File::put($filePathToGenerate, $template);
    }

    /**
     * Compile the file
     *
     * @param $templatePath
     * @param  array  $data
     * @param  TemplateCompiler  $compiler
     * @return mixed
     */
    public function compile($templatePath, array $data, TemplateCompiler $compiler)
    {
        return $compiler->compile(File::get($templatePath), $data);
    }
}
