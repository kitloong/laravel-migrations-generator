<?php

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\Decorator;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class CharsetModifier
{
    private $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->decorator = $decorator;
    }

    public function generate(string $tableName, Column $column): string
    {
        if (app(MigrationsGeneratorSetting::class)->isUseDBCollation()) {
            $charset = $column->getPlatformOptions()['charset'] ?? null;
            if ($charset != null) {
                return $this->decorator->decorate(
                    ColumnModifier::CHARSET,
                    [$this->decorator->columnDefaultToString($charset)]
                );
            }
        }

        return '';
    }
}
