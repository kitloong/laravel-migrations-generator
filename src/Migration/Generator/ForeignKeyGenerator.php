<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator;

use KitLoong\MigrationsGenerator\Enum\Migrations\Method\Foreign;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\ForeignKey;
use KitLoong\MigrationsGenerator\Setting;
use KitLoong\MigrationsGenerator\Support\TableName;

class ForeignKeyGenerator
{
    use TableName;

    /**
     * Converts foreign keys into migration foreign key method.
     */
    public function generate(ForeignKey $foreignKey): Method
    {
        $method = $this->makeMethod($foreignKey);

        $method->chain(Foreign::REFERENCES, $foreignKey->getForeignColumns())
            ->chain(Foreign::ON, $this->stripTablePrefix($foreignKey->getForeignTableName()));

        if ($foreignKey->getOnUpdate() !== null) {
            $method->chain(Foreign::ON_UPDATE, $foreignKey->getOnUpdate());
        }

        if ($foreignKey->getOnDelete() !== null) {
            $method->chain(Foreign::ON_DELETE, $foreignKey->getOnDelete());
        }

        return $method;
    }

    /**
     * Generates drop foreign migration method.
     */
    public function generateDrop(ForeignKey $foreignKey): Method
    {
        if ($this->shouldSkipName($foreignKey)) {
            return new Method(Foreign::DROP_FOREIGN, $this->makeLaravelForeignKeyName($foreignKey));
        }

        if ($foreignKey->getName() === null) {
            return new Method(Foreign::DROP_FOREIGN);
        }

        return new Method(Foreign::DROP_FOREIGN, $foreignKey->getName());
    }

    /**
     * Create a new Method with foreignKey and columns.
     */
    public function makeMethod(ForeignKey $foreignKey): Method
    {
        if ($this->shouldSkipName($foreignKey)) {
            return new Method(Foreign::FOREIGN, $foreignKey->getLocalColumns());
        }

        return new Method(Foreign::FOREIGN, $foreignKey->getLocalColumns(), $foreignKey->getName());
    }

    /**
     * Checks should skip current foreign key name from DB.
     */
    private function shouldSkipName(ForeignKey $foreignKey): bool
    {
        if (app(Setting::class)->isIgnoreForeignKeyNames()) {
            return true;
        }

        return $this->makeLaravelForeignKeyName($foreignKey) === $foreignKey->getName();
    }

    /**
     * Makes foreign key name with Laravel way.
     */
    private function makeLaravelForeignKeyName(ForeignKey $foreignKey): string
    {
        $name = strtolower(
            $foreignKey->getTableName() . '_' . implode('_', $foreignKey->getLocalColumns()) . '_foreign',
        );
        return str_replace(['-', '.'], '_', $name);
    }
}
