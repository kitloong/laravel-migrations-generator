<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

use Illuminate\Support\Collection;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\MethodName;
use KitLoong\MigrationsGenerator\Enum\Migrations\Property\PropertyName;
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

    /**
     * @var array<int, \KitLoong\MigrationsGenerator\Migration\Blueprint\Method|\KitLoong\MigrationsGenerator\Migration\Blueprint\Property|\KitLoong\MigrationsGenerator\Migration\Enum\Space>
     */
    private array $lines;

    /**
     * By default, generate 3 tabs for each line.
     */
    private int $numberOfPrefixTab = 3;

    public function __construct()
    {
        $this->lines = [];
    }

    /**
     * @param  \KitLoong\MigrationsGenerator\Enum\Migrations\Property\PropertyName  $name  Property name.
     */
    public function setProperty(PropertyName $name, mixed $value): Property
    {
        $property      = new Property($name, $value);
        $this->lines[] = $property;
        return $property;
    }

    /**
     * @param  \KitLoong\MigrationsGenerator\Enum\Migrations\Method\MethodName  $name  Method name.
     * @param  mixed  ...$values  Method arguments.
     */
    public function setMethodByName(MethodName $name, mixed ...$values): Method
    {
        $method        = new Method($name, ...$values);
        $this->lines[] = $method;
        return $method;
    }

    public function setMethod(Method $method): Method
    {
        $this->lines[] = $method;
        return $method;
    }

    public function setLineBreak(): void
    {
        $this->lines[] = Space::LINE_BREAK;
    }

    /**
     * @return array<int, \KitLoong\MigrationsGenerator\Migration\Blueprint\Method|\KitLoong\MigrationsGenerator\Migration\Blueprint\Property|\KitLoong\MigrationsGenerator\Migration\Enum\Space>
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
     * Increase number of prefix tab by 1.
     */
    public function increaseNumberOfPrefixTab(): void
    {
        $this->numberOfPrefixTab++;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $lines = [];

        foreach ($this->lines as $line) {
            $lines[] = match (true) {
                $line instanceof Property => $this->propertyToString($line),
                $line instanceof Method => $this->methodToString($line),
                default => $this->convertFromAnyTypeToString($line),
            };
        }

        return $this->flattenLines($lines, $this->numberOfPrefixTab);
    }

    /**
     * Generates $table property, example:
     *
     * $table->collation = 'utf8mb4';
     * $table->test = false;
     * $table->test = true;
     * $table->test = null;
     * $table->test = [1, 'abc', true];
     */
    private function propertyToString(Property $property): string
    {
        $v = $this->convertFromAnyTypeToString($property->getValue());
        return '$table->' . $property->getName()->value . " = $v;";
    }

    /**
     * Generates $table method with chains, example:
     *
     * $table->string('name', 100)->comment('Hello')->default('Test');
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
     */
    private function flattenMethod(Method $method): string
    {
        $v = (new Collection($method->getValues()))->map(fn ($v) => $this->convertFromAnyTypeToString($v))->implode(', ');
        return $method->getName()->value . "($v)";
    }
}
