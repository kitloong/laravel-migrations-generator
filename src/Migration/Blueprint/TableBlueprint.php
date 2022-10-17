<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\MergeTimestamps;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Support\Stringable;
use KitLoong\MigrationsGenerator\Migration\Enum\Space;

/**
 * Create migration lines with `$table->`.
 *
 * eg 1 ({@see \KitLoong\MigrationsGenerator\Migration\Blueprint\Property}):
 * ```
 * $table->collation = 'utf8mb4';
 * ```
 *
 * eg 2 ({}@see \KitLoong\MigrationsGenerator\Migration\Blueprint\Method):
 * ```
 * $table->string('name');
 * $table->string('email')->unique();
 * $table->timestamps();
 * ```
 *
 * eg 3 ({}@see \KitLoong\MigrationsGenerator\Migration\Blueprint\Method):
 * ```
 * $table->foreign(['user_id'])->references(['id'])->on('users');
 * $table->dropForeign('user_id_foreign');
 * ```
 */
class TableBlueprint implements WritableBlueprint
{
    use MergeTimestamps;
    use Stringable;

    /** @var \KitLoong\MigrationsGenerator\Migration\Blueprint\Property[]|\KitLoong\MigrationsGenerator\Migration\Blueprint\Method[]|string[] */
    private $lines;

    public function __construct()
    {
        $this->lines = [];
    }

    /**
     * @param  string  $name  Property name.
     * @param  mixed  $value
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Property
     */
    public function setProperty(string $name, $value): Property
    {
        $property      = new Property($name, $value);
        $this->lines[] = $property;
        return $property;
    }

    /**
     * @param  string  $name  Method name.
     * @param  mixed  ...$values  Method arguments.
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method
     */
    public function setMethodByName(string $name, ...$values): Method
    {
        $method        = new Method($name, ...$values);
        $this->lines[] = $method;
        return $method;
    }

    /**
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method
     */
    public function setMethod(Method $method): Method
    {
        $this->lines[] = $method;
        return $method;
    }

    public function setLineBreak(): void
    {
        $this->lines[] = Space::LINE_BREAK();
    }

    /**
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method|\KitLoong\MigrationsGenerator\Migration\Blueprint\Property|string|null
     */
    public function removeLastLine()
    {
        return array_pop($this->lines);
    }

    /**
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Property[]|\KitLoong\MigrationsGenerator\Migration\Blueprint\Method[]|string[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * Checks lines and merge into timestamps or timestampsTz if possible.
     */
    public function mergeTimestamps(): void
    {
        $this->lines = $this->merge($this->lines, false);
        $this->lines = $this->merge($this->lines, true);
    }

    /**
     * @inheritDoc
     */
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

        return $this->flattenLines($lines, 3);
    }

    /**
     * Generates $table property, example:
     *
     * $table->collation = 'utf8mb4';
     * $table->test = false;
     * $table->test = true;
     * $table->test = null;
     * $table->test = [1, 'abc', true];
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Property  $property
     * @return string
     */
    private function propertyToString(Property $property): string
    {
        $v = $this->convertFromAnyTypeToString($property->getValue());
        return '$table->' . $property->getName() . " = $v;";
    }

    /**
     * Generates $table method with chains, example:
     *
     * $table->string('name', 100)->comment('Hello')->default('Test');
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
     * @return string
     */
    private function methodToString(Method $method): string
    {
        $methodStrings = [$this->flattenMethod($method)];

        if ($method->countChain() > 0) {
            foreach ($method->getChains() as $chain) {
                $methodStrings[] = $this->flattenMethod($chain);
            }
        }

        return '$table->' . implode('->', $methodStrings) . ";";
    }

    /**
     * Generates $table method, example:
     *
     * string('name', 100)
     * comment('Hello')
     * default('Test')
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
     * @return string
     */
    private function flattenMethod(Method $method): string
    {
        $v = (new Collection($method->getValues()))->map(function ($v) {
            return $this->convertFromAnyTypeToString($v);
        })->implode(', ');
        return $method->getName() . "($v)";
    }
}
