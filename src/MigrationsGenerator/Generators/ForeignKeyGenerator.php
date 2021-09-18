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

    public function generateDrop(ForeignKeyConstraint $foreignKey): Method
    {
        return new Method(Foreign::DROP_FOREIGN, $foreignKey->getName());
    }

    private function shouldSkipName(string $table, ForeignKeyConstraint $foreignKey): bool
    {
        if (app(MigrationsGeneratorSetting::class)->isIgnoreForeignKeyNames()) {
            return true;
        }

        $guessIndexName = strtolower($table.'_'.implode('_', $foreignKey->getUnquotedLocalColumns()).'_foreign');
        $guessIndexName = str_replace(['-', '.'], '_', $guessIndexName);
        return $guessIndexName === $foreignKey->getName();
    }
}
