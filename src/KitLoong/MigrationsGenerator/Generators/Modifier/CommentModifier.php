<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 */

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use KitLoong\MigrationsGenerator\Generators\Decorator;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;

class CommentModifier
{
    private $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->decorator = $decorator;
    }

    public function generate(string $comment): string
    {
        return $this->decorator->decorate(
            ColumnModifier::COMMENT,
            ["'".$this->decorator->addSlash($comment)."'"]
        );
    }
}
