<?php

namespace KitLoong\MigrationsGenerator\Migration\Generator\Modifiers;

use Illuminate\Support\Facades\DB;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnModifier;
use KitLoong\MigrationsGenerator\Enum\Migrations\Method\ColumnType;
use KitLoong\MigrationsGenerator\Migration\Blueprint\Method;
use KitLoong\MigrationsGenerator\Schema\Models\Column;
use KitLoong\MigrationsGenerator\Schema\Models\Table;
use KitLoong\MigrationsGenerator\Support\CheckMigrationMethod;

class DefaultModifier implements Modifier
{
    use CheckMigrationMethod;

    private $chainerMap = [];

    public function __construct()
    {
        foreach (
            [
                ColumnType::BIG_INTEGER(),
                ColumnType::INTEGER(),
                ColumnType::MEDIUM_INTEGER(),
                ColumnType::SMALL_INTEGER(),
                ColumnType::TINY_INTEGER(),
                ColumnType::UNSIGNED_BIG_INTEGER(),
                ColumnType::UNSIGNED_INTEGER(),
                ColumnType::UNSIGNED_MEDIUM_INTEGER(),
                ColumnType::UNSIGNED_SMALL_INTEGER(),
                ColumnType::UNSIGNED_TINY_INTEGER(),
            ] as $columnType
        ) {
            $this->chainerMap[$columnType->getValue()] = function (Method $method, Column $column): Method {
                return call_user_func([$this, 'chainDefaultForInteger'], $method, $column);
            };
        }

        foreach (
            [
                ColumnType::DECIMAL(),
                ColumnType::UNSIGNED_DECIMAL(),
                ColumnType::FLOAT(),
                ColumnType::DOUBLE(),
            ] as $columnType
        ) {
            $this->chainerMap[$columnType->getValue()] = function (Method $method, Column $column): Method {
                return call_user_func([$this, 'chainDefaultForDecimal'], $method, $column);
            };
        }

        $this->chainerMap[ColumnType::BOOLEAN()->getValue()] = function (Method $method, Column $column): Method {
            return call_user_func([$this, 'chainDefaultForBoolean'], $method, $column);
        };

        foreach (
            [
                ColumnType::SOFT_DELETES(),
                ColumnType::SOFT_DELETES_TZ(),
                ColumnType::DATE(),
                ColumnType::DATETIME(),
                ColumnType::DATETIME_TZ(),
                ColumnType::TIME(),
                ColumnType::TIME_TZ(),
                ColumnType::TIMESTAMP(),
                ColumnType::TIMESTAMP_TZ(),
            ] as $columnType
        ) {
            $this->chainerMap[$columnType->getValue()] = function (Method $method, Column $column): Method {
                return call_user_func([$this, 'chainDefaultForDatetime'], $method, $column);
            };
        }
    }

    /**
     * @inheritDoc
     */
    public function chain(Method $method, Table $table, Column $column, ...$args): Method
    {
        if ($column->getDefault() === null) {
            return $method;
        }

        if (isset($this->chainerMap[$column->getType()->getValue()])) {
            return $this->chainerMap[$column->getType()->getValue()]($method, $column);
        }

        return $this->chainDefaultForString($method, $column);
    }

    /**
     * Set default value to method for integer column.
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Column  $column
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method
     */
    protected function chainDefaultForInteger(Method $method, Column $column): Method
    {
        $method->chain(ColumnModifier::DEFAULT(), (int) $column->getDefault());
        return $method;
    }

    /**
     * Set default value to method for decimal column.
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Column  $column
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method
     */
    protected function chainDefaultForDecimal(Method $method, Column $column): Method
    {
        $method->chain(ColumnModifier::DEFAULT(), (float) $column->getDefault());
        return $method;
    }

    /**
     * Set default value to method for boolean column.
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Column  $column
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method
     */
    protected function chainDefaultForBoolean(Method $method, Column $column): Method
    {
        $method->chain(ColumnModifier::DEFAULT(), (int) $column->getDefault() === 1);
        return $method;
    }

    /**
     * Set default value to method for datetime column.
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Column  $column
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method
     */
    protected function chainDefaultForDatetime(Method $method, Column $column): Method
    {
        switch ($column->getDefault()) {
            case 'now()':
            case 'CURRENT_TIMESTAMP':
                // By default, `timestamp()` and `timestampTz()` will generate column as:
                // `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, migration translated to `useCurrent()` and `useCurrentOnUpdate()`.
                // Due to old Laravel version does not have `useCurrentOnUpdate()`,
                // if column has both `DEFAULT CURRENT_TIMESTAMP` and `ON UPDATE CURRENT_TIMESTAMP`,
                // we need to generate column without chain.
                // New laravel is okay to chain `useCurrent()` and `useCurrentOnUpdate()`.
                if (!$this->hasUseCurrentOnUpdate()) {
                    if (!$column->isOnUpdateCurrentTimestamp()) {
                        $method->chain(ColumnModifier::USE_CURRENT());
                    }

                    break;
                }

                $method->chain(ColumnModifier::USE_CURRENT());
                break;

            default:
                $default = $column->getDefault();

                if ($column->isRawDefault()) {
                    // Set default with DB::raw(), which will return an instance of \Illuminate\Database\Query\Expression.
                    // Writer will check for Expression instance and generate as DB::raw().
                    $default = DB::raw($default);
                }

                $method->chain(ColumnModifier::DEFAULT(), $default);
        }

        return $method;
    }

    /**
     * Set default value to method, which support string.
     *
     * @param  \KitLoong\MigrationsGenerator\Migration\Blueprint\Method  $method
     * @param  \KitLoong\MigrationsGenerator\Schema\Models\Column  $column
     * @return \KitLoong\MigrationsGenerator\Migration\Blueprint\Method
     */
    protected function chainDefaultForString(Method $method, Column $column): Method
    {
        $quotes  = '\'';
        $default = $column->getDefault();
        // To replace from ' to \\\'
        $method->chain(ColumnModifier::DEFAULT(), str_replace($quotes, '\\\\' . $quotes, $default));

        return $method;
    }
}
