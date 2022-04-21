<?php

namespace KitLoong\MigrationsGenerator\Migration;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\SchemaBuilder;
use KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint;
use KitLoong\MigrationsGenerator\Migration\Blueprint\TableBlueprint;
use KitLoong\MigrationsGenerator\Migration\Generator\ForeignKeyGenerator;

class ForeignKeyMigration
{
    private $foreignKeyGenerator;

    public function __construct(
        ForeignKeyGenerator $foreignKeyGenerator
    ) {
        $this->foreignKeyGenerator = $foreignKeyGenerator;
    }

    /**
     * Generates `up` schema for foreign key.
     *
     * @param  string  $table
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\ForeignKey>  $foreignKeys
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint
     */
    public function up(string $table, Collection $foreignKeys): SchemaBlueprint
    {
        $up          = $this->getSchemaBlueprint($table);
        $upBlueprint = new TableBlueprint();
        foreach ($foreignKeys as $foreignKey) {
            $method = $this->foreignKeyGenerator->generate($foreignKey);
            $upBlueprint->setMethod($method);
        }
        $up->setBlueprint($upBlueprint);

        return $up;
    }

    /**
     * Generates `down` schema for foreign key.
     *
     * @param  string  $table
     * @param  \Illuminate\Support\Collection<\KitLoong\MigrationsGenerator\Schema\Models\ForeignKey>  $foreignKeys
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint
     */
    public function down(string $table, Collection $foreignKeys): SchemaBlueprint
    {
        $down          = $this->getSchemaBlueprint($table);
        $downBlueprint = new TableBlueprint();
        foreach ($foreignKeys as $foreignKey) {
            $method = $this->foreignKeyGenerator->generateDrop($foreignKey);
            $downBlueprint->setMethod($method);
        }
        $down->setBlueprint($downBlueprint);

        return $down;
    }

    /**
     * @param  string  $table
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\SchemaBlueprint
     */
    private function getSchemaBlueprint(string $table): SchemaBlueprint
    {
        return new SchemaBlueprint(
            DB::getName(),
            $table,
            SchemaBuilder::TABLE()
        );
    }
}
