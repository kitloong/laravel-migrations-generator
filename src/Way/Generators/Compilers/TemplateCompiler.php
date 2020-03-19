<?php namespace Way\Generators\Compilers;

class TemplateCompiler implements Compiler {

    /**
     * Compile the template using
     * the given data
     *
     * @param $template
     * @param $data
     * @return mixed
     */
    public function compile($template, $data)
    {
        foreach($data as $key => $value)
        {
            $template = preg_replace("/\\$$key\\$/i", $value, $template);
        }

        return $template;
    }

}
