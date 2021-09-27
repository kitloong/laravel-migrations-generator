<?php

namespace MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\Method;
use MigrationsGenerator\Generators\MigrationConstants\Method\Foreign;
use MigrationsGenerator\MigrationsGeneratorSetting;

class ForeignKeyGenerator
{
    private $tableNameGenerator;

    public function __construct(TableNameGenerator $tableNameGenerator)
    {
        $this->tableNameGenerator = $tableNameGenerator;
    }

    /**
     * Converts foreign keys into migration foreign key method.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint  $foreignKey
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function generate(Table $table, ForeignKeyConstraint $foreignKey): Method
    {
        if ($this->shouldSkipName($table->getName(), $foreignKey)) {
            $method = new Method(Foreign::FOREIGN, $foreignKey->getUnquotedLocalColumns());
        } else {
            $method = new Method(Foreign::FOREIGN, $foreignKey->getUnquotedLocalColumns(), $foreignKey->getName());
        }

        $method->chain(Foreign::REFERENCES, $foreignKey->getUnquotedForeignColumns())
            ->chain(Foreign::ON, $this->tableNameGenerator->stripPrefix($foreignKey->getForeignTableName()));

        if ($foreignKey->hasOption('onUpdate')) {
            $method->chain(Foreign::ON_UPDATE, $foreignKey->getOption('onUpdate'));
        }

        if ($foreignKey->hasOption('onDelete')) {
            $method->chain(Foreign::ON_DELETE, $foreignKey->getOption('onDelete'));
        }

        return $method;
    }

    /**
     * Generates drop foreign migration method.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint  $foreignKey
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function generateDrop(Table $table, ForeignKeyConstraint $foreignKey): Method
    {
        if ($this->shouldSkipName($table->getName(), $foreignKey)) {
            return new Method(Foreign::DROP_FOREIGN, $this->makeLaravelForeignKeyName($table->getName(), $foreignKey));
        } else {
            return new Method(Foreign::DROP_FOREIGN, $foreignKey->getName());
        }
    }

    /**
     * Checks should skip current foreign key name from DB.
     *
     * @param  string  $table  Table name.
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint  $foreignKey
     * @return bool
     */
    private function shouldSkipName(string $table, ForeignKeyConstraint $foreignKey): bool
    {
        if (app(MigrationsGeneratorSetting::class)->isIgnoreForeignKeyNames()) {
            return true;
        }

        return $this->makeLaravelForeignKeyName($table, $foreignKey) === $foreignKey->getName();
    }

    /**
     * Makes foreign key name with Laravel way.
     *
     * @param  string  $table  Table name.
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint  $foreignKey
     * @return string
     */
    private function makeLaravelForeignKeyName(string $table, ForeignKeyConstraint $foreignKey): string
    {
        $name = strtolower($table.'_'.implode('_', $foreignKey->getUnquotedLocalColumns()).'_foreign');
        return str_replace(['-', '.'], '_', $name);
    }
}
