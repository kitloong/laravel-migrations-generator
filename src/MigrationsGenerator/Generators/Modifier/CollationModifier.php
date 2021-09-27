<?php

namespace MigrationsGenerator\Generators\Modifier;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\MigrationsGeneratorSetting;

class CollationModifier
{
    /**
     * Set collation.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @param  \Doctrine\DBAL\Schema\Column  $column
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function chainCollation(Table $table, Method $method, Column $column): Method
    {
        if (!app(MigrationsGeneratorSetting::class)->isUseDBCollation()) {
            return $method;
        }

        // collation is not set in PgSQL
        $defaultCollation = $table->getOptions()['collation'] ?? '';

        $collation = $column->getPlatformOptions()['collation'] ?? null;
        if ($collation !== null && $collation !== $defaultCollation) {
            $method->chain(ColumnModifier::COLLATION, $collation);
        }

        return $method;
    }
}
