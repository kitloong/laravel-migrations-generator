<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/29
 */

namespace KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\MigrationsGeneratorSetting;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\Types\DBALTypes;

class DecimalField
{
    // (8, 2) are default value of decimal, float
    private const DEFAULT_DECIMAL_PRECISION = 8;
    private const DEFAULT_DECIMAL_SCALE = 2;

    // DBAL return (10, 0) if double length is empty
    private const EMPTY_PRECISION = 10;
    private const EMPTY_SCALE = 0;

    private $decorator;

    public function __construct(Decorator $decorator)
    {
        $this->decorator = $decorator;
    }

    public function makeField(array $field, Column $column): array
    {
        /** @var MigrationsGeneratorSetting $setting */
        $setting = app(MigrationsGeneratorSetting::class);

        switch ($setting->getPlatform()) {
            case Platform::POSTGRESQL:
                if ($field['type'] === DBALTypes::DECIMAL) {
                    $args = $this->getDecimalPrecision($column->getPrecision(), $column->getScale());
                }
                break;
            default:
                if (in_array($field['type'], [DBALTypes::DECIMAL, DBALTypes::FLOAT])) {
                    $args = $this->getDecimalPrecision($column->getPrecision(), $column->getScale());
                } else {
                    // double
                    $args = $this->getDoublePrecision($column->getPrecision(), $column->getScale());
                }
        }

        if (!empty($args)) {
            $field['args'] = $args;
        }

        if ($column->getUnsigned()) {
            $field['decorators'][] = ColumnModifier::UNSIGNED;
        }
        return $field;
    }

    private function getDecimalPrecision(int $precision, int $scale): array
    {
        $return = [];
        if ($precision != self::DEFAULT_DECIMAL_PRECISION || $scale != self::DEFAULT_DECIMAL_SCALE) {
            $return[] = $precision;
            if ($scale != self::DEFAULT_DECIMAL_SCALE) {
                $return[] = $scale;
            }
        }
        return $return;
    }

    private function getDoublePrecision(int $precision, int $scale)
    {
        if ($precision === self::EMPTY_PRECISION && $scale === self::EMPTY_SCALE) {
            return [];
        } else {
            return [$precision, $scale];
        }
    }
}
