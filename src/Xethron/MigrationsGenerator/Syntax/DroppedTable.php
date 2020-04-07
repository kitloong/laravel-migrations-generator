<?php

namespace Xethron\MigrationsGenerator\Syntax;

class DroppedTable extends Table
{
    /**
     * @inheritDoc
     */
    protected function getItem(array $item): string
    {
        return '';
    }
}
