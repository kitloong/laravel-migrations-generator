<?php

namespace MigrationsGenerator\Generators\Blueprint;

use Illuminate\Support\Collection;
use MigrationsGenerator\Generators\MigrationConstants\ColumnName;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnModifier;
use MigrationsGenerator\Generators\MigrationConstants\Method\ColumnType;

class TableBlueprint
{
    use Stringable;

    /** @var Property|Method|string[] */
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
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function setMethodByName(string $name, ...$values): Method
    {
        $method        = new Method($name, ...$values);
        $this->lines[] = $method;
        return $method;
    }

    /**
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @return \MigrationsGenerator\Generators\Blueprint\Method
     */
    public function setMethod(Method $method): Method
    {
        $this->lines[] = $method;
        return $method;
    }

    public function setLineBreak(): void
    {
        $this->lines[] = $this->lineBreak;
    }

    /**
     * @return \MigrationsGenerator\Generators\Blueprint\Property|\MigrationsGenerator\Generators\Blueprint\Method|string|null
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
            if (!$line instanceof Method) {
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
        if (!$updatedAt instanceof Method) {
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
                $this->lines[$createdAtLineKey] = new Method(ColumnType::TIMESTAMPS);
            } else {
                $this->lines[$createdAtLineKey] = new Method(ColumnType::TIMESTAMPS, $length);
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
                case $line instanceof Method:
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
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @return string
     */
    private function methodToString(Method $method): string
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
     * @param  \MigrationsGenerator\Generators\Blueprint\Method  $method
     * @return string
     */
    private function flattenMethod(Method $method): string
    {
        $v = (new Collection($method->getValues()))->map(function ($v) {
            return $this->convertFromAnyTypeToString($v);
        })->implode(', ');
        return $method->getName()."($v)";
    }

    private function checkTimestamps(string $name, Method $method): bool
    {
        if ($method->getName() !== ColumnType::TIMESTAMP) {
            return false;
        }

        if ($method->getValues()[0] !== $name) {
            return false;
        }

        if ($method->countChain() !== 1) {
            return false;
        }

        return $method->getChains()[0]->getName() === ColumnModifier::NULLABLE && empty($method->getChains()[0]->getValues());
    }
}
