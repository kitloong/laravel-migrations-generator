<?php

namespace MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Table;
use MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;
use MigrationsGenerator\Generators\Blueprint\TableBlueprint;
use MigrationsGenerator\Generators\MigrationConstants\Method\SchemaBuilder;
use MigrationsGenerator\MigrationsGeneratorSetting;

class ForeignKeyMigration
{
    private $foreignKeyGenerator;
    private $setting;

    public function __construct(
        ForeignKeyGenerator $foreignKeyGenerator,
        MigrationsGeneratorSetting $setting
    ) {
        $this->foreignKeyGenerator = $foreignKeyGenerator;
        $this->setting             = $setting;
    }

    /**
     * Generates `up` schema for foreign key.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint[]  $foreignKeys
     * @return SchemaBlueprint
     */
    public function up(Table $table, array $foreignKeys): SchemaBlueprint
    {
        $up          = $this->getSchemaBlueprint($table);
        $upBlueprint = new TableBlueprint();
        foreach ($foreignKeys as $foreignKey) {
            $method = $this->foreignKeyGenerator->generate($table, $foreignKey);
            $upBlueprint->setMethod($method);
        }
        $up->setBlueprint($upBlueprint);

        return $up;
    }

    /**
     * Generates `down` schema for foreign key.
     *
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint[]  $foreignKeys
     * @return SchemaBlueprint
     */
    public function down(Table $table, array $foreignKeys): SchemaBlueprint
    {
        $down          = $this->getSchemaBlueprint($table);
        $downBlueprint = new TableBlueprint();
        foreach ($foreignKeys as $foreignKey) {
            $method = $this->foreignKeyGenerator->generateDrop($table, $foreignKey);
            $downBlueprint->setMethod($method);
        }
        $down->setBlueprint($downBlueprint);

        return $down;
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @return \MigrationsGenerator\Generators\Blueprint\SchemaBlueprint
     */
    private function getSchemaBlueprint(Table $table): SchemaBlueprint
    {
        return new SchemaBlueprint(
            $this->setting->getConnection()->getName(),
            $table->getName(),
            SchemaBuilder::TABLE
        );
    }
}
