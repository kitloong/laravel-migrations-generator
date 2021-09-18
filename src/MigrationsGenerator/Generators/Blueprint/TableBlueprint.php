<?php

namespace MigrationsGenerator\Generators\Blueprint;

use Illuminate\Support\Collection;
use MigrationsGenerator\Generators\MigrationConstants\ColumnName;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;

class TableBlueprint
{
    use Stringable;

    /** @var Property|ColumnMethod|string[] */
    private $lines;

    public function __construct()
    {
        $this->lines = [];
    }

    /**
     * @param  string  $name  Property name.
     * @param  mixed  $value
     * @return \MigrationsGenerator\Generators\Blueprint\Property
     */
    public function setProperty(string $name, $value): Property
    {
        $property      = new Property($name, $value);
        $this->lines[] = $property;
        return $property;
    }

    /**
     * @param  string  $name  Method name.
     * @param  mixed  ...$values
     * @return \MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    public function setColumnMethodByName(string $name, ...$values): ColumnMethod
    {
        $method        = new ColumnMethod($name, ...$values);
        $this->lines[] = $method;
        return $method;
    }

    /**
     * @param  \MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @return \MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    public function setColumnMethod(ColumnMethod $method): ColumnMethod
    {
        $this->lines[] = $method;
        return $method;
    }

    public function setLineBreak(): void
    {
        $this->lines[] = $this->lineBreak;
    }

    /**
     * @return \MigrationsGenerator\Generators\Blueprint\Property|\MigrationsGenerator\Generators\Blueprint\ColumnMethod|string|null
     */
    public function removeLastLine()
    {
        return array_pop($this->lines);
    }

    /**
     * @return array
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function mergeTimestamps(): void
    {
        $length           = 0;
        $createdAtLineKey = 0;
        $updatedAtLineKey = 0;
        $isTimestamps     = false;

        foreach ($this->lines as $key => $line) {
            if (!$line instanceof ColumnMethod) {
                continue;
            }

            if (!$this->checkTimestamps(ColumnName::CREATED_AT, $line)) {
                continue;
            }

            $length           = $line->getValues()[1] ?? 0;
            $createdAtLineKey = $key;
            $updatedAtLineKey = $key + 1;
            break;
        }

        $updatedAt = $this->lines[$updatedAtLineKey] ?? null;
        if (!$updatedAt instanceof ColumnMethod) {
            return;
        }

        if (!$this->checkTimestamps(ColumnName::UPDATED_AT, $updatedAt)) {
            return;
        }

        $updatedAtLength = $updatedAt->getValues()[1] ?? 0;
        if ($length === $updatedAtLength) {
            $isTimestamps = true;
        }

        if ($isTimestamps === true) {
            if ($length === 0) { // MIGRATION_DEFAULT_PRECISION = 0
                $this->lines[$createdAtLineKey] = new ColumnMethod(ColumnType::TIMESTAMPS);
            } else {
                $this->lines[$createdAtLineKey] = new ColumnMethod(ColumnType::TIMESTAMPS, $length);
            }

            unset($this->lines[$updatedAtLineKey]);
        }
    }

    public function toString(): string
    {
        $lines = [];
        foreach ($this->lines as $line) {
            switch (true) {
                case $line instanceof Property:
                    $lines[] = $this->propertyToString($line);
                    break;
                case $line instanceof ColumnMethod:
                    $lines[] = $this->methodToString($line);
                    break;
                default:
                    $lines[] = $this->convertFromAnyTypeToString($line);
            }
        }

        return $this->implodeLines($lines, 3);
    }

    /**
     * Generates $table property, example:
     *
     * $table->collation = 'utf-8';
     * $table->test = false;
     * $table->test = true;
     * $table->test = null;
     * $table->test = [1, 'abc', true];
     *
     * @param  \MigrationsGenerator\Generators\Blueprint\Property  $property
     * @return string
     */
    private function propertyToString(Property $property): string
    {
        $v = $this->convertFromAnyTypeToString($property->getValue());
        return '$table->'.$property->getName()." = $v;";
    }

    /**
     * Generates $table method with chains, example:
     *
     * $table->string('name', 100)->comment('Hello')->default('Test');
     *
     * @param  \MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @return string
     */
    private function methodToString(ColumnMethod $method): string
    {
        $methodStrings[] = $this->flattenMethod($method);
        if ($method->countChain() > 0) {
            foreach ($method->getChains() as $chain) {
                $methodStrings[] = $this->flattenMethod($chain);
            }
        }
        return '$table->'.implode('->', $methodStrings).";";
    }

    /**
     * Generates $table method, example:
     *
     * string('name', 100)
     * comment('Hello')
     * default('Test')
     *
     * @param  \MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @return string
     */
    private function flattenMethod(ColumnMethod $method): string
    {
        $v = (new Collection($method->getValues()))->map(function ($v) {
            return $this->convertFromAnyTypeToString($v);
        })->implode(', ');
        return $method->getName()."($v)";
    }

    private function checkTimestamps(string $name, ColumnMethod $columnMethod): bool
    {
        if ($columnMethod->getName() !== ColumnType::TIMESTAMP) {
            return false;
        }

        if ($columnMethod->getValues()[0] !== $name) {
            return false;
        }

        if ($columnMethod->countChain() !== 1) {
            return false;
        }

        return $columnMethod->getChains()[0]->getName() === ColumnModifier::NULLABLE && empty($columnMethod->getChains()[0]->getValues());
    }
}
