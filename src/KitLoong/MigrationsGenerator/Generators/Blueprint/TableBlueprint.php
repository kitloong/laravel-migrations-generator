<?php

namespace KitLoong\MigrationsGenerator\Generators\Blueprint;

use Illuminate\Support\Collection;

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
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\Property
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
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod
     */
    public function setColumnMethodByName(string $name, ...$values): ColumnMethod
    {
        $method        = new ColumnMethod($name, ...$values);
        $this->lines[] = $method;
        return $method;
    }

    /**
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod
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
     * @return \KitLoong\MigrationsGenerator\Generators\Blueprint\Property|\KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod|string|null
     */
    public function removeLastLine()
    {
        return array_pop($this->lines);
    }

    /**
     * @param  string[]  $columnNameList
     */
    public function removeLinesByColumnNames(array $columnNameList): void
    {
        $this->lines = collect($this->lines)->filter(function ($line) use ($columnNameList) {
            if ($line instanceof ColumnMethod) {
                $columnName = $line->getValues()[0] ?? '';
                return !in_array($columnName, $columnNameList);
            }

            return true;
        })->toArray();
    }

    /**
     * @return array
     */
    public function getLines(): array
    {
        return $this->lines;
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
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\Property  $property
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
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
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
     * @param  \KitLoong\MigrationsGenerator\Generators\Blueprint\ColumnMethod  $method
     * @return string
     */
    private function flattenMethod(ColumnMethod $method): string
    {
        $v = (new Collection($method->getValues()))->map(function ($v) {
            return $this->convertFromAnyTypeToString($v);
        })->implode(', ');
        return $method->getName()."($v)";
    }
}
