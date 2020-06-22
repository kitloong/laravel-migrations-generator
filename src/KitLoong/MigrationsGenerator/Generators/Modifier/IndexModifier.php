<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use KitLoong\MigrationsGenerator\Generators\Decorator;

class IndexModifier
{
    private $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->decorator = $decorator;
    }

    public function generate(array $index): string
    {
        return $this->decorator->decorate(
            $index['type'],
            // $index['args'] is wrapped with '
            (!empty($index['args'][0]) ? [$index['args'][0]] : [])
        );
    }
}
