<?php namespace Way\Generators\Compilers;

class TemplateCompiler implements Compiler
{
    /**
     * @inheritDoc
     */
    public function compile(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = preg_replace("/\\$$key\\$/i", $value, $template);
        }

        return $template;
    }
}
