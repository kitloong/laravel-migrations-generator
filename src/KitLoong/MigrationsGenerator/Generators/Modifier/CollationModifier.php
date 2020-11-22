<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/11/22
 */

namespace KitLoong\MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\Decorator;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

class CollationModifier
{
    private $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->decorator = $decorator;
    }

    public function generate(string $tableName, Column $column): string
    {
        $setting = app(MigrationsGeneratorSetting::class);
        $tableCollation = $setting->getSchema()->listTableDetails($tableName)->getOptions()['collation'] ?? null;

        $columnCollation = $column->getPlatformOptions()['collation'] ?? null;
        if (!empty($column->getPlatformOptions()['collation'])) {
            if ($columnCollation !== $tableCollation) {
                return $this->decorator->decorate(
                    ColumnModifier::COLLATION,
                    [$this->decorator->columnDefaultToString($columnCollation)]
                );
            }
        }

        return '';
    }
}
