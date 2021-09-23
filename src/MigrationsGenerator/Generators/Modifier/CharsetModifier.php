<?php

namespace MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Str;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\MigrationsGeneratorSetting;

class CharsetModifier
{
    /**
     * Set charset.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function chainCharset(Table $table, Method $method, Column $column): Method
    {
        if (!app(MigrationsGeneratorSetting::class)->isUseDBCollation()) {
            return $method;
        }

        // collation is not set in PgSQL
        $defaultCollation = $table->getOptions()['collation'] ?? '';
        $defaultCharset   = Str::before($defaultCollation, '_');

        $charset = $column->getPlatformOptions()['charset'] ?? null;
        if ($charset !== null && $charset !== $defaultCharset) {
            $method->chain(ColumnModifier::CHARSET, $charset);
        }

        return $method;
    }
}
