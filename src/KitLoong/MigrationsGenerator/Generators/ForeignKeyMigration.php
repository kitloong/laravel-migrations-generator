<?php

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Table;
use KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Generators\Blueprint\TableBlueprint;
use KitLoong\MigrationsGenerator\Generators\MigrationConstants\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;

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
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint[]  $foreignKeys
     * @return SchemaBlueprint
     */
    public function up(Table $table, array $foreignKeys): SchemaBlueprint
    {
        $up          = $this->getSchemaBlueprint($table);
        $upBlueprint = new TableBlueprint();
        foreach ($foreignKeys as $foreignKey) {
            $columnMethod = $this->foreignKeyGenerator->generate($table, $foreignKey);
            $upBlueprint->setColumnMethod($columnMethod);
        }
        $up->setBlueprint($upBlueprint);

        return $up;
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @param  \Doctrine\DBAL\Schema\ForeignKeyConstraint[]  $foreignKeys
     * @return SchemaBlueprint
     */
    public function down(Table $table, array $foreignKeys): SchemaBlueprint
    {
        $down          = $this->getSchemaBlueprint($table);
        $downBlueprint = new TableBlueprint();
        foreach ($foreignKeys as $foreignKey) {
            $columnMethod = $this->foreignKeyGenerator->generateDrop($foreignKey);
            $downBlueprint->setColumnMethod($columnMethod);
        }
        $down->setBlueprint($downBlueprint);

        return $down;
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Table  $table
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\SchemaBlueprint
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
