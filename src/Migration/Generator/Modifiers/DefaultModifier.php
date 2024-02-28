<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Modifiers;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;

class DefaultModifier implements Modifier
{
    /**
     * @var array<string, \Closure(\KitLoong\MigrationsGenerator\Migration\Blueprint\Method $method, \KitLoong\MigrationsGenerator\Schema\Models\Column $column): \KitLoong\MigrationsGenerator\Migration\Blueprint\Method>
     */
    private array $chainerMap = [];

    public function __construct()
    {
        foreach (
            [
                ColumnType::BIG_INTEGER,
                ColumnType::INTEGER,
                ColumnType::MEDIUM_INTEGER,
                ColumnType::SMALL_INTEGER,
                ColumnType::TINY_INTEGER,
                ColumnType::UNSIGNED_BIG_INTEGER,
                ColumnType::UNSIGNED_INTEGER,
                ColumnType::UNSIGNED_MEDIUM_INTEGER,
                ColumnType::UNSIGNED_SMALL_INTEGER,
                ColumnType::UNSIGNED_TINY_INTEGER,
            ] as $columnType
        ) {
            $this->chainerMap[$columnType->value] = fn (Method $method, Column $column): Method => call_user_func([$this, 'chainDefaultForInteger'], $method, $column);
        }

        foreach (
            [
                ColumnType::DECIMAL,
                ColumnType::FLOAT,
                ColumnType::DOUBLE,
            ] as $columnType
        ) {
            $this->chainerMap[$columnType->value] = fn (Method $method, Column $column): Method => call_user_func([$this, 'chainDefaultForDecimal'], $method, $column);
        }

        $this->chainerMap[ColumnType::BOOLEAN->value] = fn (Method $method, Column $column): Method => call_user_func([$this, 'chainDefaultForBoolean'], $method, $column);

        foreach (
            [
                ColumnType::SOFT_DELETES,
                ColumnType::SOFT_DELETES_TZ,
                ColumnType::DATE,
                ColumnType::DATETIME,
                ColumnType::DATETIME_TZ,
                ColumnType::TIME,
                ColumnType::TIME_TZ,
                ColumnType::TIMESTAMP,
                ColumnType::TIMESTAMP_TZ,
            ] as $columnType
        ) {
            $this->chainerMap[$columnType->value] = fn (Method $method, Column $column): Method => call_user_func([$this, 'chainDefaultForDatetime'], $method, $column);
        }
    }

    /**
     * @inheritDoc
     */
    public function chain(Method $method, Table $table, Column $column, mixed ...$args): Method
    {
        if ($column->getDefault() === null) {
            return $method;
        }

        if (isset($this->chainerMap[$column->getType()->value])) {
            return $this->chainerMap[$column->getType()->value]($method, $column);
        }

        return $this->chainDefaultForString($method, $column);
    }

    /**
     * Set default value to method for integer column.
     */
    protected function chainDefaultForInteger(Method $method, Column $column): Method
    {
        $method->chain(ColumnModifier::DEFAULT, (int) $column->getDefault());
        return $method;
    }

    /**
     * Set default value to method for decimal column.
     */
    protected function chainDefaultForDecimal(Method $method, Column $column): Method
    {
        $method->chain(ColumnModifier::DEFAULT, (float) $column->getDefault());
        return $method;
    }

    /**
     * Set default value to method for boolean column.
     */
    protected function chainDefaultForBoolean(Method $method, Column $column): Method
    {
        $default = match ($column->getDefault()) {
            'true', '1' => true,
            default => false,
        };

        $method->chain(ColumnModifier::DEFAULT, $default);
        return $method;
    }

    /**
     * Set default value to method for datetime column.
     */
    protected function chainDefaultForDatetime(Method $method, Column $column): Method
    {
        switch ($column->getDefault()) {
            case 'CURRENT_TIMESTAMP':
                $method->chain(ColumnModifier::USE_CURRENT);
                break;

            default:
                $default = $column->getDefault();

                if ($column->isRawDefault()) {
                    // Set default with DB::raw(), which will return an instance of \Illuminate\Database\Query\Expression.
                    // Writer will check for Expression instance and generate as DB::raw().
                    $default = DB::raw($default);
                }

                $method->chain(ColumnModifier::DEFAULT, $default);
        }

        return $method;
    }

    /**
     * Set default value to method, which support string.
     */
    protected function chainDefaultForString(Method $method, Column $column): Method
    {
        $method->chain(ColumnModifier::DEFAULT, $column->getDefault());

        return $method;
    }
}
